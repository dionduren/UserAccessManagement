<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\UserGenericUnitKerja;
use Illuminate\Http\Request;
use App\Models\Periode;
use App\Models\Company;
use App\Models\userGeneric;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class UIDGenericUnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $periodeId = (int) $request->get('periode_id');
            if (!$periodeId) {
                return response()->json(['data' => []]);
            }

            $query = UserGenericUnitKerja::with(['userGeneric', 'kompartemen', 'departemen'])
                ->where('periode_id', $periodeId);

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
        $companies = Company::orderBy('company_code')->get(['company_code', 'nama']);

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
                // Exclude existing (not soft-deleted) user_cc across the table
                Rule::unique('ms_generic_unit_kerja', 'user_cc')->whereNull('deleted_at'),
            ],
            'kompartemen_id' => ['nullable', 'string', 'max:255'],
            'departemen_id'  => ['nullable', 'string', 'max:255'],
        ]);

        $row = UserGenericUnitKerja::create($data);

        return redirect()->route('unit_kerja.user_generic.index')->with('success', 'Created: ID ' . $row->id);
    }

    public function show(UserGenericUnitKerja $userGenericUnitKerja)
    {
        return view('unit-kerja.user-generic.show', compact('userGenericUnitKerja'));
    }

    public function edit(UserGenericUnitKerja $userGenericUnitKerja)
    {
        // Provide same dropdown sources as in create
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);
        $companies = Company::orderBy('company_code')->get(['company_code', 'nama']);
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
                // Keep unique across table, ignore this record, still exclude soft-deleted rows
                Rule::unique('ms_generic_unit_kerja', 'user_cc')
                    ->ignore($userGenericUnitKerja->id)
                    ->whereNull('deleted_at'),
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

    // New: return kompartemen and departemen lists from master_data.json by company_code
    public function companyStructure(Request $request)
    {
        $companyCode = $request->query('company');
        if (!$companyCode) {
            return response()->json(['message' => 'company is required'], 422);
        }

        $path = public_path('storage/master_data.json');
        if (!File::exists($path)) {
            return response()->json([
                'kompartemen' => [],
                'departemen_by_kompartemen' => [],
                'departemen_wo' => [],
            ]);
        }

        $json = json_decode(File::get($path), true) ?: [];
        $company = collect($json)->firstWhere('company_id', $companyCode);

        if (!$company) {
            return response()->json([
                'kompartemen' => [],
                'departemen_by_kompartemen' => [],
                'departemen_wo' => [],
            ]);
        }

        // Build kompartemen and its departemen (sorted)
        $komps = collect($company['kompartemen'] ?? [])
            ->map(function ($k) {
                return [
                    'id' => $k['kompartemen_id'] ?? null,
                    'text' => $k['nama'] ?? ($k['kompartemen_id'] ?? ''),
                    'departemen' => collect($k['departemen'] ?? [])
                        ->filter(fn($d) => isset($d['departemen_id']) || isset($d['nama']))
                        ->map(function ($d) {
                            return [
                                'id' => $d['departemen_id'] ?? ($d['nama'] ?? ''),
                                'text' => $d['nama'] ?? ($d['departemen_id'] ?? ''),
                            ];
                        })
                        ->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn($k) => !empty($k['id']) || !empty($k['text']))
            ->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $departemenByKomps = [];
        foreach ($komps as $k) {
            $departemenByKomps[$k['id']] = $k['departemen'];
        }

        // Build departemen_without_kompartemen (sorted)
        $depWo = collect($company['departemen_without_kompartemen'] ?? [])
            ->filter(fn($d) => isset($d['departemen_id']) || isset($d['nama']))
            ->map(function ($d) {
                return [
                    'id' => $d['departemen_id'] ?? ($d['nama'] ?? ''),
                    'text' => $d['nama'] ?? ($d['departemen_id'] ?? ''),
                ];
            })
            ->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return response()->json([
            'kompartemen' => $komps->map(fn($k) => ['id' => $k['id'], 'text' => $k['text']])->all(),
            'departemen_by_kompartemen' => $departemenByKomps,
            'departemen_wo' => $depWo,
        ]);
    }

    // New: Select2 search for userGeneric not yet in ms_generic_unit_kerja, filtered by company
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $company = $request->query('company');

        $query = userGeneric::query()->with('Company');

        if ($company) {
            $query->whereHas('Company', function ($qq) use ($company) {
                $qq->where('company_code', $company);
            });
        }

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('user_code', 'like', '%' . $q . '%')
                    ->orWhere('user_profile', 'like', '%' . $q . '%');
            });
        }

        $query->whereNotIn('user_code', function ($sub) {
            $sub->select('user_cc')
                ->from('ms_generic_unit_kerja')
                ->whereNull('deleted_at');
        });

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
