<?php

namespace App\Exports;

use App\Models\Company;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserGenericUnitKerjaTemplateExport implements WithMultipleSheets
{
    public function __construct(private ?string $companyCode = null) {}

    public function sheets(): array
    {
        return [
            new UGUKTemplateSheet(),
            new UGUKMasterSheet($this->companyCode),
        ];
    }
}

class UGUKTemplateSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    private array $rows;

    public function __construct()
    {
        $this->rows = [
            [
                'user_cc'          => '',
                'kompartemen_id'     => '',
                'departemen_id'      => '',
            ],
        ];
    }

    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function collection()
    {
        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return [
            'user_cc',
            'kompartemen_id',
            'departemen_id',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}

class UGUKMasterSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private ?string $companyCode = null) {}

    public function title(): string
    {
        return 'MASTER_UNIT_KERJA';
    }

    public function collection()
    {
        $companies = Company::select('company_code', 'nama')
            ->when(
                $this->companyCode && $this->companyCode !== 'A000',
                fn($q) => $q->where('company_code', $this->companyCode)
            )
            ->with([
                'kompartemen' => fn($q) => $q->select('kompartemen_id', 'company_id', 'nama'),
                'kompartemen.departemen' => fn($q) => $q->select('departemen_id', 'kompartemen_id', 'nama'),
                'departemen' => fn($q) => $q->select('departemen_id', 'company_id', 'kompartemen_id', 'nama'),
            ])
            ->orderBy('company_code')
            ->get();

        $rows = [];

        foreach ($companies as $company) {
            if ($company->kompartemen->isNotEmpty()) {
                foreach ($company->kompartemen as $komp) {
                    if ($komp->departemen->isNotEmpty()) {
                        foreach ($komp->departemen as $dept) {
                            $rows[] = [
                                'company_code'     => $company->company_code,
                                'company_name'     => $company->nama,
                                'kompartemen_id'   => $komp->kompartemen_id,
                                'kompartemen_name' => $komp->nama,
                                'departemen_id'    => $dept->departemen_id,
                                'departemen_name'  => $dept->nama,
                            ];
                        }
                    } else {
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
            } elseif ($company->departemen->isNotEmpty()) {
                foreach ($company->departemen as $dept) {
                    $rows[] = [
                        'company_code'     => $company->company_code,
                        'company_name'     => $company->nama,
                        'kompartemen_id'   => null,
                        'kompartemen_name' => null,
                        'departemen_id'    => $dept->departemen_id,
                        'departemen_name'  => $dept->nama,
                    ];
                }
            } else {
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
