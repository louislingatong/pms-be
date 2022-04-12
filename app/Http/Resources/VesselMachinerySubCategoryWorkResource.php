<?php

namespace App\Http\Resources;

use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VesselMachinerySubCategoryWorkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
        $vesselMachinerySubCategory = $this->resource;
        return [
            'id' => $vesselMachinerySubCategory->getAttribute('id'),
            'code' => $vesselMachinerySubCategory->getAttribute('code'),
            'installed_date' => Carbon::parse($vesselMachinerySubCategory->getAttribute('installed_date'))->format('d-M-Y'),
            'due_date' => $vesselMachinerySubCategory->getAttribute('due_date')
                ? Carbon::create($vesselMachinerySubCategory->getAttribute('due_date'))->format('d-M-Y')
                : '',
            'interval' => new IntervalResource($vesselMachinerySubCategory->interval),
            'sub_category' => new MachinerySubCategoryResource($vesselMachinerySubCategory->subCategory),
            'description' => new MachinerySubCategoryDescriptionResource($vesselMachinerySubCategory->description),
            'status' => $vesselMachinerySubCategory->getAttribute('due_date')
                ? $this->getStatus($vesselMachinerySubCategory->getAttribute('due_date'))
                : config('work.statuses.dry_dock'),
            'current_work' => new WorkResource($vesselMachinerySubCategory->currentWork),
            'work_history' => WorkResource::collection($vesselMachinerySubCategory->worksHistory),
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
        } else if ($currentDate->lessThan($dueDate)) {
            return config('work.statuses.jobs_done');
        } else {
            return '';
        }
    }
}
