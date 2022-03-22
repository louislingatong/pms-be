<?php

namespace App\Exports;

use App\Models\Interval;
use App\Models\MachinerySubCategoryDescription;
use App\Models\User;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkHistoryExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $vesselMachinerySubCategory;

    public function __construct(VesselMachinerySubCategory $vesselMachinerySubCategory)
    {
        $this->vesselMachinerySubCategory = $vesselMachinerySubCategory;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->vesselMachinerySubCategory->worksHistory->toArray();
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
            'Encoded Data',
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
        /** @var VesselMachinerySubCategory $subCategory */
        $subCategory = $this->vesselMachinerySubCategory->subCategory;
        /** @var MachinerySubCategoryDescription $description */
        $description = $this->vesselMachinerySubCategory->description;
        /** @var Interval $intervals */
        $intervals = $this->vesselMachinerySubCategory->interval;
        /** @var VesselMachinery $machinery */
        $machinery = $this->vesselMachinerySubCategory->vesselMachinery;
        /** @var User $creator */
        $creator = User::find($row['creator_id']);
        return [
            $this->vesselMachinerySubCategory->getAttribute('code'),
            $subCategory->getAttribute('name'),
            $description->getAttribute('description') ?: '',
            $intervals->getAttribute('name'),
            Carbon::create($machinery->getAttribute('installed_date'))->format('d-M-Y'),
            Carbon::create($row['last_done'])->format('d-M-Y'),
            $row['running_hours'] ?: '',
            Carbon::create($this->vesselMachinerySubCategory->getAttribute('due_date'))->format('d-M-Y'),
            $this->getStatus($this->vesselMachinerySubCategory->getAttribute('due_date')),
            $row['instructions'] ?: '',
            $row['remarks'] ?: '',
            Carbon::create($row['created_at'])->format('d-M-Y'),
            $creator->getAttribute('full_name'),
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
