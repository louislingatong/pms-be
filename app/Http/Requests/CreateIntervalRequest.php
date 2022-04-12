<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateIntervalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'value' => 'nullable',
            'unit' => 'required_with:value',
            'name' => 'required_without:value',
        ];
    }

    public function getUnit()
    {
        return $this->input('unit', null);
    }

    public function getValue()
    {
        return $this->input('value', null);
    }

    public function getName()
    {
        return $this->input('name', null);
    }
}
