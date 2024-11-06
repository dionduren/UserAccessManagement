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
    // Display the index page with data
    public function index()
    {
        $companies = Company::all();
        $single_roles = SingleRole::with('compositeRoles', 'tcodes')->get();
        return view('single_roles.index', compact('companies', 'single_roles'));
    }

    // Show the details of a Single Role
    public function show($id)
    {
        $singleRole = SingleRole::findOrFail($id);
        return view('single_roles.show', compact('singleRole'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('single_roles.create', compact('companies'));
    }


    // Store a new Single Role
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama' => 'required|string|unique:tr_single_roles,nama',
            'deskripsi' => 'nullable|string',
        ]);

        $singleRole = SingleRole::create($request->all());

        // Check if the request is an AJAX request
        if ($request->ajax()) {
            // Return HTML for the table row or a success message
            $view = view('single_roles.partials.single_role_row', compact('singleRole'))->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role created successfully.');
    }

    public function edit($id)
    {
        $singleRole = SingleRole::findOrFail($id);
        $companies = Company::all();

        // Render the view and pass the data to it
        return view('single_roles.edit', compact('singleRole', 'companies'))->render();
    }


    public function update(Request $request, $id)
    {
        $singleRole = SingleRole::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama' => 'required|string|unique:tr_single_roles,nama,' . $singleRole->id,
            'deskripsi' => 'nullable|string',
        ]);

        $singleRole->update($request->all());

        if ($request->ajax()) {
            $view = view('single_roles.partials.single_role_row', compact('singleRole'))->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role updated successfully.');
    }



    public function destroy(SingleRole $singleRole)
    {
        $singleRole->delete();

        return redirect()->route('single-roles.index')->with('status', 'Single role deleted successfully.');
    }
}
