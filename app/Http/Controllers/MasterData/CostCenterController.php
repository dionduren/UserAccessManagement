<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\CostPrevUser;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class CostCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = 'zzz';

        if ($userCompanyCode === 'A000') {
            $companies = Company::all();
        } else {
            // Get all companies with the same first character as userCompany
            $firstChar = substr($userCompanyCode, 0, 1);
            $companies = Company::where('company_code', 'LIKE', $firstChar . '%')
                ->orderBy('company_code')
                ->get();
        }

        if ($request->ajax()) {
            $query = CostCenter::select('id', 'company_id', 'parent_id', 'level', 'level_id', 'level_name', 'cost_center', 'cost_code', 'deskripsi');

            if ($userCompanyCode === 'A000') {
                if ($request->has('company_id') && !empty($request->company_id)) {
                    $query->where('company_id', $request->company_id);
                }
            } else {
                $allowedCompanyIds = $companies->pluck('company_code');
                $query->whereIn('company_id', $allowedCompanyIds);
            }

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('cost-center.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning">Edit</a> 
            <button onclick="deleteCostCenter(' . $row->id . ')" class="btn btn-sm btn-danger">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('master-data.cost_center.index', compact('companies'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $shortName = 'zzz';

        if ($userCompanyCode === 'A000') {
            $shortName = Company::all();
        } else {
            $shortName = Company::where('company_code', $userCompanyCode)->get();
        }


        return view('master-data.cost_center.create', compact('shortName'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group' => 'required|string',
            'parent_id' => 'required|string',
            'level' => 'required|string',
            'level_id' => 'required|string',
            'level_name' => 'required|string|max:255',
            'cost_center' => 'required|string|max:255',
            'cost_code' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $data = [];

        try {
            // Prepare data for insertion
            $data = [
                'company_id' => $request->input('group'),
                'parent_id' => $request->input('parent_id'),
                'level' => $request->input('level'),
                'level_id' => $request->input('level_id'),
                'level_name' => $request->input('level_name'),
                'cost_center' => $request->input('cost_center'),
                'cost_code' => $request->input('cost_code'),
                'deskripsi' => $request->input('deskripsi'),
                'created_by' => auth()->user()->name,
                'updated_by' => auth()->user()->name,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            CostCenter::create($data);

            return redirect()->route('cost-center.index')->with('success', 'Cost Center created successfully!');
        } catch (\Exception $e) {
            // return back()->with('error', $e->getMessage());
            Log::info('Error creating Cost Center = ', $e->getMessage());
            // return back()->with('error', 'Failed to create the cost center. Please try again.');
            return redirect()->back()
                ->with('error', 'Failed to update the cost center. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    // public function show($id)
    // {
    //     // $costCenter = CostCenter::findOrFail($id);
    //     // return view('master-data.cost_center.show', compact('costCenter'));
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $shortName = 'zzz';

        $costCenter = CostCenter::findOrFail($id);

        if ($userCompanyCode !== 'A000' && $costCenter->company_id !== $userCompanyCode) {
            return redirect()
                ->route('cost-center.index')
                ->withErrors(['error' => 'You are not authorized to edit this cost center.']);
        }

        if ($userCompanyCode === 'A000') {
            $shortName = Company::all();
        } else {
            $shortName = Company::where('company_code', $userCompanyCode)->get();
        }

        return view('master-data.cost_center.edit', compact('costCenter', 'shortName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'group' => 'required|string',
            'parent_id' => 'required|string',
            'level' => 'required|string',
            'level_id' => 'required|string',
            'level_name' => 'required|string|max:255',
            'cost_center' => 'required|unique:ms_cost_center,cost_center,' . $id,
            'cost_code' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $data = [];

        try {
            // Prepare data for insertion
            $data = [
                'company_id' => $request->input('group'),
                'parent_id' => $request->input('parent_id'),
                'level' => $request->input('level'),
                'level_id' => $request->input('level_id'),
                'level_name' => $request->input('level_name'),
                'cost_center' => $request->input('cost_center'),
                'cost_code' => $request->input('cost_code'),
                'deskripsi' => $request->input('deskripsi'),
                'updated_by' => auth()->user()->name,
                'updated_at' => now(),
            ];

            // Update the cost center
            $costCenter = CostCenter::findOrFail($id);
            $costCenter->update($data);

            return redirect()->route('cost-center.index')->with('success', 'Cost Center updated successfully!');
        } catch (\Exception $e) {
            Log::info('Error updating Cost Center = ', $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update the cost center. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $costCenter = CostCenter::findOrFail($id);
        $costCenter->delete();

        return response()->json(['success' => 'Cost Center deleted successfully.']);
    }

    // public function index_prev_user(Request $request)
    // {
    //     if ($request->ajax()) {
    //         // $costCenters = CostPrevUser::select('id', 'user_code', 'user_name', 'cost_code', 'dokumen_keterangan');
    //         $costCenters = CostPrevUser::select('id', 'user_code', 'user_name', 'cost_code', 'flagged', 'keterangan');
    //         return DataTables::of($costCenters)
    //             ->addColumn('action', function ($row) {
    //                 return '<button type="button" class="btn btn-sm btn-primary btn-edit"
    //                 data-id="' . $row->id . '"
    //                 data-flagged="' . $row->flagged . '"
    //                 data-keterangan="' . e($row->keterangan) . '">
    //                 Tandai
    //             </button>
    //             <a href="' . route('prev-user.edit', $row->id) . '" class="btn btn-sm btn-secondary" target="_blank">
    //                 Edit
    //             </a>';
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('master-data.cost_center.prev-user.index');
    // }

    // public function update_prev_user(Request $request)
    // {
    //     $costPrevUser = CostPrevUser::findOrFail($request->id);
    //     $costPrevUser->flagged = $request->flagged;
    //     $costPrevUser->keterangan = $request->keterangan;
    //     $costPrevUser->save();

    //     return response()->json(['success' => true]);
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit_prev_user($id)
    // {
    //     $costPrevUser = CostPrevUser::findOrFail($id);
    //     return view('master-data.cost_center.prev-user.edit', compact('costPrevUser'));
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function full_update_prev_user(Request $request, $id)
    // {
    //     $request->validate([
    //         'user_code' => 'required|string|max:255',
    //         'user_name' => 'required|string|max:255',
    //         'cost_code' => 'required|string|max:255',
    //         'flagged' => 'required|boolean',
    //         'keterangan' => 'nullable|string',
    //     ]);

    //     try {
    //         $costPrevUser = CostPrevUser::findOrFail($id);
    //         $costPrevUser->update($request->all());

    //         return redirect()->route('prev-user.index')->with('success', 'Previous User updated successfully!');
    //     } catch (\Exception $e) {
    //         Log::info('Error updating Previous User = ', $e->getMessage());
    //         return redirect()->back()->with('error', 'Failed to update the previous user. Please try again.');
    //     }
    // }
}
