<?php

namespace App\Http\Controllers;

use App\Models\CompositeRole;
use Illuminate\Http\Request;

class CompositeRoleController extends Controller
{
    public function index()
    {
        $composite_roles = CompositeRole::all();
        return view('composite_roles.index', compact('composite_roles'));
    }

    public function create()
    {
        return view('composite_roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'nama' => 'required|string|unique:composite_roles,nama',
            'deskripsi' => 'nullable|string',
            'jabatan_id' => 'required|exists:job_roles,id',
        ]);

        CompositeRole::create($request->all());

        return redirect()->route('composite-roles.index')->with('status', 'Composite role created successfully.');
    }

    public function edit(CompositeRole $compositeRole)
    {
        return view('composite_roles.edit', compact('compositeRole'));
    }

    public function update(Request $request, CompositeRole $compositeRole)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'nama' => 'required|string|unique:composite_roles,nama,' . $compositeRole->id,
            'deskripsi' => 'nullable|string',
            'jabatan_id' => 'required|exists:job_roles,id',
        ]);

        $compositeRole->update($request->all());

        return redirect()->route('composite-roles.index')->with('status', 'Composite role updated successfully.');
    }

    public function destroy(CompositeRole $compositeRole)
    {
        $compositeRole->delete();

        return redirect()->route('composite-roles.index')->with('status', 'Composite role deleted successfully.');
    }
}
