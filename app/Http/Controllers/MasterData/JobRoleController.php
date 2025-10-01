<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Services\JSONService;
use App\Exports\MasterData\JobUserIdExport;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use App\Models\PenomoranJobRole;
use App\Exports\JobRoleFlaggedExport;
use App\Models\Periode;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JobRoleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = 'zzz';

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();
        } else {
            $companies = Company::where('company_code', $userCompanyCode)->get();
        }

        $periodes = Periode::orderByDesc('id')
            ->get(['id', 'definisi']);

        return view('master-data.job_roles.index', compact('companies', 'periodes'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('master-data.job_roles.create', compact('companies'));
    }

    // TODO: add create job_role_id after storing the job role
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

            $jobRole = JobRole::create($request->all() + [
                'created_by' => auth()->user()->name
            ]);

            // Increment PenomoranJobRole if job_role_id is set
            if ($request->job_role_id) {
                PenomoranJobRole::updateOrCreate(
                    ['company_id' => $request->company_id],
                    ['last_number' => \DB::raw('last_number + 1')]
                );
            }

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

        // Load kompartemens and departemen based on the current jobRole's selections
        $kompartemen = Kompartemen::where('company_id', $jobRole->company_id)->first();
        $departemen = Departemen::where('kompartemen_id', $jobRole->kompartemen_id)->first();

        return view('master-data.job_roles.edit', compact('jobRole', 'company', 'kompartemen', 'departemen'));
    }

    public function update(Request $request, JobRole $jobRole)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:ms_company,company_code',
                'job_role_id' => [
                    'required',
                    'string',
                    Rule::unique('tr_job_roles', 'job_role_id')
                        ->where('company_id', $request->company_id)
                        ->ignore($jobRole->id, 'id')
                ],
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

            $oldJobRoleId = $jobRole->job_role_id;
            $jobRole->update($request->all() + [
                'updated_by' => auth()->user()->name
            ]);

            // Only increment if job_role_id is changed
            if ($request->job_role_id && $request->job_role_id !== $oldJobRoleId) {
                PenomoranJobRole::updateOrCreate(
                    ['company_id' => $request->company_id],
                    ['last_number' => \DB::raw('last_number + 1')]
                );
            }

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
            'job_role_id' => $jobRole['job_role_id'] ?? "Not Assigned",
            'company' => $companyName,
            'kompartemen' => $kompartemenName ?? '-',
            'departemen' => $departemenName ?? '-',
            'job_role' => $jobRole['nama'],
            'deskripsi' => $jobRole['deskripsi'] ?? 'N/A',
            'status' => $jobRole['status'] ?? 'active',
            'flagged' => $jobRole['flagged'] ?? false,
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

    public function updateFlaggedStatus(Request $request)
    {
        $request->validate([
            'job_role_id' => 'required|exists:tr_job_roles,job_role_id',
            'flagged' => 'required|boolean',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $jobRole = JobRole::where('job_role_id', $request->job_role_id)->firstOrFail();
            $jobRole->flagged = $request->flagged;
            $jobRole->keterangan = $request->keterangan;
            $jobRole->updated_by = auth()->user()->name ?? 'system';
            $jobRole->updated_at = now();
            $jobRole->save();

            // Regenerate the JSON file after updating flagged status
            app(JSONService::class)->generateMasterDataJson();

            return response()->json([
                'success' => true,
                'message' => 'Flagged status updated successfully and JSON regenerated.',
                'flagged' => $jobRole->flagged,
                'keterangan' => $jobRole->keterangan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating flagged status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update flagged status.',
            ], 500);
        }
    }

    public function editFlagged($id)
    {
        $jobRole = JobRole::where('id', $id)->firstOrFail();
        return view('master-data.job_roles.edit-flagged', compact('jobRole'));
    }

    public function updateFlagged(Request $request, $id)
    {
        $request->validate([
            'flagged' => 'required|boolean',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $jobRole = JobRole::where('id', $id)->firstOrFail();
            $jobRole->flagged = $request->flagged;
            $jobRole->keterangan = $request->keterangan;
            $jobRole->updated_by = auth()->user()->name ?? 'system';
            $jobRole->updated_at = now();
            $jobRole->save();

            // Regenerate the JSON file after updating flagged status
            app(JSONService::class)->generateMasterDataJson();

            return redirect()->route('job-roles.index')->with('success', 'Flagged status updated and JSON regenerated.');
        } catch (\Exception $e) {
            \Log::error('Error updating flagged status: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update flagged status: ' . $e->getMessage()]);
        }
    }


    public function generateJobRoleId(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,company_code',
            'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
            'departemen_id' => 'nullable|exists:ms_departemen,departemen_id',
        ]);

        try {
            $row = [
                'company_code' => $request->company_id,
                'kompartemen_id' => $request->kompartemen_id,
                'departemen_id' => $request->departemen_id,
            ];

            // --- Copy logic from CompanyKompartemenService ---
            $costCenter = null;
            $cc_level = null;

            if (!empty($row['departemen_id'])) {
                $costCenter = \App\Models\CostCenter::where('level_id', $row['departemen_id'])
                    ->where('level', 'Departemen')
                    ->first();
                if (!$costCenter) {
                    return response()->json(['error' => "CostCenter tidak ditemukan untuk Departemen ID: {$row['departemen_id']}"], 422);
                }
                $cc_level = 'DEP';
            } elseif (!empty($row['kompartemen_id'])) {
                $costCenter = \App\Models\CostCenter::where('level_id', $row['kompartemen_id'])
                    ->where('level', 'Kompartemen')
                    ->first();
                if (!$costCenter) {
                    return response()->json(['error' => "CostCenter tidak ditemukan untuk Kompartemen ID: {$row['kompartemen_id']}"], 422);
                }
                $cc_level = 'KOM';
            } else {
                return response()->json(['error' => "Tidak ada departemen_id atau kompartemen_id yang valid ditemukan di CostCenter."], 422);
            }

            $penomoran = \App\Models\PenomoranJobRole::where('company_id', $row['company_code'])->first();
            $nextNumber = $penomoran ? $penomoran->last_number + 1 : 1;

            $costCode = $costCenter ? $costCenter->cost_code : '';
            $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $job_role_id = $costCode . '_' . $cc_level . '_JR_' . $formattedNumber;

            return response()->json(['job_role_id' => $job_role_id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportFlagged(Request $request)
    {
        $user = auth()->user();
        $userCompany = $user->loginDetail->company_code ?? null;

        $requested = $request->query('company_code');
        $companyCode = ($userCompany === 'A000') ? ($requested ?: null) : $userCompany;

        $filename = 'Flagged_JobRoles'
            . ($companyCode ? "_{$companyCode}" : '')
            . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new JobRoleFlaggedExport($companyCode), $filename);
    }

    // Scopes
    public function scopeFlagged($q, bool $flag = true)
    {
        return $q->where('flagged', $flag);
    }

    public function scopeCompany($q, string $companyCode)
    {
        return $q->where('company_id', $companyCode);
    }

    public function exportUserId(Request $request)
    {
        $userCompany = optional(auth()->user()->loginDetail)->company_code;

        $filters = $request->only([
            'company_id',
            'kompartemen_id',
            'departemen_id',
            'job_role_id',
            'periode_id',
        ]);

        $filename = 'job_role_user_ids_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new JobUserIdExport($userCompany, $filters),
            $filename
        );
    }

    public function bulkDestroy(Request $request)
    {
        $ids = array_filter($request->input('ids', []), fn($id) => !empty($id));

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada job role yang dipilih.'
            ], 422);
        }

        $jobRoles = JobRole::whereIn('id', $ids)->get();

        if ($jobRoles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Job role tidak ditemukan atau sudah dihapus.'
            ], 404);
        }

        $deleted = 0;
        foreach ($jobRoles as $jobRole) {
            if ($jobRole->delete()) {
                $deleted++;
            }
        }

        app(JSONService::class)->generateMasterDataJson();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "{$deleted} job role berhasil dihapus."
        ]);
    }
}
