<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CopyVesselMachineryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'vesselFrom' => [
                'required',
                'exists:vessels,name',
            ],
            'vesselTo' => [
                'required',
                'exists:vessels,name',
            ],
        ];
    }

    public function getVesselFrom()
    {
        return $this->input('vesselFrom', null);
    }

    public function getVesselTo()
    {
        return $this->input('vesselTo', '');
    }
}
