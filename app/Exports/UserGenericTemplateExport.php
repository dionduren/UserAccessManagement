<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserGenericTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new UserGenericUploadSheet(),
            new UserGenericInstructionSheet(),
        ];
    }
}

class UserGenericUploadSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'UPLOAD_TEMPLATE';
    }

    public function collection(): Collection
    {
        return new Collection([
            [
                'group'         => '',
                'user_code'     => '',
                'user_type'     => '',
                'user_profile'  => '',
                'nik'           => '',
                'cost_code'     => '',
                'license_type'  => '',
                'last_login'    => '',
                'valid_from'    => '',
                'valid_to'      => '',
                'keterangan'    => '',
                'uar_listed'    => '',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'group',
            'user_code',
            'user_type',
            'user_profile',
            'nik',
            'cost_code',
            'license_type',
            'last_login',
            'valid_from',
            'valid_to',
            'keterangan',
            'uar_listed',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'       => ['bold' => true],
                'alignment'  => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}

class UserGenericInstructionSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'INSTRUCTIONS';
    }

    public function collection(): Collection
    {
        return new Collection([
            ['group', 'Kode perusahaan atau grup user', 'A000'],
            ['user_code', 'User code unik (wajib)', 'USR000123'],
            ['user_type', 'Jenis user (NIK / Generic)', 'Generic'],
            ['user_profile', 'Nama lengkap user', 'Budi Santoso'],
            ['nik', 'Nomor induk karyawan (opsional)', '73004567'],
            ['cost_code', 'Kode cost center user', 'A0011000000'],
            ['license_type', 'Jenis lisensi SAP/APP (wajib)', 'CA/CB/FN/FX'],
            ['last_login', 'Tanggal login terakhir (format YYYY-MM-DD)', '2025-01-30'],
            ['valid_from', 'Tanggal mulai berlaku (format YYYY-MM-DD)', '2025-02-01'],
            ['valid_to', 'Tanggal akhir berlaku (format YYYY-MM-DD)', '2025-12-31'],
            ['keterangan', 'Catatan tambahan', 'Migrasi dari sistem lama'],
            ['uar_listed', 'Isi “Yes/No” bila tercantum di UAR', 'Yes'],
        ]);
    }

    public function headings(): array
    {
        return ['Column', 'Description', 'Example'];
    }
}
