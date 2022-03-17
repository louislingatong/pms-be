<?php

namespace App\Services;

use App\Exceptions\VesselMachineryNotFoundException;
use App\Http\Resources\VesselMachineryResource;
use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\Machinery;
use App\Models\MachineryMaker;
use App\Models\MachineryModel;
use App\Models\MachinerySubCategory;
use App\Models\Rank;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VesselMachineryService
{
    /** @var VesselMachinery $vesselMachinery */
    protected $vesselMachinery;

    /**
     * VesselMachineryService constructor.
     *
     * @param VesselMachinery $vesselMachinery
     */
    public function __construct(VesselMachinery $vesselMachinery)
    {
        $this->vesselMachinery = $vesselMachinery;
    }

    /**
     * List of vessel machinery by conditions
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

        $results = $query->skip($skip)
            ->orderBy('id', 'ASC')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, VesselMachineryResource::class, $page, $urlParams);
    }

    /**
     * Creates a new vessel machinery in the database
     *
     * @param array $params
     * @return VesselMachinery
     * @throws
     */
    public function create(array $params): VesselMachinery
    {
        DB::beginTransaction();

        try {
            /** @var Vessel $vessel */
            $vessel = Vessel::whereName($params['vessel'])->first();
            /** @var Machinery $machinery */
            $machinery = Machinery::whereName($params['machinery'])->first();
            /** @var Rank $inchargeRank */
            $inchargeRank = Rank::whereName($params['incharge_rank'])->first();
            if (isset($params['model'])) {
                $machineryModel = $this->findOrCreateModelByName($params['model']);
                $params['machinery_model_id'] = $machineryModel->getAttribute('id');
            }
            if (isset($params['maker'])) {
                $machineryMaker = $this->findOrCreateMakerByName($params['maker']);
                $params['machinery_maker_id'] = $machineryMaker->getAttribute('id');
            }
            $vesselMachinery = $this->vesselMachinery->create([
                'vessel_id' => $vessel->getAttribute('id'),
                'machinery_id' => $machinery->getAttribute('id'),
                'incharge_rank_id' => $inchargeRank->getAttribute('id'),
                'machinery_model_id' => (isset($machineryModel) && $machineryModel instanceof MachineryModel)
                    ? $machineryModel->getAttribute('id')
                    : null,
                'machinery_maker_id' => (isset($machineryMaker) && $machineryMaker instanceof MachineryMaker)
                    ? $machineryMaker->getAttribute('id')
                    : null,
                'installed_date' => $params['installed_date'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $vesselMachinery;
    }

    /**
     * Retrieve/Create the machinery model
     *
     * @param string $name
     * @return MachineryModel
     * @throws
     */
    public function findOrCreateModelByName(string $name): MachineryModel
    {
        return MachineryModel::firstOrCreate(['name' => $name]);
    }

    /**
     * Retrieve/Create the machinery maker
     *
     * @param string $name
     * @return MachineryMaker
     * @throws
     */
    public function findOrCreateMakerByName(string $name): MachineryMaker
    {
        return MachineryMaker::firstOrCreate(['name' => $name]);
    }

    /**
     * Updates vessel machinery in the database
     *
     * @param array $params
     * @param VesselMachinery $vesselMachinery
     * @return VesselMachinery
     * @throws
     */
    public function update(array $params, VesselMachinery $vesselMachinery): VesselMachinery
    {
        /** @var Vessel $vessel */
        $vessel = Vessel::whereName($params['vessel'])->first();
        /** @var Machinery $machinery */
        $machinery = Machinery::whereName($params['machinery'])->first();
        /** @var Rank $inchargeRank */
        $inchargeRank = Rank::whereName($params['incharge_rank'])->first();
        if ($params['model']) {
            $machineryModel = $this->findOrCreateModelByName($params['model']);
            $params['machinery_model_id'] = $machineryModel->getAttribute('id');
        }
        if ($params['maker']) {
            $machineryMaker = $this->findOrCreateMakerByName($params['model']);
            $params['machinery_maker_id'] = $machineryMaker->getAttribute('id');
        }
        $vesselMachinery->update([
            'vessel_id' => $vessel->getAttribute('id'),
            'machinery_id' => $machinery->getAttribute('id'),
            'incharge_rank_id' => $inchargeRank->getAttribute('id'),
            'machinery_model_id' => (isset($machineryModel) && $machineryModel instanceof MachineryModel)
                ? $machineryModel->getAttribute('id')
                : null,
            'machinery_maker_id' => (isset($machineryMaker) && $machineryMaker instanceof MachineryMaker)
                ? $machineryMaker->getAttribute('id')
                : null,
            'installed_date' => $params['installed_date'],
        ]);
        return $vesselMachinery;
    }

    /**
     * Deletes the vessel machinery in the database
     *
     * @param VesselMachinery $vesselMachinery
     * @return bool
     * @throws
     */
    public function delete(VesselMachinery $vesselMachinery): bool
    {
        if (!($vesselMachinery instanceof VesselMachinery)) {
            throw new VesselMachineryNotFoundException();
        }
        $vesselMachinery->delete();
        return true;
    }

    /**
     * Edit vessel machinery sub categories
     *
     * @param array $params
     * @param VesselMachinery $vesselMachinery
     * @return VesselMachinery
     * @throws
     */
    public function editMachinerySubCategories(array $params, VesselMachinery $vesselMachinery): VesselMachinery
    {
        DB::beginTransaction();

        try {
            $newVesselMachinerySubCategories = [];
            $removeVesselMachinerySubCategories = [];
            if (is_array($params['vessel_machinery_sub_categories'])) {
                foreach ($params['vessel_machinery_sub_categories'] as $subCategory) {
                    if (!isset($subCategory['code'])
                        && !isset($subCategory['description'])
                        && !isset($subCategory['interval'])) {
                        $removeVesselMachinerySubCategories[] = $subCategory['machinery_sub_category_id'];
                        continue;
                    }
                    /** @var Interval $interval */
                    $interval = Interval::whereName($subCategory['interval'])->first();

                    $dueDate = $this->getDueDate($vesselMachinery->getAttribute('installed_date'), $interval);

                    /** @var MachinerySubCategory $machinerySubCategory */
                    $machinerySubCategory = MachinerySubCategory::find($subCategory['machinery_sub_category_id']);
                    if ($subCategory['description']) {
                        $description = $machinerySubCategory
                            ->descriptions()
                            ->firstOrCreate([
                                'name' => $subCategory['description'],
                            ]);
                    }

                    /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
                    $vesselMachinerySubCategory = $vesselMachinery->subCategories()
                        ->whereHas('subCategory', function ($q) use ($machinerySubCategory) {
                            $q->whereId($machinerySubCategory->getAttribute('id'));
                        })
                        ->first();

                    if ($vesselMachinerySubCategory instanceof VesselMachinerySubCategory) {
                        $vesselMachinerySubCategory->update([
                            'code' => $subCategory['code'],
                            'due_date' => $dueDate,
                            'interval_id' => $interval->getAttribute('id'),
                            'machinery_sub_category_description_id' => isset($description)
                                ? $description->getAttribute('id')
                                : null,
                        ]);
                        continue;
                    }

                    $newVesselMachinerySubCategories[] = new VesselMachinerySubCategory([
                        'code' => $subCategory['code'],
                        'due_date' => $dueDate,
                        'interval_id' => $interval->getAttribute('id'),
                        'machinery_sub_category_id' => $machinerySubCategory->getAttribute('id'),
                        'machinery_sub_category_description_id' => isset($description)
                            ? $description->getAttribute('id')
                            : null,
                    ]);
                }
            }

            if (!empty($newVesselMachinerySubCategories)) {
                $vesselMachinery->subCategories()->saveMany($newVesselMachinerySubCategories);
            }

            if (!empty($removeVesselMachinerySubCategories)) {
                $vesselMachinery->subCategories()->whereIn('id', $removeVesselMachinerySubCategories)->delete();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $vesselMachinery;
    }

    /**
     * Get the job due date
     *
     * @param string $date
     * @param Interval $interval
     * @return Carbon
     */
    public function getDueDate(string $date, Interval $interval): Carbon
    {
        $dueDate = Carbon::create($date);
        /** @var IntervalUnit $intervalUnit */
        $intervalUnit = $interval->unit;
        switch ($intervalUnit->getAttribute('name')) {
            case config('interval.units.days'):
                $dueDate->addDays($interval->getAttribute('value'));
                break;
            case config('interval.units.hours'):
                $dueDate->addHours($interval->getAttribute('value'));
                break;
            case config('interval.units.weeks'):
                $dueDate->addWeeks($interval->getAttribute('value'));
                break;
            case config('interval.units.months'):
                $dueDate->addMonths($interval->getAttribute('value'));
                break;
            case config('interval.units.years'):
                $dueDate->addYears($interval->getAttribute('value'));
                break;
        }
        return $dueDate;
    }
}
