<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\CompositeRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CompanyKompartemenImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Validate and map data from the row
        $company = Company::firstOrCreate(['company_code' => $row['company']]);
        $kompartemen = Kompartemen::firstOrCreate(['name' => $row['kompartemen']]);
        $departemen = Departemen::firstOrCreate(['name' => $row['departemen']]);
        $jobRole = JobRole::firstOrCreate(['nama_jabatan' => $row['job_function']]);
        $compositeRole = CompositeRole::firstOrCreate(['nama' => $row['composite_role']]);

        // Logic to relate the models can be added here if necessary
        // For example: attaching composite roles to job roles, etc.
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
