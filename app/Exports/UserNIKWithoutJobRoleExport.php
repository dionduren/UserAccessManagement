<?php

namespace App\Exports;

use App\Models\userNIK;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserNIKWithoutJobRoleExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
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
        $query = userNIK::query()
            ->select([
                'tr_user_ussm_nik.id',
                'tr_user_ussm_nik.group',
                'tr_user_ussm_nik.user_code',
                'user_details.nama as nama',
                'kompartemen.nama as kompartemen',
                'departemen.nama as departemen',
            ])
            ->where('group', $this->companyShortname) // Filter by user's company
            // Aggregate any wrong job_role_id(s) for this user+periode (comma-separated)
            ->selectSub(function ($sub) {
                $sub->from('tr_ussm_job_role as jr')
                    ->leftJoin('tr_job_roles as j', function ($join) {
                        $join->on('jr.job_role_id', '=', 'j.job_role_id')
                            ->whereNull('j.deleted_at'); // treat soft-deleted as missing
                    })
                    ->selectRaw("string_agg(DISTINCT jr.job_role_id::text, ',' ORDER BY jr.job_role_id::text)")
                    ->whereColumn('jr.nik', 'tr_user_ussm_nik.user_code')
                    ->where('jr.periode_id', $this->periodeId)
                    ->whereNull('jr.deleted_at')
                    ->whereNull('j.job_role_id');
            }, 'wrong_job_role_id')
            ->leftJoin('ms_master_data_karyawan as user_details', 'tr_user_ussm_nik.user_code', '=', 'user_details.nik')
            ->leftJoin('ms_kompartemen as kompartemen', 'user_details.kompartemen_id', '=', 'kompartemen.kompartemen_id')
            ->leftJoin('ms_departemen as departemen', 'user_details.departemen_id', '=', 'departemen.departemen_id')
            ->where('tr_user_ussm_nik.periode_id', $this->periodeId)
            ->where(function ($q) {
                // Include users with no assignment in this period
                $q->whereNotExists(function ($q1) {
                    $q1->selectRaw(1)
                        ->from('tr_ussm_job_role as jr')
                        ->whereColumn('jr.nik', 'tr_user_ussm_nik.user_code')
                        ->where('jr.periode_id', $this->periodeId)
                        ->whereNull('jr.deleted_at');
                })
                    // OR users that have at least one invalid assignment (job_role_id not found in JobRole)
                    ->orWhereExists(function ($q2) {
                        $q2->selectRaw(1)
                            ->from('tr_ussm_job_role as jr2')
                            ->leftJoin('tr_job_roles as j', function ($join) {
                                $join->on('jr2.job_role_id', '=', 'j.job_role_id')
                                    ->whereNull('j.deleted_at');
                            })
                            ->whereColumn('jr2.nik', 'tr_user_ussm_nik.user_code')
                            ->where('jr2.periode_id', $this->periodeId)
                            ->whereNull('jr2.deleted_at')
                            ->whereNull('j.job_role_id');
                    });
            });

        return $query->get()->map(function ($item) {
            return [
                'perusahaan'        => $item->group ?? '-',
                'nik'               => $item->user_code,
                'nama'              => $item->nama ?? '-',
                'kompartemen'       => $item->kompartemen ?? '-',
                'departemen'        => $item->departemen ?? '-',
                'wrong_job_role_id' => $item->wrong_job_role_id ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Perusahaan',
            'NIK',
            'Nama',
            'Kompartemen',
            'Departemen',
            'Wrong Job Role ID',
        ];
    }

    public function title(): string
    {
        return 'NIK Without Job Role';
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
