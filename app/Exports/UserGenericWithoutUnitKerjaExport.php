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

class UserGenericWithoutUnitKerjaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
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
        $query = userGeneric::query()
            ->with('Company')
            ->where('periode_id', $this->periodeId)
            ->whereNull('deleted_at')
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('ms_generic_unit_kerja as guk')
                    ->whereColumn('guk.user_cc', 'tr_user_generic.user_code')
                    ->where('guk.periode_id', $this->periodeId)
                    ->whereNull('guk.deleted_at');
            });

        // Filter by company unless A000
        if ($this->userCompany && $this->userCompany !== 'A000') {
            $query->whereHas('Company', function ($q) {
                $q->where('company_code', $this->userCompany);
            });
        }

        return $query->latest('id')->get()->map(function ($u) {
            return [
                'company'    => optional($u->Company)->company_code ?? '-',
                'user_code'  => $u->user_code,
                'nama'       => $u->user_profile,
                'last_login' => $u->last_login,
                'valid_from' => $u->valid_from,
                'valid_to'   => $u->valid_to,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Perusahaan',
            'User Code',
            'Nama',
            'Last Login',
            'Valid From',
            'Valid To',
        ];
    }

    public function title(): string
    {
        return 'User Generic Without Unit Kerja';
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
