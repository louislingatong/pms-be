<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchPermissionRequest;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /** @var PermissionService */
    protected $permissionService;

    /**
     * PermissionController constructor
     *
     * @param PermissionService $permissionService
     */
    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();

        $this->permissionService = $permissionService;

        // enable api middleware
        $this->middleware(['auth:api']);
    }

    /**
     * Retrieves the List of permissions
     *
     * @param SearchPermissionRequest $request
     * @return JsonResponse
     */
    public function index(SearchPermissionRequest $request): JsonResponse
    {
        $request->validated();

        try {
            $conditions = [
                'keyword' => $request->getKeyword(),
                'page' => $request->getPage(),
                'limit' => $request->getLimit(),
            ];
            $results = $this->permissionService->search($conditions);
            $this->response = array_merge($results, $this->response);
        } catch (Exception $e) {
            $this->response = [
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }

        return response()->json($this->response, $this->response['code']);
    }
}
