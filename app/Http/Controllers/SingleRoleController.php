<?php

namespace App\Http\Controllers;

use App\Models\SingleRole;
use Illuminate\Http\Request;

class SingleRoleController extends Controller
{
    public function index()
    {
        $single_roles = SingleRole::all();
        return view('single_roles.index', compact('single_roles'));
    }

    public function create()
    {
        return view('single_roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tr_single_roles,name',
            'description' => 'nullable|string',
        ]);

        SingleRole::create($request->all());

        return redirect()->route('single-roles.index')->with('status', 'Single role created successfully.');
    }

    public function edit(SingleRole $singleRole)
    {
        return view('single_roles.edit', compact('singleRole'));
    }

    public function update(Request $request, SingleRole $singleRole)
    {
        $request->validate([
            'name' => 'required|string|unique:tr_single_roles,name,' . $singleRole->id,
            'description' => 'nullable|string',
        ]);

        $singleRole->update($request->all());

        return redirect()->route('single-roles.index')->with('status', 'Single role updated successfully.');
    }

    public function destroy(SingleRole $singleRole)
    {
        $singleRole->delete();

        return redirect()->route('single-roles.index')->with('status', 'Single role deleted successfully.');
    }
}
