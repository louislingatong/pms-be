<?php

namespace App\Http\Controllers;

use App\Exports\AllWorksExport;
use App\Exports\WorkHistoryExport;
use App\Http\Requests\CountWorksRequest;
use App\Http\Requests\CreateWorkRequest;
use App\Http\Requests\DownloadFileRequest;
use App\Http\Requests\ExportWorkRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\SearchWorkRequest;
use App\Http\Resources\VesselMachinerySubCategoryWorkResource;
use App\Imports\WorksImport;
use App\Models\User;
use App\Models\VesselMachinerySubCategory;
use App\Services\WorkService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkController extends Controller
{
    /** @var WorkService */
    protected $workService;

    /**
     * WorkController constructor
     *
     * @param WorkService $workService
     */
    public function __construct(WorkService $workService)
    {
        parent::__construct();

        $this->workService = $workService;

        // enable api middleware
        $this->middleware(['auth:api']);
    }

    /**
     * Retrieves the List of vessel machinery with work
     *
     * @param SearchWorkRequest $request
     * @return JsonResponse
     */
    public function index(SearchWorkRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $conditions = [
                'vessel' => $request->getVessel(),
                'department' => $request->getDepartment(),
                'machinery' => $request->getMachinery(),
                'status' => $request->getStatus(),
                'keyword' => $request->getKeyword(),
                'page' => $request->getPage(),
                'limit' => $request->getLimit(),
            ];
            $results = $this->workService->search($conditions);
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
     * Creates a new work of the vessel sub category. Creator must be authenticated.
     *
     * @param CreateWorkRequest $request
     * @return JsonResponse
     */
    public function create(CreateWorkRequest $request): JsonResponse
    {
        $request->validated();

        /** @var User $creator */
        $creator = $request->user();

        try {
            $formData = [
                'vessel_machinery_sub_category_Ids' => $request->getVesselMachinerySubCategoryIds(),
                'last_done' => Carbon::create($request->getLastDone()),
                'running_hours' => $request->getRunningHours(),
                'instructions' => $request->getInstructions(),
                'remarks' => $request->getRemarks(),
                'creator_id' => $creator->getAttribute('id'),
                'file' => $request->getFile(),
            ];
            $works = $this->workService->create($formData);
            $this->response['data'] = VesselMachinerySubCategoryWorkResource::collection($works);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Import works
     *
     * @param ImportRequest $request
     * @return JsonResponse
     * @throws
     */
    public function import(ImportRequest $request): JsonResponse
    {
        try {
            $import = new WorksImport();
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

    /**
     * Export work
     *
     * @param ExportWorkRequest $request
     * @return BinaryFileResponse
     */
    public function export(ExportWorkRequest $request): BinaryFileResponse
    {
        $request->validated();

        $conditions = [
            'vessel' => $request->getVessel(),
            'department' => $request->getDepartment(),
            'machinery' => $request->getMachinery(),
            'status' => $request->getStatus(),
            'keyword' => $request->getKeyword(),
        ];

        $results = $this->workService->export($conditions);

        return Excel::download(new AllWorksExport($results, $request->getVessel()), 'Works.xls');
    }

    /**
     * Export work history
     *
     * @param VesselMachinerySubCategory $vesselMachinerySubCategory
     * @return BinaryFileResponse
     */
    public function exportWorkHistory(VesselMachinerySubCategory $vesselMachinerySubCategory): BinaryFileResponse
    {
        return Excel::download(new WorkHistoryExport($vesselMachinerySubCategory), 'Work History.xls');
    }

    /**
     * Download work history
     *
     * @param DownloadFileRequest $request
     * @return BinaryFileResponse
     */
    public function downloadFile(DownloadFileRequest $request)
    {
        return Storage::disk('public')->download($request->getPath());
    }

    /**
     * Works count by status
     *
     * @param CountWorksRequest $request
     * @return JsonResponse
     */
    public function workCount(CountWorksRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $vessel = $request->getVessel();
            $workCounts = $this->workService->countWorkAllStatus($vessel);
            $this->response['data'] = $workCounts;
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }
}
