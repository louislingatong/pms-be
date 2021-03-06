<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMachineryRequest;
use App\Http\Requests\CreateSubCategoryRequest;
use App\Http\Requests\DeleteMachineriesRequest;
use App\Http\Requests\DeleteSubCategoriesRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\SearchMachineryRequest;
use App\Http\Requests\UpdateMachineryRequest;
use App\Http\Resources\MachineryWithSubCategoriesResource;
use App\Imports\MachineryImport;
use App\Models\Machinery;
use App\Services\MachineryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Validators\ValidationException;

class MachineryController extends Controller
{
    /** @var MachineryService */
    protected $machineryService;

    /**
     * MachineryController constructor
     *
     * @param MachineryService $machineryService
     */
    public function __construct(MachineryService $machineryService)
    {
        parent::__construct();

        $this->machineryService = $machineryService;

        // enable api middleware
        $this->middleware(['auth:api']);
    }

    /**
     * Retrieves the List of machinery
     *
     * @param SearchMachineryRequest $request
     * @return JsonResponse
     */
    public function index(SearchMachineryRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $conditions = [
                'department' => $request->getDepartment(),
                'keyword' => $request->getKeyword(),
                'page' => $request->getPage(),
                'limit' => $request->getLimit(),
            ];
            $results = $this->machineryService->search($conditions);
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
     * Creates a new machinery. Creator must be authenticated.
     *
     * @param CreateMachineryRequest $request
     * @return JsonResponse
     */
    public function create(CreateMachineryRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'vessel_department' => $request->getDepartment(),
                'code_name' => $request->getCodeName(),
                'name' => $request->getName(),
            ];
            $machinery = $this->machineryService->create($formData);
            $this->response['data'] = new MachineryWithSubCategoriesResource($machinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Retrieves machinery information
     *
     * @param Machinery $machinery
     * @return JsonResponse
     */
    public function read(Machinery $machinery): JsonResponse
    {
        try {
            $this->response['data'] = new MachineryWithSubCategoriesResource($machinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Updates machinery information
     *
     * @param UpdateMachineryRequest $request
     * @param Machinery $machinery
     * @return JsonResponse
     */
    public function update(UpdateMachineryRequest $request, Machinery $machinery): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'vessel_department' => $request->getDepartment(),
                'code_name' => $request->getCodeName(),
                'name' => $request->getName(),
            ];
            $machinery = $this->machineryService->update($formData, $machinery);
            $this->response['data'] = new MachineryWithSubCategoriesResource($machinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Delete machinery/s
     *
     * @param DeleteMachineriesRequest $request
     * @return JsonResponse
     */
    public function delete(DeleteMachineriesRequest $request): JsonResponse
    {
        try {
            $formData = [
                'machinery_ids' => $request->getMachineryIds(),
            ];
            $this->response['delete'] = $this->machineryService->delete($formData);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * Import machinery
     *
     * @param ImportRequest $request
     * @return JsonResponse
     * @throws
     */
    public function import(ImportRequest $request): JsonResponse
    {
        try {
            $import = new MachineryImport;
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
     * @param CreateSubCategoryRequest $request
     * @param Machinery $machinery
     * @return JsonResponse
     */
    public function createSubCategory(CreateSubCategoryRequest $request, Machinery $machinery): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'code' => $request->getCode(),
                'name' => $request->getName(),
            ];
            $machinery = $this->machineryService->addSubCategory($formData, $machinery);
            $this->response['data'] = new MachineryWithSubCategoriesResource($machinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }

    /**
     * @param DeleteSubCategoriesRequest $request
     * @param Machinery $machinery
     * @return JsonResponse
     */
    public function deleteSubCategory(DeleteSubCategoriesRequest $request, Machinery $machinery): JsonResponse
    {
        $request->validated();

        try {
            $formData = [
                'sub_category_ids' => $request->getSubCategoryIds(),
            ];
            $machinery = $this->machineryService->removeSubCategory($formData, $machinery);
            $this->response['data'] = new MachineryWithSubCategoriesResource($machinery);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }
}
