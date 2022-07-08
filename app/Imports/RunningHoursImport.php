<?php

namespace App\Imports;

use App\Exceptions\VesselMachineryNotFoundException;
use App\Models\RunningHour;
use App\Models\User;
use App\Models\VesselMachinery;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RunningHoursImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return RunningHour
     * @throws
     */
    public function model(array $row): ?RunningHour
    {
        if (is_numeric($row['running_hours'])) {
            /** @var VesselMachinery $vesselMachinery */
            $vesselMachinery = VesselMachinery::whereHas('vessel', function ($q) use ($row) {
                $q->where('name', '=', $row['vessel']);
            })->whereHas('machinery', function ($q) use ($row) {
                $q->where('name', '=', $row['machinery']);
            })->first();

            if (!($vesselMachinery instanceof VesselMachinery)) {
                throw new VesselMachineryNotFoundException('Unable to retrieve machinery ' . $row['machinery'] . ' in vessel ' . $row['vessel']);
            }

            $updatingDate = Carbon::create($row['updating_date']);

            /** @var User $user */
            $user = auth()->user();

            return new RunningHour([
                'vessel_machinery_id' => $vesselMachinery->getAttribute('id'),
                'running_hours' => $row['running_hours'],
                'updating_date' => $updatingDate,
                'creator_id' => $user->getAttribute('id'),
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
            '*.vessel' => [
                'required',
                'exists:vessels,name'
            ],
            '*.machinery' => [
                'required',
                'exists:machineries,name'
            ],
            '*.updating_date' => [
                'date',
                'date_format:d-M-y',
            ]
        ];
    }
}
