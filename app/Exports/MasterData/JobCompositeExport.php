<?php

namespace App\Exports\MasterData;

use App\Models\CompositeRole;
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
        $query = CompositeRole::query()
            ->with([
                'company:company_code,nama,shortname',
                'jobRole:id,job_role_id,nama,kompartemen_id,departemen_id',
                'jobRole.kompartemen:kompartemen_id,nama',
                'jobRole.departemen:departemen_id,nama',
            ])
            ->when(
                $this->userCompanyCode && $this->userCompanyCode !== 'A000',
                fn(Builder $q) => $q->where('company_id', $this->userCompanyCode)
            );

        if ($company = data_get($this->filters, 'company')) {
            $query->where('company_id', $company);
        }

        if ($kompartemen = data_get($this->filters, 'kompartemen')) {
            $query->whereHas('jobRole', fn(Builder $q) => $q->where('kompartemen_id', $kompartemen));
        }

        if ($departemen = data_get($this->filters, 'departemen')) {
            $query->whereHas('jobRole', fn(Builder $q) => $q->where('departemen_id', $departemen));
        }

        if ($jobRole = data_get($this->filters, 'job_role')) {
            $query->where('jabatan_id', $jobRole);
        }

        return $query->orderBy('company_id')
            ->orderBy('nama')
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
            'Source',
        ];
    }

    public function map($composite): array
    {
        $jobRole     = $composite->jobRole;
        $kompartemen = $jobRole?->kompartemen;
        $departemen  = $jobRole?->departemen;

        return [
            $composite->company->nama ?? '-',
            $kompartemen->nama ?? '-',
            $kompartemen->kompartemen_id ?? '-',
            $departemen->nama ?? '-',
            $departemen->departemen_id ?? '-',
            $jobRole->job_role_id ?? '-',
            $jobRole->nama ?? '-',
            $composite->nama ?? '-',
            $composite->source ?? 'ERR',
        ];
    }
}
