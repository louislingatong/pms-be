<?php

namespace App\Imports;

use App\Models\Interval;
use App\Models\IntervalUnit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class IntervalImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return Interval
     * @throws
     */
    public function model(array $row): Interval
    {
        /** @var IntervalUnit $intervalUnit */
        $intervalUnit = IntervalUnit::where('name', $row['unit'])->first();

        return new Interval([
            'interval_unit_id' => $intervalUnit->getAttribute('id'),
            'value' => $row['value'],
            'name' => $row['name'],
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.value' => 'nullable',
            '*.unit' => [
                'required_with:value',
                'exists:interval_units,name',
            ],
            '*.name' => 'required_without:value',
        ];
    }
}
