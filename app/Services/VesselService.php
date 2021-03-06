<?php

namespace App\Services;

use App\Http\Resources\VesselResource;
use App\Models\Employee;
use App\Models\Vessel;
use App\Models\VesselOwner;
use Exception;
use Illuminate\Support\Facades\DB;

class VesselService
{
    /** @var Vessel $vessel */
    protected $vessel;

    /**
     * VesselService constructor.
     *
     * @param Vessel $vessel
     */
    public function __construct(Vessel $vessel)
    {
        $this->vessel = $vessel;
    }

    /**
     * List of vessel by conditions
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

        $user = auth()->user();

        $employee = $user->employee;

        if ($employee instanceof Employee) {
            $query = $user->employee->vessels();
        } else {
            $query = $this->vessel;
        }

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        $results = $query->skip($skip)
            ->orderBy('id', 'ASC')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, VesselResource::class, $page, $urlParams);
    }

    /**
     * Creates a new vessel in the database
     *
     * @param array $params
     * @return Vessel
     * @throws
     */
    public function create(array $params): Vessel
    {
        DB::beginTransaction();

        try {
            $vesselOwner = $this->findOrCreateVesselOwnerByName($params['owner']);
            $params['vessel_owner_id'] = $vesselOwner->getAttribute('id');
            $vessel = $this->vessel->create($params);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $vessel;
    }

    /**
     * Retrieve/Create the machinery maker
     *
     * @param string $name
     * @return VesselOwner
     * @throws
     */
    public function findOrCreateVesselOwnerByName(string $name): VesselOwner
    {
        return VesselOwner::firstOrCreate(['name' => $name]);
    }

    /**
     * Updates vessel in the database
     *
     * @param array $params
     * @param Vessel $vessel
     * @return Vessel
     * @throws
     */
    public function update(array $params, Vessel $vessel): Vessel
    {
        $vesselOwner = $this->findOrCreateVesselOwnerByName($params['owner']);
        $params['vessel_owner_id'] = $vesselOwner->getAttribute('id');
        $vessel->update($params);
        return $vessel;
    }

    /**
     * Deletes the vessel/s in the database
     *
     * @param array $params
     * @return bool
     * @throws
     */
    public function delete(array $params): bool
    {

        DB::beginTransaction();

        try {
            $this->vessel->whereIn('id', $params['vessel_ids'])->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return true;
    }
}
