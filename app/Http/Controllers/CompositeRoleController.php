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
        $composite_roles = CompositeRole::with(['company', 'jobRole'])->get();
        return view('composite_roles.index', compact('composite_roles'));
    }

    public function show($id)
    {
        $compositeRole = CompositeRole::with(['jobRole', 'company', 'singleRoles'])->findOrFail($id);

        // Load a partial view and pass the data to it for rendering in the modal
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
            'jabatan_id' => 'nullable|exists:tr_job_roles,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        // Create the Composite Role
        CompositeRole::create([
            'company_id' => $request->company_id,
            'jabatan_id' => $request->jabatan_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
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
            'jabatan_id' => 'nullable|exists:tr_job_roles,id',
            'nama' => 'required|string|unique:tr_composite_roles,nama,' . $compositeRole->id,
            'deskripsi' => 'nullable|string',
        ]);

        // Update Composite Role details
        $compositeRole->update([
            'company_id' => $request->company_id,
            'jabatan_id' => $request->jabatan_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('composite-roles.index')->with('status', 'Composite role updated successfully.');
    }


    public function destroy(CompositeRole $compositeRole)
    {
        $compositeRole->delete();

        return redirect()->route('composite-roles.index')->with('status', 'Composite role deleted successfully.');
    }
}
