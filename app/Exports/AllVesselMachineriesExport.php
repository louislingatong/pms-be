<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllVesselMachineriesExport implements FromArray, WithMultipleSheets
{
    protected $allVesselMachineries;

    public function __construct(Collection $allVesselMachineries)
    {
        $this->allVesselMachineries = $allVesselMachineries;
    }

    public function array(): array
    {
        return $this->allVesselMachineries->toArray();
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->allVesselMachineries as $vesselMachinery) {
            $sheets[] = new VesselMachineryExport($vesselMachinery);
        }

        return $sheets;
    }
}
