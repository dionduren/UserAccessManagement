<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\CompositeRole;
use App\Exports\JobCompositeFlaggedExport; // added

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel; // added

class JobCompositeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('relationship.job-composite.index', compact('userCompanyCode', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        if ($userCompanyCode === 'A000') {
            $jobRoles = JobRole::whereNotExists(function ($query) {
                $query->select('*')
                    ->from('tr_composite_roles')
                    ->whereColumn('tr_job_roles.id', 'tr_composite_roles.jabatan_id')
                    ->whereNull('tr_composite_roles.deleted_at');
            })->get();

            $compositeRoles = CompositeRole::whereNull('jabatan_id')->get();
        } else {
            $jobRoles = JobRole::whereHas('company', function ($q) use ($userCompanyCode) {
                $q->where('company_code', $userCompanyCode);
            })->whereNotExists(function ($query) {
                $query->select('*')
                    ->from('tr_composite_roles')
                    ->whereColumn('tr_job_roles.id', 'tr_composite_roles.jabatan_id');
            })->get();

            $compositeRoles = CompositeRole::whereNull('jabatan_id')
                ->whereHas('company', function ($q) use ($userCompanyCode) {
                    $q->where('company_code', $userCompanyCode);
                })->get();
        }

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
        // $companyId = $request->get('company_id');
        $compositeRoles = CompositeRole::whereNull('jabatan_id')->get();

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

        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        if ($userCompanyCode !== 'A000' && $relationship->company_id !== $userCompanyCode) {
            return redirect()
                ->route('job-composite.index')
                ->withErrors(['error' => 'You are not authorized to edit this Relationship.']);
        }

        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();

            $jobRoles = JobRole::whereNotExists(function ($q) use ($relationship) {
                $q->select('*')
                    ->from('tr_composite_roles')
                    ->whereColumn('tr_job_roles.id', 'tr_composite_roles.jabatan_id')
                    ->where('tr_composite_roles.id', '!=', $relationship->id);
            })->get();

            $compositeRoles = CompositeRole::where(function ($q) use ($relationship) {
                $q->whereNull('jabatan_id')
                    ->orWhere('id', $relationship->id);
            })->get();
        } else {
            $companies = Company::where('company_code', $userCompanyCode)->get();

            $jobRoles = JobRole::whereHas('company', function ($q) use ($userCompanyCode) {
                $q->where('company_code', $userCompanyCode);
            })->whereNotExists(function ($q) use ($relationship) {
                $q->select('*')
                    ->from('tr_composite_roles')
                    ->whereColumn('tr_job_roles.id', 'tr_composite_roles.jabatan_id')
                    ->where('tr_composite_roles.id', '!=', $relationship->id);
            })->get();

            $compositeRoles = CompositeRole::whereHas('company', function ($q) use ($userCompanyCode) {
                $q->where('company_code', $userCompanyCode);
            })->where(function ($q) use ($relationship) {
                $q->whereNull('jabatan_id')
                    ->orWhere('id', $relationship->id);
            })->get();
        }

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
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $draw  = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        // New filter inputs from dropdowns
        $filterCompany     = $request->input('filter_company');        // expects company_code
        $filterKompartemen = $request->input('filter_kompartemen');    // expects kompartemen_id
        $filterDepartemen  = $request->input('filter_departemen');     // expects departemen_id
        $filterJobRole     = $request->input('filter_job_role');       // expects job role id

        // Total (before search / dropdown filters but respecting user company restriction)
        $recordsTotal = CompositeRole::when($userCompanyCode && $userCompanyCode !== 'A000', function ($q) use ($userCompanyCode) {
            $q->whereHas('company', fn($cq) => $cq->where('company_code', $userCompanyCode));
        })
            ->count();

        // Base working query
        $query = CompositeRole::query()
            ->with(['company', 'jobRole', 'singleRoles'])
            ->leftJoin('ms_company as c', 'c.company_code', '=', 'tr_composite_roles.company_id')
            ->leftJoin('tr_job_roles as jr', 'jr.id', '=', 'tr_composite_roles.jabatan_id')
            ->select('tr_composite_roles.*')
            ->when($userCompanyCode && $userCompanyCode !== 'A000', function ($q) use ($userCompanyCode) {
                $q->where('c.company_code', $userCompanyCode);
            });

        // Dropdown filters
        if ($filterCompany) {
            $query->where('c.company_code', $filterCompany);
        }
        if ($filterKompartemen) {
            // assumes tr_job_roles has kompartemen_id
            $query->where('jr.kompartemen_id', $filterKompartemen);
        }
        if ($filterDepartemen) {
            // assumes tr_job_roles has departemen_id
            $query->where('jr.departemen_id', $filterDepartemen);
        }
        if ($filterJobRole) {
            $query->where('jr.id', $filterJobRole);
        }

        // Column searches (text inputs)
        if ($request->filled('search_company')) {
            $term = strtolower($request->input('search_company'));
            $query->whereRaw('LOWER(c.nama) LIKE ?', ["%{$term}%"]);
        }
        if ($request->filled('search_job_role')) {
            $term = strtolower($request->input('search_job_role'));
            $query->whereRaw('LOWER(jr.nama) LIKE ?', ["%{$term}%"]);
        }
        if ($request->filled('search_composite_role')) {
            $term = strtolower($request->input('search_composite_role'));
            $query->whereRaw('LOWER(tr_composite_roles.nama) LIKE ?', ["%{$term}%"]);
        }

        // Filtered count
        $recordsFiltered = (clone $query)->distinct('tr_composite_roles.id')->count('tr_composite_roles.id');

        // Ordering
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $orderableMap = [
            0 => 'c.nama',
            1 => 'jr.nama',
            2 => 'tr_composite_roles.nama'
        ];
        $orderColumn = $orderableMap[$orderColumnIndex] ?? 'c.nama';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $compositeRoles = $query->get();

        $data = $compositeRoles->map(function ($role) {
            return [
                'company' => $role->company->nama ?? 'N/A',
                'job_role' => $role->jobRole->nama ?? 'Not Assigned',
                'nama' => $role->nama,
                'actions' => view('relationship.job-composite.components.action_buttons', ['role' => $role])->render(),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function exportFlagged(Request $request)
    {
        $user = auth()->user();
        $userCompany = $user->loginDetail->company_code ?? null;

        // Use selected company if provided; enforce user restriction (non-A000 forced to own)
        $requested = $request->query('company_code');
        $companyCode = ($userCompany === 'A000') ? ($requested ?: null) : $userCompany;

        $filename = 'Flagged_JobRole_Composite'
            . ($companyCode ? "_{$companyCode}" : '')
            . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new JobCompositeFlaggedExport($companyCode), $filename);
    }
}
