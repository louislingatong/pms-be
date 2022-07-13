<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteIntervalsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'interval_ids.*' => [
                'required',
                'exists:intervals,id',
            ],
        ];
    }

    public function getVesselIds()
    {
        return $this->input('interval_ids.*', null);
    }
}
