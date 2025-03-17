<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Periode;
use App\Models\userGeneric;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class UserGenericController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // $userGenerics = UserGeneric::with(['costCenter, periode'])
            $userGenerics = UserGeneric::with(['periode'])
                ->select('id', 'group', 'periode_id', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to')
                ->when($request->filled('periode'), function ($query) use ($request) {
                    return $query->where('periode_id', $request->input('periode'));
                })
                ->when(!$request->filled('periode'), function ($query) {
                    return $query->whereNull('periode_id');
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
                // ->addColumn('cost_center', function ($row) {
                //     return $row->costCenter->cost_center ?? 'N/A';
                // })
                // ->addColumn('deskripsi', function ($row) {
                //     return $row->costCenter->deskripsi ?? 'N/A';
                // })
                ->addColumn('action', function ($row) {
                    // return '<a href="' . route('user-generic.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning">Edit</a> 
                    return '<a target="_blank" class="btn btn-sm btn-outline-warning" disabled>
                    <i class="fas fa-edit"></i>Edit
                    </a>
                    <button onclick="deleteUserGeneric(' . $row->id . ')" class="btn btn-sm btn-outline-danger" disabled>
                    <i class="fas fa-trash"></i>Delete</button>';
                    // <button onclick="deleteUserGeneric(' . $row->id . ')" class="btn btn-sm btn-danger">Delete</button>';                
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
            $userGenerics = UserGeneric::with(['costCenter', 'currentUser', 'prevUser'])
                ->select('id', 'group', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to');

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
                })
                ->addColumn('dokumen_keterangan', function ($row) {
                    // currentUser is a hasOne relation
                    return $row->currentUser ? $row->currentUser->dokumen_keterangan : '-';
                })
                ->addColumn('prev_user', function ($row) {
                    // prevUser is a hasMany relation; join user_name values if multiple exist
                    return ($row->prevUser && $row->prevUser->isNotEmpty())
                        ? $row->prevUser->pluck('user_name')->implode(', ')
                        : '-';
                })
                ->make(true);
        }

        return view('dashboard.costcenter-user.index');
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
    public function edit(UserGeneric $userGeneric)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserGeneric $userGeneric)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserGeneric $userGeneric)
    {
        //
    }
}
