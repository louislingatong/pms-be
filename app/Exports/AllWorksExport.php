<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllWorksExport implements FromArray, WithMultipleSheets
{
    protected $allWorks;
    protected $vesselName;

    public function __construct(array $allWorks, string $vesselName)
    {
        $this->allWorks = $allWorks;
        $this->vesselName = $vesselName;
    }

    public function array(): array
    {
        return $this->allWorks;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->allWorks as $key => $work) {
            $sheets[] = new WorkExport($work, $this->vesselName, $key);
        }

        return $sheets;
    }
}
