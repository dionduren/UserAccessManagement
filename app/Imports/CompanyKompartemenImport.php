<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\CompositeRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompanyKompartemenImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Validate and map data from the row
        $company = Company::firstOrCreate(['company_code' => $row['company']]);
        $kompartemen = !empty($row['kompartemen'])
            ? Kompartemen::firstOrCreate(['name' => $row['kompartemen'], 'company_id' => $company->id])
            : null;
        $departemen = !empty($row['departemen'])
            ? Departemen::firstOrCreate([
                'name' => $row['departemen'],
                'company_id' => $company->id,
                'kompartemen_id' => $kompartemen->id ?? null,
            ])
            : null;

        $jobRole = JobRole::firstOrCreate([
            'nama_jabatan' => $row['job_function'],
            'company_id' => $company->id,
        ], [
            'kompartemen_id' => $kompartemen->id ?? null,
            'departemen_id' => $departemen->id ?? null,
            'deskripsi' => $row['job_description'] ?? null,
        ]);

        // Create or Update CompositeRole
        if (!empty($row['composite_role'])) {
            $compositeRole = CompositeRole::firstOrCreate([
                'nama' => $row['composite_role'],
                'company_id' => $company->id,
            ]);

            // Associate JobRole with CompositeRole
            $compositeRole->jobRole()->associate($jobRole);
            $compositeRole->save();
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
