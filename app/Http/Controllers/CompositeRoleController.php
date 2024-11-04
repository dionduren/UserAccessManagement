<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\CompositeRole;

use Illuminate\Http\Request;

class CompositeRoleController extends Controller
{
    public function index()
    {
        return view('composite_roles.index');
    }

    // Method to fetch composite role data for AJAX DataTable
    public function getCompositeRolesAjax()
    {
        $compositeRoles = CompositeRole::with(['jobRole', 'company'])->get();

        // Format the data for DataTable
        $formattedData = $compositeRoles->map(function ($role) {
            return [
                'company' => $role->company->name ?? 'N/A',
                'name' => $role->nama,
                'job_role' => $role->jobRole->nama_jabatan ?? 'Not Assigned',
                'description' => $role->deskripsi,
                'actions' => view('composite_roles.partials.actions', ['role' => $role])->render(),
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    public function show($id)
    {
        $compositeRole = CompositeRole::with(['jobRole', 'company'])->findOrFail($id);
        return view('composite_roles.show', compact('compositeRole'));
    }


    public function create()
    {
        $companies = Company::all();

        // Structure job roles data by Company > Kompartemen > Departemen
        $job_roles_data = [];
        $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->get();

        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $kompartemenName = $jobRole->kompartemen->name;
            $departemenName = $jobRole->departemen->name;

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama_jabatan' => $jobRole->nama_jabatan,
            ];
        }

        return view('composite_roles.create', compact('companies', 'job_roles_data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'jabatan_id' => 'required|exists:tr_composite_roles,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        CompositeRole::create([
            'company_id' => $request->company_id,
            'jabatan_id' => $request->jabatan_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'created_by' => auth()->id(), // Assumes user authentication
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('composite-roles.index')->with('success', 'Composite Role created successfully.');
    }


    public function edit(CompositeRole $compositeRole)
    {
        $companies = Company::all();

        // Structure job roles data by Company > Kompartemen > Departemen
        $job_roles_data = [];
        $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->get();

        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $kompartemenName = $jobRole->kompartemen->name;
            $departemenName = $jobRole->departemen->name;

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama_jabatan' => $jobRole->nama_jabatan,
            ];
        }

        return view('composite_roles.edit', compact('compositeRole', 'companies', 'job_roles_data'));
    }


    public function update(Request $request, CompositeRole $compositeRole)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama' => 'required|string|unique:tr_composite_roles,nama,' . $compositeRole->id,
            'deskripsi' => 'nullable|string',
            'jabatan_id' => 'required|exists:tr_job_roles,id',
        ]);

        $compositeRole->update($request->all());

        return redirect()->route('composite-roles.index')->with('status', 'Composite role updated successfully.');
    }

    public function destroy(CompositeRole $compositeRole)
    {
        $compositeRole->delete();

        return redirect()->route('composite-roles.index')->with('status', 'Composite role deleted successfully.');
    }

    public function getFilteredData(Request $request)
    {
        $companyId = $request->input('company_id');
        $kompartemenId = $request->input('kompartemen_id');

        // Fetch Kompartemen by Company
        $kompartemens = Kompartemen::where('company_id', $companyId)->get();

        // Fetch Departemen by Kompartemen
        $departemens = $kompartemenId
            ? Departemen::where('kompartemen_id', $kompartemenId)->get()
            : collect(); // empty collection if no kompartemen selected

        // Fetch Job Roles by Kompartemen and Departemen
        $jobRoles = [];
        if ($kompartemenId) {
            $jobRoles = JobRole::where('kompartemen_id', $kompartemenId)
                ->whereIn('departemen_id', $departemens->pluck('id'))
                ->get()
                ->groupBy('departemen_id');
        }

        return response()->json([
            'kompartemens' => $kompartemens,
            'departemens' => $departemens,
            'jobRoles' => $jobRoles,
        ]);
    }
}
