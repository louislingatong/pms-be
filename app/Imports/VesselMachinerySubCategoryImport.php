<?php

namespace App\Imports;

use App\Exceptions\IntervalNotFoundException;
use App\Exceptions\VesselMachineryNotFoundException;
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
     * @throws
     */
    public function model(array $row): VesselMachinerySubCategory
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::whereHas('vessel', function ($q) use ($row) {
            $q->where('name', '=', $row['vessel']);
        })->whereHas('machinery', function ($q) use ($row) {
            $q->where('name', '=', $row['machinery']);
        })->first();

        if (!($vesselMachinery instanceof VesselMachinery)) {
            throw new VesselMachineryNotFoundException();
        }

        /** @var Interval $interval */
        $interval = Interval::where('name', $row['interval'])->first();

        if (!($interval instanceof Interval)) {
            throw new IntervalNotFoundException();
        }

        $dueDate = $this->getDueDate($row['commissioning_date'], $interval);

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
            'installed_date' => $row['commissioning_date'] ? Carbon::create($row['commissioning_date']) : null,
            'due_date' => $dueDate,
            'machinery_sub_category_id' => $machinerySubCategory->getAttribute('id'),
            'machinery_sub_category_description_id' => (isset($description) && ($description instanceof MachinerySubCategoryDescription))
                ? $description->getAttribute('id')
                : null,
        ]);
    }

    /**
     * Get the job due date
     *
     * @param $date
     * @param Interval $interval
     * @return Carbon | null
     */
    public function getDueDate($date, Interval $interval)
    {
        /** @var IntervalUnit $intervalUnit */
        $intervalUnit = $interval->unit;
        if ($date && $intervalUnit instanceof IntervalUnit) {
            $dueDate = Carbon::create($date);
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
        } else {
            return null;
        }
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
            '*.commissioning_date' => [
                'nullable',
                'date',
                'date_format:d-M-y',
            ],
        ];
    }
}
