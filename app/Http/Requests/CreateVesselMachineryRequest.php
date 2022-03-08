<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVesselMachineryRequest extends FormRequest
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
            'machinery' => [
                'required',
                'exists:machineries,name',
            ],
            'incharge_rank' => [
                'required',
                'exists:ranks,name',
            ],
            'model' => 'nullable',
            'maker' => 'nullable',
            'installed_date' => [
                'required',
                'date',
                'date_format:d-M-Y',
            ],
        ];
    }

    public function getVessel()
    {
        return $this->input('vessel', null);
    }

    public function getMachinery()
    {
        return $this->input('machinery', null);
    }

    public function getInchargeRank()
    {
        return $this->input('incharge_rank', null);
    }

    public function getModel()
    {
        return $this->input('model', null);
    }

    public function getMaker()
    {
        return $this->input('maker', null);
    }

    public function getInstalledDate()
    {
        return $this->input('installed_date', null);
    }
}
