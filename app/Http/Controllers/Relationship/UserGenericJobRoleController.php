<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\JobRole;
use \App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\UserGeneric;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserGenericJobRoleController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();

        if ($request->ajax()) {
            // Only load data if periode is selected
            if (!$request->filled('periode')) {
                return DataTables::of(collect([]))->make(true);
            }

            $query = UserGeneric::query()
                ->select([
                    'id',
                    'periode_id',
                    'group',
                    'user_code',
                    'flagged',
                    'keterangan_flagged',
                    'user_profile as definisi'
                ])
                ->with(['periode', 'NIKJobRole'])
                ->where('periode_id', $request->input('periode'));

            return DataTables::eloquent($query)
                ->addColumn('periode', function ($row) {
                    return $row->periode ? $row->periode->definisi : '-';
                })
                ->addColumn('job_role_id', function ($row) {
                    return $row->NIKJobRole->pluck('job_role_id')->implode(', ');
                })
                ->addColumn('job_role_name', function ($row) {
                    return $row->NIKJobRole->map(function ($nikJobRole) {
                        return $nikJobRole->jobRole ? $nikJobRole->jobRole->nama : '-';
                    })->implode(', ');
                })
                ->addColumn('flagged', function ($row) {
                    $flaggedValues = $row->NIKJobRole->map(function ($nikJobRole) {
                        return $nikJobRole->flagged ?? false;
                    });

                    // If all flagged values are false, check $row->flagged
                    if ($flaggedValues->every(fn($value) => !$value)) {
                        return $row->flagged ? 'true' : 'false';
                    }
                })
                // ->addColumn('action', function ($row) {
                //     return '<a href="' . route('user-generic-job-role.edit', $row->id) . '" class="btn btn-sm btn-outline-warning">
                // <i class="fas fa-edit"></i> Edit
                // </a>
                // <button onclick="deleteRelationship(' . $row->id . ')" class="btn btn-sm btn-outline-danger">
                // <i class="fas fa-trash"></i> Delete
                // </button>';
                // })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('relationship.generic-job_role.index', compact('periodes'));
    }

    public function create()
    {
        // TODO: filter berdasarkan periode aktif?
        $periodes = Periode::select('id', 'definisi')->get();
        $userGenerics = UserGeneric::whereNull('job_role_id')->get();
        $jobRoles = JobRole::select('job_role_id', 'nama')->get();
        return view('relationship.generic-job_role.create', compact('periodes', 'userGenerics', 'jobRoles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_generic_id' => 'required|exists:tr_user_generic,id',
            'job_role_id' => 'required|string',
            'job_role_name' => 'required|string',
            'periode_id' => 'required|exists:ms_periode,id',
        ]);

        $userGeneric = UserGeneric::findOrFail($request->user_generic_id);
        $userGeneric->job_role_id = $request->job_role_id;
        $userGeneric->job_role_name = $request->job_role_name;
        $userGeneric->periode_id = $request->periode_id;
        $userGeneric->save();

        return redirect()->route('user-generic-job-role.index')->with('success', 'Relasi User Generic - Job Role berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Show the form for editing the specified resource.
    }

    public function update(Request $request, $id)
    {
        // Update the specified resource in storage.
    }

    public function destroy($id)
    {
        // Remove the specified resource from storage.
        $nikJobRole = NIKJobRole::findOrFail($id);
        $nikJobRole->delete();
    }

    public function show($id)
    {
        $userGeneric = UserGeneric::with([
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

            $query = UserGeneric::query()
                ->select([
                    'id',
                    'group',
                    'user_code',
                    'last_login'
                ])
                ->with(['periode', 'userGenericUnitKerja.kompartemen', 'userGenericUnitKerja.departemen'])
                ->where('periode_id', $request->input('periode'))
                ->whereDoesntHave('NIKJobRole');

            return DataTables::eloquent($query)
                ->addColumn('kompartemen', function ($row) {
                    // Assuming userGenericUnitKerja is a hasOne or belongsTo relationship
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
    // ...existing code...
}
