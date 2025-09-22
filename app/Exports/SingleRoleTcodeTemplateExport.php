<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SingleRoleTcodeTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SingleRoleTcodeUploadSheet(),
            new SingleRoleTcodeInfoSheet(),
        ];
    }
}

class SingleRoleTcodeUploadSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function headings(): array
    {
        return [
            'single_role',
            'single_role_description',
            'tcode',
            'tcode_description',
        ];
    }

    public function collection()
    {
        return new Collection([
            [
                'single_role' => '',
                'single_role_description' => '',
                'tcode' => '',
                'tcode_description' => '',
            ]
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));
                $header = "A1:{$lastCol}1";
                $range  = "A1:{$lastCol}" . (1 + $this->collection()->count());

                $event->sheet->getStyle($header)->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => '4472C4']
                    ],
                ]);
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                ]);
                $event->sheet->getDelegate()->freezePane('A2');
            }
        ];
    }
}

class SingleRoleTcodeInfoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'DATA_OOC';
    }

    public function headings(): array
    {
        return [
            'single_role',
            'single_role_description',
            'tcode',
            'tcode_description',
        ];
    }

    public function collection()
    {
        return new Collection([
            [
                'single_role' => 'Nama Single Role yang digunakan',
                'single_role_description' => 'Penjelasan fungsi Single Role tersebut',
                'tcode' => 'Nama TCODE yang dipetakan ke Single Role',
                'tcode_description' => 'Penjelasan fungsi TCODE tersebut',
            ]
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['wrapText' => true]],
        ];
    }
}
