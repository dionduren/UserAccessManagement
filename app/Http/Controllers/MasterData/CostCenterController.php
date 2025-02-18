<?php

namespace App\Http\Controllers\MasterData;

use App\Models\CostCenter;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Company;

class CostCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $costCenters = CostCenter::select('id', 'group', 'cost_center', 'cost_code', 'deskripsi');
            return DataTables::of($costCenters)
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('cost-center.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning">Edit</a> 
                        <button onclick="deleteCostCenter(' . $row->id . ')" class="btn btn-sm btn-danger">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('master-data.cost_center.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shortName = Company::all();

        return view('master-data.cost_center.create', compact('shortName'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group' => 'nullable|string',
            'cost_center' => 'required|string|max:255',
            'cost_code' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            CostCenter::create($request->all());

            return redirect()->route('cost-center.index')->with('success', 'Cost Center created successfully!');
        } catch (\Exception $e) {
            // return back()->with('error', $e->getMessage());
            Log::info('Error creating Cost Center = ', $e->getMessage());
            // return back()->with('error', 'Failed to create the cost center. Please try again.');
            return redirect()->back()
                ->withErrors($e->validator->errors())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $costCenter = CostCenter::findOrFail($id);
        return view('master-data.cost_center.show', compact('costCenter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $shortName = Company::all();
        $costCenter = CostCenter::findOrFail($id);
        return view('master-data.cost_center.edit', compact('costCenter', 'shortName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'group' => 'required|string',
            'cost_center' => 'required|unique:ms_cost_center,cost_center,' . $id,
            'cost_code' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $costCenter = CostCenter::findOrFail($id);
            $costCenter->update($request->all());

            return redirect()->route('cost-center.index')->with('success', 'Cost Center updated successfully!');
        } catch (\Exception $e) {
            Log::info('Error updating Cost Center = ', $e->getMessage());
            return redirect()->back()
                ->withErrors($e->validator->errors())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy($id)
    // {
    //     $costCenter = CostCenter::findOrFail($id);
    //     $costCenter->delete();

    //     return redirect()->route('cost-center.index')->with('success', 'Cost Center deleted successfully!');
    // }
    public function destroy($id)
    {
        $costCenter = CostCenter::findOrFail($id);
        $costCenter->delete();

        return response()->json(['success' => 'Cost Center deleted successfully.']);
    }
}
