<?php

namespace App\Imports;

use App\Models\Machinery;
use App\Models\MachinerySubCategory;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MachinerySubCategoryImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return MachinerySubCategory
     */
    public function model(array $row): MachinerySubCategory
    {
        /** @var Machinery $machinery */
        $machinery = Machinery::where('name', $row['machinery'])->first();

        return new MachinerySubCategory([
            'name' => $row['name'],
            'machinery_id' => $machinery->getAttribute('id'),
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.machinery' => [
                'required',
                'exists:machineries,name',
            ]
        ];
    }
}
