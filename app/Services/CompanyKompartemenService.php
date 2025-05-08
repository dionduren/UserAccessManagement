<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\CostCenter;
use App\Models\CompositeRole;
use App\Models\PenomoranJobRole;
use Illuminate\Support\Facades\Log;
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

        $jobRole = null;
        $cc_level = null;

        try {
            // Get cost code from CostCenter based on departemen_id or kompartemen_id
            $costCenter = null;
            if (!empty($row['departemen_id'])) {
                $costCenter = CostCenter::where('level_id', $row['departemen_id'])
                    ->where('level', 'Departemen')
                    ->first();
                $cc_level = 'DEP';
            } elseif (!empty($row['kompartemen_id'])) {
                $costCenter = CostCenter::where('level_id', $row['kompartemen_id'])
                    ->where('level', 'Kompartemen')
                    ->first();
                $cc_level = 'KOM';
            } else {
                throw new \Exception("Tidak ada Kompartemen ataupun Departemen yang terdaftar pada Cost Center.");
            }

            // Get next number from PenomoranJobRole
            $penomoran = PenomoranJobRole::where('company_id', $row['company_code'])->first();
            $nextNumber = $penomoran ? $penomoran->last_number + 1 : 1;

            // Update the number in PenomoranJobRole
            PenomoranJobRole::updateOrCreate(
                [
                    'company_id' => $row['company_code']
                ],
                ['last_number' => $nextNumber]
            );


            // Format job_role_id
            $costCode = $costCenter ? $costCenter->cost_code : '';
            // Log::info('Try - $costCenter = ' . $costCenter);
            $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $job_role_id = $costCode . '_' . $cc_level . '_JR_' . $formattedNumber;


            // Log::info('Try - $job_role_id = ' . $job_role_id);

            // Create/Update JobRole
            $jobRole = JobRole::updateOrCreate(
                [
                    'company_id' => $company->company_code,
                    'nama' => $row['job_function'],
                    'kompartemen_id' => $row['kompartemen_id'],
                    'departemen_id' => $row['departemen_id'],
                ],
                [
                    'kompartemen_id' => $row['kompartemen_id'],
                    'departemen_id' => $row['departemen_id'],
                    'job_role_id' => $job_role_id,
                    'created_by' => $user,
                    'updated_by' => $user,
                    'flagged' => false,
                    'keterangan' => null
                ]
            );

            Log::info('Try - $jobRole = ' . $jobRole);
        } catch (\Exception $e) {
            // Create/Update JobRole with error details
            Log::error('Catch - Error Message = ' . $e->getMessage());
            $jobRole = JobRole::updateOrCreate(
                [
                    'company_id' => $company->company_code,
                    'nama' => $row['job_function'],
                    'kompartemen_id' => $row['kompartemen_id'],
                    'departemen_id' => $row['departemen_id'],
                ],
                [
                    'job_role_id' => null,
                    'error_kompartemen_name' => $row['kompartemen'],
                    'error_departemen_name' => $row['departemen'],
                    'created_by' => $user,
                    'updated_by' => $user,
                    'flagged' => true,
                    'keterangan' => $e->getMessage()
                ]
            );

            // Log::error('Catch - $jobRole = ' . $jobRole);
        }

        // Composite Role
        $compositeRole = CompositeRole::updateOrCreate(
            // CompositeRole::updateOrCreate(
            [
                'company_id' => $company->company_code,
                'nama' => $row['composite_role'],
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
        $jobRole->compositeRole()->save($compositeRole);
    }
}
