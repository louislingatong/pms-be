<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeeVessel extends Pivot
{
    /**
     * Retrieves the employee of the employee vessel
     *
     * @return BelongsTo Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Retrieves the vessel of the employee vessel
     *
     * @return BelongsTo Vessel
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }
}
