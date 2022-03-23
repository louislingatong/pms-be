<?php

namespace App\Exports;

use App\Models\Interval;
use App\Models\MachinerySubCategoryDescription;
use App\Models\User;
use App\Models\VesselMachinery;
use App\Models\VesselMachinerySubCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WorkHistoryExport implements FromArray, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, ShouldAutoSize
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
            'Commissioning Date',
            'Last Done',
            'Running Hours',
            'Due Date',
            'Instructions',
            'Encoded Data',
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
        /** @var User $creator */
        $creator = User::find($row['creator_id']);
        return [
            Carbon::create($this->vesselMachinerySubCategory->getAttribute('installed_date'))->format('d-M-Y'),
            Carbon::create($row['last_done'])->format('d-M-Y'),
            $row['running_hours'] ?: '',
            Carbon::create($this->vesselMachinerySubCategory->getAttribute('due_date'))->format('d-M-Y'),
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
        $code = $this->vesselMachinerySubCategory->getAttribute('code');
        /** @var VesselMachinerySubCategory $subCategory */
        $subCategory = $this->vesselMachinerySubCategory->subCategory;
        /** @var MachinerySubCategoryDescription $description */
        $description = $this->vesselMachinerySubCategory->description;
        /** @var Interval $interval */
        $interval = $this->vesselMachinerySubCategory->interval;

        $fontBoldStyle = [
            'font' => [
                'bold' => true,
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
            BeforeSheet::class => function(BeforeSheet $event) use ($code, $subCategory, $description, $interval) {
                $event->sheet->setCellValue('A1', 'Code');
                $event->sheet->setCellValue('A2', 'Sub Category');
                $event->sheet->setCellValue('A3', 'Description');
                $event->sheet->setCellValue('A4', 'Intervals');

                $event->sheet->setCellValue('B1', $code);
                $event->sheet->setCellValue('B2', $subCategory->getAttribute('name'));
                $event->sheet->setCellValue('B3', $description->getAttribute('name'));
                $event->sheet->setCellValue('B4', $interval->getAttribute('name'));
            },
            AfterSheet::class => function(AfterSheet $event) use ($fontBoldStyle, $fillGrayStyle) {
                $event->sheet->getStyle('A6:G6')->applyFromArray(array_merge($fontBoldStyle, $fillGrayStyle));
                $event->sheet->getStyle('A1:A4')->applyFromArray($fontBoldStyle);
            }
        ];
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A6';
    }
}
