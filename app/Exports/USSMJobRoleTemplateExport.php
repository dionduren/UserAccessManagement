<?php

namespace App\Exports;

use App\Models\JobRole;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class USSMJobRoleTemplateExport implements WithMultipleSheets
{
    public function __construct(private ?string $companyCode = null) {}

    public function sheets(): array
    {
        return [
            new USSMJobRoleUploadSheet(),
            new USSMJobRoleMasterSheet($this->companyCode),
        ];
    }
}

class USSMJobRoleUploadSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function collection()
    {
        return new Collection([
            [
                'nik'         => '',
                'job_role_id' => '',
                'user_type'   => '',
            ],
        ]);
    }

    public function headings(): array
    {
        return ['nik', 'job_role_id', 'user_type'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}

class USSMJobRoleMasterSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private ?string $companyCode = null) {}

    public function title(): string
    {
        return 'MASTER_JOB_ROLES';
    }

    public function collection()
    {
        $query = JobRole::with(['company', 'kompartemen', 'departemen'])
            ->select('company_id', 'kompartemen_id', 'departemen_id', 'job_role_id', 'nama')
            ->whereNotNull('job_role_id')
            ->orderBy('company_id')
            ->orderBy('kompartemen_id')
            ->orderBy('departemen_id')
            ->orderBy('job_role_id');

        // If user is not A000, restrict by their company_code
        if (!empty($this->companyCode) && $this->companyCode !== 'A000') {
            $query->where('company_id', $this->companyCode);
        }

        $roles = $query->get();

        $rows = $roles->map(function ($r) {
            return [
                // company_id equals ms_company.company_code
                'company_code'      => $r->company_id,
                'company_nama'      => optional($r->company)->nama,
                'kompartemen_id'    => $r->kompartemen_id,
                'kompartemen_nama'  => optional($r->kompartemen)->nama,
                'departemen_id'     => $r->departemen_id,
                'departemen_nama'   => optional($r->departemen)->nama,
                'job_role_id'       => $r->job_role_id,
                'job_role_nama'     => $r->nama,
            ];
        });

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'company_code',
            'company_nama',
            'kompartemen_id',
            'kompartemen_nama',
            'departemen_id',
            'departemen_nama',
            'job_role_id',
            'job_role_nama',
        ];
    }
}
