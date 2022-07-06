<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission as OriginalPermission;

class Permission extends OriginalPermission
{
    /**
     * Create a scope to search all permissions where name contains the provided search string.
     *
     * @param Builder $query
     * @param string $searchString
     * @return Builder
     */
    public function scopeSearch($query, $searchString)
    {
        return $query->where('name', 'like', '%' . $searchString . '%');
    }
}
