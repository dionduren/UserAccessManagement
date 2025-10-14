<?php

namespace App\Exports;

use App\Models\JobRole;
use App\Models\Company;
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
        // Get companies with their organizational structure
        $companies = Company::select('company_code', 'nama')
            ->when(
                $this->companyCode && $this->companyCode !== 'A000',
                fn($q) => $q->where('company_code', $this->companyCode)
            )
            ->with([
                'kompartemen' => fn($q) => $q->select('kompartemen_id', 'company_id', 'nama'),
                'kompartemen.departemen' => fn($q) => $q->select('departemen_id', 'kompartemen_id', 'nama'),
                'kompartemen.departemen.jobRoles' => fn($q) => $q->select('job_role_id', 'departemen_id', 'nama')
                    ->whereNotNull('job_role_id'),
                // Load departments directly under company (no kompartemen)
                'departemenWithoutKompartemen' => fn($q) => $q->select('departemen_id', 'company_id', 'kompartemen_id', 'nama')
                    ->whereNull('kompartemen_id'),
                'departemenWithoutKompartemen.jobRoles' => fn($q) => $q->select('job_role_id', 'departemen_id', 'nama')
                    ->whereNotNull('job_role_id'),
            ])
            ->orderBy('company_code')
            ->get();

        $rows = [];

        foreach ($companies as $company) {
            // First: Process kompartemen -> departemen -> job roles
            if ($company->kompartemen->isNotEmpty()) {
                foreach ($company->kompartemen as $komp) {
                    if ($komp->departemen->isNotEmpty()) {
                        foreach ($komp->departemen as $dept) {
                            if ($dept->jobRoles->isNotEmpty()) {
                                foreach ($dept->jobRoles as $role) {
                                    $rows[] = [
                                        'company_code'      => $company->company_code,
                                        'company_nama'      => $company->nama,
                                        'kompartemen_id'    => $komp->kompartemen_id,
                                        'kompartemen_nama'  => $komp->nama,
                                        'departemen_id'     => $dept->departemen_id,
                                        'departemen_nama'   => $dept->nama,
                                        'job_role_id'       => $role->job_role_id,
                                        'job_role_nama'     => $role->nama,
                                        'user_type'         => 'NIK/Generic',
                                    ];
                                }
                            } else {
                                // Department without job roles
                                $rows[] = [
                                    'company_code'      => $company->company_code,
                                    'company_nama'      => $company->nama,
                                    'kompartemen_id'    => $komp->kompartemen_id,
                                    'kompartemen_nama'  => $komp->nama,
                                    'departemen_id'     => $dept->departemen_id,
                                    'departemen_nama'   => $dept->nama,
                                    'job_role_id'       => null,
                                    'job_role_nama'     => null,
                                    'user_type'         => 'NIK/Generic',
                                ];
                            }
                        }
                    } else {
                        // Kompartemen without departemen
                        $rows[] = [
                            'company_code'      => $company->company_code,
                            'company_nama'      => $company->nama,
                            'kompartemen_id'    => $komp->kompartemen_id,
                            'kompartemen_nama'  => $komp->nama,
                            'departemen_id'     => null,
                            'departemen_nama'   => null,
                            'job_role_id'       => null,
                            'job_role_nama'     => null,
                            'user_type'         => 'NIK/Generic',
                        ];
                    }
                }
            }

            // Second: Process departemen directly under company (no kompartemen)
            if ($company->departemenWithoutKompartemen->isNotEmpty()) {
                foreach ($company->departemenWithoutKompartemen as $dept) {
                    if ($dept->jobRoles->isNotEmpty()) {
                        foreach ($dept->jobRoles as $role) {
                            $rows[] = [
                                'company_code'      => $company->company_code,
                                'company_nama'      => $company->nama,
                                'kompartemen_id'    => null,
                                'kompartemen_nama'  => null,
                                'departemen_id'     => $dept->departemen_id,
                                'departemen_nama'   => $dept->nama,
                                'job_role_id'       => $role->job_role_id,
                                'job_role_nama'     => $role->nama,
                                'user_type'         => 'NIK/Generic',
                            ];
                        }
                    } else {
                        // Department without job roles
                        $rows[] = [
                            'company_code'      => $company->company_code,
                            'company_nama'      => $company->nama,
                            'kompartemen_id'    => null,
                            'kompartemen_nama'  => null,
                            'departemen_id'     => $dept->departemen_id,
                            'departemen_nama'   => $dept->nama,
                            'job_role_id'       => null,
                            'job_role_nama'     => null,
                            'user_type'         => 'NIK/Generic',
                        ];
                    }
                }
            }

            // Third: Company without any organizational structure or job roles
            if (
                $company->kompartemen->isEmpty() &&
                $company->departemenWithoutKompartemen->isEmpty()
            ) {
                $rows[] = [
                    'company_code'      => $company->company_code,
                    'company_nama'      => $company->nama,
                    'kompartemen_id'    => null,
                    'kompartemen_nama'  => null,
                    'departemen_id'     => null,
                    'departemen_nama'   => null,
                    'job_role_id'       => null,
                    'job_role_nama'     => null,
                    'user_type'         => 'NIK/Generic',
                ];
            }
        }

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
            'user_type',
        ];
    }
}
