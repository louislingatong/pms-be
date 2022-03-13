<?php

namespace App\Imports;

use App\Models\Machinery;
use App\Models\MachineryMaker;
use App\Models\MachineryModel;
use App\Models\Rank;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VesselMachineryImport implements ToModel, WithHeadingRow, SkipsOnError, WithValidation, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     * @return VesselMachinery
     */
    public function model(array $row): VesselMachinery
    {
        $installedDate = Carbon::create($row['installed_date']);

        /** @var Vessel $vessel */
        $vessel = Vessel::where('name', $row['vessel'])->first();

        /** @var Machinery $machinery */
        $machinery = Machinery::where('name', $row['machinery'])->first();

        /** @var Rank $inchargeRank */
        $inchargeRank = Rank::where('name', $row['incharge_rank'])->first();

        if (isset($row['model'])) {
            /** @var MachineryModel $machineryModel */
            $machineryModel = MachineryModel::findOrCreateModelByName($row['model']);
        }
        if (isset($row['maker'])) {
            /** @var MachineryMaker $machineryMaker */
            $machineryMaker = MachineryMaker::findOrCreateMakerByName($row['maker']);
        }

        return new VesselMachinery([
            'installed_date'=> $installedDate,
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

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.installed_date' => [
                'required',
                'date',
                'date_format:d-M-Y',
            ],
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
