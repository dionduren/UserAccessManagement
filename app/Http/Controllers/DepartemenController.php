<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

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
        return view('departemen.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:ms_company,id',
                'kompartemen_id' => 'nullable|exists:ms_kompartemen,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            Departemen::create($request->all());
            return redirect()->route('departemens.index')->with('success', 'Departemen created successfully.');
        } catch (ValidationException $e) {
            // Redirect back with validation errors
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (QueryException $e) {
            // Log the query error and return a user-friendly message
            Log::error('Error creating Job Role: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => 'An unexpected error occurred while saving the job role.'])
                ->withInput();
        }
    }

    public function edit(Departemen $departemen)
    {
        $company = Company::where('id', $departemen->company_id)->first();
        $kompartemen = Kompartemen::where('company_id', $departemen->company_id)->first();
        return view('departemen.edit', compact('company', 'kompartemen', 'departemen'));
    }

    public function update(Request $request, Departemen $departemen)
    {
        // Validate the request data
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update the departemen with the validated data
        $departemen->update([
            'company_id' => $request->input('company_id'),
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
        $companyId = $request->get('company_id');
        $kompartemenId = $request->get('kompartemen_id');

        $departemenQuery = Departemen::query();

        if ($kompartemenId) {
            // Get departemen based on the specified kompartemen
            $departemenQuery->where('kompartemen_id', $kompartemenId);
        } elseif ($companyId) {
            // Get departemen without kompartemen within the specified company
            $departemenQuery->where('company_id', $companyId)->whereNull('kompartemen_id');
        }

        $departemen = $departemenQuery->get();

        return response()->json($departemen);
    }

    public function getDepartemenByCompany(Request $request)
    {
        $companyId = $request->get('company_id');

        // Fetch departemens with no kompartemen within the specified company
        $departemen = Departemen::where('company_id', $companyId)->whereNull('kompartemen_id')->get();

        return response()->json($departemen);
    }
}
