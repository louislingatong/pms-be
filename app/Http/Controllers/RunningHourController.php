<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRunningHourRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\SearchRunningHourRequest;
use App\Http\Resources\RunningHourResource;
use App\Imports\RunningHoursImport;
use App\Models\User;
use App\Services\RunningHourService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Validators\ValidationException;

class RunningHourController extends Controller
{
    /** @var RunningHourService */
    protected $runningHourService;

    /**
     * RunningHourController constructor
     *
     * @param RunningHourService $runningHourService
     */
    public function __construct(RunningHourService $runningHourService)
    {
        parent::__construct();

        $this->runningHourService = $runningHourService;

        // enable api middleware
        $this->middleware(['auth:api']);
    }

    /**
     * Retrieves the List of vessel machinery with running hours
     *
     * @param SearchRunningHourRequest $request
     * @return JsonResponse
     */
    public function index(SearchRunningHourRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $conditions = [
                'vessel' => $request->getVessel(),
                'department' => $request->getDepartment(),
                'keyword' => $request->getKeyword(),
                'page' => $request->getPage(),
                'limit' => $request->getLimit(),
            ];
            $results = $this->runningHourService->search($conditions);
            $this->response = array_merge($results, $this->response);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Creates a new running hour of the vessel machinery. Creator must be authenticated.
     *
     * @param CreateRunningHourRequest $request
     * @return JsonResponse
     */
    public function create(CreateRunningHourRequest $request): JsonResponse
    {
        $request->validated();

        /** @var User $creator */
        $creator = $request->user();

        try {
            $formData = [
                'vessel_machinery_id' => $request->getVesselMachineryId(),
                'running_hours' => $request->getRunningHours(),
                'updating_date' => Carbon::create($request->getUpdatingDate()),
                'creator_id' => $creator->getAttribute('id'),
            ];
            $runningHour = $this->runningHourService->create($formData);
            $this->response['data'] = new RunningHourResource($runningHour);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Import vessel machinery running hours
     *
     * @param ImportRequest $request
     * @return JsonResponse
     * @throws
     */
    public function import(ImportRequest $request): JsonResponse
    {
        try {
            $import = new RunningHoursImport();
            $import->import($request->getFile());
        } catch (ValidationException $e) {
            if (!empty($e->failures())) {
                $this->response = [
                    'error' => $e->failures(),
                    'code' => 422,
                ];
            }

            if (!empty($e->errors())) {
                $this->response = [
                    'error' => $e->errors(),
                    'code' => 500,
                ];
            }
        }

        return response()->json($this->response, $this->response['code']);
    }
}
