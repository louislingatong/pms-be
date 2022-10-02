<?php

namespace App\Imports;

use App\Exceptions\IntervalNotFoundException;
use App\Exceptions\MachinerySubCategoryNotFoundException;
use App\Exceptions\VesselMachineryNotFoundException;
use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\MachinerySubCategory;
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
    public function model(array $row): ?VesselMachinerySubCategory
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::whereHas('vessel', function ($q) use ($row) {
            $q->where('name', '=', $row['vessel']);
        })->whereHas('machinery', function ($q) use ($row) {
            $q->where('name', '=', $row['machinery']);
        })->first();

        if (!($vesselMachinery instanceof VesselMachinery)) {
            $machinery = $row['machinery'];
            $vessel = $row['vessel'];
            $message = "Unable to retrieve machinery $machinery in vessel $vessel";
            throw new VesselMachineryNotFoundException($message);
        }

        /** @var MachinerySubCategory $machinerySubCategory */
        $machinerySubCategory = MachinerySubCategory::where('code', $row['code'])
            ->where('name', $row['name'])
            ->first();

        if (!($machinerySubCategory instanceof MachinerySubCategory)) {
            $machinery = $row['machinery'];
            $subCategoryCode = $row['code'];
            $message = "Unable to retrieve sub category code $subCategoryCode in machinery $machinery";
            throw new MachinerySubCategoryNotFoundException($message);
        }

        /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
        $vesselMachinerySubCategory = VesselMachinerySubCategory::where('code', $row['code'])
            ->where('vessel_machinery_id', $vesselMachinery->getAttribute('id'))
            ->where('machinery_sub_category_id', $machinerySubCategory->getAttribute('id'))
            ->first();

        if (is_null($vesselMachinerySubCategory)) {
            $interval = null;
            $dueDate = null;

            if ($row['interval']) {
                /** @var Interval $interval */
                $interval = Interval::where('name', $row['interval'])->first();

                if (!($interval instanceof Interval)) {
                    throw new IntervalNotFoundException('Unable to retrieve interval ' . $row['interval']);
                }

                $dueDate = $interval->getAttribute('value')
                    ? $this->getDueDate($row['commissioning_date'], $interval)
                    : null;
            }

            $description = null;

            if ($row['description']) {
                $description = $machinerySubCategory
                    ->descriptions()
                    ->firstOrCreate([
                        'name' => $row['description'],
                    ]);
            }

            return new VesselMachinerySubCategory([
                'code' => $row['code'],
                'vessel_machinery_id' => $vesselMachinery->getAttribute('id'),
                'interval_id' => $row['interval'] ? $interval->getAttribute('id') : null,
                'installed_date' => $row['commissioning_date'] ? Carbon::create($row['commissioning_date']) : null,
                'due_date' => $dueDate,
                'machinery_sub_category_id' => $machinerySubCategory->getAttribute('id'),
                'machinery_sub_category_description_id' => $row['description']
                    ? $description->getAttribute('id')
                    : null,
            ]);
        }

        return null;
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
            '*.name' => [
                'required',
                'exists:machinery_sub_categories,name'
            ],
            '*.commissioning_date' => [
                'nullable',
                'date',
                'date_format:d-M-Y',
            ],
        ];
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
        if ($date) {
            $dueDate = Carbon::create($date);
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
            $dueDate->subDay();
            return $dueDate;
        } else {
            return null;
        }
    }
}
