<?php

namespace App\Exports;

use App\Models\Vessel;
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

class RunningHoursExport implements FromArray, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $runningHours;
    protected $vesselName;

    public function __construct(array $runningHours, string $vesselName)
    {
        $this->runningHours = $runningHours;
        $this->vesselName = $vesselName;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->runningHours;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Code',
            'Machinery',
            'Running Hours',
            'Updating Date',
            'Encoded Date',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['machinery']['code_name'],
            $row['machinery']['name'],
            $row['current_running_hour']['running_hours'] ?: '0',
            $row['current_running_hour']['updating_date']
                ? Carbon::create($row['current_running_hour']['updating_date'])->format('d-M-Y')
                : '',
            $row['current_running_hour']['created_at']
                ? Carbon::create($row['current_running_hour']['created_at'])->format('d-M-Y')
                : '',
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        /** @var Vessel $vessel */
        $vessel = Vessel::where('name', '=', $this->vesselName)->first();

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
                $event->sheet->getStyle('A:E')->applyFromArray($style);
                $event->sheet->getStyle('A1:E2')->applyFromArray($fontBoldStyle);
                $event->sheet->getStyle('B1:B2')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('D1:D2')->applyFromArray($alignRightStyle);
                $event->sheet->getStyle('C1')->applyFromArray(array_merge($fillLightYellowStyle, $borderBottomStyle));
                $event->sheet->getStyle('C2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E1')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('E2')->applyFromArray($borderBottomStyle);
                $event->sheet->getStyle('A4:E4')->applyFromArray($headerStyle);
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
        ];
    }
}
