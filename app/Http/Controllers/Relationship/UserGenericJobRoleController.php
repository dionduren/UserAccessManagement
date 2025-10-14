<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\JobRole;
use \App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\userGeneric;

use App\Exports\UserGenericWithoutJobRoleExport;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class UserGenericJobRoleController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();

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
                ->with(['periode', 'NIKJobRole' => function ($q) use ($periodeId) {
                    $q->where('periode_id', $periodeId)->with('jobRole');
                }])
                ->where('periode_id', $periodeId)
                ->whereHas('NIKJobRole', function ($q) use ($periodeId) {
                    $q->where('periode_id', $periodeId);
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
        // Show the form for editing the specified resource.
        // Get userGeneric records that do not have any related NIKJobRole
        $userGenerics = userGeneric::orderBy('user_code')->get();
        $periodes = Periode::select('id', 'definisi')->get();

        $userGeneric = userGeneric::with(['NIKJobRole.jobRole'])->findOrFail($id);
        $jobRoles = JobRole::select('job_role_id', 'nama')->get();
        $nikJobRole = $userGeneric->NIKJobRole->first();
        if (!$nikJobRole) {
            return redirect()->route('user-generic-job-role.index')->with('error', 'Tidak ada Job Role yang terkait dengan User Generic ini.');
        }
        return view('relationship.generic-job_role.edit', compact('userGeneric', 'jobRoles', 'nikJobRole', 'userGenerics', 'periodes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_generic_id' => 'required|exists:tr_user_generic,user_code', // user_code sekarang
            'job_role_id'     => 'required|string',
            'periode_id'      => 'required|exists:ms_periode,id',
        ]);

        $nik = $request->user_generic_id;

        $record = NIKJobRole::where('nik', $nik)
            ->where('periode_id', $request->periode_id)
            ->first();

        if ($record) {
            $record->job_role_id = $request->job_role_id;
            $record->save();
        } else {
            NIKJobRole::create([
                'nik'         => $nik,
                'job_role_id' => $request->job_role_id,
                'periode_id'  => $request->periode_id,
            ]);
        }

        return redirect()
            ->route('user-generic-job-role.index')
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
            'user_code' => $userGeneric->user_code,
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

        if ($request->ajax()) {
            // Only load data if periode is selected
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
                // Collect wrong job_role_id(s) (not found in tr_job_roles or soft-deleted)
                ->selectSub(function ($sub) use ($periodeId) {
                    $sub->from('tr_ussm_job_role as jr')
                        ->leftJoin('tr_job_roles as j', function ($join) {
                            $join->on('jr.job_role_id', '=', 'j.job_role_id')
                                ->whereNull('j.deleted_at'); // treat soft-deleted as missing
                        })
                        // PostgreSQL string_agg with DISTINCT + ORDER BY must match argument
                        ->selectRaw("string_agg(DISTINCT jr.job_role_id::text, ',' ORDER BY jr.job_role_id::text)")
                        ->whereColumn('jr.nik', 'tr_user_generic.user_code')
                        ->where('jr.periode_id', $periodeId)
                        ->whereNull('jr.deleted_at')
                        ->whereNull('j.job_role_id');
                }, 'wrong_job_role_id')
                ->with(['periode', 'userGenericUnitKerja.kompartemen', 'userGenericUnitKerja.departemen'])
                ->where('periode_id', $periodeId)
                ->where(function ($q) use ($periodeId) {
                    // Users with NO assignment in this period
                    $q->whereNotExists(function ($q1) use ($periodeId) {
                        $q1->selectRaw(1)
                            ->from('tr_ussm_job_role as jr')
                            ->whereColumn('jr.nik', 'tr_user_generic.user_code')
                            ->where('jr.periode_id', $periodeId)
                            ->whereNull('jr.deleted_at');
                    })
                        // OR users with at least one invalid assignment (job_role_id not present in tr_job_roles)
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

        if (!$periodeId) {
            return redirect()->back()->with('error', 'Periode harus dipilih untuk export');
        }

        // Get periode name for filename
        $periode = Periode::find($periodeId);
        $periodeName = $periode ? $periode->definisi : 'Unknown';

        $filename = 'User_Generic_Without_Job_Role_' . $periodeName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new UserGenericWithoutJobRoleExport($periodeId),
            $filename
        );
    }
}
