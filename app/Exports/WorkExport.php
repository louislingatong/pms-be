<?php

namespace App\Exports;

use App\Models\Machinery;
use App\Models\MachineryMaker;
use App\Models\MachineryModel;
use App\Models\RunningHour;
use App\Models\Vessel;
use App\Models\VesselMachinery;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WorkExport implements FromArray, WithTitle, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $works;
    protected $vesselName;
    protected $machineryName;

    public function __construct(Collection $works, string $vesselName, string $machineryName)
    {
        $this->works = $works;
        $this->vesselName = $vesselName;
        $this->machineryName = $machineryName;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->works->toArray();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->machineryName;
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
            'Last Done (DD-MMM-YYYY)',
            'Last Done (Run Hours)',
            'Due Date',
            'Status',
            'Instructions',
            'Remarks',
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
            $row['code'],
            $row['sub_category']['name'],
            $row['description']['name'],
            $row['interval']['name'],
            Carbon::create($row['installed_date'])->format('d-M-Y'),
            $row['current_work']['last_done']
                ? Carbon::create($row['current_work']['last_done'])->format('d-M-Y')
                : '',
            $row['current_work']['running_hours'] ?: '',
            $row['due_date'] ? Carbon::create($row['due_date'])->format('d-M-Y') : '',
            $row['due_date'] ? $this->getStatus($row['due_date']) : config('work.statuses.dry_dock'),
            $row['current_work']['instructions'] ?: '',
            $row['current_work']['remarks'] ?: '',
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        /** @var Vessel $vessel */
        $vessel = Vessel::where('name', '=', $this->vesselName)->first();

        /** @var Machinery $machinery */
        $machinery = Machinery::where('name', '=', $this->machineryName)->first();
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = VesselMachinery::where('vessel_id', $vessel->getAttribute('id'))
            ->where('machinery_id', $machinery->getAttribute('id'))
            ->first();
        /** @var MachineryModel $model */
        $model = $vesselMachinery->model;
        /** @var MachineryMaker $maker */
        $maker = $vesselMachinery->maker;
        /** @var RunningHour $runningHour */
        $runningHour = $vesselMachinery->currentRunningHour;

        $wrapTextAlignTopStyle = [
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


        $fillGrayStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'FFA0A0A0',
                ],
            ],
        ];

        return [
            BeforeSheet::class => function (BeforeSheet $event) use ($vessel, $machinery, $model, $maker, $runningHour) {
                $event->sheet->setCellValue('B1', 'Name of Vessel:');
                $event->sheet->setCellValue('B2', 'Vessel\'s Flag:');
                $event->sheet->setCellValue('B3', 'Name of Machinery:');
                $event->sheet->setCellValue('B4', 'Model:');
                $event->sheet->setCellValue('B5', 'Maker:');

                $event->sheet->setCellValue('D1', 'Class:');
                $event->sheet->setCellValue('D2', 'IMO No.:');
                $event->sheet->setCellValue('D3', 'Machinery Code No.:');
                $event->sheet->setCellValue('D4', 'Running Hours:');
                $event->sheet->setCellValue('D5', 'Date Updated:');

                $event->sheet->setCellValue('C1', $vessel->getAttribute('name'));
                $event->sheet->setCellValue('C2', $vessel->getAttribute('flag'));
                $event->sheet->setCellValue('C3', $machinery->getAttribute('name'));
                $event->sheet->setCellValue('C4', ($model instanceof MachineryModel)
                    ? $model->getAttribute('name')
                    : ''
                );
                $event->sheet->setCellValue('C5', ($maker instanceof MachineryMaker)
                    ? $maker->getAttribute('name')
                    : ''
                );

                $event->sheet->setCellValue('E1', '');
                $event->sheet->setCellValue('E2', $vessel->getAttribute('imo_no'));
                $event->sheet->setCellValue('E3', $machinery->getAttribute('code_name'));
                if ($runningHour instanceof RunningHour) {
                    $event->sheet->setCellValue('E4', $runningHour->getAttribute('running_hours'));
                    $event->sheet->setCellValue('E5', $runningHour->getAttribute('updating_date'));
                }
            },
            AfterSheet::class => function (AfterSheet $event) use (
                $wrapTextAlignTopStyle,
                $fontBoldStyle,
                $alignRightStyle,
                $fillLightYellowStyle,
                $borderBottomStyle,
                $fillGrayStyle
            ) {
                $event->sheet->getStyle('A:K')->applyFromArray($wrapTextAlignTopStyle);
                $event->sheet->getStyle('A1:K7')->applyFromArray($fontBoldStyle);
                $event->sheet->getStyle('A7:K7')->applyFromArray($fillGrayStyle);
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
                $event->sheet->getStyle('E4')->applyFromArray(array_merge($fillLightYellowStyle, $borderBottomStyle));
                $event->sheet->getStyle('E5')->applyFromArray(array_merge($fillLightYellowStyle, $borderBottomStyle));
            }
        ];
    }

    /**
     * Get the work status
     *
     * @param string $dueDate
     * @return string
     */
    public function getStatus(string $dueDate): string
    {
        $currentDate = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($dueDate);
        if ($currentDate->greaterThan($dueDate)) {
            return config('work.statuses.overdue');
        } else if ($currentDate->isSameDay($dueDate)) {
            return config('work.statuses.due');
        } else if ($currentDate->diffInDays($dueDate) <= config('work.warning_days')) {
            return config('work.statuses.warning');
        } else if ($currentDate->lessThan($dueDate)) {
            return config('work.statuses.jobs_done');
        } else {
            return '';
        }
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
            'F' => 12,
            'G' => 12,
            'J' => 12,
        ];
    }
}
