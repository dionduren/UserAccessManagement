<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

class JobRoleController extends Controller
{
    public function index()
    {
        $job_roles = JobRole::all();
        return view('job_roles.index', compact('job_roles'));
    }

    public function create()
    {
        $companies = Company::all();
        $kompartemens = Kompartemen::all();
        $departemens = Departemen::all();
        return view('job_roles.create', compact('companies', 'kompartemens', 'departemens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama_jabatan' => 'required|string|unique:tr_job_roles,nama_jabatan',
            'deskripsi' => 'nullable|string',
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'departemen_id' => 'required|exists:ms_departemen,id',
        ]);

        JobRole::create($request->all());

        return redirect()->route('job-roles.index')->with('status', 'Job role created successfully.');
    }

    public function edit(JobRole $jobRole)
    {
        return view('job_roles.edit', compact('jobRole'));
    }

    public function update(Request $request, JobRole $jobRole)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama_jabatan' => 'required|string|unique:tr_job_roles,nama_jabatan,' . $jobRole->id,
            'deskripsi' => 'nullable|string',
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'departemen_id' => 'required|exists:ms_departemen,id',
        ]);

        $jobRole->update($request->all());

        return redirect()->route('job-roles.index')->with('status', 'Job role updated successfully.');
    }

    public function destroy(JobRole $jobRole)
    {
        $jobRole->delete();

        return redirect()->route('job-roles.index')->with('status', 'Job role deleted successfully.');
    }
}
