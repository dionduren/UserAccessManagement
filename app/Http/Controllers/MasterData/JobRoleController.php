<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Traits\AuditsActivity;
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
    use AuditsActivity;
    public function index()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = 'zzz';

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();
        } else {
            // Get all companies with the same first character as userCompany
            $firstChar = substr($userCompanyCode, 0, 1);
            $companies = Company::where('company_code', 'LIKE', $firstChar . '%')->get();
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
                'company_id'     => 'required|exists:ms_company,company_code',
                'nama'           => ['required', 'string'],
                'deskripsi'      => 'nullable|string',
                'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
                'departemen_id'  => 'nullable|exists:ms_departemen,departemen_id',
                // optionally validate job_role_id uniqueness if you allow setting it on create:
                // 'job_role_id' => ['nullable','string', Rule::unique('tr_job_roles','job_role_id')->where('company_id', $request->company_id)],
            ]);

            $jobRole = null;
            DB::transaction(function () use ($request, &$jobRole) {
                $jobRole = JobRole::create($request->all() + [
                    'created_by' => auth()->user()->name
                ]);

                // Increment numbering only if a job_role_id was assigned
                if (filled($request->job_role_id)) {
                    $counter = PenomoranJobRole::firstOrCreate(
                        ['company_id' => $request->company_id],
                        ['last_number' => 0]
                    );
                    $counter->increment('last_number');
                }
            });

            // Audit trail
            if ($jobRole) {
                $this->auditCreate($jobRole);
            }

            return redirect()
                ->route('job-roles.index')
                ->with('status', 'Job role created successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (QueryException $e) {
            Log::error('Error creating Job Role: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred while saving the job role.'])->withInput();
        }
    }

    public function show(JobRole $job_role)
    {
        $jobRole = $job_role->load(['company', 'kompartemen', 'departemen', 'compositeRole']);
        return view('master-data.job_roles.show', compact('jobRole'));
    }

    public function edit(JobRole $job_role)
    {
        $jobRole   = $job_role; // keep view variable name
        $company   = Company::where('company_code', $jobRole->company_id)->first();
        $kompartemen = Kompartemen::where('company_id', $jobRole->company_id)->first();
        $departemen  = Departemen::where('kompartemen_id', $jobRole->kompartemen_id)->first();

        return view('master-data.job_roles.edit', compact('jobRole', 'company', 'kompartemen', 'departemen'));
    }

    public function update(Request $request, JobRole $job_role)
    {
        try {
            $request->validate([
                'company_id'  => 'required|exists:ms_company,company_code',
                'job_role_id' => [
                    'required',
                    'string',
                    Rule::unique('tr_job_roles', 'job_role_id')
                        ->where('company_id', $request->company_id)
                        ->ignore($job_role->id, 'id'),
                ],
                'nama'           => ['required', 'string'],
                'deskripsi'      => 'nullable|string',
                'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
                'departemen_id'  => 'nullable|exists:ms_departemen,departemen_id',
            ]);

            // Store original data for audit
            $originalData = $job_role->toArray();

            DB::transaction(function () use ($request, $job_role) {
                $oldJobRoleId = $job_role->job_role_id;

                $job_role->update($request->all() + ['updated_by' => auth()->user()->name]);

                // If job_role_id changed (including from null -> value), update related and increment numbering
                if (filled($request->job_role_id) && $request->job_role_id !== $oldJobRoleId) {

                    // ✅ FIX: Use correct table name 'tr_ussm_job_role' instead of 'tr_nik_job_roles'
                    if ($oldJobRoleId && DB::table('tr_ussm_job_role')->where('job_role_id', $oldJobRoleId)->exists()) {
                        DB::table('tr_ussm_job_role')
                            ->where('job_role_id', $oldJobRoleId)
                            ->update(['job_role_id' => $request->job_role_id]);
                    }

                    $counter = PenomoranJobRole::firstOrCreate(
                        ['company_id' => $request->company_id],
                        ['last_number' => 0]
                    );
                    $counter->increment('last_number');
                }
            });

            // Audit trail
            $this->auditUpdate($job_role, $originalData);

            return redirect()->route('job-roles.index')->with('status', 'Job role updated successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (QueryException $e) {
            Log::error('Error updating Job Role: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred while saving the job role.'])->withInput();
        }
    }

    public function destroy(JobRole $job_role)
    {
        // Audit trail
        $this->auditDelete($job_role);

        $job_role->delete();
        return redirect()->route('job-roles.index')->with('status', 'Job role deleted successfully.');
    }

    public function getJobRoles(Request $request)
    {
        $userCompany = optional(auth()->user()->loginDetail)->company_code;

        $q = JobRole::query()
            // SoftDeletes global scope already hides deleted rows
            ->leftJoin('ms_company as c', 'c.company_code', '=', 'tr_job_roles.company_id')
            ->leftJoin('ms_kompartemen as k', 'k.kompartemen_id', '=', 'tr_job_roles.kompartemen_id')
            ->leftJoin('ms_departemen as d', 'd.departemen_id', '=', 'tr_job_roles.departemen_id')
            ->select([
                'tr_job_roles.id',
                'tr_job_roles.job_role_id',
                'tr_job_roles.company_id',
                'tr_job_roles.kompartemen_id',
                'tr_job_roles.departemen_id',
                'tr_job_roles.nama',
                'tr_job_roles.deskripsi',
                'tr_job_roles.status',
                'tr_job_roles.flagged',
                'tr_job_roles.keterangan',
                'tr_job_roles.deleted_at',
                // display fields
                DB::raw("COALESCE(c.shortname, c.nama, tr_job_roles.company_id) as company_name"), // fix here
                DB::raw("COALESCE(k.nama, '-') as kompartemen_nama"),
                DB::raw("COALESCE(d.nama, '-') as departemen_nama"),
            ]);

        // Company scope: A000 sees all, others only own company
        if ($userCompany && $userCompany !== 'A000') {
            $q->where('tr_job_roles.company_id', $userCompany);
        }

        // Optional filters
        if ($request->filled('company_id')) {
            $q->where('tr_job_roles.company_id', $request->company_id);
        }
        if ($request->filled('kompartemen_id')) {
            $q->where('tr_job_roles.kompartemen_id', $request->kompartemen_id);
        }
        if ($request->filled('departemen_id')) {
            $q->where('tr_job_roles.departemen_id', $request->departemen_id);
        }
        if ($request->filled('job_role_id')) {
            $q->where('tr_job_roles.job_role_id', $request->job_role_id);
        }
        if ($request->filled('flagged')) {
            $q->where('tr_job_roles.flagged', (bool) $request->boolean('flagged'));
        }

        $rows = $q->orderBy('tr_job_roles.id', 'desc')->get();

        // Map to the same structure your DataTable expects
        $data = $rows->map(function ($r) {
            // Provide both id and job_role_id to actions partial
            $partialPayload = (object) [
                'id'         => $r->id,
                'job_role_id' => $r->job_role_id,
                'deleted_at' => $r->deleted_at,
            ];

            return [
                'id'          => $r->id,
                'job_role_id' => $r->job_role_id ?: 'Not Assigned',
                'company'     => $r->company_name,
                'kompartemen' => $r->kompartemen_nama,
                'departemen'  => $r->departemen_nama,
                'job_role'    => $r->nama,
                'deskripsi'   => $r->deskripsi ?? 'N/A',
                'status'      => $r->status ?? 'Active',
                'flagged'     => (bool) $r->flagged,
                'actions'     => view('master-data.job_roles.partials.actions', ['jobRole' => $partialPayload])->render(),
            ];
        });

        return response()->json($data->values());
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

    // Add this helper
    private function shouldInclude(array $jobRole): bool
    {
        // Skip if soft-deleted
        return empty($jobRole['deleted_at']);
    }

    private function filterByCompany($company, &$filteredJobRoles)
    {
        // job_roles_without_relations
        foreach ($company['job_roles_without_relations'] ?? [] as $jobRole) {
            if (!$this->shouldInclude($jobRole)) continue; // <- added
            $filteredJobRoles[] = $this->mapJobRole(
                $company['company_name'],
                '-',
                '-',
                $jobRole
            );
        }

        // kompartemen -> job_roles
        foreach ($company['kompartemen'] ?? [] as $kompartemen) {
            foreach ($kompartemen['job_roles'] ?? [] as $jobRole) {
                if (!$this->shouldInclude($jobRole)) continue; // <- added
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    $kompartemen['nama'],
                    '-',
                    $jobRole
                );
            }

            // kompartemen -> departemen -> job_roles
            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    if (!$this->shouldInclude($jobRole)) continue; // <- added
                    $filteredJobRoles[] = $this->mapJobRole(
                        $company['company_name'],
                        $kompartemen['nama'],
                        $departemen['nama'],
                        $jobRole
                    );
                }
            }
        }

        // departemen_without_kompartemen -> job_roles
        foreach ($company['departemen_without_kompartemen'] ?? [] as $departemen) {
            foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                if (!$this->shouldInclude($jobRole)) continue; // <- added
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
            if ($kompartemenId != $kompartemen['kompartemen_id']) continue;

            foreach ($kompartemen['job_roles'] ?? [] as $jobRole) {
                if (!$this->shouldInclude($jobRole)) continue; // <- added
                $filteredJobRoles[] = $this->mapJobRole(
                    $company['company_name'],
                    $kompartemen['nama'],
                    '-',
                    $jobRole
                );
            }

            foreach ($kompartemen['departemen'] ?? [] as $departemen) {
                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    if (!$this->shouldInclude($jobRole)) continue; // <- added
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
                if ($departemenId != $departemen['departemen_id']) continue;

                foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                    if (!$this->shouldInclude($jobRole)) continue; // <- added
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
            if ($departemenId != $departemen['departemen_id']) continue;

            foreach ($departemen['job_roles'] ?? [] as $jobRole) {
                if (!$this->shouldInclude($jobRole)) continue; // <- added
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

    public function editFlagged(JobRole $job_role)
    {
        $jobRole = $job_role;
        return view('master-data.job_roles.edit-flagged', compact('jobRole'));
    }

    public function updateFlagged(Request $request, JobRole $job_role)
    {
        $request->validate([
            'flagged' => 'required|boolean',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $job_role->flagged = $request->flagged;
        $job_role->keterangan = $request->keterangan;
        $job_role->updated_by = auth()->user()->name ?? 'system';
        $job_role->updated_at = now();
        $job_role->save();

        app(\App\Services\JSONService::class)->generateMasterDataJson();

        return redirect()->route('job-roles.index')->with('success', 'Flagged status updated and JSON regenerated.');
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
