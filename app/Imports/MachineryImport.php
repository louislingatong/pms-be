<?php

namespace App\Imports;

use App\Models\Machinery;
use App\Models\VesselDepartment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MachineryImport implements ToCollection, WithHeadingRow, SkipsOnError, WithValidation, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            /** @var VesselDepartment $department */
            $department = VesselDepartment::where('name', $row['department'])->first();

            $formData = [
                'vessel_department_id' => $department->getAttribute('id'),
                'name' => $row['name'],
                'code_name' => $row['code_name'],
            ];

            Machinery::create($formData);
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.department' => ['required', 'exists:vessel_departments,name']
        ];
    }
}
