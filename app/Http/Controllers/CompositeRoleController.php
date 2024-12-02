<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

use App\Models\CompositeRole;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;


class CompositeRoleController extends Controller
{
    public function index()
    {
        $companies = Company::all();

        return view('composite_roles.index', compact('companies'));
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

    public function getCompositeRoles(Request $request)
    {
        $query = CompositeRole::with(['company', 'jobRole', 'singleRoles']);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('kompartemen_id')) {
            $query->whereHas('jobRole', function ($q) use ($request) {
                $q->where('kompartemen_id', $request->kompartemen_id);
            });
        }

        if ($request->filled('departemen_id')) {
            $query->whereHas('jobRole', function ($q) use ($request) {
                $q->where('departemen_id', $request->departemen_id);
            });
        }

        if ($request->filled('job_role_id')) {
            $query->where('jabatan_id', $request->job_role_id);
        }

        // Debug the SQL Query
        Log::info($query->toSql());
        Log::info($query->getBindings());

        $recordsTotal = $query->count();
        $compositeRoles = $query->skip($request->start)->take($request->length)->get();

        // Check the fetched data
        Log::info($compositeRoles);

        $data = $compositeRoles->map(function ($role) {
            return [
                'company' => $role->company->name ?? 'N/A',
                'nama' => $role->nama,
                'job_role' => $role->jobRole->nama_jabatan ?? 'Not Assigned',
                'single_roles' => $role->singleRoles
                    ->pluck('nama')
                    ->map(function ($roleName) {
                        return "<li>{$roleName}</li>";
                    })
                    ->implode('') ?? '<li>No Single Roles</li>',
                'actions' => view('composite_roles.components.action_buttons', ['role' => $role])->render(),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
        ]);
    }
}
