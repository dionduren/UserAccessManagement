<?php

namespace App\Http\Controllers\MasterData;

use App\Models\userNIK;
use Illuminate\Http\Request;
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
            $userNik = UserNIK::with(['userDetail.kompartemen']) // Join UserDetail and Kompartemen
                ->select('id', 'group', 'user_code', 'user_type', 'license_type', 'valid_from', 'valid_to');

            return DataTables::of($userNik)
                ->editColumn('valid_from', function ($row) {
                    return $row->valid_from ? Carbon::createFromFormat('d.m.Y', $row->valid_from)->format('d M Y') : '-';
                })
                ->editColumn('valid_to', function ($row) {
                    return $row->valid_to ? Carbon::createFromFormat('d.m.Y', $row->valid_to)->format('d M Y') : '-';
                })
                ->addColumn('nama', function ($row) {
                    return $row->userDetail->nama ?? 'N/A'; // Get User's Name
                })
                ->addColumn('kompartemen_name', function ($row) {
                    return $row->userDetail->kompartemen->name ?? 'N/A'; // Get Kompartemen Name
                })
                ->addColumn('direktorat', function ($row) {
                    return $row->userDetail->direktorat ?? 'N/A'; // Get Direktorat
                })
                ->addColumn('cost_center', function ($row) {
                    return $row->userDetail->cost_center ?? 'N/A'; // Get Direktorat
                })
                // ->addColumn('grade', function ($row) {
                //     return $row->userDetail->grade ?? 'N/A'; // Get Direktorat
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

        return view('master-data.user_nik.index');
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
    public function show(UserNIK $userNIK)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserNIK $userNIK)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserNIK $userNIK)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserNIK $userNIK)
    {
        //
    }
}
