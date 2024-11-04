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
        $tcodes = Tcode::all();
        return view('tcodes.index', compact('tcodes'));
    }

    public function create()
    {
        $companies = Company::all();
        $single_roles = SingleRole::all();
        return view('tcodes.create', compact('companies', 'single_roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|exists:ms_company,id',
            'code' => 'required|string|unique:tr_tcodes,code',
            'deskripsi' => 'nullable|string',
        ]);

        Tcode::create($request->all());

        return redirect()->route('tcodes.index')->with('status', 'Tcode created successfully.');
    }

    public function edit(Tcode $tcode)
    {
        return view('tcodes.edit', compact('tcode'));
    }

    public function update(Request $request, Tcode $tcode)
    {
        $request->validate([
            'company_id' => 'nullable|exists:ms_company,id',
            'code' => 'required|string|unique:tr_tcodes,code,' . $tcode->id,
            'deskripsi' => 'nullable|string',
        ]);

        $tcode->update($request->all());

        return redirect()->route('tcodes.index')->with('status', 'Tcode updated successfully.');
    }

    public function destroy(Tcode $tcode)
    {
        $tcode->delete();

        return redirect()->route('tcodes.index')->with('status', 'Tcode deleted successfully.');
    }
}
