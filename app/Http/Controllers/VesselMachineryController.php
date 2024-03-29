<?php

namespace App\Http\Controllers;

use App\Exports\AllVesselMachineriesExport;
use App\Exports\VesselMachineryExport;
use App\Http\Requests\CopyVesselMachineryRequest;
use App\Http\Requests\CreateVesselMachineryRequest;
use App\Http\Requests\DeleteVesselMachineriesRequest;
use App\Http\Requests\EditVesselMachinerySubCategoryRequest;
use App\Http\Requests\ExportVesselMachineryRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\SearchVesselMachineryRequest;
use App\Http\Requests\UpdateVesselMachineryRequest;
use App\Http\Resources\VesselMachineryWithSubCategoriesResource;
use App\Imports\VesselMachineryImport;
use App\Models\VesselMachinery;
use App\Services\VesselMachineryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VesselMachineryController extends Controller
{
    /** @var VesselMachineryService */
    protected $vesselMachineryService;

    /**
     * VesselMachineryController constructor
     *
     * @param VesselMachineryService $vesselMachineryService
     */
    public function __construct(VesselMachineryService $vesselMachineryService)
    {
        parent::__construct();

        $this->vesselMachineryService = $vesselMachineryService;

        // enable api middleware
        $this->middleware(['auth:api']);
    }

    /**
     * Retrieves the List of vessel machinery
     *
     * @param SearchVesselMachineryRequest $request
     * @return JsonResponse
     */
    public function index(SearchVesselMachineryRequest $request): JsonResponse
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
            $results = $this->vesselMachineryService->search($conditions);
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
     * Creates a new vessel machinery. Creator must be authenticated.
     *
     * @param CreateVesselMachineryRequest $request
     * @return JsonResponse
     */
    public function create(CreateVesselMachineryRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'vessel' => $request->getVessel(),
                'machinery' => $request->getMachinery(),
                'incharge_rank' => $request->getInchargeRank(),
                'model' => $request->getModel(),
                'maker' => $request->getMaker(),
            ];
            $vesselMachinery = $this->vesselMachineryService->create($formData);
            $this->response['data'] = new VesselMachineryWithSubCategoriesResource($vesselMachinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Retrieves vessel machinery information
     *
     * @param VesselMachinery $vesselMachinery
     * @return JsonResponse
     */
    public function read(VesselMachinery $vesselMachinery): JsonResponse
    {
        try {
            $this->response['data'] = new VesselMachineryWithSubCategoriesResource($vesselMachinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Updates vessel machinery information
     *
     * @param UpdateVesselMachineryRequest $request
     * @param VesselMachinery $vesselMachinery
     * @return JsonResponse
     */
    public function update(UpdateVesselMachineryRequest $request, VesselMachinery $vesselMachinery): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'vessel' => $request->getVessel(),
                'machinery' => $request->getMachinery(),
                'incharge_rank' => $request->getInchargeRank(),
                'model' => $request->getModel(),
                'maker' => $request->getMaker(),
            ];
            $vesselMachinery = $this->vesselMachineryService->update($formData, $vesselMachinery);
            $this->response['data'] = new VesselMachineryWithSubCategoriesResource($vesselMachinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Delete vessel machinery/s
     *
     * @param DeleteVesselMachineriesRequest $request
     * @return JsonResponse
     */
    public function delete(DeleteVesselMachineriesRequest $request): JsonResponse
    {
        try {
            $formData = [
                'vessel_machinery_ids' => $request->getVesselMachineryIds(),
            ];
            $this->response['delete'] = $this->vesselMachineryService->delete($formData);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * @param EditVesselMachinerySubCategoryRequest $request
     * @param VesselMachinery $vesselMachinery
     * @return JsonResponse
     */
    public function editMachinerySubCategories(
        EditVesselMachinerySubCategoryRequest $request,
        VesselMachinery $vesselMachinery
    ): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'vessel_machinery_sub_categories' => $request->getVesselMachinerySubCategories(),
            ];
            $vesselMachinery = $this->vesselMachineryService->editMachinerySubCategories($formData, $vesselMachinery);
            $this->response['data'] = new VesselMachineryWithSubCategoriesResource($vesselMachinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Import vessel machinery
     *
     * @param ImportRequest $request
     * @return JsonResponse
     * @throws
     */
    public function import(ImportRequest $request): JsonResponse
    {
        try {
            $import = new VesselMachineryImport();
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
     * @param ExportVesselMachineryRequest $request
     * @return BinaryFileResponse
     */
    public function export(ExportVesselMachineryRequest $request): BinaryFileResponse
    {
        $request->validated();

        $conditions = [
            'vessel' => $request->getVessel(),
            'department' => $request->getDepartment(),
            'keyword' => $request->getKeyword(),
        ];

        $results = $this->vesselMachineryService->export($conditions);

        return Excel::download(new AllVesselMachineriesExport($results), 'All Vessel Machinery.xls');
    }

    /**
     * Export vessel machinery
     *
     * @param VesselMachinery $vesselMachinery
     * @return BinaryFileResponse
     */
    public function exportVesselMachinery(VesselMachinery $vesselMachinery): BinaryFileResponse
    {
        return Excel::download(new VesselMachineryExport($vesselMachinery), 'Vessel Machinery.xls');
    }

    /**
     * Copy Vessel Machinery
     *
     * @param CopyVesselMachineryRequest $request
     * @return JsonResponse
     */
    public function copyAllMachinery(CopyVesselMachineryRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'vesselFrom' => $request->getVesselFrom(),
                'vesselTo' => $request->getVesselTo(),
            ];
            $vesselMachinery = $this->vesselMachineryService->copyAllMachinery($formData);
            $this->response['data'] = true;
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }
}
