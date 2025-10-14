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

class UserNIKWithoutUnitKerjaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected int $periodeId;
    protected ?string $userCompany;

    public function __construct(int $periodeId, ?string $userCompany = null)
    {
        $this->periodeId = $periodeId;
        $this->userCompany = $userCompany;
    }

    public function collection()
    {
        $query = userNIK::query()
            ->with(['Company'])
            ->select([
                'tr_user_ussm_nik.id',
                'tr_user_ussm_nik.group',
                'tr_user_ussm_nik.user_code',
                'tr_user_ussm_nik.last_login',
                'tr_user_ussm_nik.valid_from',
                'tr_user_ussm_nik.valid_to',
            ])
            ->where('tr_user_ussm_nik.periode_id', $this->periodeId)
            ->whereNull('tr_user_ussm_nik.deleted_at')
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('ms_nik_unit_kerja as uk')
                    ->whereColumn('uk.nik', 'tr_user_ussm_nik.user_code')
                    ->where('uk.periode_id', $this->periodeId)
                    ->whereNull('uk.deleted_at');
            });

        // Filter by company unless A000
        if ($this->userCompany && $this->userCompany !== 'A000') {
            $query->whereHas('Company', function ($q) {
                $q->where('company_code', $this->userCompany);
            });
        }

        return $query->latest('tr_user_ussm_nik.id')->get()->map(function ($item) {
            return [
                'perusahaan'  => $item->Company->nama ?? $item->group ?? '-',
                'user_code'   => $item->user_code,
                'last_login'  => $item->last_login,
                'valid_from'  => $item->valid_from,
                'valid_to'    => $item->valid_to,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Perusahaan',
            'User Code (NIK)',
            'Last Login',
            'Valid From',
            'Valid To',
        ];
    }

    public function title(): string
    {
        return 'User NIK Without Unit Kerja';
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
