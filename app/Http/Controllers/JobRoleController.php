<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

class JobRoleController extends Controller
{
    public function index()
    {
        $job_roles = JobRole::with(['compositeRole', 'company', 'kompartemen', 'departemen'])->get();
        return view('job_roles.index', compact('job_roles'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('job_roles.create', compact('companies'));
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

    public function show($id)
    {
        $jobRole = JobRole::with(['company', 'kompartemen', 'departemen', 'compositeRole'])->findOrFail($id);
        return view('job_roles.show', compact('jobRole'));
    }

    public function edit(JobRole $jobRole)
    {
        $companies = Company::all();

        // Load kompartemens and departemens based on the current jobRole's selections
        $kompartemens = Kompartemen::where('company_id', $jobRole->company_id)->get();
        $departemens = Departemen::where('kompartemen_id', $jobRole->kompartemen_id)->get();

        return view('job_roles.edit', compact('jobRole', 'companies', 'kompartemens', 'departemens'));
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

    /**
     * AJAX Method to fetch kompartemen and departemen based on selected company.
     */
    public function getFilteredData(Request $request)
    {
        if ($request->has('company_id')) {
            // Fetch kompartemens based on selected company
            $kompartemens = Kompartemen::where('company_id', $request->input('company_id'))->get();

            return response()->json([
                'kompartemens' => $kompartemens,
            ]);
        }

        if ($request->has('kompartemen_id')) {
            // Fetch departemens based on selected kompartemen
            $departemens = Departemen::where('kompartemen_id', $request->input('kompartemen_id'))->get();

            return response()->json([
                'departemens' => $departemens,
            ]);
        }

        return response()->json([], 400); // Bad request if parameters are missing
    }
}
