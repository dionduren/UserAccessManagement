<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Sheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TcodeTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Define the headings for the template.
     */
    public function headings(): array
    {
        return [
            'Company ID',
            'Code',
            'Description',
            'Single Role Names',
        ];
    }

    /**
     * Optional: Provide a sample row format
     */
    public function collection()
    {
        return new Collection([
            [
                '1',
                'TcodeSample',
                'Sample Tcode',
                'SingleRole1, SingleRole2',
            ],
        ]);
    }

    /**
     * Apply column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 35,
            'D' => 30,
        ];
    }

    /**
     * Apply styles to the Excel sheet.
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['argb' => '000000FF'], // Dark Blue background for headers
            ],
        ]);

        $sheet->getStyle('A1:D1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
