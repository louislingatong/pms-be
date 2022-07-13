<?php

namespace App\Services;

use App\Http\Resources\MachineryWithSubCategoriesResource;
use App\Models\Machinery;
use App\Models\MachinerySubCategory;
use App\Models\VesselDepartment;
use Exception;
use Illuminate\Support\Facades\DB;

class MachineryService
{
    /** @var Machinery $machinery */
    protected $machinery;

    /**
     * MachineryService constructor.
     *
     * @param Machinery $machinery
     */
    public function __construct(Machinery $machinery)
    {
        $this->machinery = $machinery;
    }

    /**
     * List of machinery by conditions
     *
     * @param array $conditions
     * @return array
     * @throws
     */
    public function search(array $conditions): array
    {
        $page = 1;
        $limit = config('search.results_per_page');

        if ($conditions['page']) {
            $page = $conditions['page'];
        }

        if ($conditions['limit']) {
            $limit = $conditions['limit'];
        }

        $skip = ($page > 1) ? ($page * $limit - $limit) : 0;

        $query = $this->machinery;

        if ($conditions['department']) {
            $query = $query->whereHas('department', function ($q) use ($conditions) {
                $q->where('name', '=', $conditions['department']);
            });
        }

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        $results = $query->skip($skip)
            ->orderBy('id')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, MachineryWithSubCategoriesResource::class, $page, $urlParams);
    }

    /**
     * Creates a new machinery in the database
     *
     * @param array $params
     * @return Machinery
     * @throws
     */
    public function create(array $params): Machinery
    {
        DB::beginTransaction();

        try {
            /** @var VesselDepartment $department */
            $department = VesselDepartment::whereName($params['vessel_department'])->first();
            $machinery = $this->machinery->create([
                'vessel_department_id' => $department->getAttribute('id'),
                'code_name' => $params['code_name'],
                'name' => $params['name'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $machinery;
    }

    /**
     * Updates machinery in the database
     *
     * @param array $params
     * @param Machinery $machinery
     * @return Machinery
     * @throws
     */
    public function update(array $params, Machinery $machinery): Machinery
    {
        DB::beginTransaction();

        try {
            /** @var VesselDepartment $department */
            $department = VesselDepartment::whereName($params['vessel_department'])->first();
            $machinery->update([
                'vessel_department_id' => $department->getAttribute('id'),
                'code_name' => $params['code_name'],
                'name' => $params['name'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $machinery;
    }

    /**
     * Deletes the machinery/s in the database
     *
     * @param array $params
     * @return bool
     * @throws
     */
    public function delete(array $params): bool
    {

        DB::beginTransaction();

        try {
            $this->machinery->whereIn('id', $params['machinery_ids'])->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return true;
    }

    /**
     * Add new sub category
     *
     * @param array $params
     * @param Machinery $machinery
     * @return Machinery
     * @throws
     */
    public function addSubCategory(array $params, Machinery $machinery): Machinery
    {
        $machinery->subCategories()->save(new MachinerySubCategory($params));
        return $machinery;
    }

    /**
     * Remove new sub category
     *
     * @param array $params
     * @param Machinery $machinery
     * @return Machinery
     * @throws
     */
    public function removeSubCategory(array $params, Machinery $machinery): Machinery
    {
        $machinery->subCategories()->whereIn('id', $params['sub_category_ids'])->delete();
        return $machinery;
    }
}
