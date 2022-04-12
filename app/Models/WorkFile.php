<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['filename'];

    /**
     * Get the owning imageable model.
     *
     * @return mixed
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
