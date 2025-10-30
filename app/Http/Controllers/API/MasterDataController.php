<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\JobRole;

class MasterDataController extends Controller
{
    public function hierarchy(Request $request)
    {
        $companyCode = $request->query('company');
        $activeOnly  = filter_var($request->query('active_only', 'false'), FILTER_VALIDATE_BOOLEAN);

        $companies = Company::with([
            'kompartemen.departemen.jobRoles',
            'kompartemen.jobRoles',
            'departemenWithoutKompartemen.jobRoles',
            'jobRolesWithoutRelations',
        ])
            ->when($companyCode, fn($q) => $q->where('company_code', $companyCode))
            ->get();

        // Only users from A000 can see roles without job_role_id or the "job_roles_without_relations" block
        $isSuper = auth()->check() && optional(auth()->user()->loginDetail)->company_code === 'A000';

        // Helper to process a roles collection into an array, with filtering and sorting
        $processRoles = function ($roles) use ($activeOnly, $isSuper) {
            // Filter by active status if requested
            $filtered = $roles->filter(function ($jr) use ($activeOnly) {
                return !$activeOnly || ($jr->status ?? null) === 'Active';
            });

            // Hide roles without job_role_id for non-A000
            if (!$isSuper) {
                $filtered = $filtered->filter(function ($jr) {
                    return !empty($jr->job_role_id);
                });
            }

            // Sort so roles without job_role_id go to the bottom (visible only for A000)
            $sorted = $filtered->sortBy(function ($jr) {
                return empty($jr->job_role_id) ? 1 : 0;
            });

            // Map to array shape
            return $sorted->values()->map(function ($jr) {
                return [
                    'id'           => $jr->id,
                    'job_role_id'  => $jr->job_role_id,
                    'nama'         => $jr->nama,
                    'deskripsi'    => $jr->deskripsi ?? 'N/A',
                    'status'       => $jr->status ?? 'Deactive',
                    'flagged'      => $jr->flagged ?? false,
                ];
            })->values();
        };

        $data = $companies->map(function ($company) use ($activeOnly, $isSuper, $processRoles) {
            return [
                'company_id'   => $company->company_code,
                'company_name' => $company->nama,

                'kompartemen'  => $company->kompartemen->map(function ($komp) use ($processRoles) {
                    // Kompartemen-level job roles (exclude departemen-bound)
                    $kompJobRoles = $processRoles(
                        $komp->jobRoles->filter(function ($jr) {
                            return is_null($jr->departemen_id);
                        })
                    );

                    return [
                        'kompartemen_id' => $komp->kompartemen_id,
                        'nama'           => $komp->nama,
                        'cost_center'    => $komp->cost_center ?? 'N/A',
                        'departemen'     => $komp->departemen->map(function ($dep) use ($processRoles) {
                            return [
                                'departemen_id' => $dep->departemen_id,
                                'nama'          => $dep->nama,
                                'cost_center'   => $dep->cost_center ?? 'N/A',
                                'job_roles'     => $processRoles($dep->jobRoles),
                            ];
                        })->values(),
                        'job_roles'      => $kompJobRoles,
                    ];
                })->values(),

                'departemen_without_kompartemen' => $company->departemenWithoutKompartemen->map(function ($dep) use ($processRoles) {
                    return [
                        'departemen_id' => $dep->departemen_id,
                        'nama'          => $dep->nama,
                        'cost_center'   => $dep->cost_center ?? 'N/A',
                        'job_roles'     => $processRoles($dep->jobRoles),
                    ];
                })->values(),

                // Only include this block for A000 users
                'job_roles_without_relations' => $isSuper
                    ? $processRoles($company->jobRolesWithoutRelations)
                    : [],
            ];
        })->values();

        return response()->json($data);
    }

    public function jobRolesByPeriode(Request $request)
    {
        $periodeId = $request->query('periode_id');
        $activeOnly = filter_var($request->query('active_only', 'false'), FILTER_VALIDATE_BOOLEAN);

        if (!$periodeId) {
            return response()->json([]);
        }

        $isSuper = auth()->check() && optional(auth()->user()->loginDetail)->company_code === 'A000';

        // Get job roles that have NIKJobRole assignments in the specified periode
        $query = JobRole::query()
            ->select('tr_job_roles.id', 'tr_job_roles.job_role_id', 'tr_job_roles.nama', 'tr_job_roles.status', 'tr_job_roles.flagged')
            ->join('tr_ussm_job_role', 'tr_job_roles.job_role_id', '=', 'tr_ussm_job_role.job_role_id')
            ->where('tr_ussm_job_role.periode_id', $periodeId)
            ->whereNull('tr_ussm_job_role.deleted_at')
            ->whereNull('tr_job_roles.deleted_at')
            ->when($activeOnly, fn($q) => $q->where('tr_job_roles.status', 'Active'))
            ->when(!$isSuper, fn($q) => $q->whereNotNull('tr_job_roles.job_role_id'))
            ->distinct();

        $jobRoles = $query->get();

        // Sort: roles with job_role_id first, then by name
        $sorted = $jobRoles->sortBy(function ($jr) {
            return [empty($jr->job_role_id) ? 1 : 0, $jr->nama];
        })->values();

        return response()->json($sorted->map(function ($jr) {
            return [
                'id'          => $jr->id,
                'job_role_id' => $jr->job_role_id,
                'nama'        => $jr->nama,
                'status'      => $jr->status ?? 'Deactive',
                'flagged'     => $jr->flagged ?? false,
            ];
        })->values());
    }
}
