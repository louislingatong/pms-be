<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMachineriesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'machinery_ids.*' => [
                'required',
                'exists:machineries,id',
            ],
        ];
    }

    public function getMachineryIds()
    {
        return $this->input('machinery_ids.*', null);
    }
}
