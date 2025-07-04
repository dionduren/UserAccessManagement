<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Periode;
use App\Models\userGeneric;
use App\Models\Company;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\UserLicenseManagement;

class UserGenericController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if (!$request->filled('periode')) {
                return DataTables::of(collect([]))->make(true);
            }

            // Load user generics with relationships
            $userGenerics = UserGeneric::with([
                'periode',
                'userGenericUnitKerja.kompartemen',
                'userGenericUnitKerja.departemen'
            ])
                ->select('id', 'group', 'periode_id', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to', 'flagged')
                ->when($request->filled('periode'), function ($query) use ($request) {
                    return $query->where('periode_id', $request->input('periode'));
                });

            return DataTables::of($userGenerics)
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from ? Carbon::createFromFormat('Y-m-d', $row->valid_from)->format('d M Y') : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to ? Carbon::createFromFormat('Y-m-d', $row->valid_to)->format('d M Y') : '-';
                })
                ->addColumn('periode', function ($row) {
                    return $row->periode ? $row->periode->definisi : 'N/A';
                })
                ->addColumn('kompartemen_id', function ($row) {
                    $unitKerja = $row->userGenericUnitKerja;
                    return $unitKerja ? $unitKerja->kompartemen_id : '-';
                })
                ->addColumn('kompartemen_name', function ($row) {
                    $unitKerja = $row->userGenericUnitKerja;
                    return $unitKerja && $unitKerja->kompartemen ? $unitKerja->kompartemen->nama : '-';
                })
                ->addColumn('departemen_id', function ($row) {
                    $unitKerja = $row->userGenericUnitKerja;
                    return $unitKerja ? $unitKerja->departemen_id : '-';
                })
                ->addColumn('departemen_name', function ($row) {
                    $unitKerja = $row->userGenericUnitKerja;
                    return $unitKerja && $unitKerja->departemen ? $unitKerja->departemen->nama : '-';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('user-generic.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i>Edit
                    </a>
                    <button onclick="deleteUserGeneric(' . $row->id . ')" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>Delete
                    </button>
                    <a href="' . route('user-generic.flagged-edit', $row->id) . '" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-flag"></i>Flagged
                    </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $periodes = Periode::select('id', 'definisi')->get();

        return view('master-data.user_generic.index', compact('periodes'));
    }

    public function index_dashboard(Request $request)
    {
        if ($request->ajax()) {
            // Load relationships: costCenter, currentUser, and prevUser
            // $userGenerics = UserGeneric::with(['costCenter', 'currentUser', 'prevUser'])
            //     ->select('id', 'group', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to');

            $periodeId = $request->input('periode_id');

            $userGenerics = UserGeneric::with(['costCenter', 'currentUser', 'prevUser'])
                ->select('id', 'group', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to');

            if ($periodeId) {
                $userGenerics->where('periode_id', $periodeId);
            }

            return DataTables::of($userGenerics)
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from ? Carbon::createFromFormat('Y-m-d', $row->valid_from)->format('d M Y') : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to ? Carbon::createFromFormat('Y-m-d', $row->valid_to)->format('d M Y') : '-';
                })
                ->addColumn('cost_center', function ($row) {
                    return $row->costCenter->cost_center ?? '-';
                })
                ->addColumn('deskripsi', function ($row) {
                    return $row->costCenter->deskripsi ?? '-';
                })
                ->addColumn('current_user', function ($row) {
                    // currentUser is a hasOne relation
                    return $row->currentUser ? $row->currentUser->user_name : '-';
                    // return $row->currentUser;
                })
                // ->addColumn('dokumen_keterangan', function ($row) {
                //     // currentUser is a hasOne relation
                //     return $row->currentUser ? $row->currentUser->dokumen_keterangan : '-';
                // })
                ->addColumn('prev_user', function ($row) {
                    // prevUser is a hasMany relation; join user_name values if multiple exist
                    return ($row->prevUser && $row->prevUser->isNotEmpty())
                        ? $row->prevUser->pluck('user_name')->implode(', ')
                        : '-';
                    // return $row->prevUser;
                })
                ->make(true);
        }

        $periodes = Periode::select('id', 'definisi')->get();

        return view('dashboard.costcenter-user.index', compact('periodes'));
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
    public function show(UserGeneric $userGeneric)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $userGeneric = UserGeneric::findOrFail($id);
        $periodes = Periode::select('id', 'definisi')->get();
        $companies = Company::select('shortname', 'company_code', 'nama')->get();
        $licenseTypes = UserLicenseManagement::select('id', 'license_type')->get();
        return view('master-data.user_generic.edit', compact('userGeneric', 'periodes', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $userGeneric = UserGeneric::findOrFail($id);

        $validated = $request->validate([
            'periode_id' => 'required|exists:ms_periode,id',
            'group' => 'nullable|string|max:50',
            'user_code' => 'required|string|max:50',
            'user_type' => 'nullable|string|max:50',
            'cost_code' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:50',
            // 'pic' => 'nullable|string|max:100',
            // 'unit_kerja' => 'nullable|string|max:100',
            // 'kompartemen_id' => 'nullable|string|max:50',
            // 'kompartemen_name' => 'nullable|string|max:100',
            // 'departemen_id' => 'nullable|string|max:50',
            // 'departemen_name' => 'nullable|string|max:100',
            // 'job_role_id' => 'nullable|string|max:50',
            // 'job_role_name' => 'nullable|string|max:100',
            // 'keterangan' => 'nullable|string',
            // 'keterangan_update' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'last_login' => 'nullable|date',
        ]);

        $userGeneric->update($validated);

        return redirect()->route('user-generic.index')->with('success', 'User Generic updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserGeneric $userGeneric)
    {
        // Soft delete the userGeneric
        $userGeneric->delete();

        return response()->json(['success' => true, 'message' => 'User Generic deleted successfully.']);
    }

    public function compare(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();

        return view('master-data.user_generic.compare', compact('periodes'));
    }

    public function getPeriodicGenericUser(Request $request)
    {
        if ($request->ajax()) {
            $userGenerics = UserGeneric::with(['periode'])
                ->select('id', 'group', 'periode_id', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to')
                ->when($request->filled('periode'), function ($query) use ($request) {
                    return $query->where('periode_id', $request->input('periode'));
                });
            return DataTables::of($userGenerics)
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

    public function editFlagged($id)
    {
        $userGeneric = UserGeneric::findOrFail($id);
        $periodes = Periode::select('id', 'definisi')->get();
        return view('master-data.user_generic.edit-flagged', compact('userGeneric', 'periodes'));
    }

    public function update_flag(Request $request, $id)
    {
        $request->validate([
            'flagged' => 'required|boolean',
        ]);

        $userGeneric = UserGeneric::findOrFail($id);
        $userGeneric->flagged = $request->input('flagged');
        $userGeneric->save();

        return redirect()->route('user-generic.index')->with('success', 'Flagged status updated successfully.');
    }
}
