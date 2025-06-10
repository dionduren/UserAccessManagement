<?php

namespace App\Http\Controllers\Relationship;

use App\Models\Company;
use App\Models\JobRole;
use Illuminate\Http\Request;
use App\Models\CompositeRole;
use Illuminate\Support\Facades\Log;
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
        $jobRoles = JobRole::whereNotExists(function ($query) {
            $query->select('*')
                ->from('tr_composite_roles')
                ->whereColumn('tr_job_roles.id', 'tr_composite_roles.jabatan_id');
        })->get();
        $compositeRoles = CompositeRole::whereNull('jabatan_id')->get();

        // \dd($jobRoles, $compositeRoles);

        $job_roles_data = [];
        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $companyShortName = $jobRole->company->shortname;
            $kompartemenName = $jobRole->kompartemen->nama ?? 'No Kompartemen';
            $departemenName = $jobRole->departemen->nama ?? 'No Departemen';

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama' => $jobRole->nama,
                'company_shortname' => $companyShortName,
            ];
            // \dd($job_roles_data[$companyId][$kompartemenName][$departemenName]);
        }

        return view('relationship.job-composite.create', compact('companies', 'job_roles_data', 'compositeRoles'));
    }

    public function getEmptyCompositeRole(Request $request)
    {
        // $compositeRoles = CompositeRole::whereNull('jabatan_id')->get();
        $companyId = $request->get('company_id');
        $compositeRoles = CompositeRole::where('company_id', $companyId)->whereNull('jabatan_id')->get();

        return response()->json($compositeRoles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'jabatan_id' => 'required',
            'composite_role_id' => 'required',
        ]);

        try {
            $compositeRole = CompositeRole::findOrFail($request->composite_role_id);

            CompositeRole::where('id', $compositeRole->id)->update([
                'jabatan_id' => $request->jabatan_id
            ]);

            return redirect()->route('job-composite.index')->with('success', 'Relationship created successfully!');
        } catch (\Exception $e) {
            // return back()->with('error', $e->getMessage());
            return back()->with('error', 'Failed to create the relationship. Please try again.');
            Log::info('Error creating relationship Job - Composite = ', $e->getMessage());
        }
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
    public function edit($id)
    {
        $relationship = CompositeRole::findOrFail($id);

        $companies = Company::all();
        $jobRoles = JobRole::all();
        $compositeRoles = CompositeRole::all();

        $job_roles_data = [];
        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $companyShortName = $jobRole->company->shortname;
            $kompartemenName = $jobRole->kompartemen->nama ?? 'No Kompartemen';
            $departemenName = $jobRole->departemen->nama ?? 'No Departemen';

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama' => $jobRole->nama,
                'company_shortname' => $companyShortName,
            ];
        }

        return view('relationship.job-composite.edit', compact('relationship', 'companies', 'job_roles_data', 'compositeRoles', 'id'));
    }

    public function getCompositeFilterCompany(Request $request)
    {
        $compositeRolesQuery = CompositeRole::query();

        // Filter by company if a company_id is provided
        if ($request->filled('company_id')) {
            $compositeRolesQuery->where('company_id', $request->input('company_id'));
        }

        $compositeRoles = $compositeRolesQuery->get();

        return response()->json($compositeRoles);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'company_id' => 'required',
            'jabatan_id' => 'required',
            'composite_role_id' => 'required'
        ]);

        try {
            CompositeRole::where('id', $id)->update([
                'jabatan_id' => $request->jabatan_id
            ]);

            return redirect()->route('job-composite.index')->with('success', 'Relationship updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update the relationship. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CompositeRole::where('id', $id)->update([
            'jabatan_id' => null
        ]);

        return redirect()->route('job-composite.index')->with('status', 'Composite role deleted successfully.');
    }

    public function getCompositeRoles(Request $request)
    {
        $query = CompositeRole::with(['company', 'jobRole', 'singleRoles']);

        if ($request->filled('search_company')) {
            $searchCompany = strtolower($request->input('search_company'));
            $query->whereHas('company', function ($companyQuery) use ($searchCompany) {
                $companyQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchCompany}%"]);
            });
        }

        if ($request->filled('search_job_role')) {
            $searchJobRole = strtolower($request->input('search_job_role'));
            $query->whereHas('jobRole', function ($jobQuery) use ($searchJobRole) {
                $jobQuery->whereRaw('LOWER(nama) LIKE ?', ["%{$searchJobRole}%"]);
            });
        }

        if ($request->filled('search_composite_role')) {
            $searchCompositeRole = strtolower($request->input('search_composite_role'));
            $query->whereRaw('LOWER(nama) LIKE ?', ["%{$searchCompositeRole}%"]);
        }

        $recordsFiltered = $query->count();
        $compositeRoles = $query->skip($request->start)->take($request->length)->get();

        $data = $compositeRoles->map(function ($role) {
            return [
                'company' => $role->company->nama ?? 'N/A',
                'nama' => $role->nama,
                'job_role' => $role->jobRole->nama ?? 'Not Assigned',
                'actions' => view('relationship.job-composite.components.action_buttons', ['role' => $role])->render(),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => CompositeRole::count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
