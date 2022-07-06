<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class BaseService
{
    /**
     * Retrieve login user.
     *
     * @return Authenticatable
     */
    public function user()
    {
        return auth()->user();
    }
}
