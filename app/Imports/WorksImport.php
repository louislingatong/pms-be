<?php

namespace App\Imports;

use App\Exceptions\MachinerySubCategoryNotFoundException;
use App\Exceptions\VesselMachineryNotFoundException;
use App\Exceptions\VesselMachinerySubCategoryNotFoundException;
use App\Models\MachinerySubCategory;
use App\Models\User;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use App\Models\Work;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class WorksImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return Work
     * @throws
     */
    public function model(array $row): Work
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::whereHas('vessel', function ($q) use ($row) {
            $q->where('name', '=', $row['vessel']);
        })->whereHas('machinery', function ($q) use ($row) {
            $q->where('name', '=', $row['machinery']);
        })->first();

        if (!($vesselMachinery instanceof VesselMachinery)) {
            throw new VesselMachineryNotFoundException();
        }

        /** @var MachinerySubCategory $machinerySubCategory */
        $machinerySubCategory = $vesselMachinery->machinery->subCategories()->where('name', $row['name'])->first();

        if (!($machinerySubCategory instanceof MachinerySubCategory)) {
            throw new MachinerySubCategoryNotFoundException();
        }

        /** @var VesselMachinerySubCategory $vesselMachinerySubCategory */
        $vesselMachinerySubCategory = VesselMachinerySubCategory::where('vessel_machinery_id', $vesselMachinery->getAttribute('id'))
            ->where('machinery_sub_category_id', $machinerySubCategory->getAttribute('id'))
            ->first();

        if (!($vesselMachinerySubCategory instanceof VesselMachinerySubCategory)) {
            throw new VesselMachinerySubCategoryNotFoundException();
        }

        /** @var User $user */
        $user = auth()->user();

        return new Work([
            'vessel_machinery_sub_category_id' => $vesselMachinerySubCategory->getAttribute('id'),
            'last_done' => $row['last_done_date'] ? Carbon::create($row['last_done_date']) : null,
            'running_hours' => $row['last_done_running_hours'] ?: null,
            'creator_id' => $user->getAttribute('id'),
            'instructions' => $row['instructions'] ?: null,
            'remarks' => $row['remarks'] ?: null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.vessel' => [
                'required',
                'exists:vessels,name'
            ],
            '*.machinery' => [
                'required',
                'exists:machineries,name'
            ],
            '*.name' => [
                'required',
                'exists:machinery_sub_categories,name'
            ],
            '*.last_done_date' => [
                'nullable',
                'date',
                'date_format:d-M-y',
            ],
        ];
    }
}
