<?php

namespace App\Exports;

use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\Company;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JobRoleCompositeTemplateExport implements WithMultipleSheets
{
    public function __construct(private ?string $companyCode = null) {}

    public function sheets(): array
    {
        return [
            new TemplateSheet(),          // Sheet title set inside class
            new HierarchyMasterSheet($this->companyCode),   // Renamed & joined master sheet
        ];
    }
}

class TemplateSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    private array $rows;

    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function __construct()
    {
        $this->rows = [
            [
                'company'        => '',
                'kompartemen_id' => '',
                'kompartemen'    => '',
                'departemen_id'  => '',
                'departemen'     => '',
                'job_function'   => '',
                'composite_role' => '',
            ],
        ];
    }


    public function collection()
    {
        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return [
            'company',
            'kompartemen_id',
            'kompartemen',
            'departemen_id',
            'departemen',
            'job_function',
            'composite_role',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ // header row
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                    'name' => 'Calibri',
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical'   => 'center',
                    'wrapText'   => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $headingsCount = count($this->headings());
                $lastCol = Coordinate::stringFromColumnIndex($headingsCount);
                $headerRange = "A1:{$lastCol}1";
                $dataLastRow = 1 + count($this->rows);
                $fullRange = "A1:{$lastCol}{$dataLastRow}";

                $event->sheet->getStyle($headerRange)->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'color'    => ['rgb' => '4472C4'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color'       => ['rgb' => 'FFFFFF'],
                        ],
                    ],
                ]);

                $event->sheet->getStyle($fullRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }
}

class HierarchyMasterSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private ?string $companyCode = null) {}

    public function title(): string
    {
        return 'MASTER_UNIT_KERJA';
    }

    public function collection()
    {
        // Eager load kompartemen and their departemen
        $companies = Company::where('company_code', $this->companyCode)->with([
            'kompartemen.departemen' => function ($q) {
                $q->select('departemen_id', 'kompartemen_id', 'company_id', 'nama');
            },
            'kompartemen' => function ($q) {
                $q->select('kompartemen_id', 'company_id', 'nama');
            },
            // Load all departemen for the company
            'departemen' => function ($q) {
                $q->select('departemen_id', 'company_id', 'kompartemen_id', 'nama');
            },
        ])->select('company_code', 'nama')->get();

        $rows = [];

        foreach ($companies as $company) {
            $processedDepartemen = collect(); // Keep track of processed departemen

            // If company has kompartemen
            if ($company->kompartemen->count()) {
                foreach ($company->kompartemen as $komp) {
                    // If kompartemen has departemen
                    if ($komp->departemen->count()) {
                        foreach ($komp->departemen as $dept) {
                            $rows[] = [
                                'company_code'     => $company->company_code,
                                'company_name'     => $company->nama,
                                'kompartemen_id'   => $komp->kompartemen_id,
                                'kompartemen_name' => $komp->nama,
                                'departemen_id'    => $dept->departemen_id,
                                'departemen_name'  => $dept->nama,
                            ];
                            $processedDepartemen->push($dept->departemen_id);
                        }
                    } else {
                        // Kompartemen without departemen
                        $rows[] = [
                            'company_code'     => $company->company_code,
                            'company_name'     => $company->nama,
                            'kompartemen_id'   => $komp->kompartemen_id,
                            'kompartemen_name' => $komp->nama,
                            'departemen_id'    => null,
                            'departemen_name'  => null,
                        ];
                    }
                }
            }

            // Add departemen that don't belong to any kompartemen (kompartemen_id is null)
            // or departemen that weren't processed above
            foreach ($company->departemen as $dept) {
                // Only add if not already processed and has no kompartemen
                if (
                    !$processedDepartemen->contains($dept->departemen_id) &&
                    (is_null($dept->kompartemen_id) || $dept->kompartemen_id === '')
                ) {
                    $rows[] = [
                        'company_code'     => $company->company_code,
                        'company_name'     => $company->nama,
                        'kompartemen_id'   => null,
                        'kompartemen_name' => null,
                        'departemen_id'    => $dept->departemen_id,
                        'departemen_name'  => $dept->nama,
                    ];
                }
            }

            // If company has no kompartemen and no departemen at all
            if ($company->kompartemen->count() === 0 && $company->departemen->count() === 0) {
                // Bare company row
                $rows[] = [
                    'company_code'     => $company->company_code,
                    'company_name'     => $company->nama,
                    'kompartemen_id'   => null,
                    'kompartemen_name' => null,
                    'departemen_id'    => null,
                    'departemen_name'  => null,
                ];
            }
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'company_code',
            'company_name',
            'kompartemen_id',
            'kompartemen_name',
            'departemen_id',
            'departemen_name',
        ];
    }
}
