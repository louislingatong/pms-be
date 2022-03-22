<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportWorkRequest extends FormRequest
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
            'machinery' => [
                'nullable',
                'exists:machineries,name',
            ],
            'status' => [
                'nullable',
                'in:' . implode(',', config('work.statuses')),
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

    public function getMachinery()
    {
        return $this->input('machinery', '');
    }

    public function getStatus()
    {
        return $this->input('status', '');
    }

    public function getKeyword()
    {
        return $this->input('keyword', '');
    }
}
