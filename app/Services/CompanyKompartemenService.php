<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\CostCenter;
use App\Models\CompositeRole;
use App\Models\PenomoranJobRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyKompartemenService
{
    // In-memory cache: key => job_role_id (PK)
    protected array $jobRoleCache = [];

    protected function cacheKey(string $company, ?string $komp, ?string $dept, string $name): string
    {
        return implode('|', [
            $company,
            $komp ?: '-',
            $dept ?: '-',
            mb_strtolower(trim($name))
        ]);
    }

    protected function rememberJobRole(JobRole $jobRole): void
    {
        $this->jobRoleCache[$this->cacheKey(
            $jobRole->company_id,
            $jobRole->kompartemen_id,
            $jobRole->departemen_id,
            $jobRole->nama
        )] = $jobRole->id;
    }

    protected function findCachedJobRoleId(string $company, ?string $komp, ?string $dept, string $name): ?int
    {
        $key = $this->cacheKey($company, $komp, $dept, $name);
        return $this->jobRoleCache[$key] ?? null;
    }

    protected function generateJobRoleId(string $companyCode, string $ccLevel, ?CostCenter $costCenter): string
    {
        $penomoran = PenomoranJobRole::firstOrCreate(
            ['company_id' => $companyCode],
            ['last_number' => 0]
        );
        $nextNumber = $penomoran->last_number + 1;
        $penomoran->update(['last_number' => $nextNumber]);

        $costCode        = $costCenter?->cost_code ?? '';
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $costCode . '_' . $ccLevel . '_JR_' . $formattedNumber;
    }

    public function handleRow(array $row): void
    {
        $user    = Auth::user()?->name ?? 'system';
        $company = Company::where('company_code', $row['company_code'] ?? null)->first();
        if (!$company) return;

        $jobRole      = null;
        $cc_level     = null;
        $flag_status  = false;
        $keterangan_flag = null;
        $costCenter   = null;

        try {
            // === Resolve cost center (existing logic unchanged) ===
            if (!empty($row['departemen']) && empty($row['departemen_id'])) {
                throw new \Exception("Departemen diberikan tanpa departemen_id.");
            }
            if (!empty($row['departemen_id'])) {
                $costCenter = CostCenter::where('level_id', $row['departemen_id'])
                    ->where('level', 'Departemen')->first();
                if (!$costCenter) {
                    throw new \Exception("CostCenter tidak ditemukan untuk Departemen ID: {$row['departemen_id']}");
                }
                $cc_level = 'DEP';
            } elseif (!empty($row['kompartemen_id'])) {
                $costCenter = CostCenter::where('level_id', $row['kompartemen_id'])
                    ->where('level', 'Kompartemen')->first();
                if (!$costCenter) {
                    throw new \Exception("CostCenter tidak ditemukan untuk Kompartemen ID: {$row['kompartemen_id']}");
                }
                $cc_level = 'KOM';
                $flag_status = true;
                $keterangan_flag = "Departemen tidak tersedia, menggunakan Kompartemen.";
            } else {
                throw new \Exception("Tidak ada departemen_id / kompartemen_id valid.");
            }

            DB::transaction(function () use (
                $row,
                $company,
                $user,
                &$jobRole,
                &$flag_status,
                &$keterangan_flag,
                &$cc_level,
                $costCenter
            ) {
                $incomingKompartemenId = $row['kompartemen_id'] ?: null;
                $incomingDepartemenId  = $row['departemen_id'] ?: null;
                $jobFunctionName       = trim((string)($row['job_function'] ?? ''));

                // Try cache first
                $cachedId = $this->findCachedJobRoleId(
                    $company->company_code,
                    $incomingKompartemenId,
                    $incomingDepartemenId,
                    $jobFunctionName
                );

                if ($cachedId) {
                    $jobRole = JobRole::find($cachedId);
                }

                if (!$jobRole) {
                    $existingJobRole = JobRole::where('company_id', $company->company_code)
                        ->where('nama', $jobFunctionName)
                        ->first();
                } else {
                    $existingJobRole = $jobRole;
                }

                if ($existingJobRole) {
                    if ($existingJobRole->kompartemen_id === null && $incomingKompartemenId) {
                        $existingJobRole->kompartemen_id = $incomingKompartemenId;
                    }
                    if ($existingJobRole->departemen_id === null && $incomingDepartemenId) {
                        $existingJobRole->departemen_id = $incomingDepartemenId;
                    }

                    $existingJobRole->flagged    = $flag_status;
                    $existingJobRole->keterangan = $keterangan_flag;
                    $existingJobRole->updated_by = $user;
                    $existingJobRole->error_departemen_name = $flag_status ? $row['departemen'] : null;

                    $currentId = (string) ($existingJobRole->job_role_id ?? '');
                    $needsReassign = $currentId === '' ||
                        str_starts_with($currentId, '_') ||
                        !preg_match('/^[A-K]/i', $currentId);

                    if ($needsReassign) {
                        $existingJobRole->job_role_id = $this->generateJobRoleId(
                            $company->company_code,
                            $cc_level,
                            $costCenter
                        );
                    }

                    if ($existingJobRole->isDirty()) {
                        $existingJobRole->save();
                    }

                    $jobRole = $existingJobRole;
                } else {
                    $newJobRoleId = $this->generateJobRoleId(
                        $company->company_code,
                        $cc_level,
                        $costCenter
                    );

                    $jobRole = new JobRole();
                    $jobRole->company_id      = $company->company_code;
                    $jobRole->nama            = $jobFunctionName;
                    $jobRole->kompartemen_id  = $incomingKompartemenId;
                    $jobRole->departemen_id   = $incomingDepartemenId;
                    $jobRole->job_role_id     = $newJobRoleId;
                    $jobRole->created_by      = $user;
                    $jobRole->updated_by      = $user;
                    $jobRole->flagged         = $flag_status;
                    $jobRole->keterangan      = $keterangan_flag;
                    if ($flag_status) {
                        $jobRole->error_departemen_name = $row['departemen'];
                    }
                    $jobRole->save();
                }

                // Cache it
                $this->rememberJobRole($jobRole);

                // === Multiple composite roles support ===
                if (!empty($row['composite_role'])) {
                    $names = collect(
                        preg_split('/[;,]/', $row['composite_role'])
                    )->map(fn($n) => trim($n))
                        ->filter()
                        ->unique();

                    foreach ($names as $compName) {
                        $compositeRole = CompositeRole::firstOrNew(['nama' => $compName]);

                        $compositeRole->company_id     = $company->company_code;
                        $compositeRole->kompartemen_id = $incomingKompartemenId;
                        $compositeRole->departemen_id  = $incomingDepartemenId;
                        if ($compositeRole->jabatan_id !== $jobRole->id) {
                            $compositeRole->jabatan_id = $jobRole->id;
                        }
                        $compositeRole->source     = $compositeRole->source ?? 'upload';
                        $compositeRole->updated_by = $user;
                        if (!$compositeRole->exists) {
                            $compositeRole->created_by = $user;
                        }
                        if ($compositeRole->isDirty()) {
                            $compositeRole->save();
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error("CompanyKompartemenService row error: " . $e->getMessage(), ['row' => $row]);
        }
    }
}
