<?php

namespace App\Exports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CompositeSingleRoleTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CompositeSingleUploadSheet(),
            new CompositeSingleInfoSheet(),
        ];
    }
}

class CompositeSingleUploadSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function headings(): array
    {
        return [
            'company_code',
            'composite_role',
            'composite_role_description',
            'single_role',
            'single_role_description',
        ];
    }

    public function collection()
    {
        // one empty example row
        return new Collection([
            [
                'company_code' => '',
                'composite_role' => '',
                'composite_role_description' => '',
                'single_role' => '',
                'single_role_description' => '',
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
                $headerRange = "A1:{$lastCol}1";
                $dataRange   = "A1:{$lastCol}" . (1 + $this->collection()->count());

                $event->sheet->getStyle($headerRange)->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => '4472C4']
                    ],
                ]);

                $event->sheet->getStyle($dataRange)->applyFromArray([
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

class CompositeSingleInfoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'DATA_OOC';
    }

    public function headings(): array
    {
        return [
            'company_code',
            'company_name',
            'composite_role',
            'composite_role_description',
            'single_role',
            'single_role_description',
        ];
    }

    public function collection()
    {
        $rows = [];

        // Row 1: Column explanations (can be kept as a reference)
        $rows[] = [
            'company_code' => 'Kode perusahaan sesuai master',
            'company_name' => 'Nama perusahaan (referensi)',
            'composite_role' => 'Nama Composite Role yang digunakan',
            'composite_role_description' => 'Nama Job Role yang terpasang pada Composite Role tersebut',
            'single_role' => 'Nama Single Role yang terhubung pada Composite Role terkait',
            'single_role_description' => 'Penjelasan fungsi Single Role tersebut',
        ];

        // Then list companies (placeholders in other cols)
        $companies = Company::select('company_code', 'nama')->orderBy('company_code')->get();
        foreach ($companies as $c) {
            $rows[] = [
                'company_code' => $c->company_code,
                'company_name' => $c->nama,
                'composite_role' => '',
                'composite_role_description' => '',
                'single_role' => '',
                'single_role_description' => '',
            ];
        }

        return new Collection($rows);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['wrapText' => true],
            ],
        ];
    }
}
