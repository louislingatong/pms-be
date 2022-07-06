<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\User;
use App\Rules\EmailAddressRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'email' => [
                'required',
                new EmailAddressRule,
                'unique:users,email,' . $this->getId() . ',id',
            ],
            'department' => [
                'required',
                'exists:employee_departments,name',
            ],
            'id_number' => 'nullable',
            'position' => 'nullable',
            'is_admin' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    public function getId()
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');
        /** @var User $user */
        $user = $employee->user;
        return $user->getAttribute('id');
    }

    public function getFirstName()
    {
        return $this->input('first_name', null);
    }

    public function getMiddleName()
    {
        return $this->input('middle_name', null);
    }

    public function getLastName()
    {
        return $this->input('last_name', null);
    }

    public function getEmail()
    {
        return $this->input('email', null);
    }

    public function getDepartment()
    {
        return $this->input('department', null);
    }

    public function getIdNumber()
    {
        return $this->input('id_number', null);
    }

    public function getPosition()
    {
        return $this->input('position', null);
    }

    public function getIsAdmin()
    {
        return $this->input('is_admin', false);
    }
}
