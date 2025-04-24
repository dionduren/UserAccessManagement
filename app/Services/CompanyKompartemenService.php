<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\CompositeRole;
use Illuminate\Support\Facades\Auth;

class CompanyKompartemenService
{
    public function handleRow(array $row): void
    {
        $user = Auth::user()?->name ?? 'system';
        $company = Company::where('company_code', $row['company_code'])->first();
        if (!$company) return;

        // $kompartemen = null;
        // $departemen = null;

        // Create or update Kompartemen and Departemen based on data
        // //1. If Kompartemen & Departemen not null
        // if (!empty($row['kompartemen']) && !empty($row['departemen'])) {
        //     $existing = Kompartemen::find($row['kompartemen_id']);
        //     if ($existing) {
        //         $existing->update([
        //             'nama' => $row['kompartemen'],
        //             'company_id' => $company->company_code,
        //             'updated_by' => $user,
        //         ]);
        //         $kompartemen = $existing;
        //     } else {
        //         $kompartemen = Kompartemen::create([
        //             'kompartemen_id' => $row['kompartemen_id'],
        //             'nama' => $row['kompartemen'],
        //             'company_id' => $company->company_code,
        //             'created_by' => $user,
        //             'updated_by' => $user,
        //         ]);
        //     }

        //     $existing = Departemen::find($row['departemen_id']);
        //     if ($existing) {
        //         $existing->update([
        //             'nama' => $row['departemen'],
        //             'company_id' => $company->company_code,
        //             'kompartemen_id' => $row['kompartemen_id'] ?? null,
        //             'updated_by' => $user,
        //         ]);
        //         $departemen = $existing;
        //     } else {
        //         $departemen = Departemen::create([
        //             'departemen_id' => $row['departemen_id'],
        //             'nama' => $row['departemen'],
        //             'company_id' => $company->company_code,
        //             'kompartemen_id' => $row['kompartemen_id'] ?? null,
        //             'created_by' => $user,
        //             'updated_by' => $user,
        //         ]);
        //     }
        // }

        // //2. If Kompartemen Row is Null 
        // elseif (!empty($row['departemen']) && empty($row['kompartemen'])) {
        //     $existing = Departemen::find($row['departemen_id']);
        //     if ($existing) {
        //         $existing->update([
        //             'nama' => $row['departemen'],
        //             'company_id' => $company->company_code,
        //             'kompartemen_id' => $row['kompartemen_id'] ?? null,
        //             'updated_by' => $user,
        //         ]);
        //         $departemen = $existing;
        //     } else {
        //         $departemen = Departemen::create([
        //             'departemen_id' => $row['departemen_id'],
        //             'nama' => $row['departemen'],
        //             'company_id' => $company->company_code,
        //             'kompartemen_id' => $row['kompartemen_id'] ?? null,
        //             'created_by' => $user,
        //             'updated_by' => $user,
        //         ]);
        //     }
        // }

        // //3. If Kompartemen Exists but Departemen Row is Null 
        // elseif (!empty($row['kompartemen']) && empty($row['departemen'])) {
        //     $existing = Kompartemen::find($row['kompartemen_id']);
        //     if ($existing) {
        //         $existing->update([
        //             'nama' => $row['kompartemen'],
        //             'company_id' => $company->company_code,
        //             'updated_by' => $user,
        //         ]);
        //         $kompartemen = $existing;
        //     } else {
        //         $kompartemen = Kompartemen::create([
        //             'kompartemen_id' => $row['kompartemen_id'],
        //             'nama' => $row['kompartemen'],
        //             'company_id' => $company->company_code,
        //             'created_by' => $user,
        //             'updated_by' => $user
        //         ]);
        //     }
        // }

        // Create/Update JobRole
        $jobRole = JobRole::updateOrCreate(
            [
                'nama' => $row['job_function'],
                'company_id' => $company->company_code,
                'kompartemen_id' => $row['kompartemen_id'],
                'departemen_id' => $row['departemen_id']
            ],
            [
                'created_by' => $user,
                'updated_by' => $user
            ]
        );

        // Composite Role
        // $compositeRole = CompositeRole::updateOrCreate(
        CompositeRole::updateOrCreate(
            [
                'nama' => $row['composite_role'],
                'company_id' => $company->company_code,
                'kompartemen_id' => $row['kompartemen_id'],
                'departemen_id' => $row['departemen_id'],
                'jabatan_id' => $jobRole->id
            ],
            [
                'created_by' => $user,
                'updated_by' => $user
            ]
        );

        // Properly associate it to the JobRole
        // $jobRole->compositeRole()->save($compositeRole);
    }
}
