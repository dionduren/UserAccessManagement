<?php

namespace App\Imports;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\Departemen;
use App\Models\SingleRole;
use App\Models\Kompartemen;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TcodeSingleRoleImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        $company = Company::firstOrCreate(['company_code' => $row['company']]);
        $kompartemen = Kompartemen::firstOrCreate(['name' => $row['kompartemen']]);
        $departemen = Departemen::firstOrCreate(['name' => $row['departemen']]);
        $singleRole = SingleRole::firstOrCreate([
            'nama' => $row['single_role'],
            'deskripsi' => $row['single_role_desc'],
        ]);

        $tcode = Tcode::firstOrCreate([
            'code' => $row['tcode'],
            'deskripsi' => $row['tcode_desc'],
        ]);

        // Logic for relating models can go here
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
