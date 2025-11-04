<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArrayExport implements WithMultipleSheets
{
    protected $data;
    protected $metadata;

    /**
     * Constructor - Backward compatible
     * Accepts either (array $data, array $metadata) or just (array $data)
     */
    public function __construct(array $data, array $metadata = [])
    {
        $this->data = $data;
        $this->metadata = $metadata;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Main data sheet
        $sheets[] = new class($this->data) implements FromArray, WithStyles
        {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function styles(Worksheet $sheet)
            {
                $sheet->setTitle('Data'); // Add sheet name

                // Header row styling (only if data has rows)
                if (count($this->data) > 0) {
                    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4472C4']
                        ],
                    ]);

                    // Auto-size columns
                    foreach (range('A', $sheet->getHighestColumn()) as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    // Freeze first row
                    $sheet->freezePane('A2');
                }

                return [];
            }
        };

        // Metadata sheet (only if metadata exists)
        if (!empty($this->metadata)) {
            $sheets[] = new class($this->metadata) implements FromArray, WithStyles
            {
                protected $metadata;

                public function __construct($metadata)
                {
                    $this->metadata = $metadata;
                }

                public function array(): array
                {
                    return $this->metadata;
                }

                public function styles(Worksheet $sheet)
                {
                    $sheet->setTitle('Export Info');

                    // Header styling
                    $sheet->getStyle('A1:B1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14]
                    ]);

                    // Column widths
                    $sheet->getColumnDimension('A')->setWidth(25);
                    $sheet->getColumnDimension('B')->setWidth(50);

                    return [];
                }
            };
        }

        return $sheets;
    }
}

// <?php

// namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromArray;

// class ArrayExport implements FromArray
// {
//     protected $array;

//     public function __construct(array $array)
//     {
//         $this->array = $array;
//     }

//     public function array(): array
//     {
//         return $this->array;
//     }
// }
