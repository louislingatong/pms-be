<?php

namespace App\Services;

use App\Exceptions\RunningHourNotFoundException;
use App\Http\Resources\VesselMachinerySubCategoryWorkResource;
use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\RunningHour;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use App\Models\Work;
use App\Models\WorkFile;
use App\Traits\Uploadable;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkService
{
    use Uploadable;

    /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
    protected $vesselMachinerySubCategory;

    /** @var Work $work */
    protected $work;

    /**
     * WorkService constructor.
     *
     * @param VesselMachinerySubCategory $vesselMachinerySubCategory
     * @param Work $work
     */
    public function __construct(
        VesselMachinerySubCategory $vesselMachinerySubCategory,
        Work $work
    )
    {
        $this->vesselMachinerySubCategory = $vesselMachinerySubCategory;
        $this->work = $work;
    }

    /**
     * List of vessel sub category with job by conditions
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

        $query = $this->vesselMachinerySubCategory->whereHas('vesselMachinery.vessel', function ($q) use ($conditions) {
            $q->where('name', '=', $conditions['vessel']);
        });

        if ($conditions['department']) {
            $query = $query->whereHas('vesselMachinery.machinery.department', function ($q) use ($conditions) {
                $q->where('name', $conditions['department']);
            });
        }

        if ($conditions['machinery']) {
            $query = $query->whereHas('vesselMachinery.machinery', function ($q) use ($conditions) {
                $q->where('name', $conditions['machinery']);
            });
        }

        if ($conditions['status']) {
            $query = $query->searchByStatus($conditions['status']);
        }

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        $user = auth()->user();

        if (!$user->hasRole(config('user.roles.admin'))) {
            $query = $query->whereHas('vesselMachinery.inchargeRank', function ($q) use ($user) {
                $q->where('name', $user->employee->position);
            });
        }

        $results = $query->skip($skip)
            ->orderBy('id', 'ASC')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, VesselMachinerySubCategoryWorkResource::class, $page, $urlParams);
    }

    /**
     * Creates a new job of vessel sub category in the database
     *
     * @param array $params
     * @return Collection
     * @throws
     */
    public function create(array $params): Collection
    {
        DB::beginTransaction();

        $updatedVesselMachinerySubCategory = collect([]);

        try {
            if (isset($params['file'])) {
                $fileUrl = $this->uploadOne($params['file'], 'files');
                $workFile = new WorkFile();
                $workFile->setAttribute('path', $fileUrl);
                $workFile->setAttribute('filename', $params['file']->getClientOriginalName());
            }

            foreach ($params['vessel_machinery_sub_category_Ids'] as $id) {
                /** @var Work $work */
                $work = $this->work->create([
                    'vessel_machinery_sub_category_id' => $id,
                    'last_done' => $params['last_done'],
                    'running_hours' => $params['running_hours'],
                    'instructions' => $params['instructions'],
                    'remarks' => $params['remarks'],
                    'creator_id' => $params['creator_id'],
                ]);

                if (isset($workFile)) {
                    $work->file()->save($workFile);
                }

                /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
                $vesselMachinerySubCategory = $work->vesselMachinerySubCategory;

                /** @var VesselMachinery $vesselMachinery */
                $vesselMachinery = $vesselMachinerySubCategory->vesselMachinery;

                /** @var Interval $interval */
                $interval = $vesselMachinerySubCategory->interval;

                if ($interval instanceof Interval) {
                    /** @var IntervalUnit $intervalUnit */
                    $intervalUnit = $interval->unit;

                    if ($intervalUnit instanceof IntervalUnit) {
                        $dueDate = null;

                        $isHours = $intervalUnit->getAttribute('name') === config('interval.units.hours');

                        if ($isHours) {
                            /** @var RunningHour $runningHour */
                            $runningHour = $vesselMachinery->currentRunningHour;

                            if (!($runningHour instanceof RunningHour)) {
                                throw new RunningHourNotFoundException('Unable to retrieve running hour of code ' . $vesselMachinerySubCategory->getAttribute('code'));
                            }

                            if ($runningHour->getAttribute('updating_date')
                                && $runningHour->getAttribute('running_hours')) {
                                $updatingDate = Carbon::create($runningHour->getAttribute('updating_date'));
                                $remainingIntervals = $runningHour->getAttribute('running_hours') - $work->getAttribute('running_hours');
                                $remainingIntervals = $interval->getAttribute('value') - $remainingIntervals;

                                $dueDate = $this->getDueDate($updatingDate, $intervalUnit->getAttribute('name'), $remainingIntervals);
                            }
                        } else {
                            if ($work->getAttribute('last_done')) {
                                $lastDoneDate = Carbon::create($work->getAttribute('last_done'));

                                $dueDate = $this->getDueDate(
                                    $lastDoneDate,
                                    $intervalUnit->getAttribute('name'),
                                    $interval->getAttribute('value')
                                );
                            }
                        }

                        if (isset($dueDate)) {
                            $vesselMachinerySubCategory->update(['due_date' => $dueDate]);
                        }
                    }
                }

                $updatedVesselMachinerySubCategory->push($vesselMachinerySubCategory);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $updatedVesselMachinerySubCategory;
    }

    /**
     * List of vessel sub category with job by conditions
     *
     * @param array $conditions
     * @return Collection
     * @throws
     */
    public function export(array $conditions): Collection
    {
        $query = $this->vesselMachinerySubCategory->whereHas('vesselMachinery.vessel', function ($q) use ($conditions) {
            $q->where('name', '=', $conditions['vessel']);
        });

        if ($conditions['department']) {
            $query = $query->whereHas('vesselMachinery.machinery.department', function ($q) use ($conditions) {
                $q->where('name', '=', $conditions['department']);
            });
        }

        if ($conditions['machinery']) {
            $query = $query->whereHas('vesselMachinery.machinery', function ($q) use ($conditions) {
                $q->where('name', '=', $conditions['machinery']);
            });
        }

        if ($conditions['status']) {
            $query = $query->searchByStatus($conditions['status']);
        }

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        return $query->with('interval', 'vesselMachinery', 'subCategory', 'description', 'currentWork')->get();
    }

    /**
     * Get the job due date
     *
     * @param Carbon $date
     * @param string $intervalUnit
     * @param string $intervalValue
     * @return Carbon
     */
    public function getDueDate(Carbon $date, string $intervalUnit, ?float $intervalValue = 0): ?Carbon
    {
        switch ($intervalUnit) {
            case config('interval.units.days'):
                $date->addDays($intervalValue);
                if ($intervalValue > 1) {
                    $date->subDay();
                }
                break;
            case config('interval.units.hours'):
                $date->addHours($intervalValue);
                break;
            case config('interval.units.weeks'):
                $date->addWeeks($intervalValue);
                break;
            case config('interval.units.months'):
                $date->addMonths($intervalValue);
                $date->subDay();
                break;
            case config('interval.units.years'):
                $years = (int)$intervalValue;
                $date->addYears($years);
                $additionalMonths = 12 * ($intervalValue - $years);
                if ($additionalMonths) {
                    $date->addMonths($additionalMonths);
                }
                break;
        }

        return $date;
    }

    /**
     * Work counts of all status by vessel
     *
     * @param string $vessel
     * @return array
     */
    public function countWorkAllStatus(string $vessel): array
    {
        $count = [];

        $count['warning'] = $this->countWorkByStatus($vessel, config('work.statuses.warning'));
        $count['due'] = $this->countWorkByStatus($vessel, config('work.statuses.due'));
        $count['overdue'] = $this->countWorkByStatus($vessel, config('work.statuses.overdue'));
        $count['jobs_done'] = $this->countWorkByStatus($vessel, config('work.statuses.jobs_done'));
        $count['dry_dock'] = $this->countWorkByStatus($vessel, config('work.statuses.dry_dock'));

        return $count;
    }

    /**
     * Work counts by vessel and status
     *
     * @param string $vessel
     * @param string $status
     * @return int
     */
    private function countWorkByStatus(string $vessel, string $status): int
    {
        $query = VesselMachinerySubCategory::searchByStatus($status)
            ->whereHas('vesselMachinery.vessel', function ($q) use ($vessel) {
                $q->where('name', '=', $vessel);
            });

        $user = auth()->user();

        if (!$user->hasRole(config('user.roles.admin'))) {
            $query = $query->whereHas('vesselMachinery.inchargeRank', function ($q) use ($user) {
                $q->where('name', $user->employee->position);
            });
        }

        return $query->count();
    }
}
