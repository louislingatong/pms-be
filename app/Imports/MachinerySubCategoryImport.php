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
    public function model(array $row): ?MachinerySubCategory
    {
        /** @var Machinery $machinery */
        $machinery = Machinery::where('name', $row['machinery'])->first();

        $machinerySubCategory = MachinerySubCategory::where('code', $row['code'])
            ->where('name', $row['name'])
            ->where('machinery_id', $machinery->getAttribute('id'))
            ->first();

        if (is_null($machinerySubCategory)) {
            return new MachinerySubCategory([
                'machinery_id' => $machinery->getAttribute('id'),
                'code' => $row['code'],
                'name' => $row['name'],
            ]);
        }

        return null;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.code' => 'required',
            '*.machinery' => [
                'required',
                'exists:machineries,name',
            ]
        ];
    }
}
