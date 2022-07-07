<?php

namespace App\Imports;

use App\Exceptions\MachinerySubCategoryNotFoundException;
use App\Exceptions\VesselMachineryNotFoundException;
use App\Exceptions\VesselMachinerySubCategoryNotFoundException;
use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\MachinerySubCategory;
use App\Models\User;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use App\Models\Work;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class WorksImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return Work
     * @throws
     */
    public function model(array $row): Work
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::whereHas('vessel', function ($q) use ($row) {
            $q->where('name', '=', $row['vessel']);
        })->whereHas('machinery', function ($q) use ($row) {
            $q->where('name', '=', $row['machinery']);
        })->first();

        if (!($vesselMachinery instanceof VesselMachinery)) {
            throw new VesselMachineryNotFoundException('Unable to retrieve machinery ' . $row['machinery'] . ' in vessel ' . $row['vessel']);
        }

        /** @var MachinerySubCategory $machinerySubCategory */
        $machinerySubCategory = $vesselMachinery->machinery->subCategories()
            ->where('code', $row['code'])
            ->where('name', $row['name'])
            ->first();

        if (!($machinerySubCategory instanceof MachinerySubCategory)) {
            throw new MachinerySubCategoryNotFoundException('Unable to retrieve [' . $row['code'] . '] - ' . $row['name']);
        }

        /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
        $vesselMachinerySubCategory = VesselMachinerySubCategory::where('vessel_machinery_id', $vesselMachinery->getAttribute('id'))
            ->where('machinery_sub_category_id', $machinerySubCategory->getAttribute('id'))
            ->first();

        if (!($vesselMachinerySubCategory instanceof VesselMachinerySubCategory)) {
            throw new VesselMachinerySubCategoryNotFoundException();
        }

        /** @var User $user */
        $user = auth()->user();

        $work = new Work([
            'vessel_machinery_sub_category_id' => $vesselMachinerySubCategory->getAttribute('id'),
            'last_done' => Carbon::create($row['last_done_date']),
            'running_hours' => $row['last_done_running_hours'],
            'instructions' => $row['instructions'],
            'remarks' => $row['remarks'],
            'creator_id' => $user->getAttribute('id'),
        ]);

        if (isset($row['last_done_date'])) {
            /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
            $vesselMachinerySubCategory = $work->vesselMachinerySubCategory;
            /** @var Interval $interval */
            $interval = $vesselMachinerySubCategory->interval;

            $vesselMachinerySubCategory->update([
                'due_date' => $this->getDueDate(
                    $work->getAttribute('last_done'),
                    $interval
                )
            ]);
        }

        return $work;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
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
            '*.last_done_date' => [
                'nullable',
                'date',
                'date_format:d-M-y',
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
    public function getDueDate(string $date, Interval $interval): ?Carbon
    {
        $dueDate = Carbon::create($date);
        /** @var IntervalUnit $intervalUnit */
        $intervalUnit = $interval->unit;
        if ($intervalUnit instanceof IntervalUnit) {
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
}
