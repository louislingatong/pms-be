<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'employee_department_id',
        'id_number',
        'position',
    ];

    /**
     * Retrieves the user of the employee
     *
     * @return BelongsTo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retrieves the department of the employee
     *
     * @return BelongsTo EmployeeDepartment
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(EmployeeDepartment::class, 'employee_department_id');
    }

    /**
     * Retrieves all employee assigned vessels
     *
     * @return BelongsToMany EmployeeVessel
     */
    public function vessels(): BelongsToMany
    {
        return $this->belongsToMany(Vessel::class, 'employee_vessels', 'employee_id')
            ->as('employee_vessel')
            ->using(EmployeeVessel::class)
            ->withPivot(
                'employee_id',
                'vessel_id'
            );
    }
}
