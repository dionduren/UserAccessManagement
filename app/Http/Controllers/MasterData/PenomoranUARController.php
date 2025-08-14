<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PenomoranUAR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PenomoranUARController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $penomoranUARs = PenomoranUAR::all();
    //     return view('master-data.penomoran_uar.index', compact('penomoranUARs'));
    // }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PenomoranUAR::with(['kompartemen', 'departemen'])->select('ms_penomoran_uar.*');
            return DataTables::of($data)
                ->addColumn('level_unit_kerja', function ($row) {
                    if ($row->departemen) {
                        return 'Departemen';
                    } elseif ($row->kompartemen) {
                        return 'Kompartemen';
                    } else {
                        return 'Perusahaan';
                    }
                })
                ->addColumn('unit_kerja', function ($row) {
                    if ($row->kompartemen) {
                        return $row->kompartemen->nama;
                    } elseif ($row->departemen) {
                        return $row->departemen->nama;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('actions', function ($row) {
                    // $show = route('penomoran-uar.show', $row->id);
                    $edit = route('penomoran-uar.edit', $row->id);
                    $delete = route('penomoran-uar.destroy', $row->id);
                    // return '
                    // <a href="' . $show . '" class="btn btn-info btn-sm">View</a>
                    return '
                    <a href="' . $edit . '" class="btn btn-warning btn-sm">Edit</a>
                    <form action="' . $delete . '" method="POST" style="display:inline;">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete?\')">Delete</button>
                    </form>
                ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('master-data.penomoran_uar.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $masterData = json_decode(Storage::disk('public')->get('master_data.json'), true);
        $companies = $masterData;
        return view('master-data.penomoran_uar.create', compact('masterData', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kompartemenId = $request->input('kompartemen_id');
        $departemenId = $request->input('departemen_id');
        $unitKerjaId = $departemenId ?? $kompartemenId;

        $request->merge(['unit_kerja_id' => $unitKerjaId]);

        $request->validate([
            'company_id' => 'required|string',
            'kompartemen_id' => 'required|string',
            'departemen_id' => 'nullable|string',
            'unit_kerja_id' => 'required|string',
            'number' => 'required|integer',
        ]);

        PenomoranUAR::create($request->all());
        return redirect()->route('penomoran-uar.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    // public function show($id)
    // {
    //     $penomoranUAR = PenomoranUAR::findOrFail($id);
    //     return view('master-data.penomoran_uar.show', compact('penomoranUAR'));
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $penomoranUAR = PenomoranUAR::findOrFail($id);
        // dd($penomoranUAR->toArray());
        $masterData = json_decode(Storage::disk('public')->get('master_data.json'), true);
        $companies = $masterData;
        $selectedCompany = $penomoranUAR->company_id ?? null;
        $selectedKompartemen = $penomoranUAR->kompartemen->kompartemen_id ?? null;
        $selectedDepartemen = $penomoranUAR->departemen->departemen_id ?? null;
        return view('master-data.penomoran_uar.edit', compact('penomoranUAR', 'masterData', 'companies', 'selectedCompany', 'selectedKompartemen', 'selectedDepartemen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $penomoranUAR = PenomoranUAR::findOrFail($id);

        $kompartemenId = $request->input('kompartemen_id');
        $departemenId = $request->input('departemen_id');
        $unitKerjaId = $departemenId ?? $kompartemenId;
        $request->merge(['unit_kerja_id' => $unitKerjaId]);

        $request->validate([
            'company_id' => 'required|string',
            'kompartemen_id' => 'required|string',
            'departemen_id' => 'nullable|string',
            'unit_kerja_id' => 'required|string',
            'number' => 'required|integer',
        ]);

        $penomoranUAR->update($request->all());
        return redirect()->route('penomoran-uar.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $penomoranUAR = PenomoranUAR::findOrFail($id);
        $penomoranUAR->number = null; // Set number to null before deletion
        $penomoranUAR->updated_by = auth()->user()->nama; // Set updated_by to the current user
        $penomoranUAR->deleted_by = auth()->user()->nama; // Set deleted_by to the current user
        $penomoranUAR->save(); // Save the change to nullify the number
        $penomoranUAR->delete();
        return redirect()->route('penomoran-uar.index')->with('success', 'Record deleted successfully.');
    }

    // OTHER FUNCTIONS

    // AJAX uniqueness check
    public function checkNumber(Request $request)
    {
        $exists = PenomoranUAR::where('number', $request->number)->exists();
        return response()->json(['exists' => $exists]);
    }
}
