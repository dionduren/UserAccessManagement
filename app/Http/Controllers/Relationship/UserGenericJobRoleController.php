<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\JobRole;
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
                    'user_code'
                ])
                ->with(['periode', 'NIKJobRole'])
                ->where('periode_id', $request->input('periode'));

            return DataTables::of($query)
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
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('user-generic-job-role.edit', $row->id) . '" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-edit"></i> Edit
                </a>
                <button onclick="deleteRelationship(' . $row->id . ')" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash"></i> Delete
                </button>';
                })
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
            'job_role_id' => 'required|string|max:50',
            'job_role_name' => 'required|string|max:100',
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
    }
}
