<?php

namespace App\Http\Controllers\Relationship;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\SingleRole;
use Illuminate\Http\Request;
use App\Models\CompositeRole;
use App\Http\Controllers\Controller;

class JobCompositeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();

        return view('relationship.job-composite.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();

        // Structure job roles data by Company > Kompartemen > Departemen
        $job_roles_data = [];
        $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->get();

        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $kompartemenName = $jobRole->kompartemen->id ?? null;
            $departemenName = $jobRole->departemen->id ?? null;

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama_jabatan' => $jobRole->nama_jabatan,
            ];
        }

        return view('relationship.job-composite.create', compact('companies', 'job_roles_data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $compositeRole = CompositeRole::with(['jobRole', 'company', 'singleRoles'])->findOrFail($id);

        // Load a partial view and pass the data to it for rendering in the modal
        return view('relationship.job-composite.show', compact('compositeRole'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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

        $recordsFiltered = $query->count();
        $compositeRoles = $query->skip($request->start)->take($request->length)->get();

        $data = $compositeRoles->map(function ($role) {
            return [
                'company' => $role->company->name ?? 'N/A',
                'nama' => $role->nama,
                'job_role' => $role->jobRole->nama_jabatan ?? 'Not Assigned',
                // 'single_roles' => $role->singleRoles
                //     ->pluck('nama')
                //     ->map(function ($roleName) {
                //         return "<li>{$roleName}</li>";
                //     })
                //     ->implode('') ?? '<li>No Single Roles</li>',
                'actions' => view('relationship.job-composite.components.action_buttons', ['role' => $role])->render(),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => CompositeRole::count(), // Total number of records
            'recordsFiltered' => $recordsFiltered, // Total number of filtered records
            'data' => $data,
        ]);
    }
}
