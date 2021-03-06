<?php

namespace App\Services;

use App\Http\Resources\VesselMachineryRunningHourResource;
use App\Models\RunningHour;
use App\Models\VesselMachinery;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RunningHourService
{
    /** @var VesselMachinery $vesselMachinery */
    protected $vesselMachinery;

    /** @var RunningHour $runningHour */
    protected $runningHour;

    /**
     * RunningHourService constructor.
     *
     * @param VesselMachinery $vesselMachinery
     * @param RunningHour $runningHour
     */
    public function __construct(
        VesselMachinery $vesselMachinery,
        RunningHour $runningHour
    )
    {
        $this->vesselMachinery = $vesselMachinery;
        $this->runningHour = $runningHour;
    }

    /**
     * List of vessel machinery with running hour by conditions
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

        $query = $this->vesselMachinery->whereHas('vessel', function ($q) use ($conditions) {
            $q->where('name', $conditions['vessel']);
        });

        if ($conditions['department']) {
            $query = $query->whereHas('machinery.department', function ($q) use ($conditions) {
                $q->where('name', $conditions['department']);
            });
        }

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        $user = auth()->user();

        if (!$user->hasRole(config('user.roles.admin'))) {
            $query = $query->whereHas('inchargeRank', function ($q) use ($user) {
                $q->where('name', $user->employee->position);
            });
        }

        $results = $query->skip($skip)
            ->orderBy('machinery_id')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, VesselMachineryRunningHourResource::class, $page, $urlParams);
    }

    /**
     * Creates a new running hour of vessel machinery in the database
     *
     * @param array $params
     * @return RunningHour
     * @throws
     */
    public function create(array $params): RunningHour
    {
        DB::beginTransaction();

        try {
            $runningHour = $this->runningHour->create($params);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $runningHour;
    }

    /**
     * List of vessel machinery with running hour by conditions
     *
     * @param array $conditions
     * @return Collection
     * @throws
     */
    public function export(array $conditions): Collection
    {
        $query = $this->vesselMachinery->whereHas('vessel', function ($q) use ($conditions) {
            $q->where('name', '=', $conditions['vessel']);
        });

        if ($conditions['department']) {
            $query = $query->whereHas('machinery.department', function ($q) use ($conditions) {
                $q->where('name', '=', $conditions['department']);
            });
        }

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        return $query->with('machinery', 'currentRunningHour')->get();
    }
}
