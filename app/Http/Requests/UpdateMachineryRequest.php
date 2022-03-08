<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMachineryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'department' => [
                'required',
                'exists:vessel_departments,name',
            ],
            'code_name' => 'required',
            'name' => 'required',
        ];
    }

    public function getDepartment()
    {
        return $this->input('department', null);
    }

    public function getCodeName()
    {
        return $this->input('code_name', null);
    }

    public function getName()
    {
        return $this->input('name', null);
    }
}
