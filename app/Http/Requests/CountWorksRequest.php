<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountWorksRequest extends FormRequest
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
        ];
    }

    public function getVessel()
    {
        return $this->input('vessel', null);
    }
}
