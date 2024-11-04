<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    public function index()
    {
        $departemens = Departemen::all();
        return view('departemen.index', compact('departemens'));
    }

    public function create()
    {
        $kompartemens = Kompartemen::all();
        return view('departemen.create', compact('kompartemens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Departemen::create($request->all());
        return redirect()->route('departemens.index')->with('success', 'Departemen created successfully.');
    }

    public function edit(Departemen $departemen)
    {
        $kompartemens = Kompartemen::all();
        return view('departemen.edit', compact('departemen', 'kompartemens'));
    }

    public function update(Request $request, Departemen $departemen)
    {
        // Validate the request data
        $request->validate([
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update the departemen with the validated data
        $departemen->update([
            'kompartemen_id' => $request->input('kompartemen_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'updated_by' => auth()->id() // Assuming you're tracking the user who updated the record
        ]);

        // Redirect back with a success message
        return redirect()->route('departemen.index')->with('status', 'Departemen updated successfully!');
    }

    public function destroy(Departemen $departemen)
    {
        $departemen->delete();
        return redirect()->route('departemens.index')->with('success', 'Departemen deleted successfully.');
    }
}
