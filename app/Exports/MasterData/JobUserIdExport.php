<?php

namespace App\Exports\MasterData;

use App\Models\JobRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JobUserIdExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private ?string $userCompanyCode;
    private array $filters;

    public function __construct(?string $userCompanyCode = null, array $filters = [])
    {
        $this->userCompanyCode = $userCompanyCode;
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $periodeId = data_get($this->filters, 'periode_id');

        $query = JobRole::query()
            ->select([
                'id',
                'job_role_id',
                'nama',
                'company_id',
                'kompartemen_id',
                'departemen_id',
            ])
            ->with([
                'company:company_code,nama',
                'kompartemen:kompartemen_id,nama',
                'departemen:departemen_id,nama',
                'NIKJobRole' => function ($q) use ($periodeId) {
                    if ($periodeId) {
                        $q->where('periode_id', $periodeId);
                    }
                    $q->select([
                        'id',
                        'periode_id',
                        'nik',
                        'user_type',
                        'job_role_id',
                    ]);
                },
            ])
            ->when(
                $this->userCompanyCode && $this->userCompanyCode !== 'A000',
                fn(Builder $q) => $q->where('company_id', $this->userCompanyCode)
            )
            ->when(
                data_get($this->filters, 'company_id'),
                fn(Builder $q, $company) => $q->where('company_id', $company)
            )
            ->when(
                data_get($this->filters, 'kompartemen_id'),
                fn(Builder $q, $kompartemen) => $q->where('kompartemen_id', $kompartemen)
            )
            ->when(
                data_get($this->filters, 'departemen_id'),
                fn(Builder $q, $departemen) => $q->where('departemen_id', $departemen)
            )
            ->when(
                data_get($this->filters, 'job_role_id'),
                fn(Builder $q, $jobRoleId) => $q->where('job_role_id', $jobRoleId)
            );

        return $query
            ->orderBy('company_id')
            ->orderBy('job_role_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Company',
            'Kompartemen',
            'Departemen',
            'Job Role ID',
            'Job Role Name',
            'Total Users',
            'Total User NIK',
            'Total User Generic',
            'User NIK List',
            'User Generic List',
        ];
    }

    public function map($jobRole): array
    {
        $assignments = $jobRole->NIKJobRole ?? collect();

        $nikUsers = $assignments
            ->where('user_type', 'NIK')
            ->pluck('nik')
            ->filter()
            ->unique()
            ->values();

        $genericUsers = $assignments
            ->where('user_type', 'Generic')
            ->pluck('nik')
            ->filter()
            ->unique()
            ->values();

        return [
            $jobRole->company->nama ?? '-',
            $jobRole->kompartemen->nama ?? '-',
            $jobRole->departemen->nama ?? '-',
            $jobRole->job_role_id ?? '-',
            $jobRole->nama ?? '-',
            $assignments->count(),
            $nikUsers->count(),
            $genericUsers->count(),
            $nikUsers->implode(', '),
            $genericUsers->implode(', '),
        ];
    }
}
