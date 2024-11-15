<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if the user has permission
        // if (!Gate::allows('manage company info')) {
        //     abort(403, 'Unauthorized access');
        // }

        $companies = Company::all();
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_code' => 'required|string|max:10|unique:ms_company',
            'description' => 'nullable|string'
        ]);

        Company::create($request->all());
        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'company_code' => 'required|string|max:10|unique:ms_company,company_code,' . $company->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $company->update($request->all());
        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy($id)
    public function destroy(Company $company)
    {
        // // Only users with 'manage company info' permission can delete
        // if (!Gate::allows('manage company info')) {
        //     abort(403, 'Unauthorized access');
        // }

        // Company::destroy($id);
        // return redirect()->route('companies.index')->with('success', 'Company deleted successfully');
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
