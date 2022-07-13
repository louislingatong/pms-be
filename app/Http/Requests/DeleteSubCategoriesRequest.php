<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSubCategoriesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sub_category_ids.*' => [
                'required',
                'exists:machinery_sub_categories,id',
            ],
        ];
    }

    public function getSubCategoryIds()
    {
        return $this->input('sub_category_ids.*', null);
    }
}
