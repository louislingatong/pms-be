<?php

namespace App\Exports;

use App\Models\Interval;
use App\Models\Machinery;
use App\Models\MachineryMaker;
use App\Models\MachineryModel;
use App\Models\MachinerySubCategory;
use App\Models\MachinerySubCategoryDescription;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VesselMachineryExport implements FromArray, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $vesselMachinery;

    public function __construct(VesselMachinery $vesselMachinery)
    {
        $this->vesselMachinery = $vesselMachinery;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->vesselMachinery->subCategories->toArray();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Code',
            'Sub Category',
            'Description',
            'Intervals',
            'Commissioning Date',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $vesselSubCategory = VesselMachinerySubCategory::find($row['id']);
        /** @var MachinerySubCategory $subCategory */
        $subCategory = $vesselSubCategory->subCategory;
        /** @var MachinerySubCategoryDescription $description */
        $description = $vesselSubCategory->description;
        /** @var Interval $interval */
        $interval = $vesselSubCategory->interval;
        /** @var VesselMachinery $machinery */
        return [
            $row['code'],
            $subCategory->getAttribute('name'),
            $description->getAttribute('name'),
            $interval->getAttribute('name'),
            Carbon::create($row['installed_date'])->format('d-M-Y'),
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $vesselMachinery = $this->vesselMachinery;
        /** @var MachineryModel $model */
        $model = $vesselMachinery->model;
        /** @var MachineryMaker $maker */
        $maker = $vesselMachinery->maker;
        /** @var Machinery $machinery */
        $machinery = $vesselMachinery->machinery;
        /** @var Vessel $vessel */
        $vessel = $vesselMachinery->vessel;

        $borderBottomStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];

        $fontBoldStyle = [
            'font' => [
                'bold' => true,
            ],
        ];

        $fillLightYellowStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'FFFFFF99',
                ],
            ],
        ];

        $fillGrayStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'FFA0A0A0',
                ],
            ],
        ];

        $alignRightStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ];

        return [
            BeforeSheet::class => function(BeforeSheet $event) use ($model, $maker, $machinery, $vessel) {
                $event->sheet->setCellValue('B1', 'Name of Vessel:');
                $event->sheet->setCellValue('B2', 'Vessel\'s Flag:');
                $event->sheet->setCellValue('B3', 'Name of Machinery:');
                $event->sheet->setCellValue('B4', 'Model:');
                $event->sheet->setCellValue('B5', 'Maker:');

                $event->sheet->setCellValue('D1', 'Class:');
                $event->sheet->setCellValue('D2', 'IMO No.:');
                $event->sheet->setCellValue('D3', 'Machinery Code No.:');
                $event->sheet->setCellValue('D4', 'Reporting Date:');

                $event->sheet->setCellValue('C1', $vessel->getAttribute('name'));
                $event->sheet->setCellValue('C2', $vessel->getAttribute('flag'));
                $event->sheet->setCellValue('C3', $machinery->getAttribute('name'));
                $event->sheet->setCellValue('C4', $model->getAttribute('name'));
                $event->sheet->setCellValue('C5', $maker->getAttribute('name'));

                $event->sheet->setCellValue('E1', '');
                $event->sheet->setCellValue('E2', $vessel->getAttribute('imo_no'));
                $event->sheet->setCellValue('E3', $machinery->getAttribute('code_name'));
                $event->sheet->setCellValue('E4', Carbon::now()->format('d-M-Y'));
            },
            AfterSheet::class => function(AfterSheet $event) use (
                $vesselMachinery,
                $fontBoldStyle,
                $borderBottomStyle,
                $fillLightYellowStyle,
                $fillGrayStyle,
                $alignRightStyle
            ) {
                $event->sheet->getStyle('A1:E7')->applyFromArray($fontBoldStyle);
                $event->sheet->getStyle('A7:E7')->applyFromArray($fillGrayStyle);
                $event->sheet->getStyle('B1:B5')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('D1:D5')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('C1')->applyFromArray(array_merge($fillLightYellowStyle, $borderBottomStyle));
                $event->sheet->getStyle('C2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('C3')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('C4')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('C5')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E1')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E3')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E4')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E8:E'.(count($vesselMachinery->subCategories) + 7))
                    ->applyFromArray($fillLightYellowStyle);
            },
        ];
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A7';
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'B' => 25,
            'C' => 30,
            'D' => 25,
            'E' => 30,
        ];
    }
}
