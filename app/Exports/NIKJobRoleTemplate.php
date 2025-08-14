<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NIKJobRoleTemplate implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
  /**
   * @return \Illuminate\Support\Collection
   */
  public function collection()
  {
    return new Collection([
      // Optional: Add sample data rows here
    ]);
  }

  /**
   * @return array
   */
  public function headings(): array
  {
    return [
      'nik',
      'job_role',
    ];
  }

  /**
   * @return array
   */
  public function columnWidths(): array
  {
    return [
      'A' => 15,
      'B' => 30,
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
    ];
  }
}
