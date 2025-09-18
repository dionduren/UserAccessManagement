<?php

namespace App\Http\Controllers\MasterData;

use \App\Models\UserGenericUnitKerja;
use App\Http\Controllers\Controller;
use App\Models\Periode;
use App\Models\UserNIKUnitKerja;
use Illuminate\Http\Request;

class UIDNIKUnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $periodeId = (int) $request->get('periode_id');
            if (!$periodeId) {
                return response()->json(['data' => []]);
            }

            $query = UserNIKUnitKerja::with(['kompartemen', 'departemen'])
                ->where('periode_id', $periodeId);

            $rows = $query->latest('periode_id')->get()->map(function ($item) {
                return array_merge($item->toArray(), [
                    'kompartemen_nama' => data_get($item, 'kompartemen.nama'),
                    'departemen_nama' => data_get($item, 'departemen.nama'),
                ]);
            });

            return response()->json([
                'data' => $rows,
            ]);
        }

        $periodes = Periode::orderByDesc('id')->get();
        return view('unit-kerja.user-nik.index', compact('periodes'));
    }

    public function create()
    {
        return view('unit-kerja.user-nik.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'string', 'max:255'],
            'direktorat_id' => ['nullable', 'string', 'max:255'],
            'kompartemen_id' => ['nullable', 'string', 'max:255'],
            'departemen_id' => ['nullable', 'string', 'max:255'],
            'atasan' => ['nullable', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:255'],
            'error_kompartemen_id' => ['nullable', 'string', 'max:255'],
            'error_kompartemen_name' => ['nullable', 'string', 'max:255'],
            'error_departemen_id' => ['nullable', 'string', 'max:255'],
            'error_departemen_name' => ['nullable', 'string', 'max:255'],
            'flagged' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $data['flagged'] = (bool)($data['flagged'] ?? false);
        $data['created_by'] = optional(auth()->user())->name;

        $row = UserNIKUnitKerja::create($data);

        return redirect()->route('unit-kerja.user-nik.index')->with('success', 'Created: ID ' . $row->id);
    }

    public function show(UserNIKUnitKerja $userNIKUnitKerja)
    {
        return view('unit-kerja.user-nik.show', compact('userNIKUnitKerja'));
    }

    public function edit(UserNIKUnitKerja $userNIKUnitKerja)
    {
        return view('unit-kerja.user-nik.edit', compact('userNIKUnitKerja'));
    }

    public function update(Request $request, UserNIKUnitKerja $userNIKUnitKerja)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'string', 'max:255'],
            'direktorat_id' => ['nullable', 'string', 'max:255'],
            'kompartemen_id' => ['nullable', 'string', 'max:255'],
            'departemen_id' => ['nullable', 'string', 'max:255'],
            'atasan' => ['nullable', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:255'],
            'error_kompartemen_id' => ['nullable', 'string', 'max:255'],
            'error_kompartemen_name' => ['nullable', 'string', 'max:255'],
            'error_departemen_id' => ['nullable', 'string', 'max:255'],
            'error_departemen_name' => ['nullable', 'string', 'max:255'],
            'flagged' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $data['flagged'] = (bool)($data['flagged'] ?? false);
        $data['updated_by'] = optional(auth()->user())->name;

        $userNIKUnitKerja->update($data);

        return redirect()->route('unit-kerja.user-nik.index')->with('success', 'Updated: ID ' . $userNIKUnitKerja->id);
    }

    public function destroy(UserNIKUnitKerja $userNIKUnitKerja)
    {
        $userNIKUnitKerja->deleted_by = optional(auth()->user())->name;
        $userNIKUnitKerja->save();
        $userNIKUnitKerja->delete();

        return response()->json(['message' => 'Soft deleted'], 200);
    }
}
