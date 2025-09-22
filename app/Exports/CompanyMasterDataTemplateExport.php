<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\CostCenter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CompanyMasterDataTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CompanyMasterUploadSheet(),
            new CompanyMasterHierarchySheet(),
        ];
    }
}

/**
 * Sheet 1: Blank upload template (headings match import)
 */
class CompanyMasterUploadSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function collection()
    {
        // Provide one empty example row users can overwrite (optional)
        return new Collection([
            [
                'company'    => '',
                'dir_id'     => '',
                'dir_title'  => '',
                'komp_id'    => '',
                'komp_title' => '',
                'dept_id'    => '',
                'dept_title' => '',
                'cost_center' => '',
                'cost_code'  => '',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'company',
            'dir_id',
            'dir_title',
            'komp_id',
            'komp_title',
            'dept_id',
            'dept_title',
            'cost_center',
            'cost_code',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
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
                $colCount   = count($this->headings());
                $lastCol    = Coordinate::stringFromColumnIndex($colCount);
                $headerRnd  = "A1:{$lastCol}1";
                $fullRange  = "A1:{$lastCol}" . (1 + $this->collection()->count());

                // Header style (bg + white border)
                $event->sheet->getStyle($headerRnd)->applyFromArray([
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

                // Thin black border for data area
                $event->sheet->getStyle($fullRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getDelegate()->freezePane('A2');
            }
        ];
    }
}

/**
 * Sheet 2: Master hierarchy rows (Directorate, Kompartemen, Departemen) with per-level cost center.
 * cost_center / cost_code correspond to that specific row's level.
 */
class CompanyMasterHierarchySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'MASTER_UNIT_KERJA';
    }

    public function collection()
    {
        // Preload data
        $companies    = Company::select('company_code', 'nama')->get()->keyBy('company_code');

        $directorates = CostCenter::where('level', 'Direktorat')
            ->select('company_id', 'level_id', 'level_name', 'cost_center', 'cost_code')
            ->get();

        $komps = Kompartemen::with('costCenter')
            ->select('kompartemen_id', 'company_id', 'nama', 'cost_center')
            ->get();

        $depts = Departemen::with(['costCenter', 'kompartemen.costCenter'])
            ->select('departemen_id', 'company_id', 'kompartemen_id', 'nama', 'cost_center')
            ->get();

        // Map directorates by their level_id for parent lookup
        $dirMap = $directorates->keyBy('level_id');

        $rows = [];

        // Directorate rows
        foreach ($directorates as $dir) {
            $rows[] = [
                'company'    => $dir->company_id,
                'dir_id'     => $dir->level_id,
                'dir_title'  => $dir->level_name,
                'komp_id'    => null,
                'komp_title' => null,
                'dept_id'    => null,
                'dept_title' => null,
                'cost_center' => $dir->cost_center,
                'cost_code'  => $dir->cost_code,
            ];
        }

        // Kompartemen rows
        foreach ($komps as $k) {
            $dir_id    = optional($k->costCenter)->parent_id;
            $dirModel  = $dir_id ? $dirMap->get($dir_id) : null;

            $rows[] = [
                'company'    => $k->company_id,
                'dir_id'     => $dirModel->level_id ?? null,
                'dir_title'  => $dirModel->level_name ?? null,
                'komp_id'    => $k->kompartemen_id,
                'komp_title' => $k->nama,
                'dept_id'    => null,
                'dept_title' => null,
                'cost_center' => optional($k->costCenter)->cost_center,
                'cost_code'  => optional($k->costCenter)->cost_code,
            ];
        }

        // Departemen rows
        foreach ($depts as $d) {
            $komp       = $d->kompartemen;
            $kompCC     = $komp?->costCenter;
            $dir_id     = $kompCC?->parent_id;
            $dirModel   = $dir_id ? $dirMap->get($dir_id) : null;

            $rows[] = [
                'company'    => $d->company_id,
                'dir_id'     => $dirModel->level_id ?? null,
                'dir_title'  => $dirModel->level_name ?? null,
                'komp_id'    => $komp?->kompartemen_id,
                'komp_title' => $komp?->nama,
                'dept_id'    => $d->departemen_id,
                'dept_title' => $d->nama,
                'cost_center' => optional($d->costCenter)->cost_center,
                'cost_code'  => optional($d->costCenter)->cost_code,
            ];
        }

        // Guarantee at least one row per company if completely empty
        foreach ($companies as $code => $comp) {
            $hasAny = collect($rows)->firstWhere('company', $code);
            if (!$hasAny) {
                $rows[] = [
                    'company'    => $code,
                    'dir_id'     => null,
                    'dir_title'  => null,
                    'komp_id'    => null,
                    'komp_title' => null,
                    'dept_id'    => null,
                    'dept_title' => null,
                    'cost_center' => null,
                    'cost_code'  => null,
                ];
            }
        }

        // Sort logically
        usort($rows, function ($a, $b) {
            return [$a['company'], $a['dir_id'], $a['komp_id'], $a['dept_id']]
                <=> [$b['company'], $b['dir_id'], $b['komp_id'], $b['dept_id']];
        });

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'company',
            'dir_id',
            'dir_title',
            'komp_id',
            'komp_title',
            'dept_id',
            'dept_title',
            'cost_center',
            'cost_code',
        ];
    }
}
