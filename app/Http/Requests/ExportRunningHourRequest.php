<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportRunningHourRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'vessel' => [
                'required',
                'exists:vessels,name',
            ],
            'department' => [
                'nullable',
                'exists:vessel_departments,name',
            ],
            'keyword' => 'nullable',
        ];
    }

    public function getVessel()
    {
        return $this->input('vessel', null);
    }

    public function getDepartment()
    {
        return $this->input('department', '');
    }

    public function getKeyword()
    {
        return $this->input('keyword', '');
    }
}
