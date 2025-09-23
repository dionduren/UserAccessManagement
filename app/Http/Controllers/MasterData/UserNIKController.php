<?php

namespace App\Http\Controllers\MasterData;

use Excel;
use App\Models\Periode;
use App\Models\userNIK;
use App\Models\UserNIKUnitKerja;
use Illuminate\Http\Request;
use App\Exports\UserNIKExport;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class UserNIKController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $userNik = UserNIK::with(['periode'])
                ->select('id', 'group', 'periode_id', 'user_code', 'user_type', 'last_login', 'license_type', 'valid_from', 'valid_to')
                ->when($request->filled('periode'), function ($query) use ($request) {
                    return $query->where('periode_id', $request->input('periode'));
                })
                ->when(!$request->filled('periode'), function ($query) {
                    return $query->whereNull('periode_id');
                });

            return DataTables::of($userNik)
                ->addColumn('user_detail_exists', function ($row) {
                    return $row->UserNIKUnitKerja ? true : false;
                })
                ->editColumn('last_login', function ($row) {
                    return $row->last_login ? Carbon::createFromFormat('Y-m-d H:i:s', $row->last_login)->format('d M Y - H:i') : '-';
                })
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from ? Carbon::createFromFormat('Y-m-d', $row->valid_from)->format('d M Y') : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to ? Carbon::createFromFormat('Y-m-d', $row->valid_to)->format('d M Y') : '-';
                })
                ->addColumn('periode', function ($row) {
                    return $row->periode ? $row->periode->definisi : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '
                    <button type="button" class="btn btn-sm btn-primary me-1" data-toggle="modal" data-target="#userNIKModal" data-id="' . $row->id . '">
                        <i class="bi bi-info-circle-fill"></i> Detail
                    </button>
                    <a href="' . route('user-nik.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning me-1">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a> 
                    <button onclick="deleteUserNIK(' . $row->id . ')" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash-fill"></i> Delete
                    </button>';
                    // <button onclick="deleteUserNIK(' . $row->id . ')" class="btn btn-sm btn-danger" disabled>Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $periodes = Periode::select('id', 'definisi')->get();

        return view('master-data.user_nik.index', compact('periodes'));
    }


    public function index_mixed(Request $request)
    {
        if ($request->ajax()) {
            $userNik = UserNIK::with(['UserNIKUnitKerja']) // Join UserNIKUnitKerja and Kompartemen
                ->select('id', 'group', 'user_code', 'user_type', 'license_type', 'valid_from', 'valid_to');

            return DataTables::of($userNik)
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from ? Carbon::createFromFormat('Y-m-d', $row->valid_from)->format('d M Y') : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to ? Carbon::createFromFormat('Y-m-d', $row->valid_to)->format('d M Y') : '-';
                })
                ->addColumn('nama', function ($row) {
                    return $row->UserNIKUnitKerja->nama ?? 'N/A'; // Get User's Name
                })
                ->addColumn('kompartemen_name', function ($row) {
                    return $row->UserNIKUnitKerja->kompartemen->nama ?? 'N/A'; // Get Kompartemen Name
                })
                ->addColumn('departemen_name', function ($row) {
                    return $row->UserNIKUnitKerja->departemen->nama ?? 'N/A'; // Get Departemen Name
                })
                ->addColumn('direktorat', function ($row) {
                    return $row->UserNIKUnitKerja->direktorat ?? 'N/A'; // Get Direktorat
                })
                ->addColumn('cost_center', function ($row) {
                    return $row->UserNIKUnitKerja->cost_center ?? 'N/A'; // Get Direktorat
                })
                // ->addColumn('grade', function ($row) {
                //     return $row->UserNIKUnitKerja->grade ?? 'N/A'; // Get Direktorat
                // })
                ->addColumn('action', function ($row) {
                    // return '<a href="' . route('user-nik.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning" disabled>Edit</a> 
                    return '<a target="_blank" class="btn btn-sm btn-outline-warning" disabled>Edit</a> 
                        <button onclick="deleteUserNIK(' . $row->id . ')" class="btn btn-sm btn-outline-danger" disabled>Delete</button>';
                    // <button onclick="deleteUserNIK(' . $row->id . ')" class="btn btn-sm btn-danger" disabled>Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('master-data.user_nik.mixed');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $userNIK = UserNIK::with([
            'unitKerja.company',
            'unitKerja.direktorat',
            'unitKerja.kompartemen',
            'unitKerja.departemen',
            'periode'
        ])->findOrFail($id);

        return view('master-data.user_nik.show', compact('userNIK'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $userNIK = UserNIK::findOrFail($id);
        $periodes = Periode::select('id', 'definisi')->get();
        return view('master-data.user_nik.edit', compact('userNIK', 'periodes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserNIK $user_nik)
    {
        try {
            $request->validate([
                'user_code' => 'required|string|max:255',
                'license_type' => 'required|string|max:255',
                'valid_from' => 'nullable|date',
                'valid_to' => 'nullable|date',
                'periode_id' => 'required',
            ]);


            UserNIK::where('id', $user_nik->id)->update([
                'user_code' => $request->input('user_code'),
                'license_type' => $request->input('license_type'),
                'valid_from' => $request->input('valid_from'),
                'valid_to' => $request->input('valid_to'),
                'periode_id' => $request->input('periode_id'),
            ]);

            // \dd($request->input('periode_id'));

            return redirect()->route('user-nik.index')->with('success', 'User NIK updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserNIK $userNIK)
    {
        //
    }


    /**
     * Check if user detail exists or not.
     */
    public function checkUserDetail(Request $request)
    {
        $userCode = $request->input('user_code');

        $userDetail = UserNIKUnitKerja::with(['company', 'direktorat', 'kompartemen', 'departemen'])
            ->where('nik', $userCode)
            ->first();

        if (empty($userDetail)) {
            return response()->json(['message' => 'Data User Tidak Ditemukan'], 404);
        }

        return response()->json(['userDetail' => $userDetail]);
    }


    /**
     * Download User NIK Template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new UserNIKExport, 'user_nik_template.xlsx');
    }


    /**
     * Show the form for uploading a new resource.
     */
    public function upload()
    {
        $periodes = Periode::select('id', 'definisi')->get();
        return view('upload.user_nik.upload', compact('periodes'));
    }

    public function compare(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();

        return view('master-data.user_nik.compare', compact('periodes'));
    }

    public function getPeriodicUserNIK(Request $request)
    {

        $userNik = UserNIK::with(['periode'])
            ->select('id', 'group', 'periode_id', 'user_code', 'user_type', 'last_login', 'license_type', 'valid_from', 'valid_to')
            ->where('periode_id', $request->input('periode'))
            ->get();

        return DataTables::of($userNik)
            ->editColumn('last_login', function ($row) {
                return $row->last_login ? Carbon::createFromFormat('Y-m-d H:i:s', $row->last_login)->format('d M Y - H:i') : '-';
            })
            ->editColumn('valid_from', function ($row) {
                return $row->valid_from ? Carbon::createFromFormat('Y-m-d', $row->valid_from)->format('d M Y') : '-';
            })
            ->editColumn('valid_to', function ($row) {
                return $row->valid_to ? Carbon::createFromFormat('Y-m-d', $row->valid_to)->format('d M Y') : '-';
            })
            ->addColumn('periode', function ($row) {
                return $row->periode ? $row->periode->definisi : 'N/A';
            })
            ->make(true);
    }
}
