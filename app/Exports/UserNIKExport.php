<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserNIKExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection([
            // Optional: Add sample data rows here
            [
                'group' => 'PI / PKG / PKC',
                'user_code' => '1180041',
                'user_type' => 'NIK',
                'license_type' => 'CA / CB',
                'last_login' => '2024-03-22 09:00:00',
                'valid_from' => '2022-01-01 00:00:00',
                'valid_to' => '2028-12-31 23:59:59',
                'note' => 'ini adalah sample, hapus bagian ini sebelum mengupload data ',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'group',
            'user_code',
            'user_type',
            'license_type',
            'last login',
            'valid_from',
            'valid_to',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 10,
            'C' => 10,
            'D' => 15,
            'E' => 20,
            'F' => 20,
            'G' => 20,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'C6E2B5',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => [
                            'argb' => '000000',
                        ],
                    ],
                ],
            ],

            // Format the second row with example data.
            2 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => '66FFFF',
                    ],
                ],
                'font' => [
                    'color' => [
                        'argb' => 'FF0000',
                    ],
                ],
            ],
        ];
    }
}
