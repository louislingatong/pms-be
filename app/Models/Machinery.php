<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machinery extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vessel_department_id',
        'code_name',
        'name',
    ];

    /**
     * Retrieves the department of the machinery
     *
     * @return BelongsTo VesselDepartment
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(VesselDepartment::class, 'vessel_department_id');
    }

    /**
     * Retrieve all sub category under this machinery
     *
     * @return HasMany MachinerySubCategory[]
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(MachinerySubCategory::class);
    }

    /**
     * Creates a scope to search all machinery by the provided keyword
     *
     * @param Builder $query
     * @param string $keyword
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('code_name', 'LIKE', "%$keyword%")
            ->orWhere('name', 'LIKE', "%$keyword%");
    }
}
