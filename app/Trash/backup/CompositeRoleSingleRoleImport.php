<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\SingleRole;
use App\Models\Kompartemen;
use App\Models\CompositeRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompositeRoleSingleRoleImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        $singleRole = SingleRole::firstOrCreate([
            'nama' => $row['single_role'],
            'deskripsi' => $row['description'],
        ]);

        $company = Company::firstOrCreate(['company_code' => $row['company']]);
        $kompartemen = Kompartemen::firstOrCreate(['name' => $row['kompartemen']]);
        $departemen = Departemen::firstOrCreate(['name' => $row['departemen']]);
        $compositeRole = CompositeRole::firstOrCreate(['nama' => $row['composite_role']]);

        // Logic for relating models can go here
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
