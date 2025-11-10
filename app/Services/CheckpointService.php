<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\CostCenter;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\MasterDataKaryawanLocal;
use App\Models\ProcessCheckpoint;
use App\Models\userGeneric;
use App\Models\UserGenericUnitKerja;
use App\Models\UserNIKUnitKerja;
use App\Models\userNIK;
use App\Models\Kompartemen;
use App\Models\SingleRole;
use App\Models\Tcode;
use App\Models\NIKJobRole;
use Illuminate\Support\Collection;
use Log;

class CheckpointService
{
    public const STEPS = [
        'organization'              => "1. Organization Data<br>(Kompartemen, Departemen, Cost Center, MasterDataKaryawan)<br><br>[Middle DB Data]",
        'roles'                     => "2. Role Data<br>(Composite, Single, Tcode)<br><br>[Middle DB Data]",
        'job_role_master'           => "3. Job Role Data<br><br>[Upload]",
        'users'                     => "4. User ID<br>(User NIK & User Generic)<br><br>[Middle DB Data]",
        'work_units'                => "5. User ID - Unit Kerja<br><br>[Upload]",
        'job_roles'                 => "6. User ID - Job Role<br><br>[Upload]<br><br><small>(Kelengkapan Data Report UAR)</small>",
        'job_role_composite'        => "7. Job Role - Composite Role<br><br>[Upload]<br><br><small>(Kelengkapan Data Report UAM)</small>",
        'composite_single_mapping'  => "8. Composite - Single Role Mapping<br><br>[Pivot]<br><br><small>(Kelengkapan Hirarki Role)</small>", // ✅ NEW
    ];

    public function steps(): array
    {
        return self::STEPS;
    }

    public function markCompleted(string $companyCode, int $periodeId, string $step, array $payload = []): ProcessCheckpoint
    {
        return ProcessCheckpoint::updateOrCreate(
            [
                'company_code' => $companyCode,
                'periode_id'   => $periodeId,
                'step'         => $step,
            ],
            [
                'status'       => ProcessCheckpoint::STATUS_COMPLETED,
                'payload'      => $payload,
                'completed_at' => now(),
            ]
        );
    }

    public function getProgress(?int $periodeId, Collection $companies): array
    {
        $matrix = [];
        foreach (self::STEPS as $key => $label) {
            foreach ($companies as $company) {
                $matrix[$key][$company->company_code] = [
                    'status'       => ProcessCheckpoint::STATUS_PENDING,
                    'completed_at' => null,
                    'payload'      => null,
                ];
            }
        }

        if (! $periodeId) {
            return $matrix;
        }

        $records = ProcessCheckpoint::query()
            ->where('periode_id', $periodeId)
            ->get();

        foreach ($records as $record) {
            if (! isset($matrix[$record->step][$record->company_code])) {
                continue;
            }

            $matrix[$record->step][$record->company_code] = [
                'status'       => $record->status,
                'completed_at' => $record->completed_at,
                'payload'      => $record->payload,
            ];
        }

        return $matrix;
    }

    public function refresh(int $periodeId, Collection $companies): void
    {
        foreach ($companies as $company) {
            foreach ($this->checkers() as $step => $checker) {
                $result = $checker($company, $periodeId);

                $status = $result['status']
                    ?? ($result['completed'] ? ProcessCheckpoint::STATUS_COMPLETED : ProcessCheckpoint::STATUS_PENDING);

                $completedAt = $status === ProcessCheckpoint::STATUS_COMPLETED
                    ? ($result['completed_at'] ?? now())
                    : null;

                ProcessCheckpoint::updateOrCreate(
                    [
                        'company_code' => $company->company_code,
                        'periode_id'   => $periodeId,
                        'step'         => $step,
                    ],
                    [
                        'status'       => $status,
                        'payload'      => $result['payload'] ?? [],
                        'completed_at' => $completedAt,
                    ]
                );
            }
        }
    }

    protected function checkers(): array
    {
        return [
            // Accept $periodeId for signature consistency
            'organization' => function (Company $company, int $periodeId): array {
                $code = $company->company_code;
                $komCount  = Kompartemen::where('company_id', $code)->count();
                $depCount  = Departemen::where('company_id', $code)->count();
                $costCount = CostCenter::where('company_id', $code)->count();
                $mdkCount  = MasterDataKaryawanLocal::where(fn($q) => $q->where('company', $code))->count();

                $completed = $komCount > 0 || $depCount > 0 || $costCount > 0 || $mdkCount > 0;

                return [
                    'completed' => $completed,
                    'payload'   => [
                        'summary' => "Kompartemen: {$komCount} <br> Departemen: {$depCount} <br> Cost Center: {$costCount} <br> MDK: {$mdkCount}"
                    ],
                ];
            },

            // Roles excluding JobRole (now on its own step)
            'roles' => function (Company $company, int $periodeId): array {
                $code       = $company->company_code;
                $composites = CompositeRole::where('company_id', $code)->count();
                $single     = SingleRole::count();
                $tcodes     = Tcode::count();

                $completed = $composites > 0 && $single > 0 && $tcodes > 0;

                return [
                    'completed' => $completed,
                    'payload'   => [
                        'summary' => "Composite: {$composites} <br> Single: {$single} <br> Tcode: {$tcodes}"
                    ],
                ];
            },

            // New step: Job Role master (Upload)
            'job_role_master' => function (Company $company, int $periodeId): array {
                $code  = $company->company_code;
                $group = $company->shortname ?? $code;

                // Count master job roles (distinct definitions uploaded)
                $jobRoles = JobRole::where('company_id', $code)->count();

                // Count users (NIK + Generic) per company/group
                $totalNik     = userNIK::where('group', $group)->where('periode_id', $periodeId)->count();
                $totalGeneric = userGeneric::where('group', $group)->where('periode_id', $periodeId)->count();

                $mappedUsers = $totalNik + $totalGeneric;

                // Determine status per requirement:
                // If JobRole < (NIK+Generic mapped) => in_progress
                // If JobRole > (NIK+Generic mapped) => failed (error)
                // If equal => completed
                // If either side zero => pending
                if ($jobRoles === 0 || $mappedUsers === 0) {
                    $status = 'pending';
                } elseif ($jobRoles > $mappedUsers) {
                    $status = 'failed';
                } elseif ($jobRoles === $mappedUsers) {
                    $status = 'completed';
                } else { // $jobRoles < $mappedUsers
                    $status = 'in_progress';
                }

                $summary = "JobRole Master: {$jobRoles} <br> Users Mapped (NIK+Generic): {$mappedUsers}";
                $diff = $mappedUsers - $jobRoles;
                // $diff = $jobRoles - $mappedUsers;
                if ($status === 'failed') {
                    $summary .= " <br><br><span class='text-danger'>Jumlah Job Role ({$jobRoles}) lebih banyak dari User ID ({$mappedUsers}) terdaftar.<br> Selisih: <b>{$diff} data</b></span>";
                } elseif ($status === 'in_progress') {
                    $summary .= " <br><br><span style='color: #FF4D00;'>Jumlah Job Role ({$jobRoles}) lebih sedikit dari User ID ({$mappedUsers}) terdaftar.<br> Selisih: <b>{$diff} data</b></span>";
                }

                return [
                    'status'  => $status,
                    'payload' => [
                        'summary' => $summary,
                        'detail'  => [
                            'job_role_count'    => $jobRoles,
                            'mapped_user_count' => $mappedUsers,
                            'difference'        => $diff,
                        ],
                    ],
                ];
            },

            'users' => function (Company $company, int $periodeId): array {
                $group = $company->shortname ?? $company->company_code;
                $today = now()->toDateString();

                $nik = userNIK::where('group', $group)
                    ->where('periode_id', $periodeId)
                    ->where(function ($w) use ($today) {
                        $w->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today);
                    })
                    ->count();

                $generic = userGeneric::where('group', $group)
                    ->where('periode_id', $periodeId)
                    ->where(function ($w) use ($today) {
                        $w->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today);
                    })
                    ->count();

                $sumUser   = $nik + $generic;
                $completed = $nik > 0 || $generic > 0;

                return [
                    'completed' => $completed,
                    'payload'   => [
                        'summary' => "UserNIK: {$nik} <br> UserGeneric: {$generic} <br> Total: {$sumUser}",
                    ],
                ];
            },

            'work_units' => function (Company $company, int $periodeId): array {
                $group = $company->shortname ?? $company->company_code;

                $totalNik     = userNIK::where('group', $group)->where('periode_id', $periodeId)->count();
                $totalGeneric = userGeneric::where('group', $group)->where('periode_id', $periodeId)->count();

                $nikWithUnit = userNIK::where('group', $group)
                    ->where('periode_id', $periodeId)
                    ->whereHas('unitKerja', fn($q) => $q->where('periode_id', $periodeId))
                    ->count();

                $genericWithUnit = userGeneric::where('group', $group)
                    ->where('periode_id', $periodeId)
                    ->whereHas('userGenericUnitKerja', fn($q) => $q->where('periode_id', $periodeId))
                    ->count();

                $diffNik     = $totalNik - $nikWithUnit;
                $diffGeneric = $totalGeneric - $genericWithUnit;

                if (($totalNik + $totalGeneric) === 0 || ($nikWithUnit + $genericWithUnit) === 0) {
                    $status = 'pending';
                } elseif ($diffNik < 0 || $diffGeneric < 0) {
                    $status = 'failed';
                } elseif ($diffNik === 0 && $diffGeneric === 0) {
                    $status = 'completed';
                } else {
                    $status = 'in_progress';
                }

                $nikPct = $totalNik ? (int) floor(min(100, $nikWithUnit / $totalNik * 100)) : 0;
                $genPct = $totalGeneric ? (int) floor(min(100, $genericWithUnit / $totalGeneric * 100)) : 0;
                $sumWithUnit       = $nikWithUnit + $genericWithUnit;
                $sumWithUnitTotal  = $totalNik + $totalGeneric;

                $summary = "NIK - Unit Kerja: {$nikWithUnit}/{$totalNik} ({$nikPct}%) <br> Generic - Unit Kerja: {$genericWithUnit}/{$totalGeneric} ({$genPct}%) <br>  <br> Total - Unit Kerja: {$sumWithUnit}/{$sumWithUnitTotal}";
                if ($status === 'failed') {
                    $summary .= " [ERROR: mapping melebihi total user]";
                }

                return [
                    'status'  => $status,
                    'payload' => ['summary' => $summary],
                ];
            },

            'job_roles' => function (Company $company, int $periodeId): array {
                $group = $company->shortname ?? $company->company_code;

                $totalNik     = userNIK::where('group', $group)->where('periode_id', $periodeId)->count();
                $totalGeneric = userGeneric::where('group', $group)->where('periode_id', $periodeId)->count();

                $nikWithRole = userNIK::where('group', $group)
                    ->where('periode_id', $periodeId)
                    ->whereHas('NIKJobRole', fn($q) => $q->where('periode_id', $periodeId))
                    ->count();

                $genericWithRole = userGeneric::where('group', $group)
                    ->where('periode_id', $periodeId)
                    ->whereHas('NIKJobRole', fn($q) => $q->where('periode_id', $periodeId))
                    ->count();

                $diffNikRole     = $totalNik - $nikWithRole;
                $diffGenericRole = $totalGeneric - $genericWithRole;

                if (($totalNik + $totalGeneric) === 0 || ($nikWithRole + $genericWithRole) === 0) {
                    $status = 'pending';
                } elseif ($diffNikRole < 0 || $diffGenericRole < 0) {
                    $status = 'failed';
                } elseif ($diffNikRole === 0 && $diffGenericRole === 0) {
                    $status = 'completed';
                } else {
                    $status = 'in_progress';
                }

                $nikPct = $totalNik ? (int) floor(min(100, $nikWithRole / $totalNik * 100)) : 0;
                $genPct = $totalGeneric ? (int) floor(min(100, $genericWithRole / $totalGeneric * 100)) : 0;
                $sumWithRole      = $nikWithRole + $genericWithRole;
                $sumWithRoleTotal = $totalNik + $totalGeneric;

                $summary = "NIK Role: {$nikWithRole}/{$totalNik} ({$nikPct}%) <br> Generic Role: {$genericWithRole}/{$totalGeneric} ({$genPct}%) <br> <br> Total Role: {$sumWithRole}/{$sumWithRoleTotal}";
                if ($status === 'failed') {
                    $summary .= " [ERROR: mapping melebihi total user]";
                }

                return [
                    'status'  => $status,
                    'payload' => ['summary' => $summary],
                ];
            },

            // ✅ FIXED: Job Role - Composite Role checker
            'job_role_composite' => function (Company $company, int $periodeId): array {
                $code  = $company->company_code;
                $group = $company->shortname ?? $code;

                $jobRoleCount = JobRole::where('company_id', $code)->count();
                $totalCompositeRoles = CompositeRole::where('company_id', $code)->count();

                // Count users (NIK + Generic) per company/group
                $totalNik     = userNIK::where('group', $group)->where('periode_id', $periodeId)->count();
                $totalGeneric = userGeneric::where('group', $group)->where('periode_id', $periodeId)->count();

                $mappedUsers = $totalNik + $totalGeneric;

                if ($jobRoleCount === 0 || $mappedUsers === 0) {
                    $baseStatus = 'pending';
                } elseif ($jobRoleCount > $mappedUsers) {
                    $baseStatus = 'failed';
                } elseif ($jobRoleCount === $mappedUsers) {
                    $baseStatus = 'completed';
                } else {
                    $baseStatus = 'in_progress';
                }

                // Perbandingan (coverage) Job Role & Composite Role terhadap User ID terdaftar (NIK+Generic)
                $jobRolePct = $mappedUsers > 0 ? round(($jobRoleCount / $mappedUsers) * 100, 2) : 0;
                $compositePct = $mappedUsers > 0 ? round(($totalCompositeRoles / $mappedUsers) * 100, 2) : 0;

                // Detail mapping (tidak mengubah status)
                $jobRolesWithComposite = JobRole::where('company_id', $code)
                    ->whereHas('compositeRole')
                    ->count();

                $compositesWithJobRole = CompositeRole::where('company_id', $code)
                    ->whereHas('jobRole')
                    ->count();

                $duplicateJobRoleTargets = CompositeRole::where('tr_composite_roles.company_id', $code)
                    ->whereNotNull('tr_composite_roles.jabatan_id')
                    ->whereNull('tr_composite_roles.deleted_at')
                    ->select('tr_composite_roles.jabatan_id')
                    ->groupBy('tr_composite_roles.jabatan_id')
                    ->havingRaw('COUNT(DISTINCT tr_composite_roles.id) > 1')
                    ->get()
                    ->count();

                $jobRolesWithMultipleCompositeRoles = JobRole::where('tr_job_roles.company_id', $code)
                    ->join('tr_composite_roles', 'tr_composite_roles.jabatan_id', '=', 'tr_job_roles.id')
                    ->whereNull('tr_composite_roles.deleted_at')
                    ->select('tr_job_roles.id')
                    ->groupBy('tr_job_roles.id')
                    ->havingRaw('COUNT(DISTINCT tr_composite_roles.id) > 1')
                    ->get()
                    ->count();

                $hasDuplicates = $duplicateJobRoleTargets > 0;

                $status = $baseStatus;
                if ($hasDuplicates && in_array($baseStatus, ['completed', 'in_progress'], true)) {
                    $status = 'warning';
                }

                $diff = $mappedUsers - $jobRoleCount;
                // $diff = $jobRoles - $mappedUsers;

                // Summary diawali info mapping baru
                $summary  = "Job Role: {$jobRoleCount} / {$mappedUsers} ({$jobRolePct}%)<br>";
                $summary .= "Composite Role: {$totalCompositeRoles} / {$mappedUsers} ({$compositePct}%)";


                // Info tambahan
                $summary .= "<br><br>Info Mapping:<br>";
                $summary .= "JobRole → Composite: {$jobRolesWithComposite} / {$compositesWithJobRole} <br>";

                // Status selisih
                if ($baseStatus === 'failed') {
                    $summary .= "<br><span class='text-danger'>Jumlah Job Role ({$jobRoleCount}) lebih banyak dari User ID ({$mappedUsers}).<br>Selisih: <b>{$diff} Data</b></span>";
                } elseif ($baseStatus === 'in_progress') {
                    $summary .= "<br><span style='color:#FF4D00;'>Jumlah Job Role ({$jobRoleCount}) lebih sedikit dari User ID ({$mappedUsers}).<br>Selisih: <b>{$diff} Data</b></span>";
                }


                if ($hasDuplicates) {
                    $summary .= "<br><br>⚠️ <strong>DUPLIKAT:</strong><br>";
                    $summary .= "Job Roles direferensikan oleh >1 Composite Role: {$duplicateJobRoleTargets}";
                }

                return [
                    'status'  => $status,
                    'payload' => [
                        'summary' => $summary,
                        'detail'  => [
                            'job_role_count'          => $jobRoleCount,
                            'composite_role_count'    => $totalCompositeRoles,
                            'mapped_user_count'       => $mappedUsers,
                            'job_role_pct'            => $jobRolePct,
                            'composite_role_pct'      => $compositePct,
                            'difference'              => $diff,
                        ],
                        'duplicates' => [
                            'job_roles_multiple_composites' => $jobRolesWithMultipleCompositeRoles,
                            'composites_multiple_job_roles' => $duplicateJobRoleTargets,
                        ],
                    ],
                ];
            },

            // ✅ NEW: Composite - Single Role Mapping
            'composite_single_mapping' => function (Company $company, int $periodeId): array {
                $code = $company->company_code;

                $totalComposite = CompositeRole::where('company_id', $code)->count();

                $compositeWithSingles = CompositeRole::where('company_id', $code)
                    ->whereHas('singleRoles')
                    ->count();

                $compositeWithoutSingles = $totalComposite - $compositeWithSingles;

                // Status fokus ke sisa yang belum terhubung
                if ($compositeWithoutSingles === 0 && $totalComposite > 0) {
                    $status = 'completed';
                } else {
                    $status = 'in_progress'; // masih ada yang belum terhubung
                }

                // Summary diawali jumlah yang belum terhubung
                $summary  = "Composite tanpa Single Role: {$compositeWithoutSingles} <br>";

                return [
                    'status'  => $status,
                    'payload' => [
                        'summary' => $summary,
                        'detail'  => [
                            'composite_without_single'   => $compositeWithoutSingles,
                        ]
                    ],
                ];
            },
        ];
    }
}
