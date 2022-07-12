<?php

namespace App\Imports;

use App\Models\Machinery;
use App\Models\VesselDepartment;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MachineryImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return Machinery
     */
    public function model(array $row): ?Machinery
    {
        $machinery = Machinery::where('code_name', $row['code_name'])
            ->where('name', $row['name'])
            ->first();

        if (is_null($machinery)) {
            /** @var VesselDepartment $department */
            $department = VesselDepartment::where('name', $row['department'])->first();

            return new Machinery([
                'vessel_department_id' => $department->getAttribute('id'),
                'code_name' => $row['code_name'],
                'name' => $row['name'],
            ]);
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.department' => [
                'required',
                'exists:vessel_departments,name',
            ]
        ];
    }
}
