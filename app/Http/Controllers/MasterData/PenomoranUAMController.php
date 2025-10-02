<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\PenomoranUAM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PenomoranUAMController extends Controller
{
    public function index(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        if ($request->ajax()) {
            $data = PenomoranUAM::with(['kompartemen', 'departemen'])
                ->select('ms_penomoran_uam.*')
                ->when(
                    $userCompany && $userCompany !== 'A000',
                    fn($q) => $q->where('company_id', $userCompany)
                );

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

        $companySet = $userCompany && $userCompany === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompany)->get();

        return view('master-data.penomoran_uam.index', compact('companySet'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        $companySet = $userCompany && $userCompany === 'A000'
            ? Company::orderBy('nama')->get()
            : Company::where('company_code', $userCompany)->orderBy('nama')->get();

        $organizationData = Company::query()
            ->when($userCompany && $userCompany !== 'A000', fn($q) => $q->where('company_code', $userCompany))
            ->with([
                'kompartemen' => fn($q) => $q->select('kompartemen_id', 'company_id', 'nama')->orderBy('nama'),
                'kompartemen.departemen' => fn($q) => $q->select('departemen_id', 'kompartemen_id', 'company_id', 'nama')->orderBy('nama'),
                'departemen' => fn($q) => $q->whereNull('kompartemen_id')->select('departemen_id', 'company_id', 'nama')->orderBy('nama'),
            ])
            ->orderBy('nama')
            ->get(['company_code', 'nama'])
            ->map(fn(Company $company) => [
                'company_code' => $company->company_code,
                'nama' => $company->nama,
                'kompartemen' => $company->kompartemen->map(fn($kom) => [
                    'kompartemen_id' => $kom->kompartemen_id,
                    'nama' => $kom->nama,
                    'departemen' => $kom->departemen->map(fn($dep) => [
                        'departemen_id' => $dep->departemen_id,
                        'nama' => $dep->nama,
                    ])->values(),
                ])->values(),
                'departemen_without_kompartemen' => $company->departemen->map(fn($dep) => [
                    'departemen_id' => $dep->departemen_id,
                    'nama' => $dep->nama,
                ])->values(),
            ])->values();

        return view('master-data.penomoran_uam.create', compact('organizationData', 'companySet'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kompartemenId = $request->input('kompartemen_id') ?: null;
        $departemenId = $request->input('departemen_id') ?: null;
        $unitKerjaId = $departemenId ?? $kompartemenId;

        $request->merge([
            'kompartemen_id' => $kompartemenId,
            'departemen_id' => $departemenId,
            'unit_kerja_id' => $unitKerjaId,
        ]);

        $request->validate([
            'company_id' => 'required|string',
            'kompartemen_id' => 'nullable|string',
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
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        $companySet = $userCompany && $userCompany === 'A000'
            ? Company::orderBy('nama')->get()
            : Company::where('company_code', $userCompany)->orderBy('nama')->get();

        $organizationData = Company::query()
            ->when($userCompany && $userCompany !== 'A000', fn($q) => $q->where('company_code', $userCompany))
            ->with([
                'kompartemen' => fn($q) => $q->select('kompartemen_id', 'company_id', 'nama')->orderBy('nama'),
                'kompartemen.departemen' => fn($q) => $q->select('departemen_id', 'kompartemen_id', 'company_id', 'nama')->orderBy('nama'),
                'departemen' => fn($q) => $q->whereNull('kompartemen_id')->select('departemen_id', 'company_id', 'nama')->orderBy('nama'),
            ])
            ->orderBy('nama')
            ->get(['company_code', 'nama'])
            ->map(fn(Company $company) => [
                'company_code' => $company->company_code,
                'nama' => $company->nama,
                'kompartemen' => $company->kompartemen->map(fn($kom) => [
                    'kompartemen_id' => $kom->kompartemen_id,
                    'nama' => $kom->nama,
                    'departemen' => $kom->departemen->map(fn($dep) => [
                        'departemen_id' => $dep->departemen_id,
                        'nama' => $dep->nama,
                    ])->values(),
                ])->values(),
                'departemen_without_kompartemen' => $company->departemen->map(fn($dep) => [
                    'departemen_id' => $dep->departemen_id,
                    'nama' => $dep->nama,
                ])->values(),
            ])->values();

        $selectedCompany = $penomoranUAM->company_id ?? null;
        $selectedKompartemen = $penomoranUAM->kompartemen_id ?? null;
        $selectedDepartemen = $penomoranUAM->departemen_id ?? null;

        return view('master-data.penomoran_uam.edit', compact(
            'penomoranUAM',
            'organizationData',
            'companySet',
            'selectedCompany',
            'selectedKompartemen',
            'selectedDepartemen'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $penomoranUAM = PenomoranUAM::findOrFail($id);

        $kompartemenId = $request->input('kompartemen_id') ?: null;
        $departemenId = $request->input('departemen_id') ?: null;
        $unitKerjaId = $departemenId ?? $kompartemenId;

        $request->merge([
            'kompartemen_id' => $kompartemenId,
            'departemen_id' => $departemenId,
            'unit_kerja_id' => $unitKerjaId,
        ]);

        $request->validate([
            'company_id' => 'required|string',
            'kompartemen_id' => 'nullable|string',
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
        $companyId = $request->input('company_id');
        $unitKerjaInfo = '';

        $exists = PenomoranUAM::where('number', $request->number)
            ->where('company_id', $companyId)
            ->exists();

        if ($exists) {
            $unitKerjaId = PenomoranUAM::where('number', $request->number)
                ->where('company_id', $companyId)
                ->first()
                ->unit_kerja_id;
            $unitKerjaInfo = Kompartemen::where('kompartemen_id', $unitKerjaId)->first()?->nama ?? Departemen::where('departemen_id', $unitKerjaId)->first()?->nama ?? 'N/A';
        }

        return response()->json(['exists' => $exists, 'unit_kerja_id' => $unitKerjaInfo]);
    }
}
