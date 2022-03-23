<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RunningHoursExport implements FromArray, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected $runningHours;

    public function __construct(array $runningHours)
    {
        $this->runningHours = $runningHours;
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
        $style = [
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
            AfterSheet::class => function(AfterSheet $event) use ($style) {
                $event->sheet->getStyle('A1:E1')->applyFromArray($style);
            }
        ];
    }
}
