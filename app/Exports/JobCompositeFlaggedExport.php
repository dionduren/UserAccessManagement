<?php

namespace App\Exports;

use App\Models\CompositeRole;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JobCompositeFlaggedExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private ?string $companyCode = null) {}

    public function collection()
    {
        $query = CompositeRole::with([
            'company',
            'jobRole.company',
            'jobRole.kompartemen',
            'jobRole.departemen',
        ])
            ->whereHas('jobRole', function ($q) {
                $q->where('flagged', true);
            });

        if (!empty($this->companyCode)) {
            $query->where('company_id', $this->companyCode);
        }

        $rows = $query->get();

        return new Collection(
            $rows->map(function ($cr) {
                $jr = $cr->jobRole;
                return [
                    'company_code'        => $cr->company_id,
                    'company_name'        => $cr->company->nama ?? '',
                    'kompartemen_id'      => $jr->kompartemen_id ?? null,
                    'kompartemen_name'    => $jr?->kompartemen?->nama ?? '',
                    'departemen_id'       => $jr->departemen_id ?? null,
                    'departemen_name'     => $jr?->departemen?->nama ?? '',
                    'job_role_pk_id'      => $jr->id ?? null,
                    'job_role_id'         => $jr->job_role_id ?? '',
                    'job_role_name'       => $jr->nama ?? '',
                    'composite_role_id'   => $cr->id,
                    'composite_role_name' => $cr->nama ?? '',
                    'flagged'             => (int) ($jr->flagged ?? 0),
                    'keterangan'          => $jr->keterangan ?? '',
                    'error_komp_id'       => $jr->error_kompartemen_id ?? '',
                    'error_komp_name'     => $jr->error_kompartemen_name ?? '',
                    'error_dept_id'       => $jr->error_departemen_id ?? '',
                    'error_dept_name'     => $jr->error_departemen_name ?? '',
                ];
            })->all()
        );
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
            'job_role_pk_id',
            'job_role_id',
            'job_role_name',
            'composite_role_id',
            'composite_role_name',
            'flagged',
            'keterangan',
            'error_komp_id',
            'error_komp_name',
            'error_dept_id',
            'error_dept_name',
        ];
    }
}
