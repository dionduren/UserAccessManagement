<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\CompositeRole;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompanyKompartemenImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Validate and map data from the row
        $company = Company::firstOrCreate(['company_code' => $row['company']]);

        $existing = Kompartemen::find($row['kompartemen_id']);
        if ($existing) {
            $existing->update([
                'nama' => $row['kompartemen'],
                'company_id' => $company->company_code,
                'updated_by' => Auth::user()->name,
            ]);
            $kompartemen = $existing;
        } else {
            $kompartemen = Kompartemen::create([
                'kompartemen_id' => $row['kompartemen_id'],
                'nama' => $row['kompartemen'],
                'company_id' => $company->company_code,
                'created_by' => Auth::user()->name,
                'updated_by' => Auth::user()->name,
            ]);
        }

        $existing = Departemen::find($row['departemen_id']);
        if ($existing) {
            $existing->update([
                'nama' => $row['departemen'],
                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                'company_id' => $company->company_code,
                'updated_by' => Auth::user()->name,
            ]);
            $departemen = $existing;
        } else {
            $departemen = Departemen::create([
                'departemen_id' => $row['departemen_id'],
                'nama' => $row['departemen'],
                'company_id' => $company->company_code,
                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                'created_by' => Auth::user()->name,
                'updated_by' => Auth::user()->name,
            ]);
        }

        $jobRole = JobRole::firstOrCreate([
            'nama' => $row['job_function'],
            'company_id' => $company->company_code,
        ], [
            'kompartemen_id' => $row['kompartemen_id'] ?? null,
            'departemen_id' => $row['departemen_id'] ?? null,
            // 'deskripsi' => $row['job_description'] ?? null,
        ]);

        // Create or Update CompositeRole
        if (!empty($row['composite_role'])) {
            $compositeRole = CompositeRole::firstOrCreate([
                'nama' => $row['composite_role'],
                'company_id' => $company->company_code,
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
