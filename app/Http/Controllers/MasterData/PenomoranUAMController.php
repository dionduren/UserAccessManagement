<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\PenomoranUAM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PenomoranUAMController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PenomoranUAM::with(['kompartemen', 'departemen'])->select('ms_penomoran_uam.*');
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
                    // $show = route('penomoran-uam.show', $row->id);
                    $edit = route('penomoran-uam.edit', $row->id);
                    $delete = route('penomoran-uam.destroy', $row->id);
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
        return view('master-data.penomoran_uam.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $masterData = json_decode(Storage::disk('public')->get('master_data.json'), true);
        $companies = $masterData;
        return view('master-data.penomoran_uam.create', compact('masterData', 'companies'));
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

        PenomoranUAM::create($request->all());
        return redirect()->route('penomoran-uam.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    // public function show($id)
    // {
    //     $penomoranUAM = PenomoranUAM::findOrFail($id);
    //     return view('master-data.penomoran_uam.show', compact('penomoranUAM'));
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $penomoranUAM = PenomoranUAM::findOrFail($id);
        // dd($penomoranUAM->toArray());
        $masterData = json_decode(Storage::disk('public')->get('master_data.json'), true);
        $companies = $masterData;
        $selectedCompany = $penomoranUAM->company_id ?? null;
        $selectedKompartemen = $penomoranUAM->kompartemen->kompartemen_id ?? null;
        $selectedDepartemen = $penomoranUAM->departemen->departemen_id ?? null;
        return view('master-data.penomoran_uam.edit', compact('penomoranUAM', 'masterData', 'companies', 'selectedCompany', 'selectedKompartemen', 'selectedDepartemen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $penomoranUAM = PenomoranUAM::findOrFail($id);

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

        $penomoranUAM->update($request->all());
        return redirect()->route('penomoran-uam.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $penomoranUAM = PenomoranUAM::findOrFail($id);
        $penomoranUAM->number = null; // Set number to null before deletion
        $penomoranUAM->updated_by = auth()->user()->nama; // Set updated_by to the current user
        $penomoranUAM->deleted_by = auth()->user()->nama; // Set deleted_by to the current user
        $penomoranUAM->save(); // Save the change to nullify the number
        $penomoranUAM->delete();
        return redirect()->route('penomoran-uam.index')->with('success', 'Record deleted successfully.');
    }

    // OTHER FUNCTIONS

    // AJAX uniqueness check
    public function checkNumber(Request $request)
    {
        $exists = PenomoranUAM::where('number', $request->number)->exists();
        return response()->json(['exists' => $exists]);
    }
}
