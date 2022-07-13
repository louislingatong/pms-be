<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivateDeactivateEmployeesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'employee_ids.*' => [
                'required',
                'exists:employees,id',
            ],
        ];
    }

    public function getEmployeeIds()
    {
        return $this->input('employee_ids.*', null);
    }
}
