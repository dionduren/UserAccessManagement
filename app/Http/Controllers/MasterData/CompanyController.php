<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Traits\AuditsActivity;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    use AuditsActivity;
    public function index()
    {
        // Check if the user has permission
        // if (!Gate::allows('manage company info')) {
        //     abort(403, 'Unauthorized access');
        // }

        $companies = Company::all();
        return view('master-data.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('master-data.companies.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'company_code' => 'required|string|max:10|unique:ms_company',
                'shortname' => 'required|string|max:255',
                'deskripsi' => 'nullable|string'
            ]);

            $company = Company::create($request->all() + [
                'created_by' => auth()->user()->name
            ]);

            // Audit trail
            $this->auditCreate($company);

            return redirect()->route('companies.index')->with('success', 'Company created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Company $company)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        if ($userCompanyCode !== 'A000') {
            return redirect()
                ->route('companies.index')
                ->withErrors(['error' => 'You are not authorized to edit this company.']);
        }
        return view('master-data.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        try {
            $request->validate([
                'company_code' => [
                    'required',
                    'string',
                    Rule::unique('ms_company', 'company_code')->ignore($company->company_code, 'company_code'),
                ],
                'nama' => 'required|string|max:255',
                'shortname' => 'required|string|max:255',
                'deskripsi' => 'nullable|string'
            ]);

            // Store original data for audit
            $originalData = $company->toArray();

            $company->update($request->all() + [
                'updated_by' => auth()->user()->name
            ]);

            // Audit trail
            $this->auditUpdate($company, $originalData);

            return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        // Audit trail
        $this->auditDelete($company);


        if ($userCompanyCode !== 'A000') {
            return redirect()
                ->route('companies.index')
                ->withErrors(['error' => 'You are not authorized to delete this company.']);
        }

        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
