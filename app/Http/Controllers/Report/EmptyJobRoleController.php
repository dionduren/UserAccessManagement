<?php

namespace App\Http\Controllers\Report;

use App\Models\JobRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmptyJobRoleController extends Controller
{

    public function index(Request $request)
    {
        $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])
            ->whereDoesntHave('NIKJobRole')
            ->get();

        return view('report.empty_job_role.index', compact('jobRoles'));
    }
}
