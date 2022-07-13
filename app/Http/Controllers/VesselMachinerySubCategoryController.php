<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportRequest;
use App\Imports\VesselMachinerySubCategoryImport;
use App\Services\VesselMachinerySubCategoryService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Validators\ValidationException;

class VesselMachinerySubCategoryController extends Controller
{
    /**
     * VesselMachinerySubCategoryController constructor
     */
    public function __construct()
    {
        parent::__construct();

        // enable api middleware
        $this->middleware(['auth:api']);
    }

    /**
     * Import vessel machinery sub category
     *
     * @param ImportRequest $request
     * @return JsonResponse
     * @throws
     */
    public function import(ImportRequest $request): JsonResponse
    {
        try {
            $import = new VesselMachinerySubCategoryImport();
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
