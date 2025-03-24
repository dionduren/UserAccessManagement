<?php

namespace App\Http\Controllers\Relationship;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Periode;
use App\Models\userNIK;
use App\Models\NIKJobRole;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class NIKJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodes = Periode::select('id', 'definisi')->get();
        return view('relationship.nik_job_role.index', compact('periodes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $periodes = Periode::select('id', 'definisi')->get();
        $userNIKs = userNIK::select('id', 'user_code')->get();
        $companies  = Company::all();  // or ->select('id','name')->get();

        return view('relationship.nik_job_role.create', compact('periodes', 'companies', 'userNIKs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:ms_periode,id',
            'job_role_id' => 'required|exists:tr_job_roles,id',
            'user_code' => 'required|exists:tr_user_ussm_nik,user_code',
        ]);

        NIKJobRole::create([
            'periode_id' => $request->input('periode_id'),
            'job_role_id' => $request->input('job_role_id'),
            'nik' => $request->input('user_code'),
        ]);

        return redirect()->route('nik-job.index')->with('success', 'NIK Job Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $nikJobRole = NIKJobRole::findOrFail($id);
        // It is assumed that you have defined the following relationships:
        // - $nikJobRole->periode
        // - $nikJobRole->userNIK
        // - $nikJobRole->jobRole and within it:
        //      ->company, ->kompartemen, and ->departemen

        return view('relationship.nik_job_role.show', compact('nikJobRole'));
    }

    public function modalShow($id)
    {
        $nikJobRole = NIKJobRole::with([
            'periode',
            'userNIK.userDetail',
            'jobRole.company',
            'jobRole.kompartemen',
            'jobRole.departemen'
        ])->findOrFail($id);

        // Return only the modal content view
        return view('relationship.nik_job_role.show', compact('nikJobRole'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Find the record or fail with a 404.
        $nikJobRole = NIKJobRole::findOrFail($id);

        // Get all required data for the dropdowns.
        $periodes  = Periode::select('id', 'definisi')->get();
        $userNIKs  = userNIK::select('id', 'user_code')->get();
        $companies = Company::all(); // or select only id and name

        // Pass the NIKJobRole record along with dropdown data.
        return view('relationship.nik_job_role.edit', compact('nikJobRole', 'periodes', 'companies', 'userNIKs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'periode_id'   => 'required|exists:ms_periode,id',
            'job_role_id'  => 'required|exists:tr_job_roles,id',
            'user_code'         => 'required|exists:tr_user_ussm_nik,user_code', // adjust if needed
        ]);

        $nikJobRole = NIKJobRole::findOrFail($id);
        $nikJobRole->update([
            'periode_id'  => $request->input('periode_id'),
            'job_role_id' => $request->input('job_role_id'),
            'nik'         => $request->input('user_code'),
        ]);

        return redirect()->route('nik-job.index')
            ->with('success', 'NIK Job Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NIKJobRole $nIKJobRole)
    {
        //
    }


    /**
     * Get NIK Job Roles based on Periode ID from request input
     */
    public function getNIKJobRolesByPeriodeId(Request $request)
    {
        $periodeId = $request->input('periode_id');

        $nikJobRoles = NIKJobRole::select('id', 'nik', 'job_role_id', 'periode_id')
            ->with(['jobRole' => function ($query) {
                $query->select('id', 'nama_jabatan');
            }])
            ->with(['periode' => function ($query) {
                $query->select('id', 'definisi');
            }])
            ->with(['userDetail' => function ($query) {
                $query->select('nik', 'nama');
            }])
            ->where('periode_id', $periodeId)
            ->get();

        return DataTables::of($nikJobRoles)
            ->addColumn('nama', function ($row) {
                return $row->userDetail ? $row->userDetail->nama : '-';
            })
            ->addColumn('job_role', function ($row) {
                return $row->jobRole ? $row->jobRole->nama_jabatan : '-';
            })
            ->addColumn('periode', function ($row) {
                return $row->periode ? $row->periode->definisi : '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('nik-job.show', $row->id) . '" target="_blank" class="btn btn-sm btn-primary me-1">
                        <i class="bi bi-info-circle-fill"></i> Detail
                    </a>
                    <a href="' . route('nik-job.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning me-1">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>';
                // <button onclick="deleteNIKJob(' . $row->id . ')" class="btn btn-sm btn-danger disabled">
                //     <i class="bi bi-trash-fill"></i> Delete
                // </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
