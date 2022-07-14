<?php

namespace App\Imports;

use App\Models\Machinery;
use App\Models\MachineryMaker;
use App\Models\MachineryModel;
use App\Models\Rank;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VesselMachineryImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return VesselMachinery
     */
    public function model(array $row): ?VesselMachinery
    {
        /** @var Vessel $vessel */
        $vessel = Vessel::where('name', $row['vessel'])->first();

        /** @var Machinery $machinery */
        $machinery = Machinery::where('name', $row['machinery'])->first();

        /** @var Rank $inchargeRank */
        $inchargeRank = Rank::where('name', $row['incharge_rank'])->first();

        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::where('vessel_id', $vessel->getAttribute('id'))
            ->where('machinery_id', $machinery->getAttribute('id'))
            ->where('incharge_rank_id', $inchargeRank->getAttribute('id'))
            ->first();

        if (is_null($vesselMachinery)) {
            if ($row['model']) {
                /** @var MachineryModel $machineryModel */
                $machineryModel = MachineryModel::firstOrCreate(['name' => $row['model']]);
            }
            if ($row['maker']) {
                /** @var MachineryMaker $machineryMaker */
                $machineryMaker = MachineryMaker::firstOrCreate(['name' => $row['maker']]);
            }

            return new VesselMachinery([
                'vessel_id' => $vessel->getAttribute('id'),
                'machinery_id' => $machinery->getAttribute('id'),
                'incharge_rank_id' => $inchargeRank->getAttribute('id'),
                'machinery_model_id' => (isset($machineryModel) && ($machineryModel instanceof MachineryModel))
                    ? $machineryModel->getAttribute('id')
                    : null,
                'machinery_maker_id' => (isset($machineryMaker) && ($machineryMaker instanceof MachineryMaker))
                    ? $machineryMaker->getAttribute('id')
                    : null,
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
            '*.vessel' => [
                'required',
                'exists:vessels,name',
            ],
            '*.machinery' => [
                'required',
                'exists:machineries,name',
            ],
            '*.incharge_rank' => [
                'required',
                'exists:ranks,name',
            ],
        ];
    }
}
