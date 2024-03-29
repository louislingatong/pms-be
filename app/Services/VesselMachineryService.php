<?php

namespace App\Services;

use App\Http\Resources\VesselMachineryWithoutSubCategoriesResource;
use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\Machinery;
use App\Models\MachineryMaker;
use App\Models\MachineryModel;
use App\Models\MachinerySubCategory;
use App\Models\MachinerySubCategoryDescription;
use App\Models\Rank;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
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
            ->orderBy('machinery_id')
            ->paginate($limit);

        $urlParams = ['keyword' => $conditions['keyword'], 'limit' => $limit];

        return paginated($results, VesselMachineryWithoutSubCategoriesResource::class, $page, $urlParams);
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
            $vesselMachinery = $this->vesselMachinery->firstOrCreate([
                'vessel_id' => $vessel->getAttribute('id'),
                'machinery_id' => $machinery->getAttribute('id'),
                'incharge_rank_id' => $inchargeRank->getAttribute('id'),
                'machinery_model_id' => (isset($machineryModel) && $machineryModel instanceof MachineryModel)
                    ? $machineryModel->getAttribute('id')
                    : null,
                'machinery_maker_id' => (isset($machineryMaker) && $machineryMaker instanceof MachineryMaker)
                    ? $machineryMaker->getAttribute('id')
                    : null,
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
        ]);
        return $vesselMachinery;
    }

    /**
     * Deletes the vessel machinery/s in the database
     *
     * @param array $params
     * @return bool
     * @throws
     */
    public function delete(array $params): bool
    {
        DB::beginTransaction();

        try {
            $this->vesselMachinery->whereIn('id', $params['vessel_machinery_ids'])->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

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
            $removedVesselMachinerySubCategories = [];
            if (is_array($params['vessel_machinery_sub_categories'])) {
                foreach ($params['vessel_machinery_sub_categories'] as $subCategory) {
                    if (!$subCategory['code']
                        && !$subCategory['description']
                        && !$subCategory['interval']) {
                        $removedVesselMachinerySubCategories[] = $subCategory['machinery_sub_category_id'];
                        continue;
                    }
                    /** @var Interval $interval */
                    $interval = Interval::whereName($subCategory['interval'])->first();

                    $dueDate = $interval->getAttribute('value')
                        ? $this->getDueDate($subCategory['installed_date'], $interval)
                        : null;

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
                            'installed_date' => Carbon::create($subCategory['installed_date']),
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
                        'installed_date' => Carbon::create($subCategory['installed_date']),
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

            if (!empty($removedVesselMachinerySubCategories)) {
                $vesselMachinery->subCategories()->whereHas('subCategory', function ($q) use ($removedVesselMachinerySubCategories) {
                    $q->whereIn('id', $removedVesselMachinerySubCategories);
                })->delete();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $vesselMachinery;
    }

    /**
     * List of vessel machineries with sub categories by conditions
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

        return $query->get();
    }

    /**
     * Get the job due date
     *
     * @param string $date
     * @param Interval $interval
     * @return Carbon | null
     */
    public function getDueDate(string $date, Interval $interval)
    {
        $dueDate = Carbon::create($date);
        /** @var IntervalUnit $intervalUnit */
        $intervalUnit = $interval->unit;
        if ($date) {
            switch ($intervalUnit->getAttribute('name')) {
                case config('interval.units.days'):
                    $dueDate->addDays($interval->getAttribute('value'));
                    if ($interval->getAttribute('value') > 1) {
                        $dueDate->subDay();
                    } else {
                        $dueDate->addDay();
                    }
                    break;
                case config('interval.units.hours'):
                    $dueDate->addHours($interval->getAttribute('value'));
                    break;
                case config('interval.units.weeks'):
                    $dueDate->addWeeks($interval->getAttribute('value'));
                    break;
                case config('interval.units.months'):
                    $dueDate->addMonths($interval->getAttribute('value'));
                    $dueDate->subDay();
                    break;
                case config('interval.units.years'):
                    $years = (int)$interval->getAttribute('value');
                    $dueDate->addYears($years);
                    $additionalMonths = 12 * ($interval->getAttribute('value') - $years);
                    if ($additionalMonths) {
                        $dueDate->addMonths($additionalMonths);
                    }
                    $dueDate->subDay();
                    break;
            }
            return $dueDate;
        } else {
            return null;
        }
    }

    /**
     * Copy all vessel machineries to a vessel
     *
     * @param array $params
     * @return bool
     * @throws
     */
    public function copyAllMachinery(array $params): bool
    {
        DB::beginTransaction();

        try {
            /** @var VesselMachinery $machineriesOfVesselFrom */
            $machineriesOfVesselFrom = $this->vesselMachinery->whereHas('vessel', function ($q) use ($params) {
                $q->where('name', '=', $params['vesselFrom']);
            })
                ->get();

            foreach ($machineriesOfVesselFrom as $machineryOfVesselFrom) {
                /** @var Machinery $machinery */
                $machinery = $machineryOfVesselFrom->machinery;
                /** @var Rank $inchargeRank */
                $inchargeRank = $machineryOfVesselFrom->inchargeRank;
                /** @var MachineryModel $machineryModel */
                $machineryModel = $machineryOfVesselFrom->machineryModel;
                /** @var MachineryMaker $machineryMaker */
                $machineryMaker = $machineryOfVesselFrom->machineryMaker;

                $vesselMachineryData = [
                    'vessel' => $params['vesselTo'],
                    'machinery' => $machinery->getAttribute('name'),
                    'incharge_rank' => $inchargeRank->getAttribute('name'),
                    'model' => $machineryModel ? $machineryModel->getAttribute('name') : null,
                    'maker' => $machineryMaker ? $machineryMaker->getAttribute('name') : null,
                ];

                $machineryOfVesselTo = $this->create($vesselMachineryData);

                /** @var VesselMachinerySubCategory $vesselSubCategories */
                $vesselSubCategories = $machineryOfVesselFrom->subCategories;

                /** @var VesselMachinerySubCategory $vesselSubCategory */
                foreach ($vesselSubCategories as $vesselSubCategory) {
                    /** @var Interval $interval */
                    $interval = $vesselSubCategory->interval;
                    /** @var MachinerySubCategoryDescription $description */
                    $description = $vesselSubCategory->description;
                    /** @var MachinerySubCategory $subCategory */
                    $subCategory = $vesselSubCategory->subCategory;

                    $machineryOfVesselTo->subCategories()->firstOrCreate([
                        'code' => $vesselSubCategory->getAttribute('code'),
                        'installed_date' => Carbon::create($vesselSubCategory->getAttribute('installed_date')),
                        'due_date' => $vesselSubCategory->getAttribute('due_date'),
                        'interval_id' => $interval ? $interval->getAttribute('id') : null,
                        'machinery_sub_category_id' => $subCategory->getAttribute('id'),
                        'machinery_sub_category_description_id' => $description ? $description->getAttribute('id') : null,
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return true;
    }
}
