<?php

namespace App\Imports;

use App\Exceptions\MachinerySubCategoryNotFoundException;
use App\Exceptions\RunningHourNotFoundException;
use App\Exceptions\VesselMachineryNotFoundException;
use App\Exceptions\VesselMachinerySubCategoryNotFoundException;
use App\Models\Interval;
use App\Models\IntervalUnit;
use App\Models\MachinerySubCategory;
use App\Models\RunningHour;
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
            throw new VesselMachinerySubCategoryNotFoundException('Unable to retrieve vessel sub category [' . $machinerySubCategory->getAttribute('code') . ']');
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

        /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
        $vesselMachinerySubCategory = $work->vesselMachinerySubCategory;

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
                        $code = $vesselMachinerySubCategory->getAttribute('code');
                        $message = "Unable to retrieve running hour of code $code";
                        throw new RunningHourNotFoundException($message);
                    }

                    if ($runningHour->getAttribute('updating_date')
                        && $runningHour->getAttribute('running_hours')
                        && !is_null($work->getAttribute('running_hours'))) {
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
                $date->subDay();
                break;
        }

        return $date;
    }
}
