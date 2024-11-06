<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\SingleRole;
use Illuminate\Http\Request;

class TcodeController extends Controller
{
    public function index()
    {
        $tcodes = Tcode::with('company')->get();
        return view('tcodes.index', compact('tcodes'));
    }

    public function show($id)
    {
        $tcode = Tcode::with('company')->findOrFail($id);
        return view('tcodes.show', compact('tcode'));
    }

    public function create()
    {
        // Retrieve necessary data for the create form
        $companies = Company::all();

        return view('tcodes.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'code' => 'required|string|unique:tr_tcodes,code',
            'deskripsi' => 'nullable|string',
        ]);

        $tcode = Tcode::create($request->only(['company_id', 'code', 'deskripsi']));

        return redirect()->route('tcodes.index')->with('status', 'Tcode created successfully.');
    }

    public function edit(Tcode $tcode)
    {
        // Retrieve necessary data for the edit form
        $companies = Company::all();

        // Pass the existing Tcode data and the lists for dropdowns to the view
        return view('tcodes.edit', compact('tcode', 'companies'));
    }

    public function update(Request $request, Tcode $tcode)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'code' => 'required|string|unique:tr_tcodes,code,' . $tcode->id,
            'deskripsi' => 'nullable|string'
        ]);

        $tcode->update($request->only(['company_id', 'code', 'deskripsi']));

        return redirect()->route('tcodes.index')->with('status', 'Tcode updated successfully.');
    }

    public function destroy(Tcode $tcode)
    {
        $tcode->delete();

        return redirect()->route('tcodes.index')->with('status', 'Tcode deleted successfully.');
    }
}
