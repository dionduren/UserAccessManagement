<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use Illuminate\Http\Request;

class TcodeController extends Controller
{
    public function index()
    {
        $tcodes = Tcode::all();
        return view('tcodes.index', compact('tcodes'));
    }

    public function show($id)
    {
        $tcode = Tcode::findOrFail($id);
        return view('tcodes.show', compact('tcode'));
    }

    public function create()
    {
        return view('tcodes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:tr_tcodes,code',
            'sap_module' => 'nullable|string|unique:tr_tcodes,sap_module',
            'deskripsi' => 'nullable|string',
        ]);

        $tcode = Tcode::create($request->only(['company_id', 'code', 'deskripsi']));

        return redirect()->route('tcodes.index')->with('status', 'Tcode created successfully.');
    }

    public function edit(Tcode $tcode)
    {
        // Pass the existing Tcode data and the lists for dropdowns to the view
        return view('tcodes.edit', compact('tcode', 'companies'));
    }

    public function update(Request $request, Tcode $tcode)
    {
        $request->validate([
            'code' => 'required|string|unique:tr_tcodes,code,' . $tcode->id,
            'sap_module' => 'nullable|string|unique:tr_tcodes,sap_module',
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
