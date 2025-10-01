<?php

namespace App\Exports\MasterData;

use App\Models\JobRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JobCompositeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
        $query = JobRole::query()
            ->with([
                'company:company_code,nama,shortname',
                'kompartemen:kompartemen_id,nama',
                'departemen:departemen_id,nama',
                'compositeRole:id,nama,source,job_role_id,company_id',
            ])
            ->when(
                $this->userCompanyCode && $this->userCompanyCode !== 'A000',
                fn(Builder $q) => $q->where('company_id', $this->userCompanyCode)
            )
            ->when(
                data_get($this->filters, 'company'),
                fn(Builder $q, $company) => $q->where('company_id', $company)
            )
            ->when(
                data_get($this->filters, 'kompartemen'),
                fn(Builder $q, $kompartemen) => $q->where('kompartemen_id', $kompartemen)
            )
            ->when(
                data_get($this->filters, 'departemen'),
                fn(Builder $q, $departemen) => $q->where('departemen_id', $departemen)
            )
            ->when(
                data_get($this->filters, 'job_role'),
                fn(Builder $q, $jobRole) => $q->where('job_role_id', $jobRole)
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
            'Kompartemen ID',
            'Departemen',
            'Departemen ID',
            'Job Role ID',
            'Job Role Name',
            'Composite Role',
            'Composite Source',
        ];
    }

    public function map($jobRole): array
    {
        $company     = $jobRole->company;
        $kompartemen = $jobRole->kompartemen;
        $departemen  = $jobRole->departemen;
        $composite   = $jobRole->compositeRole;

        return [
            $company->nama ?? '-',
            $kompartemen->nama ?? '-',
            $kompartemen->kompartemen_id ?? '-',
            $departemen->nama ?? '-',
            $departemen->departemen_id ?? '-',
            $jobRole->job_role_id ?? '-',
            $jobRole->nama ?? '-',
            $composite->nama ?? '-',
            $composite->source ?? '-',
        ];
    }
}
