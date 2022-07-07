<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VesselMachinery extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vessel_id',
        'machinery_id',
        'incharge_rank_id',
        'machinery_model_id',
        'machinery_maker_id',
    ];

    /**
     * Retrieves the vessel of the vessel machinery
     *
     * @return BelongsTo Vessel
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }

    /**
     * Retrieves the machinery of the vessel machinery
     *
     * @return BelongsTo Machinery
     */
    public function machinery(): BelongsTo
    {
        return $this->belongsTo(Machinery::class, 'machinery_id');
    }

    /**
     * Retrieves the rank in-charge of the vessel machinery
     *
     * @return BelongsTo Rank
     */
    public function inchargeRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'incharge_rank_id');
    }

    /**
     * Retrieves the model of the vessel machinery
     *
     * @return BelongsTo MachineryModel
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(MachineryModel::class, 'machinery_model_id');
    }

    /**
     * Retrieves the maker of the vessel machinery
     *
     * @return BelongsTo MachineryMaker
     */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(MachineryMaker::class, 'machinery_maker_id');
    }

    /**
     * Retrieve all vessel sub categories under this vessel machinery
     *
     * @return HasMany MachinerySubCategory[]
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(VesselMachinerySubCategory::class);
    }

    /**
     * Retrieve current running hour under this vessel machinery
     *
     * @return HasOne RunningHour
     */
    public function currentRunningHour(): HasOne
    {
        return $this->HasOne(RunningHour::class, 'vessel_machinery_id')
            ->orderBy('updating_date', 'DESC');
    }

    /**
     * Retrieve all running hour under this vessel machinery
     *
     * @return HasMany RunningHour[]
     */
    public function runningHoursHistory(): HasMany
    {
        return $this->hasMany(RunningHour::class)
            ->orderBy('updating_date', 'DESC');
    }

    /**
     * Creates a scope to search all vessel machinery by the provided keyword
     *
     * @param Builder $query
     * @param string $keyword
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->whereHas('machinery', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                    ->orWhere('code_name', 'LIKE', "%$keyword%")
                    ->orWhereHas('department', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%$keyword%");
                    });
            })
            ->orWhereHas('inchargeRank', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
            });
    }
}
