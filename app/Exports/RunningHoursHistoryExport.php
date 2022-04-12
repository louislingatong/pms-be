<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselMachinery;
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

class RunningHoursHistoryExport implements FromArray, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, WithColumnWidths
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
        return $this->vesselMachinery->runningHoursHistory->toArray();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Machinery',
            'Running Hours',
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
        /** @var VesselMachinery $machinery */
        $machinery = $this->vesselMachinery->machinery;
        /** @var User $creator */
        $creator = User::find($row['creator_id']);
        return [
            $machinery->getAttribute('name'),
            $row['running_hours'],
            Carbon::create($row['created_at'])->format('d-M-Y'),
            $creator->getAttribute('full_name'),
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        /** @var Vessel $vessel */
        $vessel = $this->vesselMachinery->vessel;

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
                $event->sheet->setCellValue('A1', 'Name of Vessel:');
                $event->sheet->setCellValue('A2', 'Vessel\'s Flag:');

                $event->sheet->setCellValue('C1', 'Class:');
                $event->sheet->setCellValue('C2', 'IMO No.:');

                $event->sheet->setCellValue('B1', $vessel->getAttribute('name'));
                $event->sheet->setCellValue('B2', $vessel->getAttribute('flag'));

                $event->sheet->setCellValue('D1', '');
                $event->sheet->setCellValue('D2', $vessel->getAttribute('imo_no'));
            },
            AfterSheet::class => function (AfterSheet $event) use (
                $style,
                $fontBoldStyle,
                $alignRightStyle,
                $fillLightYellowStyle,
                $borderBottomStyle,
                $headerStyle
            ) {
                $event->sheet->getStyle('A:D')->applyFromArray($style);
                $event->sheet->getStyle('A1:D2')->applyFromArray($fontBoldStyle);
                $event->sheet->getStyle('A1:A2')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('C1:C2')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('B1')->applyFromArray(array_merge($fillLightYellowStyle, $borderBottomStyle));
                $event->sheet->getStyle('B2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('D1')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('D2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('A4:D4')->applyFromArray($headerStyle);
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
            'A' => 25,
            'B' => 30,
            'C' => 25,
            'D' => 30,
        ];
    }
}
