<?php

namespace App\Exports;

use App\Models\User;
use App\Models\VesselMachinery;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RunningHoursExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $runningHours;

    public function __construct(array $runningHours)
    {
        $this->runningHours = $runningHours;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->runningHours;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Code',
            'Machinery',
            'Running Hours',
            'Updating Date',
            'Encoded Date',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['machinery']['code_name'],
            $row['machinery']['name'],
            $row['current_running_hour']['running_hours'] ?: '0',
            $row['current_running_hour']['updating_date']
                ? Carbon::create($row['current_running_hour']['updating_date'])->format('d-M-Y')
                : '',
            $row['current_running_hour']['created_at']
                ? Carbon::create($row['current_running_hour']['created_at'])->format('d-M-Y')
                : '',
        ];
    }
}
