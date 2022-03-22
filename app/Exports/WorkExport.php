<?php

namespace App\Exports;

use App\Models\Interval;
use App\Models\MachinerySubCategoryDescription;
use App\Models\User;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $works;

    public function __construct(array $works)
    {
        $this->works = $works;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->works;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Code',
            'Sub Category',
            'Description',
            'Intervals',
            'Commissioning Date',
            'Last Done',
            'Running Hours',
            'Due Date',
            'Status',
            'Instructions',
            'Remarks',
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
            $row['code'],
            $row['sub_category']['name'],
            $row['description']['name'],
            $row['interval']['name'],
            Carbon::create($row['vessel_machinery']['installed_date'])->format('d-M-Y'),
            $row['current_work']['last_done']
                ? Carbon::create($row['current_work']['last_done'])->format('d-M-Y')
                : '',
            $row['current_work']['running_hours'] ?: '',
            Carbon::create($row['due_date'])->format('d-M-Y'),
            $this->getStatus($row['due_date']),
            $row['current_work']['instructions'] ?: '',
            $row['current_work']['remarks'] ?: '',
        ];
    }

    /**
     * Get the work status
     *
     * @param string $dueDate
     * @return string
     */
    public function getStatus(string $dueDate): string
    {
        $currentDate = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($dueDate);
        if ($currentDate->greaterThan($dueDate)) {
            return config('work.statuses.overdue');
        } else if ($currentDate->isSameDay($dueDate)) {
            return config('work.statuses.due');
        } else if ($currentDate->diffInDays($dueDate) <= config('work.warning_days')) {
            return config('work.statuses.warning');
        } else {
            return '';
        }
    }
}
