<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Periode;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\userGeneric;
use App\Models\UserGenericUnitKerja;

use App\Exports\UserGenericWithoutUnitKerjaExport;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UIDGenericUnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code;

        if ($request->wantsJson()) {
            $periodeId = (int) $request->get('periode_id');
            if (!$periodeId) {
                return response()->json(['data' => []]);
            }

            $query = UserGenericUnitKerja::with(['userGeneric', 'kompartemen', 'departemen'])
                ->where('periode_id', $periodeId);

            // Filter by company unless A000
            if ($userCompany !== 'A000') {
                $query->whereHas('userGeneric.Company', function ($q) use ($userCompany) {
                    $q->where('company_code', $userCompany);
                });
            }

            $rows = $query->latest('periode_id')->get()->map(function ($item) {
                return array_merge($item->toArray(), [
                    'company' => data_get($item, 'userGeneric.Company.company_code'),
                    'nama' => data_get($item, 'userGeneric.user_profile'),
                    'kompartemen_nama' => data_get($item, 'kompartemen.nama'),
                    'departemen_nama' => data_get($item, 'departemen.nama'),
                ]);
            });

            return response()->json([
                'data' => $rows,
            ]);
        }

        $periodes = Periode::orderByDesc('id')->get();
        return view('unit-kerja.user-generic.index', compact('periodes'));
    }

    public function create()
    {
        // Pass periodes and companies for Select2
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);

        $userCompany = auth()->user()->loginDetail->company_code;
        if ($userCompany !== 'A000') {
            $companies = Company::select('company_code', 'nama')->where('company_code', $userCompany)->get();
        } else {
            $companies = Company::select('company_code', 'nama')->get();
        }

        return view('unit-kerja.user-generic.create', compact('periodes', 'companies'));
    }

    public function store(Request $request)
    {
        // Validate only fields that the form actually submits
        $data = $request->validate([
            'periode_id'     => ['required', 'integer', 'exists:ms_periode,id'],
            'user_cc'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('ms_generic_unit_kerja', 'user_cc')
                    ->where(function ($q) use ($request) {
                        return $q->where('periode_id', $request->input('periode_id'))
                            ->whereNull('deleted_at');
                    }),
            ],
            'kompartemen_id' => ['nullable', 'string', 'max:255'],
            'departemen_id'  => ['nullable', 'string', 'max:255'],
        ]);

        $row = UserGenericUnitKerja::create($data);

        return redirect()->route('unit_kerja.user_generic.index')->with('success', 'Created: ID ' . $row->id);
    }

    // public function show(UserGenericUnitKerja $userGenericUnitKerja)
    // {
    //     return view('unit-kerja.user-generic.show', compact('userGenericUnitKerja'));
    // }

    public function edit(UserGenericUnitKerja $userGenericUnitKerja)
    {
        // Provide same dropdown sources as in create
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);

        $userCompany = auth()->user()->loginDetail->company_code;
        if ($userCompany !== 'A000') {
            $companies = Company::select('company_code', 'nama')->where('company_code', $userCompany)->get();
        } else {
            $companies = Company::select('company_code', 'nama')->get();
        }
        // Infer the user's company_code for initial cascade selection in the view
        $selectedCompany = data_get($userGenericUnitKerja, 'userGeneric.Company.company_code');

        return view(
            'unit-kerja.user-generic.edit',
            compact('userGenericUnitKerja', 'periodes', 'companies', 'selectedCompany')
        );
    }

    public function update(Request $request, UserGenericUnitKerja $userGenericUnitKerja)
    {
        $data = $request->validate([
            'periode_id'     => ['required', 'integer', 'exists:ms_periode,id'],
            'user_cc'        => [
                'required',
                'string',
                'max:255',
                // // Keep unique across table, ignore this record, still exclude soft-deleted rows
                // Rule::unique('ms_generic_unit_kerja', 'user_cc')
                //     ->ignore($userGenericUnitKerja->id)
                //     ->whereNull('deleted_at'),
                Rule::unique('ms_generic_unit_kerja', 'user_cc')
                    ->where(function ($q) use ($request) {
                        return $q->where('periode_id', $request->input('periode_id'))
                            ->whereNull('deleted_at');
                    }),
            ],
            'kompartemen_id' => ['nullable', 'string', 'max:255'],
            'departemen_id'  => ['nullable', 'string', 'max:255'],
        ]);

        $userGenericUnitKerja->update($data);

        return redirect()->route('unit_kerja.user_generic.index')->with('success', 'Updated: ID ' . $userGenericUnitKerja->id);
    }

    public function destroy(UserGenericUnitKerja $userGenericUnitKerja)
    {
        $userGenericUnitKerja->delete();

        return response()->json(['message' => 'Soft deleted'], 200);
    }

    // Updated: return kompartemen and departemen from database models
    public function companyStructure(Request $request)
    {
        $companyCode = $request->query('company');
        if (!$companyCode) {
            return response()->json(['message' => 'company is required'], 422);
        }

        // Get kompartemen for this company
        $kompartemenList = Kompartemen::where('company_id', $companyCode)
            ->orderBy('nama')
            ->get(['kompartemen_id', 'nama']);

        $kompartemen = $kompartemenList->map(function ($k) {
            return [
                'id' => $k->kompartemen_id,
                'text' => $k->nama,
            ];
        });

        // Get departemen grouped by kompartemen
        $departemenByKompartemen = [];
        foreach ($kompartemenList as $komp) {
            $depts = Departemen::where('kompartemen_id', $komp->kompartemen_id)
                ->orderBy('nama')
                ->get(['departemen_id', 'nama']);

            $departemenByKompartemen[$komp->kompartemen_id] = $depts->map(function ($d) {
                return [
                    'id' => $d->departemen_id,
                    'text' => $d->nama,
                ];
            })->toArray();
        }

        // Get departemen without kompartemen (kompartemen_id is null)
        $departemenWo = Departemen::where('company_id', $companyCode)
            ->whereNull('kompartemen_id')
            ->orderBy('nama')
            ->get(['departemen_id', 'nama'])
            ->map(function ($d) {
                return [
                    'id' => $d->departemen_id,
                    'text' => $d->nama,
                ];
            });

        return response()->json([
            'kompartemen' => $kompartemen,
            'departemen_by_kompartemen' => $departemenByKompartemen,
            'departemen_wo' => $departemenWo,
        ]);
    }

    // Updated: Select2 search filtered by company and periode
    public function without(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code;

        if ($request->wantsJson()) {
            $periodeId = (int) $request->get('periode_id');
            if (!$periodeId) {
                return response()->json(['data' => []]);
            }

            $query = userGeneric::query()
                ->with('Company')
                ->where('periode_id', $periodeId)
                ->whereNull('deleted_at')
                ->whereNotExists(function ($q) use ($periodeId) {
                    $q->selectRaw('1')
                        ->from('ms_generic_unit_kerja as guk')
                        ->whereColumn('guk.user_cc', 'tr_user_generic.user_code')
                        ->where('guk.periode_id', $periodeId)
                        ->whereNull('guk.deleted_at');
                });

            // Filter by company unless A000
            if ($userCompany !== 'A000') {
                $query->whereHas('Company', function ($q) use ($userCompany) {
                    $q->where('company_code', $userCompany);
                });
            }

            $rows = $query->latest('id')->get()->map(function ($u) {
                return [
                    'company'    => optional($u->Company)->company_code ?? '-',
                    // 'company'    => optional($u->Company)->company_code ?? $u->group ?? '-',
                    'user_code'  => $u->user_code,
                    'nama'       => $u->user_profile,
                    'last_login' => $u->last_login,
                    'valid_from' => $u->valid_from,
                    'valid_to'   => $u->valid_to,
                ];
            });

            return response()->json(['data' => $rows]);
        }

        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);
        return view('unit-kerja.user-generic.without', compact('periodes'));
    }

    public function exportWithout(Request $request)
    {
        $periodeId = (int) $request->get('periode_id');

        if (!$periodeId) {
            return redirect()->back()->with('error', 'Periode harus dipilih untuk export');
        }

        $userCompany = auth()->user()->loginDetail->company_code;

        // Get periode name for filename
        $periode = \App\Models\Periode::find($periodeId);
        $periodeName = $periode ? $periode->definisi : 'Unknown';

        $filename = 'User_Generic_Without_Unit_Kerja_' . $periodeName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new UserGenericWithoutUnitKerjaExport($periodeId, $userCompany),
            $filename
        );
    }

    // Add this method for Select2 user search
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $company = $request->query('company');
        $periode = $request->query('periode_id');
        $mode = $request->query('mode', 'create'); // 'create' or 'edit'
        $editingUserId = $request->query('editing_user_id'); // for edit mode

        $query = userGeneric::query()->with('Company');

        // Filter by periode first (required)
        if ($periode) {
            $query->where('periode_id', $periode);
        } else {
            // No periode selected, return empty
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        // Filter by company if provided
        if ($company) {
            $query->whereHas('Company', function ($qq) use ($company) {
                $qq->where('company_code', $company);
            });
        }

        // Search by user_code or user_profile
        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('user_code', 'like', '%' . $q . '%')
                    ->orWhere('user_profile', 'like', '%' . $q . '%');
            });
        }

        // Different exclusion logic based on mode
        if ($mode === 'create') {
            // CREATE: Exclude users already assigned to unit kerja
            $query->whereNotIn('user_code', function ($sub) use ($periode) {
                $sub->select('user_cc')
                    ->from('ms_generic_unit_kerja')
                    ->where('periode_id', $periode)
                    ->whereNull('deleted_at');
            });
        } elseif ($mode === 'edit' && $editingUserId) {
            // EDIT: Exclude users already assigned to unit kerja EXCEPT the one being edited
            $query->whereNotIn('user_code', function ($sub) use ($periode, $editingUserId) {
                $sub->select('user_cc')
                    ->from('ms_generic_unit_kerja')
                    ->where('periode_id', $periode)
                    ->where('user_cc', '!=', $editingUserId)
                    ->whereNull('deleted_at');
            });
        }
        // If mode is 'edit' but no editingUserId, show all users (fallback)

        $results = $query->limit(20)->get()->map(function ($u) {
            $code = $u->user_code ?? '';
            $name = $u->user_profile ?? '[tanpa nama]';
            return [
                'id' => $code,
                'text' => trim($code . ' - ' . $name),
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false],
        ]);
    }
}
