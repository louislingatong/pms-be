<?php

namespace App\Services;

use App\Exceptions\IntervalNotFoundException;
use App\Exceptions\IntervalUnitNotFoundException;
use App\Http\Resources\IntervalResource;
use App\Models\Interval;
use App\Models\IntervalUnit;
use Exception;
use Illuminate\Support\Facades\DB;

class IntervalService
{
    /** @var Interval $interval */
    protected $interval;


    /**
     * IntervalService constructor.
     *
     * @param Interval $interval
     */
    public function __construct(Interval $interval)
    {
        $this->interval = $interval;
    }

    /**
     * List of interval by conditions
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

        $query = $this->interval;

        if ($conditions['keyword']) {
            $query = $query->search($conditions['keyword']);
        }

        $results = $query->skip($skip)
            ->orderBy('id')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, IntervalResource::class, $page, $urlParams);
    }

    /**
     * Creates a new interval in the database
     *
     * @param array $params
     * @return Interval
     * @throws
     */
    public function create(array $params): Interval
    {
        DB::beginTransaction();

        try {
            $newInterval = array();
            if ($params['value'] && $params['interval_unit']) {
                /** @var IntervalUnit $intervalUnit */
                $intervalUnit = IntervalUnit::whereName($params['interval_unit'])->first();

                if (!($intervalUnit instanceof IntervalUnit)) {
                    throw new IntervalUnitNotFoundException();
                }

                $newInterval['value'] = $params['value'];
                $newInterval['interval_unit_id'] = $intervalUnit->getAttribute('id');
                $newInterval['name'] = $params['name'] ?: $params['value'] . ' ' . $intervalUnit->getAttribute('name');
            } else {
                $newInterval['name'] = $params['name'];
            }

            $interval = $this->interval->create($newInterval);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $interval;
    }

    /**
     * Updates interval in the database
     *
     * @param array $params
     * @param Interval $interval
     * @return Interval
     * @throws
     */
    public function update(array $params, Interval $interval): Interval
    {
        /** @var IntervalUnit $intervalUnit */
        $intervalUnit = IntervalUnit::whereName($params['interval_unit'])->first();

        if (!($intervalUnit instanceof IntervalUnit)) {
            throw new IntervalUnitNotFoundException();
        }

        $interval->update([
            'value' => $params['value'],
            'interval_unit_id' => $intervalUnit->getAttribute('id'),
            'name' => $params['name'] ?: $params['value'] . ' ' . $intervalUnit->getAttribute('name'),
        ]);
        return $interval;
    }

    /**
     * Deletes the interval in the database
     *
     * @param Interval $interval
     * @return bool
     * @throws
     */
    public function delete(Interval $interval): bool
    {
        if (!($interval instanceof Interval)) {
            throw new IntervalNotFoundException();
        }
        $interval->delete();
        return true;
    }
}
