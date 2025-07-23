<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Kompartemen;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KompartemenController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        $kompartemens = Kompartemen::all();
        return view('master-data.kompartemen.index', compact('companies', 'kompartemens'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('master-data.kompartemen.create', compact('companies'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'kompartemen_id' => 'required|string|unique:ms_kompartemen,kompartemen_id',
                'nama' => 'required|string|max:255',
                'company_id' => 'required|string',
                'deskripsi' => 'nullable|string',
            ]);

            Kompartemen::create($request->all() + [
                'created_by' => auth()->user()->name
            ]);

            return redirect()->route('kompartemens.index')->with('success', 'Kompartemen created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (QueryException $e) {
            Log::error('Error creating Kompartemen: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred while saving the kompartemen.'])->withInput();
        }
    }

    public function edit(Kompartemen $kompartemen)
    {
        $companies = Company::all();
        return view('master-data.kompartemen.edit', compact('kompartemen', 'companies'));
    }

    public function update(Request $request, Kompartemen $kompartemen)
    {
        // Validate the request data
        $request->validate([
            'company_id' => 'required|string',
            'kompartemen_id' => 'required|string|unique:ms_kompartemen,kompartemen_id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        // Update the kompartemen with the validated data
        $kompartemen->update([
            'company_id' => $request->input('company_id'),
            'kompartemen_id' => $request->input('kompartemen_id'),
            'nama' => $request->input('nama'),
            'deskripsi' => $request->input('deskripsi'),
            'updated_by' => auth()->user()->name // Assuming you're tracking the user who updated the record
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
