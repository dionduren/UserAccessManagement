<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class JobRoleController extends Controller
{
    public function index()
    {
        // $job_roles = JobRole::with(['compositeRole', 'company', 'kompartemen', 'departemen'])->get();
        // return view('job_roles.index', compact('job_roles'));

        // Fetch all companies to populate the initial dropdown
        $companies = Company::all();

        return view('master-data.job_roles.index', compact('companies'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('master-data.job_roles.create', compact('companies'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:ms_company,company_code',
                'nama' => [
                    'required',
                    'string',
                    Rule::unique('tr_job_roles', 'nama')
                        ->where('company_id', $request->company_id)
                ],
                'deskripsi' => 'nullable|string',
                'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
                'departemen_id' => 'nullable|exists:ms_departemen,departemen_id',
            ]);

            JobRole::create($request->all() + [
                'created_by' => auth()->user()->name
            ]);

            return redirect()
                ->route('job-roles.index')
                ->with('status', 'Job role created successfully.');
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

    public function show($id)
    {
        $jobRole = JobRole::with(['company', 'kompartemen', 'departemen', 'compositeRole'])->findOrFail($id);
        return view('master-data.job_roles.show', compact('jobRole'));
    }

    public function edit(JobRole $jobRole)
    {
        $company = Company::where('company_code', $jobRole->company_id)->first();

        // Load kompartemens and departemens based on the current jobRole's selections
        $kompartemen = Kompartemen::where('company_id', $jobRole->company_id)->first();
        $departemen = Departemen::where('kompartemen_id', $jobRole->kompartemen_id)->first();

        return view('master-data.job_roles.edit', compact('jobRole', 'company', 'kompartemen', 'departemen'));
    }

    public function update(Request $request, JobRole $jobRole)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:ms_company,company_code',
                'nama' => [
                    'required',
                    'string',
                    Rule::unique('tr_job_roles', 'nama')
                        ->where('company_id', $request->company_id)
                        ->ignore($jobRole->id, 'id')
                ],
                'deskripsi' => 'nullable|string',
                'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
                'departemen_id' => 'nullable|exists:ms_departemen,departemen_id',
            ]);

            $jobRole->update($request->all() + [
                'updated_by' => auth()->user()->name
            ]);

            return redirect()->route('job-roles.index')->with('status', 'Job role updated successfully.');
        } catch (ValidationException $e) {
            // Redirect back with validation errors
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (QueryException $e) {
            // Log the query error and return a user-friendly message
            Log::error('Error updating Job Role: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => 'An unexpected error occurred while saving the job role.'])
                ->withInput();
        }
    }

    public function destroy(JobRole $jobRole)
    {
        $jobRole->delete();

        return redirect()->route('job-roles.index')->with('status', 'Job role deleted successfully.');
    }

    public function getJobRoles(Request $request)
    {
        $jsonPath = storage_path('/app/public/master_data.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Master data JSON not found'], 404);
        }

        $companyData = json_decode(file_get_contents($jsonPath), true);
        $filteredJobRoles = [];

        foreach ($companyData as $company) {
            if ($request->has('company_id') && $request->get('company_id') != $company['company_id']) {
                continue;
            }

            // Filter by departemen if set
            if ($request->get('departemen_id')) {
                $this->filterByDepartemen($company, $request->get('departemen_id'), $filteredJobRoles);
            }
            // Filter by kompartemen if set
            elseif ($request->get('kompartemen_id')) {
                $this->filterByKompartemen($company, $request->get('kompartemen_id'), $filteredJobRoles);
            }
            // Show all data for the company if no other filters are set
            else {
                $this->filterByCompany($company, $filteredJobRoles);
            }
        }

        return response()->json($filteredJobRoles);
    }

    /**
     * Helper function to map a job role
     */
    private function mapJobRole($companyName, $kompartemenName, $departemenName, $jobRole)
    {
        return [
            'id' => $jobRole['id'],
            'company' => $companyName,
            'kompartemen' => $kompartemenName ?? '-',
            'departemen' => $departemenName ?? '-',
            'job_role' => $jobRole['nama'],
            'deskripsi' => $jobRole['description'] ?? 'N/A',
            'actions' => view('master-data.job_roles.partials.actions', ['jobRole' => (object) $jobRole])->render(),
        ];
    }

    private function filterByCompany($company, &$filteredJobRoles)
    {
        // Add job roles without relations
        foreach ($company['job_roles_without_relations'] ?? [] as $jobRole) {
            $filteredJobRoles[] = $this->mapJobRole(
                $company['company_name'],
                '-',
                '-',
                $jobRole
            );
        }

        // Add job roles under kompartemen
        foreach ($company['kompartemen'] ?? [] as $kompartemen) {
            foreach ($kompartemen['job_roles'] ?? [] as $jobRole) {
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    $kompartemen['nama'],
                    '-',
                    $jobRole
                );
            }

            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['nama'],
                        $departemen['nama'],
                        $jobRole
                    );
                }
            }
        }

        // Add job roles under departemen_without_kompartemen
        foreach ($company['departemen_without_kompartemen'] ?? [] as $departemen) {
            foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    '-',
                    $departemen['nama'],
                    $jobRole
                );
            }
        }
    }

    private function filterByKompartemen($company, $kompartemenId, &$filteredJobRoles)
    {
        foreach ($company['kompartemen'] ?? [] as $kompartemen) {
            if ($kompartemenId != $kompartemen['kompartemen_id']) {
                continue;
            }

            foreach ($kompartemen['job_roles'] ?? [] as $jobRole) {
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    $kompartemen['nama'],
                    '-',
                    $jobRole
                );
            }

            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['nama'],
                        $departemen['nama'],
                        $jobRole
                    );
                }
            }
        }
    }

    private function filterByDepartemen($company, $departemenId, &$filteredJobRoles)
    {
        foreach ($company['kompartemen'] ?? [] as $kompartemen) {
            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                if ($departemenId != $departemen['departemen_id']) {
                    continue;
                }

                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['nama'],
                        $departemen['nama'],
                        $jobRole
                    );
                }
            }
        }

        foreach ($company['departemen_without_kompartemen'] ?? [] as $departemen) {
            if ($departemenId != $departemen['departemen_id']) {
                continue;
            }

            foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    '-',
                    $departemen['nama'],
                    $jobRole
                );
            }
        }
    }
}
