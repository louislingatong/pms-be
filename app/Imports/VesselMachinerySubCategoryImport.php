<?php

namespace App\Imports;

use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\MachinerySubCategory;
use App\Models\MachinerySubCategoryDescription;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VesselMachinerySubCategoryImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return VesselMachinerySubCategory
     */
    public function model(array $row): VesselMachinerySubCategory
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::whereHas('vessel', function ($q) use ($row) {
            $q->where('name', '=', $row['vessel']);
        })->whereHas('machinery', function ($q) use ($row) {
            $q->where('name', '=', $row['machinery']);
        })->first();

        /** @var Interval $interval */
        $interval = Interval::where('name', $row['interval'])->first();

        $dueDate = $this->getDueDate($vesselMachinery->getAttribute('installed_date'), $interval);

        /** @var MachinerySubCategory $machinerySubCategory */
        $machinerySubCategory = MachinerySubCategory::where('name', $row['name'])
            ->first();

        if (isset($row['description'])) {
            $description = $machinerySubCategory
                ->descriptions()
                ->firstOrCreate([
                    'name' => $row['description'],
                ]);
        }

        return new VesselMachinerySubCategory([
            'code' => $row['code'],
            'vessel_machinery_id' => $vesselMachinery->getAttribute('id'),
            'interval_id' => $interval->getAttribute('id'),
            'due_date' => $dueDate,
            'machinery_sub_category_id' => $machinerySubCategory->getAttribute('id'),
            'machinery_sub_category_description_id' => (isset($description) && ($description instanceof MachinerySubCategoryDescription))
                ? $description->getAttribute('id')
                : null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.code' => 'required',
            '*.vessel' => [
                'required',
                'exists:vessels,name'
            ],
            '*.machinery' => [
                'required',
                'exists:machineries,name'
            ],
            '*.interval' => [
                'required',
                'exists:intervals,name'
            ],
            '*.name' => [
                'required',
                'exists:machinery_sub_categories,name'
            ],
        ];
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
