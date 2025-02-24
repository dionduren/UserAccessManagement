<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\userGeneric;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;

class UserGenericController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $userGenerics = UserGeneric::with(['costCenter'])
                ->select('id', 'group', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to');
            return DataTables::of($userGenerics)
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from ? Carbon::createFromFormat('d.m.Y', $row->valid_from)->format('d M Y') : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to ? Carbon::createFromFormat('d.m.Y', $row->valid_to)->format('d M Y') : '-';
                })
                ->addColumn('cost_center', function ($row) {
                    return $row->costCenter->cost_center ?? 'N/A'; // Get User's Name
                })
                ->addColumn('deskripsi', function ($row) {
                    return $row->costCenter->deskripsi ?? 'N/A'; // Get User's Name
                })
                ->addColumn('action', function ($row) {
                    // return '<a href="' . route('user-generic.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning">Edit</a> 
                    return '<a target="_blank" class="btn btn-sm btn-outline-warning" disabled>Edit</a> 
                        <button onclick="deleteUserGeneric(' . $row->id . ')" class="btn btn-sm btn-outline-danger" disabled>Delete</button>';
                    // <button onclick="deleteUserGeneric(' . $row->id . ')" class="btn btn-sm btn-danger">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('master-data.user_generic.index');
    }

    public function index_dashboard(Request $request)
    {
        if ($request->ajax()) {
            // Load relationships: costCenter, currentUser, and prevUser
            $userGenerics = UserGeneric::with(['costCenter', 'currentUser', 'prevUser'])
                ->select('id', 'group', 'user_code', 'user_type', 'cost_code', 'license_type', 'valid_from', 'valid_to');

            return DataTables::of($userGenerics)
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from
                        ? Carbon::createFromFormat('d.m.Y', $row->valid_from)->format('d M Y')
                        : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to
                        ? Carbon::createFromFormat('d.m.Y', $row->valid_to)->format('d M Y')
                        : '-';
                })
                ->addColumn('cost_center', function ($row) {
                    return $row->costCenter->cost_center ?? 'N/A';
                })
                ->addColumn('deskripsi', function ($row) {
                    return $row->costCenter->deskripsi ?? 'N/A';
                })
                ->addColumn('current_user', function ($row) {
                    // currentUser is a hasOne relation
                    return $row->currentUser ? $row->currentUser->user_name : 'N/A';
                })
                ->addColumn('dokumen_keterangan', function ($row) {
                    // currentUser is a hasOne relation
                    return $row->currentUser ? $row->currentUser->dokumen_keterangan : 'N/A';
                })
                ->addColumn('prev_user', function ($row) {
                    // prevUser is a hasMany relation; join user_name values if multiple exist
                    return ($row->prevUser && $row->prevUser->isNotEmpty())
                        ? $row->prevUser->pluck('user_name')->implode(', ')
                        : 'N/A';
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
