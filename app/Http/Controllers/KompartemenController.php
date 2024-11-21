<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

class KompartemenController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        $kompartemens = Kompartemen::all();
        return view('kompartemen.index', compact('companies', 'kompartemens'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('kompartemen.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:ms_company,id',
            'description' => 'nullable|string',
        ]);

        Kompartemen::create($request->all());
        return redirect()->route('kompartemens.index')->with('success', 'Kompartemen created successfully.');
    }

    public function edit(Kompartemen $kompartemen)
    {
        $companies = Company::all();
        return view('kompartemen.edit', compact('kompartemen', 'companies'));
    }

    public function update(Request $request, Kompartemen $kompartemen)
    {
        // Validate the request data
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update the kompartemen with the validated data
        $kompartemen->update([
            'company_id' => $request->input('company_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'updated_by' => auth()->id() // Assuming you're tracking the user who updated the record
        ]);

        // Redirect back with a success message
        return redirect()->route('kompartemens.index')->with('status', 'Kompartemen updated successfully!');
    }

    public function destroy(Kompartemen $kompartemen)
    {
        $kompartemen->delete();
        return redirect()->route('kompartemens.index')->with('success', 'Kompartemen deleted successfully.');
    }

    public function getKompartemenByCompany(Request $request)
    {
        $companyId = $request->get('company_id');
        $kompartemen = Kompartemen::where('company_id', $companyId)->get();
        return response()->json($kompartemen);
    }
}
