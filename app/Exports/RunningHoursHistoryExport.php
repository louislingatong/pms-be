<?php

namespace App\Exports;

use App\Models\User;
use App\Models\VesselMachinery;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RunningHoursHistoryExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $vesselMachinery;

    public function __construct(VesselMachinery $vesselMachinery)
    {
        $this->vesselMachinery = $vesselMachinery;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->vesselMachinery->runningHoursHistory->toArray();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Machinery',
            'Running Hours',
            'Encoded Date',
            'Encoded By',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        /** @var VesselMachinery $machinery */
        $machinery = $this->vesselMachinery->machinery;
        /** @var User $creator */
        $creator = User::find($row['creator_id']);
        return [
            $machinery->getAttribute('name'),
            $row['running_hours'],
            Carbon::create($row['created_at'])->format('d-M-Y'),
            $creator->getAttribute('full_name'),
        ];
    }
}
