<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeePermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'permissions.*' => [
                'required',
                'exists:permissions,name',
            ],
        ];
    }

    public function getPermissions()
    {
        return $this->input('permissions.*', null);
    }
}
