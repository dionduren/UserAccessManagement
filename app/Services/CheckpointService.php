<?php
// filepath: app/Services/CheckpointService.php
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
        'organization' => "1. Organization Data<br>(Company, Kompartemen, Departemen, Cost Center, MasterDataKaryawan)<br><br>[Middle DB Data]",
        'roles'        => "2. Role Data<br>(Job Role, Composite, Single, Tcode)<br><br>[Middle DB Data]",
        'users'        => "3. User ID<br>(User NIK & User Generic)<br><br>[Middle DB Data]",
        'work_units'   => "4. User ID - Unit Kerja<br><br>[Upload]",
        'job_roles'    => "5. User ID - Job Role<br><br>[Upload]",
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
            'organization' => function (Company $company): array {
                $code = $company->company_code;
                $komCount = Kompartemen::where('company_id', $code)->count();
                $depCount = Departemen::where('company_id', $code)->count();
                $costCount = CostCenter::where('company_id', $code)->count();
                $mdkCount = MasterDataKaryawanLocal::where(fn($q) => $q->where('company', $code))->count();

                $completed = $komCount > 0 && $depCount > 0 && $costCount > 0 && $mdkCount > 0;

                return [
                    'completed' => $completed,
                    'payload'   => [
                        'summary' => "Kompartemen: {$komCount}, Departemen: {$depCount}, Cost Center: {$costCount}, MDK: {$mdkCount}"
                    ],
                ];
            },
            'roles' => function (Company $company): array {
                $code = $company->company_code;
                $jobRoles = JobRole::where('company_id', $code)->count();
                $composites = CompositeRole::where('company_id', $code)->count();
                $singleRoles = SingleRole::count();
                $tcodes = Tcode::count();

                $completed = $jobRoles > 0 && $composites > 0 && $singleRoles > 0 && $tcodes > 0;

                return [
                    'completed' => $completed,
                    'payload'   => [
                        'summary' => "JobRole: {$jobRoles}, Composite: {$composites}, Single: {$singleRoles}, Tcode: {$tcodes}"
                    ],
                ];
            },
            'users' => function (Company $company): array {
                $group = $company->shortname ?? $company->company_code;
                $nik = userNIK::where('group', $group)->count();
                $generic = userGeneric::where('group', $group)->count();
                $sumUser = $nik + $generic;

                $completed = $nik > 0 || $generic > 0;

                return [
                    'completed' => $completed,
                    'payload'   => [
                        'summary' => "UserNIK: {$nik}, UserGeneric: {$generic}, Total: {$sumUser}",
                    ],
                ];
            },
            'work_units' => function (Company $company): array {
                $group = $company->shortname ?? $company->company_code;

                $totalNik     = userNIK::where('group', $group)->count();
                $totalGeneric = userGeneric::where('group', $group)->count();

                $nikWithUnit     = userNIK::where('group', $group)->whereHas('unitKerja')->count();
                $genericWithUnit = userGeneric::where('group', $group)->whereHas('userGenericUnitKerja')->count();

                // Raw differences (can be negative => data error / duplication)
                $diffNik     = $totalNik - $nikWithUnit;
                $diffGeneric = $totalGeneric - $genericWithUnit;

                if (($totalNik + $totalGeneric) === 0 || ($nikWithUnit + $genericWithUnit) === 0) {
                    $status = 'pending';
                } elseif ($diffNik < 0 || $diffGeneric < 0) {
                    $status = 'failed'; // error status
                } elseif ($diffNik === 0 && $diffGeneric === 0) {
                    $status = 'completed';
                } else {
                    $status = 'in_progress';
                }

                $nikPct = $totalNik ? round(min(100, $nikWithUnit / $totalNik * 100)) : 0;
                $genPct = $totalGeneric ? round(min(100, $genericWithUnit / $totalGeneric * 100)) : 0;

                $summary = "NIK - Unit Kerja: {$nikWithUnit}/{$totalNik} ({$nikPct}%), Generic - Unit Kerja: {$genericWithUnit}/{$totalGeneric} ({$genPct}%)";
                if ($status === 'failed') {
                    $summary .= " [ERROR: mapping melebihi total user]";
                }

                return [
                    'status'  => $status,
                    'payload' => ['summary' => $summary],
                ];
            },
            'job_roles' => function (Company $company): array {
                $group = $company->shortname ?? $company->company_code;

                $totalNik     = userNIK::where('group', $group)->count();
                $totalGeneric = userGeneric::where('group', $group)->count();

                $nikWithRole     = userNIK::where('group', $group)->whereHas('NIKJobRole')->count();
                $genericWithRole = userGeneric::where('group', $group)->whereHas('NIKJobRole')->count();

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

                $nikPct = $totalNik ? round(min(100, $nikWithRole / $totalNik * 100)) : 0;
                $genPct = $totalGeneric ? round(min(100, $genericWithRole / $totalGeneric * 100)) : 0;

                $summary = "NIK Role: {$nikWithRole}/{$totalNik} ({$nikPct}%), Generic Role: {$genericWithRole}/{$totalGeneric} ({$genPct}%)";
                if ($status === 'failed') {
                    $summary .= " [ERROR: mapping melebihi total user]";
                }

                return [
                    'status'  => $status,
                    'payload' => ['summary' => $summary],
                ];
            },
        ];
    }
}
