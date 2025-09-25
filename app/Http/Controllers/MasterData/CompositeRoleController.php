<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

use App\Models\CompositeRole;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CompositeRoleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = 'zzz';

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();
        } else {
            $companies = Company::where('company_code', $userCompanyCode)->get();
        }

        return view('master-data.composite_roles.index', compact('companies'));
    }

    public function show($id)
    {
        $compositeRole = CompositeRole::with(['jobRole', 'company', 'singleRoles'])->findOrFail($id);

        // Load a partial view and pass the data to it for rendering in the modal
        return view('master-data.composite_roles.show', compact('compositeRole'));
    }

    public function create()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = 'zzz';
        $jobRoles = 'zzz';

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();
            $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->get();
        } else {
            $companies = Company::where('company_code', $userCompanyCode)->get();
            $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->where('company_id', $userCompanyCode)->get();
        }

        // Structure job roles data by Company > Kompartemen > Departemen
        $job_roles_data = [];

        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $kompartemenName = $jobRole->kompartemen->nama ?? 'No Kompartemen';
            $departemenName = $jobRole->departemen->nama ?? 'No Departemen';

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama' => $jobRole->nama,
            ];
        }

        return view('master-data.composite_roles.create', compact('companies', 'job_roles_data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,company_code',
            'jabatan_id' => 'nullable|exists:tr_job_roles,id',
            // 'nama' => 'required|string|max:255',
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_composite_roles', 'nama')
                    ->where('company_id', $request->company_id)
            ],
            'deskripsi' => 'nullable|string',
        ]);

        // Create the Composite Role
        CompositeRole::create([
            'company_id' => $request->company_id,
            'jabatan_id' => $request->jabatan_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'Status' => "Active",
            'source' => 'manual',
        ]);

        return redirect()->route('composite-roles.index')->with('success', 'Composite Role created successfully.');
    }

    public function edit(CompositeRole $compositeRole)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = 'zzz';
        $jobRoles = 'zzz';

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();
            $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->get();
        } else {
            $companies = Company::where('company_code', $userCompanyCode)->get();
            $jobRoles = JobRole::with(['company', 'kompartemen', 'departemen'])->where('company_id', $userCompanyCode)->get();
        }

        // Structure job roles data by Company > Kompartemen > Departemen
        $job_roles_data = [];

        foreach ($jobRoles as $jobRole) {
            $companyId = $jobRole->company_id;
            $kompartemenName = $jobRole->kompartemen->nama ?? 'No Kompartemen';
            $departemenName = $jobRole->departemen->nama ?? 'No Departemen';

            $job_roles_data[$companyId][$kompartemenName][$departemenName][] = [
                'id' => $jobRole->id,
                'nama' => $jobRole->nama,
            ];
        }

        return view('master-data.composite_roles.edit', compact('compositeRole', 'companies', 'job_roles_data'));
    }

    public function update(Request $request, CompositeRole $compositeRole)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,company_code',
            'jabatan_id' => 'nullable|exists:tr_job_roles,id',
            // 'nama' => 'required|string|unique:tr_composite_roles,nama,' . $compositeRole->id,
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_composite_roles', 'nama')
                    ->where('company_id', $request->company_id)
                    ->ignore($compositeRole->id),
            ],
            'deskripsi' => 'nullable|string',
        ]);

        $request->merge(['source' => 'edit']);

        // Update Composite Role details
        $compositeRole->update([
            'company_id' => $request->company_id,
            'jabatan_id' => $request->jabatan_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'source' => $request->source,
        ]);

        // dd($request->all(), $result, $compositeRole);

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

        // ✅ Apply general search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('company', fn($q2) => $q2->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('jobRole', fn($q3) => $q3->where('nama', 'like', "%{$search}%"))
                    ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();
        $compositeRoles = $query->skip($request->start)->take($request->length)->get();

        $data = $compositeRoles->map(function ($role) {
            return [
                'company' => $role->company->nama ?? 'N/A',
                'nama' => $role->nama,
                'deskripsi' => $role->deskripsi ?? '-',
                'source' => $role->source ?? '-',
                'actions' => view('master-data.composite_roles.components.action_buttons', ['role' => $role])->render(),
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
