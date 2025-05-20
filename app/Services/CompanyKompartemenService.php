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

        $jobRole = null;
        $cc_level = null;
        $flag_status = false;
        $keterangan_flag = null;

        try {
            // Get cost code from CostCenter based on departemen_id or kompartemen_id

            $costCenter = null;
            $costCode = null;

            // 🚫 departemen provided but no ID → skip & log
            if (!empty($row['departemen']) && empty($row['departemen_id'])) {
                throw new \Exception("Departemen diberikan tanpa departemen_id. Harap cek kembali mapping master data.");
            }

            // ✅ Normal case: departemen_id exists
            if (!empty($row['departemen_id'])) {
                $costCenter = CostCenter::where('level_id', $row['departemen_id'])
                    ->where('level', 'Departemen')
                    ->first();

                if (!$costCenter) {
                    throw new \Exception("CostCenter tidak ditemukan untuk Departemen ID: {$row['departemen_id']}");
                }

                $cc_level = 'DEP';
            }

            // ✅ Fallback: no departemen info at all → use kompartemen
            if (empty($row['departemen']) && empty($row['departemen_id']) && !empty($row['kompartemen_id'])) {
                $costCenter = CostCenter::where('level_id', $row['kompartemen_id'])
                    ->where('level', 'Kompartemen')
                    ->first();

                if (!$costCenter) {
                    throw new \Exception("CostCenter tidak ditemukan untuk Kompartemen ID: {$row['kompartemen_id']}");
                }

                $cc_level = 'KOM';
                $flag_status = true;
                $keterangan_flag = "Departemen tidak tersedia, menggunakan Kompartemen sebagai cost center.";
            }

            if (!$costCenter) {
                throw new \Exception("Tidak ada departemen_id atau kompartemen_id yang valid ditemukan di CostCenter.");
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

            // Create/Update JobRole
            if ($flag_status) {
                $jobRole = JobRole::updateOrCreate(
                    [
                        'company_id' => $company->company_code,
                        'nama' => $row['job_function'],
                        'kompartemen_id' => $row['kompartemen_id'],
                        'departemen_id' => $row['departemen_id'],
                    ],
                    [
                        'error_departemen_name' => $row['departemen'],
                        'job_role_id' => $job_role_id,
                        'created_by' => $user,
                        'updated_by' => $user,
                        'flagged' => $flag_status,
                        'keterangan' => $keterangan_flag
                    ]
                );
            } else {
                $jobRole = JobRole::updateOrCreate(
                    [
                        'company_id' => $company->company_code,
                        'nama' => $row['job_function'],
                        'kompartemen_id' => $row['kompartemen_id'],
                        'departemen_id' => $row['departemen_id'],
                    ],
                    [
                        'job_role_id' => $job_role_id,
                        'created_by' => $user,
                        'updated_by' => $user,
                        'flagged' => $flag_status,
                        'keterangan' => $keterangan_flag
                    ]
                );
            }



            // Log::info('Try - $jobRole = ' . $jobRole);
        } catch (\Exception $e) {
            // Create/Update JobRole with error details
            Log::error('Catch - Error Message = ' . $e->getMessage());
            $jobRole = JobRole::updateOrCreate(
                [
                    'company_id' => $company->company_code,
                    'nama' => $row['job_function'],
                ],
                [
                    'job_role_id' => null,
                    'error_kompartemen_id' => $row['kompartemen_id'],
                    'error_kompartemen_name' => $row['kompartemen'],
                    'error_departemen_id' => $row['departemen_id'],
                    'error_departemen_name' => $row['departemen'],
                    'created_by' => $user,
                    'updated_by' => $user,
                    'flagged' => true,
                    'keterangan' => $e->getMessage()
                ]
            );
        }

        // Composite Role
        $compositeRole = CompositeRole::updateOrCreate(
            [
                'company_id'      => trim($company->company_code),
                'nama'            => trim($row['composite_role']),
                'kompartemen_id'  => $row['kompartemen_id'] ?: null,
                'departemen_id'   => $row['departemen_id'] ?: null,
                'jabatan_id'      => $jobRole->id,
            ],
            [
                'created_by'      => $user,
                'updated_by'      => $user,
            ]
        );

        // Properly associate it to the JobRole if not already linked
        if ($compositeRole->jabatan_id !== $jobRole->id) {
            $compositeRole->jabatan_id = $jobRole->id;
            $compositeRole->updated_by = $user;
            $compositeRole->save();
        }
    }
}
