<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\JobRole;
use \App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\userGeneric;

use App\Exports\UserGenericWithoutJobRoleExport;
use App\Models\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class UserGenericJobRoleController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();
        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companyShortname = Company::where('company_code', $userCompany)->value('shortname');
        if ($userCompany == 'A000') {
            $companyShortname = null;
        }

        if ($request->ajax()) {
            if (!$request->filled('periode')) {
                return DataTables::of(collect([]))->make(true);
            }
            $periodeId = (int)$request->input('periode');

            $query = userGeneric::query()
                ->select([
                    'id',
                    'periode_id',
                    'group',
                    'user_code',
                    'flagged',
                    'keterangan_flagged',
                    'user_profile as definisi'
                ])
                ->when($companyShortname, fn($q) => $q->where('group', $companyShortname))
                ->with([
                    'periode',
                    'NIKJobRole' => function ($q) use ($periodeId) {
                        $q->where('periode_id', $periodeId)
                            ->whereNull('deleted_at')
                            ->with([
                                'jobRole:id,job_role_id,nama',
                            ]);
                    }
                ])
                ->where('periode_id', $periodeId)
                ->whereHas('NIKJobRole', function ($q) use ($periodeId) {
                    $q->where('periode_id', $periodeId)
                        ->whereNull('deleted_at');
                });

            return DataTables::eloquent($query)
                ->addColumn('periode', fn($row) => $row->periode?->definisi ?? '-')
                ->addColumn('job_role_id', fn($row) => $row->NIKJobRole->pluck('job_role_id')->unique()->implode(', '))
                ->addColumn('job_role_name', fn($row) => $row->NIKJobRole->map(
                    fn($r) => $r->jobRole?->nama ?? '-'
                )->unique()->implode(', '))
                ->addColumn('flagged', function ($row) {
                    $flags = $row->NIKJobRole->pluck('flagged')->filter();
                    return $flags->count() ? 'true' : 'false';
                })
                ->addColumn('job_role_count', fn($row) => $row->NIKJobRole->count())
                ->make(true);
        }

        return view('relationship.generic-job_role.index', compact('periodes'));
    }

    public function create()
    {
        // TODO: filter berdasarkan periode aktif?
        $periodes = Periode::select('id', 'definisi')->get();
        $userGenerics = userGeneric::whereDoesntHave('NIKJobRole')->get();
        $jobRoles = JobRole::select('job_role_id', 'nama')->get();
        return view('relationship.generic-job_role.create', compact('periodes', 'userGenerics', 'jobRoles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_generic_id' => 'required|exists:tr_user_generic,id',
            'job_role_id'     => 'required|string',
            'periode_id'      => 'required|exists:ms_periode,id',
        ]);

        $ug = userGeneric::findOrFail($request->user_generic_id);

        // Cegah duplikat (nik + job_role_id + periode_id)
        $exists = NIKJobRole::where('nik', $ug->user_code)
            ->where('job_role_id', $request->job_role_id)
            ->where('periode_id', $request->periode_id)
            ->exists();

        if (!$exists) {
            NIKJobRole::create([
                'nik'         => $ug->user_code,
                'job_role_id' => $request->job_role_id,
                'periode_id'  => $request->periode_id,
            ]);
        }

        return redirect()
            ->route('user-generic-job-role.index')
            ->with('success', 'Relasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Get companies based on user access
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        if ($userCompany && $userCompany !== 'A000') {
            $companies = Company::where('company_code', $userCompany)->get();
        } else {
            $companies = Company::all();
        }

        // Show the form for editing the specified resource.
        $userGenerics = userGeneric::orderBy('user_code')->get();
        $periodes = Periode::select('id', 'definisi')->get();

        $userGeneric = userGeneric::with(['NIKJobRole.jobRole'])->findOrFail($id);
        $nikJobRole = $userGeneric->NIKJobRole->first();

        if (!$nikJobRole) {
            return redirect()->route('user-generic-job-role.index')
                ->with('error', 'Tidak ada Job Role yang terkait dengan User Generic ini.');
        }

        return view('relationship.generic-job_role.edit', compact(
            'userGeneric',
            'nikJobRole',
            'userGenerics',
            'periodes',
            'companies',
            'userCompany'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_generic_id' => 'required|exists:tr_user_generic,user_code',
            'job_role_id'     => 'required|string|exists:tr_job_roles,job_role_id', // Ensure job_role_id exists
            'periode_id'      => 'required|exists:ms_periode,id',
        ]);

        // Find the existing NIKJobRole record by ID
        $record = NIKJobRole::findOrFail($id);

        // Check for duplicate (nik + job_role_id + periode_id) EXCLUDING current record
        $duplicate = NIKJobRole::where('nik', $request->user_generic_id)
            ->where('job_role_id', $request->job_role_id)
            ->where('periode_id', $request->periode_id)
            ->where('id', '!=', $id) // Exclude current record
            ->exists();

        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Relasi dengan User Generic, Job Role, dan Periode yang sama sudah ada.');
        }

        // Update all fields
        $record->update([
            'nik'         => $request->user_generic_id,
            'job_role_id' => $request->job_role_id,
            'periode_id'  => $request->periode_id,
            'user_type'   => 'generic', // Ensure user_type is set correctly
        ]);

        return redirect()->route('user-generic-job-role.index')
            ->with('success', 'Relasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Remove the specified resource from storage.
        $nikJobRole = NIKJobRole::findOrFail($id);
        $nikJobRole->delete();
    }

    public function show($id)
    {
        $userGeneric = userGeneric::with([
            'NIKJobRole.jobRole'
        ])->findOrFail($id);

        // Assuming only one job role per user generic for simplicity
        $nikJobRole = $userGeneric->NIKJobRole->first();

        return response()->json([
            'periode' => $userGeneric->periode->definisi,
            'user_code' => $userGeneric->user_code,
            'periode_job_role' => $nikJobRole?->periode->definisi,
            'job_role_id' => $nikJobRole?->job_role_id,
            'job_role_name' => $nikJobRole?->jobRole?->nama,
            'kompartemen_id' => $userGeneric->userGenericUnitKerja?->kompartemen_id,
            'kompartemen_nama' => $userGeneric->userGenericUnitKerja?->kompartemen?->nama,
            'departemen_id' => $userGeneric->userGenericUnitKerja?->departemen_id,
            'departemen_nama' => $userGeneric->userGenericUnitKerja?->departemen?->nama,
            'flagged' => $nikJobRole?->flagged,
            'keterangan_flagged' => $nikJobRole?->keterangan_flagged,
        ]);
    }

    public function updateFlagged(Request $request, $id)
    {
        $jobRole = NIKJobRole::findOrFail($id);
        $jobRole->flagged = $request->input('flagged', 0);
        $jobRole->keterangan_flagged = $request->input('keterangan_flagged');
        $jobRole->save();

        return response()->json(['success' => true]);
    }

    /**
     * Display a listing of the resource without job roles.
     */
    public function indexWithoutJobRole(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();
        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companyShortname = Company::where('company_code', $userCompany)->value('shortname');
        if ($userCompany == 'A000') {
            $companyShortname = null; // A000 => no filter
        }

        if ($request->ajax()) {
            if (!$request->filled('periode')) {
                return DataTables::of(collect([]))->make(true);
            }

            $periodeId = (int) $request->input('periode');

            $query = userGeneric::query()
                ->select([
                    'id',
                    'group',
                    'user_code',
                    'last_login',
                ])
                // Only filter by company for non-A000
                ->when($companyShortname, fn($q) => $q->where('group', $companyShortname))
                // Collect wrong job_role_id(s) (not found in tr_job_roles or soft-deleted)
                ->selectSub(function ($sub) use ($periodeId) {
                    $sub->from('tr_ussm_job_role as jr')
                        ->leftJoin('tr_job_roles as j', function ($join) {
                            $join->on('jr.job_role_id', '=', 'j.job_role_id')
                                ->whereNull('j.deleted_at'); // treat soft-deleted as missing
                        })
                        ->selectRaw("string_agg(DISTINCT jr.job_role_id::text, ',' ORDER BY jr.job_role_id::text)")
                        ->whereColumn('jr.nik', 'tr_user_generic.user_code')
                        ->where('jr.periode_id', $periodeId)
                        ->whereNull('jr.deleted_at')
                        ->whereNull('j.job_role_id');
                }, 'wrong_job_role_id')
                ->with(['periode', 'userGenericUnitKerja.kompartemen', 'userGenericUnitKerja.departemen'])
                ->where('periode_id', $periodeId)
                ->where(function ($q) use ($periodeId) {
                    $q->whereNotExists(function ($q1) use ($periodeId) {
                        $q1->selectRaw(1)
                            ->from('tr_ussm_job_role as jr')
                            ->whereColumn('jr.nik', 'tr_user_generic.user_code')
                            ->where('jr.periode_id', $periodeId)
                            ->whereNull('jr.deleted_at');
                    })
                        ->orWhereExists(function ($q2) use ($periodeId) {
                            $q2->selectRaw(1)
                                ->from('tr_ussm_job_role as jr2')
                                ->leftJoin('tr_job_roles as j', function ($join) {
                                    $join->on('jr2.job_role_id', '=', 'j.job_role_id')
                                        ->whereNull('j.deleted_at');
                                })
                                ->whereColumn('jr2.nik', 'tr_user_generic.user_code')
                                ->where('jr2.periode_id', $periodeId)
                                ->whereNull('jr2.deleted_at')
                                ->whereNull('j.job_role_id');
                        });
                });

            return DataTables::eloquent($query)
                ->addColumn('kompartemen', function ($row) {
                    return $row->userGenericUnitKerja && $row->userGenericUnitKerja->kompartemen
                        ? $row->userGenericUnitKerja->kompartemen->nama
                        : '-';
                })
                ->addColumn('departemen', function ($row) {
                    return $row->userGenericUnitKerja && $row->userGenericUnitKerja->departemen
                        ? $row->userGenericUnitKerja->departemen->nama
                        : '-';
                })
                ->make(true);
        }

        return view('relationship.generic-job_role.no-relationship', compact('periodes'));
    }

    public function exportWithoutJobRole(Request $request)
    {
        $periodeId = (int) $request->get('periode');
        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companyShortname = Company::where('company_code', $userCompany)->value('shortname');
        if ($userCompany == 'A000') {
            $companyShortname = null; // A000 => no filter
        }

        if (!$periodeId) {
            return redirect()->back()->with('error', 'Periode harus dipilih untuk export');
        }

        $periode = Periode::find($periodeId);
        $periodeName = $periode ? $periode->definisi : 'Unknown';
        $filename = 'User_Generic_Without_Job_Role_' . $periodeName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new UserGenericWithoutJobRoleExport($periodeId, $companyShortname), // ensure export uses ->when($companyShortname, ...)
            $filename
        );
    }

    /**
     * Show form to resolve duplicates for a specific user
     */
    public function resolveDuplicates($userCode, Request $request)
    {
        $periodeId = $request->query('periode_id');
        if (!$periodeId) {
            return redirect()->route('user-generic-job-role.index')
                ->with('error', 'Periode harus dipilih');
        }

        $userGeneric = userGeneric::where('user_code', $userCode)->firstOrFail();

        // Get all job roles for this user in this periode
        $duplicateRecords = NIKJobRole::where('nik', $userCode)
            ->where('periode_id', $periodeId)
            ->whereNull('deleted_at')
            ->with('jobRole:id,job_role_id,nama', 'periode')
            ->get();

        if ($duplicateRecords->count() <= 1) {
            return redirect()->route('user-generic-job-role.index')
                ->with('info', 'User ini tidak memiliki duplikat job role di periode yang dipilih');
        }

        $periodes = Periode::select('id', 'definisi')->get();

        return view('relationship.generic-job_role.resolve-duplicates', compact(
            'userGeneric',
            'duplicateRecords',
            'periodes',
            'periodeId'
        ));
    }

    /**
     * Process the split: assign each job role to different periode
     */
    public function splitDuplicates($userCode, Request $request)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.record_id' => 'required|exists:tr_ussm_job_role,id',
            'assignments.*.action' => 'required|in:keep,update,delete',
            // Make periode_id conditional: required only for 'update' action
            'assignments.*.periode_id' => 'required_if:assignments.*.action,update|nullable|exists:ms_periode,id',
        ]);

        $userGeneric = userGeneric::where('user_code', $userCode)->firstOrFail();

        foreach ($request->assignments as $assignment) {
            $record = NIKJobRole::findOrFail($assignment['record_id']);

            switch ($assignment['action']) {
                case 'keep':
                    // Do nothing, keep as is
                    break;

                case 'update':
                    // Validate periode_id is provided
                    if (empty($assignment['periode_id'])) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Periode harus dipilih untuk aksi "Pindah ke Periode Lain"');
                    }

                    // Check if target periode already has this job role for this user
                    $exists = NIKJobRole::where('nik', $userCode)
                        ->where('job_role_id', $record->job_role_id)
                        ->where('periode_id', $assignment['periode_id'])
                        ->where('id', '!=', $record->id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', "Job Role {$record->job_role_id} sudah ada di periode yang dipilih untuk user ini");
                    }

                    $record->update([
                        'periode_id' => $assignment['periode_id']
                    ]);
                    break;

                case 'delete':
                    $record->delete();
                    break;
            }
        }

        return redirect()->route('user-generic-job-role.index')
            ->with('success', 'Duplikat berhasil diselesaikan untuk user ' . $userCode);
    }
}
