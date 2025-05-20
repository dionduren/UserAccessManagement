<?php

namespace App\Http\Controllers\Report;

use App\Models\JobRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class JobRoleReportController extends Controller
{
    public function index(Request $request)
    {
        $jobRoles = JobRole::with([
            'company:company_code,nama',
            'kompartemen:kompartemen_id,nama',
            'departemen:departemen_id,nama',
            'NIKJobRole' => function ($query) {
                $query->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->select('id', 'nik', 'job_role_id');
            },
            'NIKJobRole.userDetail:nik,nama'
        ])
            ->whereHas('NIKJobRole', function ($query) {
                $query->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->whereHas('userDetail'); // Add this to ensure userDetail exists
            })
            ->select([
                'id',
                'job_role_id',
                'company_id',
                'kompartemen_id',
                'departemen_id',
                'nama'
            ])
            ->orderBy('nama')
            ->get();

        // For debugging
        \Log::info('Job Roles Query:', [
            'sql' => JobRole::with([
                'company:company_code,nama',
                'kompartemen:kompartemen_id,nama',
                'departemen:departemen_id,nama',
                'NIKJobRole',
                'NIKJobRole.userDetail'
            ])->toSql(),
            'count' => $jobRoles->count(),
            'sample' => $jobRoles->first()
        ]);

        return view('report.filled_job_role.index', compact('jobRoles'));
    }

    public function index_empty(Request $request)
    {
        $jobRoles = JobRole::with([
            'company:company_code,nama',
            'kompartemen:kompartemen_id,nama',
            'departemen:departemen_id,nama'
        ])
            ->whereDoesntHave('NIKJobRole', function ($query) {
                $query->whereNull('deleted_at')
                    ->where('is_active', true);
            })
            ->get();

        return view('report.empty_job_role.index', compact('jobRoles'));
    }
}
