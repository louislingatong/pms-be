<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as OriginalRole;

class Role extends OriginalRole
{
    /**
     * Create a scope to search all roles where name contains the provided search string.
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
