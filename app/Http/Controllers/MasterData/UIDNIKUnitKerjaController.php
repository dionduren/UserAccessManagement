<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Periode;
use App\Models\Company;
use App\Models\userNIK;
use App\Models\UserNIKUnitKerja;
use App\Exports\UserNIKWithoutUnitKerjaExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UIDNIKUnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        if ($request->wantsJson()) {
            $periodeId = (int) $request->get('periode_id');
            if (!$periodeId) {
                return response()->json(['data' => []]);
            }

            $query = UserNIKUnitKerja::with([
                'kompartemen',
                'departemen',
                'company',
                // FIXED: Apply periode filter in the eager loading closure
                'userNIK' => function ($q) use ($periodeId) {
                    $q->where('tr_user_ussm_nik.periode_id', $periodeId)
                        ->whereNull('tr_user_ussm_nik.deleted_at');
                }
            ])
                ->where('periode_id', $periodeId);

            // Filter by company unless A000
            if ($userCompany && $userCompany !== 'A000') {
                $query->where('company_id', $userCompany);
            }

            $rows = $query->latest('periode_id')->get()->map(function ($item) {
                return array_merge($item->toArray(), [
                    'company_nama' => data_get($item, 'company.nama'),
                    'user_group' => data_get($item, 'userNIK.group', '-'),
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
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);

        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companies = Company::select('company_code', 'nama')
            ->when($userCompany && $userCompany !== 'A000', fn($q) => $q->where('company_code', $userCompany))
            ->orderBy('company_code')
            ->get();

        return view('unit-kerja.user-nik.create', compact('periodes', 'companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ms_nik_unit_kerja', 'nik')
                    ->where(fn($q) => $q->where('periode_id', $request->input('periode_id'))
                        ->whereNull('deleted_at')),
            ],
            'company_id' => ['nullable', 'string', 'max:255'],
            'direktorat_id' => ['nullable', 'string', 'max:255'],
            'kompartemen_id' => ['nullable', 'string', 'max:255'],
            'departemen_id' => ['nullable', 'string', 'max:255'],
            'atasan' => ['nullable', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:255'],
        ]);

        $data['flagged'] = (bool)($data['flagged'] ?? false);
        $data['created_by'] = optional(auth()->user())->name;

        $row = UserNIKUnitKerja::create($data);

        return redirect()->route('unit_kerja.user_nik.index')->with('success', 'Created: ID ' . $row->id);
    }

    public function show(UserNIKUnitKerja $userNIKUnitKerja)
    {
        return view('unit-kerja.user-nik.show', compact('userNIKUnitKerja'));
    }

    public function edit(UserNIKUnitKerja $userNIKUnitKerja)
    {
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);

        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companies = Company::select('company_code', 'nama')
            ->when($userCompany && $userCompany !== 'A000', fn($q) => $q->where('company_code', $userCompany))
            ->orderBy('company_code')
            ->get();

        $selectedCompany = $userNIKUnitKerja->company_id;

        return view('unit-kerja.user-nik.edit', compact(
            'userNIKUnitKerja',
            'periodes',
            'companies',
            'selectedCompany'
        ));
    }

    public function update(Request $request, UserNIKUnitKerja $userNIKUnitKerja)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ms_nik_unit_kerja', 'nik')
                    ->ignore($userNIKUnitKerja->id, 'id') // <-- ignore this row
                    ->where(fn($q) => $q->where('periode_id', $request->input('periode_id'))
                        ->whereNull('deleted_at')),
            ],
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

        return redirect()->route('unit_kerja.user_nik.index')->with('success', 'Updated: ID ' . $userNIKUnitKerja->id);
    }

    public function destroy(UserNIKUnitKerja $userNIKUnitKerja)
    {
        $userNIKUnitKerja->deleted_by = optional(auth()->user())->name;
        $userNIKUnitKerja->save();
        $userNIKUnitKerja->delete();

        return response()->json(['message' => 'Soft deleted'], 200);
    }

    public function withoutUnitKerja(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        if ($request->wantsJson()) {
            $periodeId = (int) $request->get('periode_id');
            if (!$periodeId) {
                return response()->json(['data' => []]);
            }

            $query = userNIK::query()
                ->with(['Company'])
                ->select([
                    'tr_user_ussm_nik.id',
                    'tr_user_ussm_nik.group',
                    'tr_user_ussm_nik.user_code',
                    'tr_user_ussm_nik.last_login',
                    'tr_user_ussm_nik.valid_from',
                    'tr_user_ussm_nik.valid_to',
                ])
                ->where('tr_user_ussm_nik.periode_id', $periodeId)
                ->whereNull('tr_user_ussm_nik.deleted_at')
                ->whereNotExists(function ($q) use ($periodeId) {
                    $q->selectRaw('1')
                        ->from('ms_nik_unit_kerja as uk')
                        ->whereColumn('uk.nik', 'tr_user_ussm_nik.user_code')
                        ->where('uk.periode_id', $periodeId)
                        ->whereNull('uk.deleted_at');
                });

            // Filter by company unless A000
            if ($userCompany && $userCompany !== 'A000') {
                $query->whereHas('Company', function ($q) use ($userCompany) {
                    $q->where('company_code', $userCompany);
                });
            }

            $rows = $query->latest('tr_user_ussm_nik.id')->get()->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'company'    => $item->Company->nama ?? $item->group ?? '-',
                    'group'      => $item->group,
                    'user_code'  => $item->user_code,
                    'last_login' => $item->last_login,
                    'valid_from' => $item->valid_from,
                    'valid_to'   => $item->valid_to,
                ];
            });

            return response()->json(['data' => $rows]);
        }

        $periodes = Periode::orderByDesc('id')->get();
        return view('unit-kerja.user-nik.without', compact('periodes'));
    }

    public function exportWithoutUnitKerja(Request $request)
    {
        $periodeId = (int) $request->get('periode_id');

        if (!$periodeId) {
            return redirect()->back()->with('error', 'Periode harus dipilih untuk export');
        }

        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        // Get periode name for filename
        $periode = Periode::find($periodeId);
        $periodeName = $periode ? $periode->definisi : 'Unknown';

        $filename = 'User_NIK_Without_Unit_Kerja_' . $periodeName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new UserNIKWithoutUnitKerjaExport($periodeId, $userCompany),
            $filename
        );
    }
}
