<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\Departemen;
use App\Models\SingleRole;
use App\Models\Kompartemen;
use App\Models\CompositeRole;

use Illuminate\Http\Request;

class SingleRoleController extends Controller
{
    public function index()
    {
        $single_roles = SingleRole::with(['company', 'compositeRole'])->get();
        return view('single_roles.index', compact('single_roles'));
    }

    public function show($id)
    {
        $singleRole = SingleRole::with(['company', 'compositeRole', 'tcodes'])->findOrFail($id);
        return view('single_roles.show', compact('singleRole'));
    }


    public function create()
    {
        $companies = Company::all();
        $tcodes = Tcode::all();
        $compositeRoles = CompositeRole::all(); // Load all composite roles

        return view('single_roles.create', compact('companies', 'tcodes', 'compositeRoles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'composite_role_id' => 'nullable|exists:tr_composite_roles,id',
            'nama' => 'required|string|unique:tr_single_roles,nama',
            'deskripsi' => 'nullable|string',
            'tcodes' => 'array'
        ]);

        // Create SingleRole and sync associated Tcodes
        $singleRole = SingleRole::create($request->except('tcodes'));
        $singleRole->tcodes()->sync($request->tcodes);

        return redirect()->route('single-roles.index')->with('status', 'Single Role created successfully.');
    }

    public function edit($id)
    {
        $singleRole = SingleRole::with(['company', 'compositeRole', 'tcodes'])->findOrFail($id);
        $companies = Company::all();
        $compositeRoles = CompositeRole::where('company_id', $singleRole->company_id)->get(); // Filtered for selected company
        $tcodes = Tcode::all();

        return view('single_roles.edit', compact('singleRole', 'companies', 'compositeRoles', 'tcodes'));
    }

    public function update(Request $request, $id)
    {
        $singleRole = SingleRole::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama' => 'required|string|unique:tr_single_roles,nama,' . $singleRole->id,
            'deskripsi' => 'nullable|string',
            'composite_role_id' => 'nullable|exists:tr_composite_roles,id',
        ]);

        $singleRole->update($request->all());

        // Sync Tcodes if provided
        if ($request->has('tcodes')) {
            $singleRole->tcodes()->sync($request->input('tcodes'));
        }

        return redirect()->route('single-roles.index')->with('status', 'Single role updated successfully.');
    }


    public function destroy(SingleRole $singleRole)
    {
        $singleRole->delete();

        return redirect()->route('single-roles.index')->with('status', 'Single role deleted successfully.');
    }

    public function getFilteredData(Request $request)
    {
        $data = [];

        if ($request->has('company_id')) {
            $data['compositeRoles'] = CompositeRole::where('company_id', $request->company_id)->get();
            $data['kompartemens'] = Kompartemen::where('company_id', $request->company_id)->get();
        }

        if ($request->has('kompartemen_id')) {
            $data['departemens'] = Departemen::where('kompartemen_id', $request->kompartemen_id)->get();
        }

        return response()->json($data);
    }
}
