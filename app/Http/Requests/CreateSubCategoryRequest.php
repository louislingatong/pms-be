<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'code' => 'required',
            'name' => 'required',
        ];
    }

    public function getCode()
    {
        return $this->input('code', null);
    }

    public function getName()
    {
        return $this->input('name', null);
    }
}
