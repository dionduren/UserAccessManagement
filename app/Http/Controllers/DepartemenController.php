<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    public function index()
    {
        // Retrieve all companies and departemens to pass to the view
        $companies = Company::all();
        $kompartemens = Kompartemen::all();
        $departemens = Departemen::with(['company'])->get();

        return view('departemen.index', compact('companies', 'departemens', 'kompartemens'));
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

    public function getDepartemenByKompartemen(Request $request)
    {
        $kompartemenId = $request->get('kompartemen_id');
        $departemen = Departemen::where('kompartemen_id', $kompartemenId)->get();
        return response()->json($departemen);
    }
}
