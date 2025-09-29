<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\CompositeRole;
use App\Models\JobRole;
use Illuminate\Http\Request;

class AnomaliDataReportController extends Controller
{
    public function jobRoleMultipleComposite(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('report.anomali.job_role_multi_composite');
        }

        $jobRoleIds = CompositeRole::query()
            ->whereNotNull('jabatan_id')
            ->selectRaw('jabatan_id, COUNT(*) AS composite_total')
            ->groupBy('jabatan_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('jabatan_id')
            ->toArray();

        if (empty($jobRoleIds)) {
            return response()->json(['data' => []]);
        }

        $jobRoles = JobRole::query()
            ->with(['company', 'kompartemen', 'departemen'])
            ->whereIn('id', $jobRoleIds)
            ->get()
            ->keyBy('id');

        $composites = CompositeRole::query()
            ->whereIn('jabatan_id', $jobRoleIds)
            ->orderBy('nama')
            ->get()
            ->groupBy('jabatan_id');

        $rows = collect($jobRoleIds)->map(function ($jobRoleId) use ($jobRoles, $composites) {
            $jobRole        = $jobRoles->get($jobRoleId);
            $list           = $composites->get($jobRoleId, collect());
            $compositeNames = $list->pluck('nama')->filter();

            $compositeNamesHtml = $compositeNames->isNotEmpty()
                ? '<ul class="mb-0 ps-3">' . $compositeNames->map(fn($name) => '<li>' . e($name) . '</li>')->implode('') . '</ul>'
                : '-';

            return [
                'job_role_id'     => $jobRole?->job_role_id,
                'job_role_name'   => $jobRole?->nama,
                'company'         => $jobRole?->company->nama ?? '-',
                'kompartemen'     => $jobRole?->kompartemen->nama ?? '-',
                'departemen'      => $jobRole?->departemen->nama ?? '-',
                'composite_total' => $list->count(),
                'composite_names' => $compositeNamesHtml,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function compositeMultipleJobRole(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('report.anomali.composite_multi_job_role');
        }

        $compositeNames = CompositeRole::query()
            ->whereNotNull('nama')
            ->selectRaw('nama, COUNT(DISTINCT jabatan_id) AS job_role_total')
            ->groupBy('nama')
            ->havingRaw('COUNT(DISTINCT jabatan_id) > 1')
            ->pluck('nama')
            ->toArray();

        if (empty($compositeNames)) {
            return response()->json(['data' => []]);
        }

        $composites = CompositeRole::query()
            ->with(['jobRole.company', 'jobRole.kompartemen', 'jobRole.departemen'])
            ->whereIn('nama', $compositeNames)
            ->orderBy('nama')
            ->get()
            ->groupBy('nama');

        $rows = collect($compositeNames)->map(function ($name) use ($composites) {
            $group     = $composites->get($name, collect());
            $jobRoles  = $group->pluck('jobRole')->filter();

            return [
                'composite_name'    => $name,
                'company'           => optional($group->first())->company->nama ?? '-',
                'job_role_total'    => $jobRoles->count(),
                'job_role_codes'    => $jobRoles->pluck('job_role_id')->filter()->implode(', '),
                'job_role_names'    => $jobRoles->pluck('nama')->filter()->implode(', '),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    // ============= UNTUK DATA YANG COMPANY BERBEDA NAMA SAMA =============
    // public function jobRoleSameName(Request $request)
    // {
    //     if (! $request->wantsJson()) {
    //         return view('report.anomali.job_role_same_name');
    //     }

    //     $duplicateNames = JobRole::query()
    //         ->whereNotNull('nama')
    //         ->selectRaw('nama, COUNT(*) AS total')
    //         ->groupBy('nama')
    //         ->havingRaw('COUNT(*) > 1')
    //         ->pluck('nama')
    //         ->toArray();

    //     if (empty($duplicateNames)) {
    //         return response()->json(['data' => []]);
    //     }

    //     $jobRoles = JobRole::query()
    //         ->with(['company', 'kompartemen', 'departemen'])
    //         ->whereIn('nama', $duplicateNames)
    //         ->orderBy('nama')
    //         ->orderBy('job_role_id')
    //         ->get();

    //     $rows = $jobRoles->map(function ($jobRole) {
    //         return [
    //             'job_role_name' => $jobRole->nama,
    //             'job_role_code' => $jobRole->job_role_id,
    //             'company'       => optional($jobRole->company)->nama ?? '-',
    //             'kompartemen'   => optional($jobRole->kompartemen)->nama ?? '-',
    //             'departemen'    => optional($jobRole->departemen)->nama ?? '-',
    //         ];
    //     });

    //     return response()->json(['data' => $rows]);
    // }

    public function jobRoleSameName(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('report.anomali.job_role_same_name');
        }

        $duplicateGroups = JobRole::query()
            ->whereNotNull('nama')
            ->whereNotNull('company_id')
            ->selectRaw('company_id, nama, COUNT(*) AS total')
            ->groupBy('company_id', 'nama')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateGroups->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $jobRoles = JobRole::query()
            ->with(['company', 'kompartemen', 'departemen'])
            ->where(function ($query) use ($duplicateGroups) {
                $duplicateGroups->each(function ($group) use ($query) {
                    $query->orWhere(function ($inner) use ($group) {
                        $inner->where('company_id', $group->company_id)
                            ->where('nama', $group->nama);
                    });
                });
            })
            ->orderBy('nama')
            ->orderBy('company_id')
            ->orderBy('job_role_id')
            ->get();

        $rows = $jobRoles->map(function ($jobRole) {
            return [
                'job_role_name' => $jobRole->nama,
                'job_role_code' => $jobRole->job_role_id,
                'company'       => optional($jobRole->company)->nama ?? '-',
                'kompartemen'   => optional($jobRole->kompartemen)->nama ?? '-',
                'departemen'    => optional($jobRole->departemen)->nama ?? '-',
            ];
        });

        return response()->json(['data' => $rows]);
    }
}
