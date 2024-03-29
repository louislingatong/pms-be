<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VesselMachinerySubCategory extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'installed_date',
        'due_date',
        'interval_id',
        'vessel_machinery_id',
        'machinery_sub_category_id',
        'machinery_sub_category_description_id',
    ];

    /**
     * Retrieves the interval of the vessel machinery
     *
     * @return BelongsTo Interval
     */
    public function interval(): BelongsTo
    {
        return $this->belongsTo(Interval::class, 'interval_id')
            ->withTrashed();
    }

    /**
     * Retrieves the vessel machinery of the vessel sub category
     *
     * @return BelongsTo VesselMachinery
     */

    public function vesselMachinery(): BelongsTo
    {
        return $this->belongsTo(VesselMachinery::class, 'vessel_machinery_id')
            ->withTrashed();
    }

    /**
     * Retrieves the sub category of the vessel sub category
     *
     * @return BelongsTo MachinerySubCategory
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(MachinerySubCategory::class, 'machinery_sub_category_id')
            ->withTrashed();
    }

    /**
     * Retrieves the description of the vessel sub category
     *
     * @return BelongsTo MachinerySubCategoryDescription
     */

    public function description(): BelongsTo
    {
        return $this->belongsTo(MachinerySubCategoryDescription::class, 'machinery_sub_category_description_id');
    }

    /**
     * Retrieve current work under this vessel machinery sub category
     *
     * @return HasOne Work
     */
    public function currentWork(): HasOne
    {
        return $this->HasOne(Work::class, 'vessel_machinery_sub_category_id')
            ->orderBy('last_done', 'DESC')
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Retrieve all works under this vessel machinery sub category
     *
     * @return HasMany Work[]
     */
    public function worksHistory(): HasMany
    {
        return $this->HasMany(Work::class, 'vessel_machinery_sub_category_id')
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Creates a scope to search all vessel sub category by the provided keyword
     *
     * @param Builder $query
     * @param string $keyword
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('code', 'LIKE', "%$keyword%")
            ->orWhereHas('subCategory', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('description', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('interval', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
            });
    }

    /**
     * Creates a scope to search all vessel sub category by the provided status
     *
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeSearchByStatus(Builder $query, string $status): Builder
    {
        $currentDate = Carbon::now();
        if ($status === config('work.statuses.overdue')) {
            $startOfDay = $currentDate->copy()->startOfDay();
            return $query->where('due_date', '<', $startOfDay);
        } else if ($status === config('work.statuses.due')) {
            $startOfDay = $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->copy()->endOfDay();
            return $query->whereBetween('due_date', [$startOfDay, $endOfDay]);
        } else if ($status === config('work.statuses.warning')) {
            $warningDateFrom = $currentDate->copy()->endOfDay();
            $warningDateTo = $warningDateFrom->copy()->addDays(config('work.warning_days'))->startOfDay();
            return $query->whereBetween('due_date', [$warningDateFrom, $warningDateTo]);
        } else if ($status === config('work.statuses.jobs_done')) {
            $warningDateFrom = $currentDate->copy()->endOfDay();
            $warningDateTo = $warningDateFrom->copy()->addDays(config('work.warning_days'))->startOfDay();
            return $query->where('due_date', '>', $currentDate)
                ->whereNotBetween('due_date', [$warningDateFrom, $warningDateTo])
                ->whereHas('currentWork', function ($q) use ($status) {
                    $q->whereNotNull('last_done');
                });
        } else if ($status === config('work.statuses.dry_dock')) {
            return $query->whereNull('due_date');
        } else {
            return $query;
        }
    }

}
