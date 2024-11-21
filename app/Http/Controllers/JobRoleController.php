<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

class JobRoleController extends Controller
{
    public function index()
    {
        // $job_roles = JobRole::with(['compositeRole', 'company', 'kompartemen', 'departemen'])->get();
        // return view('job_roles.index', compact('job_roles'));

        // Fetch all companies to populate the initial dropdown
        $companies = Company::all();

        return view('job_roles.index', compact('companies'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('job_roles.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama_jabatan' => 'required|string|unique:tr_job_roles,nama_jabatan',
            'deskripsi' => 'nullable|string',
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'departemen_id' => 'required|exists:ms_departemen,id',
        ]);

        JobRole::create($request->all());

        return redirect()->route('job-roles.index')->with('status', 'Job role created successfully.');
    }

    public function show($id)
    {
        $jobRole = JobRole::with(['company', 'kompartemen', 'departemen', 'compositeRole'])->findOrFail($id);
        return view('job_roles.show', compact('jobRole'));
    }

    public function edit(JobRole $jobRole)
    {
        $companies = Company::all();

        // Load kompartemens and departemens based on the current jobRole's selections
        $kompartemens = Kompartemen::where('company_id', $jobRole->company_id)->get();
        $departemens = Departemen::where('kompartemen_id', $jobRole->kompartemen_id)->get();

        return view('job_roles.edit', compact('jobRole', 'companies', 'kompartemens', 'departemens'));
    }


    public function update(Request $request, JobRole $jobRole)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama_jabatan' => 'required|string|unique:tr_job_roles,nama_jabatan,' . $jobRole->id,
            'deskripsi' => 'nullable|string',
            'kompartemen_id' => 'required|exists:ms_kompartemen,id',
            'departemen_id' => 'required|exists:ms_departemen,id',
        ]);

        $jobRole->update($request->all());

        return redirect()->route('job-roles.index')->with('status', 'Job role updated successfully.');
    }

    public function destroy(JobRole $jobRole)
    {
        $jobRole->delete();

        return redirect()->route('job-roles.index')->with('status', 'Job role deleted successfully.');
    }

    public function getJobRoles(Request $request)
    {
        $jsonPath = storage_path('app/public/master_data.json');
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
            'job_role' => $jobRole['name'],
            'deskripsi' => $jobRole['description'] ?? 'N/A',
            'actions' => view('job_roles.partials.actions', ['jobRole' => (object) $jobRole])->render(),
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
                    $kompartemen['name'],
                    '-',
                    $jobRole
                );
            }

            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['name'],
                        $departemen['name'],
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
                    $departemen['name'],
                    $jobRole
                );
            }
        }
    }

    private function filterByKompartemen($company, $kompartemenId, &$filteredJobRoles)
    {
        foreach ($company['kompartemen'] ?? [] as $kompartemen) {
            if ($kompartemenId != $kompartemen['id']) {
                continue;
            }

            foreach ($kompartemen['job_roles'] ?? [] as $jobRole) {
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    $kompartemen['name'],
                    '-',
                    $jobRole
                );
            }

            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['name'],
                        $departemen['name'],
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
                if ($departemenId != $departemen['id']) {
                    continue;
                }

                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['name'],
                        $departemen['name'],
                        $jobRole
                    );
                }
            }
        }

        foreach ($company['departemen_without_kompartemen'] ?? [] as $departemen) {
            if ($departemenId != $departemen['id']) {
                continue;
            }

            foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    '-',
                    $departemen['name'],
                    $jobRole
                );
            }
        }
    }
}
