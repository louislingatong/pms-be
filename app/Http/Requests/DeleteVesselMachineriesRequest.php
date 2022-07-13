<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteVesselMachineriesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vessel_machinery_ids.*' => [
                'required',
                'exists:vessel_machineries,id',
            ],
        ];
    }

    public function getVesselMachineryIds()
    {
        return $this->input('vessel_machinery_ids.*', null);
    }
}
