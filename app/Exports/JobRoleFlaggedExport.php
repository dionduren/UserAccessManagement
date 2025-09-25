<?php

namespace App\Exports;

use App\Models\JobRole;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JobRoleFlaggedExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private ?string $companyCode = null) {}

    public function collection()
    {
        $q = JobRole::with(['company', 'kompartemen', 'departemen', 'compositeRole'])
            ->where('flagged', true);

        if (!empty($this->companyCode)) {
            $q->where('company_id', $this->companyCode);
        }

        $rows = $q->orderBy('company_id')
            ->orderBy('kompartemen_id')
            ->orderBy('departemen_id')
            ->orderBy('job_role_id')
            ->get();

        return new Collection($rows->map(function ($r) {
            return [
                'company_code'      => $r->company_id,
                'company_nama'      => $r->company->nama ?? '',
                'kompartemen_id'    => $r->kompartemen_id,
                'kompartemen_nama'  => $r->kompartemen->nama ?? '',
                'departemen_id'     => $r->departemen_id,
                'departemen_nama'   => $r->departemen->nama ?? '',
                'job_role_id'       => $r->job_role_id,
                'job_role_nama'     => $r->nama,
                'composite_roles'   => $r->compositeRole->nama ?? '',
                'keterangan'        => $r->keterangan,
                'error_komp_id'     => $r->error_kompartemen_id,
                'error_komp_name'   => $r->error_kompartemen_name,
                'error_dept_id'     => $r->error_departemen_id,
                'error_dept_name'   => $r->error_departemen_name,
            ];
        })->all());
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
            'composite_roles',
            'keterangan',
            'error_komp_id',
            'error_komp_name',
            'error_dept_id',
            'error_dept_name',
        ];
    }
}
