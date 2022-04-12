<?php

namespace App\Exports;

use App\Models\Interval;
use App\Models\MachinerySubCategoryDescription;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
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

class WorkHistoryExport implements FromArray, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $vesselMachinerySubCategory;

    public function __construct(VesselMachinerySubCategory $vesselMachinerySubCategory)
    {
        $this->vesselMachinerySubCategory = $vesselMachinerySubCategory;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->vesselMachinerySubCategory->worksHistory->toArray();
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
            'Last Done (DD-MMM-YYYY)',
            'Last Done (Run Hours)',
            'Instructions',
            'Encoded Date',
            'Encoded By',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $code = $this->vesselMachinerySubCategory->getAttribute('code');
        /** @var VesselMachinerySubCategory $subCategory */
        $subCategory = $this->vesselMachinerySubCategory->subCategory;
        /** @var MachinerySubCategoryDescription $description */
        $description = $this->vesselMachinerySubCategory->description;
        /** @var Interval $interval */
        $interval = $this->vesselMachinerySubCategory->interval;
        /** @var User $creator */
        $creator = User::find($row['creator_id']);
        return [
            $code,
            $subCategory->getAttribute('name'),
            $description->getAttribute('name'),
            $interval->getAttribute('name'),
            Carbon::create($row['last_done'])->format('d-M-Y'),
            $row['running_hours'] ?: '',
            $row['instructions'] ?: '',
            Carbon::create($row['created_at'])->format('d-M-Y'),
            $creator->getAttribute('full_name'),
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = $this->vesselMachinerySubCategory->vesselMachinery;
        /** @var Vessel $vessel */
        $vessel = $vesselMachinery->vessel;

        $style = [
            'alignment' => [
                'wrapText' => true,
                'vertical' => Alignment::VERTICAL_TOP,
            ]
        ];

        $fontBoldStyle = [
            'font' => [
                'bold' => true,
            ],
        ];

        $alignRightStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
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

        $borderBottomStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];

        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'FFA0A0A0',
                ],
            ],
        ];

        return [
            BeforeSheet::class => function (BeforeSheet $event) use ($vessel) {
                $event->sheet->setCellValue('B1', 'Name of Vessel:');
                $event->sheet->setCellValue('B2', 'Vessel\'s Flag:');

                $event->sheet->setCellValue('D1', 'Class:');
                $event->sheet->setCellValue('D2', 'IMO No.:');

                $event->sheet->setCellValue('C1', $vessel->getAttribute('name'));
                $event->sheet->setCellValue('C2', $vessel->getAttribute('flag'));

                $event->sheet->setCellValue('E1', '');
                $event->sheet->setCellValue('E2', $vessel->getAttribute('imo_no'));
            },
            AfterSheet::class => function (AfterSheet $event) use (
                $style,
                $fontBoldStyle,
                $alignRightStyle,
                $fillLightYellowStyle,
                $borderBottomStyle,
                $headerStyle
            ) {
                $event->sheet->getStyle('A:I')->applyFromArray($style);
                $event->sheet->getStyle('A1:I2')->applyFromArray($fontBoldStyle);
                $event->sheet->getStyle('B1:B2')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('D1:D2')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('C1')->applyFromArray(array_merge($fillLightYellowStyle, $borderBottomStyle));
                $event->sheet->getStyle('C2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E1')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
            }
        ];
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A4';
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
            'F' => 13,
            'G' => 12,
            'H' => 15,
            'I' => 12,
        ];
    }
}
