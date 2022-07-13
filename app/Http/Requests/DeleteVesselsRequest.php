<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteVesselsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vessel_ids.*' => [
                'required',
                'exists:vessels,id',
            ],
        ];
    }

    public function getVesselIds()
    {
        return $this->input('vessel_ids.*', null);
    }
}
