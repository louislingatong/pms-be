<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOverdueDateRequest extends FormRequest
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
            'interval' => [
                'required',
                'exists:intervals,name',
            ],
        ];
    }

    public function getVessel()
    {
        return $this->input('vessel', null);
    }

    public function getInterval()
    {
        return $this->input('interval', null);
    }
}
