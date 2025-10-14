<?php

namespace App\Exports;

use App\Models\userGeneric;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserGenericWithoutJobRoleExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected int $periodeId;
    protected ?string $companyShortname;

    public function __construct(int $periodeId, ?string $companyShortname = null)
    {
        $this->periodeId = $periodeId;
        $this->companyShortname = $companyShortname;
    }

    public function collection()
    {
        $query = userGeneric::query()
            ->select([
                'id',
                'group',
                'user_code',
                'last_login',
            ])
            ->where('group', $this->companyShortname) // Filter by user's company
            // Collect wrong job_role_id(s) (not found in tr_job_roles or soft-deleted)
            ->selectSub(function ($sub) {
                $sub->from('tr_ussm_job_role as jr')
                    ->leftJoin('tr_job_roles as j', function ($join) {
                        $join->on('jr.job_role_id', '=', 'j.job_role_id')
                            ->whereNull('j.deleted_at'); // treat soft-deleted as missing
                    })
                    // PostgreSQL string_agg with DISTINCT + ORDER BY must match argument
                    ->selectRaw("string_agg(DISTINCT jr.job_role_id::text, ',' ORDER BY jr.job_role_id::text)")
                    ->whereColumn('jr.nik', 'tr_user_generic.user_code')
                    ->where('jr.periode_id', $this->periodeId)
                    ->whereNull('jr.deleted_at')
                    ->whereNull('j.job_role_id');
            }, 'wrong_job_role_id')
            ->with(['periode', 'userGenericUnitKerja.kompartemen', 'userGenericUnitKerja.departemen'])
            ->where('periode_id', $this->periodeId)
            ->where(function ($q) {
                // Users with NO assignment in this period
                $q->whereNotExists(function ($q1) {
                    $q1->selectRaw(1)
                        ->from('tr_ussm_job_role as jr')
                        ->whereColumn('jr.nik', 'tr_user_generic.user_code')
                        ->where('jr.periode_id', $this->periodeId)
                        ->whereNull('jr.deleted_at');
                })
                    // OR users with at least one invalid assignment (job_role_id not present in tr_job_roles)
                    ->orWhereExists(function ($q2) {
                        $q2->selectRaw(1)
                            ->from('tr_ussm_job_role as jr2')
                            ->leftJoin('tr_job_roles as j', function ($join) {
                                $join->on('jr2.job_role_id', '=', 'j.job_role_id')
                                    ->whereNull('j.deleted_at');
                            })
                            ->whereColumn('jr2.nik', 'tr_user_generic.user_code')
                            ->where('jr2.periode_id', $this->periodeId)
                            ->whereNull('jr2.deleted_at')
                            ->whereNull('j.job_role_id');
                    });
            });

        return $query->get()->map(function ($item) {
            return [
                'perusahaan'        => $item->group ?? '-',
                'user_code'         => $item->user_code,
                'kompartemen'       => $item->userGenericUnitKerja && $item->userGenericUnitKerja->kompartemen
                    ? $item->userGenericUnitKerja->kompartemen->nama
                    : '-',
                'departemen'        => $item->userGenericUnitKerja && $item->userGenericUnitKerja->departemen
                    ? $item->userGenericUnitKerja->departemen->nama
                    : '-',
                'last_login'        => $item->last_login,
                'wrong_job_role_id' => $item->wrong_job_role_id ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Perusahaan',
            'User Code',
            'Kompartemen',
            'Departemen',
            'Last Login',
            'Wrong Job Role ID',
        ];
    }

    public function title(): string
    {
        return 'User Generic Without Job Role';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }
}
