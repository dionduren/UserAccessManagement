<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\SingleRole;

use Yajra\DataTables\Facades\DataTables;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SingleTcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $companies = $userCompanyCode === 'A000'
            ? Company::orderBy('nama')->get()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('relationship.single-tcode.index', [
            'companies'       => $companies,
            'userCompanyCode' => $userCompanyCode,
            'selectedCompany' => $request->get('company_id')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        $singleRoles = SingleRole::all();
        $tcodes = Tcode::all();

        return view('relationship.single-tcode.create', compact('companies', 'singleRoles', 'tcodes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'single_role_id' => 'required|exists:tr_single_roles,id',
            'tcode_id' => 'required|array|exists:tr_tcodes,code',
        ]);

        $singleRole = SingleRole::findOrFail($validatedData['single_role_id']);
        $singleRole->tcodes()->syncWithoutDetaching($validatedData['tcode_id']);

        $singleRole->update([
            'created_by' => auth()->user()->name ?? null
        ]);

        return redirect()->route('single-tcode.index')->with('success', 'Relationship created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singleTcode = Tcode::with('singleRoles')->findOrFail($id);

        return view('relationship.single-tcode.show', compact('singleTcode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) // $id = single_role_id
    {
        $singleRole = SingleRole::with('tcodes')->findOrFail($id);

        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        if ($userCompanyCode !== 'A000' && $singleRole->company_id !== $userCompanyCode) {
            return redirect()
                ->route('single-tcode.index')
                ->withErrors(['error' => 'You are not authorized to edit this single role.']);
        }

        $companies = $userCompanyCode === 'A000'
            ? Company::with('singleRoles')->get()
            : Company::with('singleRoles')->where('company_code', $userCompanyCode)->get();

        $tcodes = Tcode::all();
        $selectedTcodes = $singleRole->tcodes->pluck('code')->toArray();

        return view('relationship.single-tcode.edit', compact(
            'singleRole',
            'companies',
            'tcodes',
            'selectedTcodes'
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'tcode_id' => 'array',
            'tcode_id.*' => 'exists:tr_tcodes,code',
        ]);

        $singleRole = SingleRole::findOrFail($id);
        $tcodeIds = $request->input('tcode_id', []); // 💥 THIS IS THE KEY

        $singleRole->tcodes()->sync($tcodeIds);

        $singleRole->update([
            'updated_by' => auth()->user()->name ?? null
        ]);

        return redirect()->route('single-tcode.index')->with('success', 'Relationship updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table('pt_single_role_tcode')
            ->where('single_role_id', $id)
            ->delete();

        return back()->with('success', 'All Tcodes removed from this Single Role.');
    }



    /**
     * Return the single tcodes in json format
     */
    public function jsonIndex(Request $request)
    {
        $query = DB::table('pt_single_role_tcode')
            ->join('tr_single_roles', 'pt_single_role_tcode.single_role_id', '=', 'tr_single_roles.id')
            ->join('tr_tcodes', 'pt_single_role_tcode.tcode_id', '=', 'tr_tcodes.code')
            ->select(
                'pt_single_role_tcode.single_role_id',
                'tr_single_roles.nama as single_role_name',
                DB::raw("STRING_AGG(tr_tcodes.code, '||' ORDER BY tr_tcodes.code) as tcode_codes")
            )
            ->groupBy('pt_single_role_tcode.single_role_id', 'tr_single_roles.nama');

        return DataTables::of($query)
            ->addColumn('tcodes', function ($row) {
                $codes = explode('||', $row->tcode_codes);
                $html = '<ul class="mb-0 pl-3">';
                foreach ($codes as $code) {
                    $html .= "<li>{$code}</li>";
                }
                $html .= '</ul>';
                return $html;
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('single-tcode.edit', $row->single_role_id);
                $deleteUrl = route('single-tcode.destroy', $row->single_role_id);

                return view('relationship.single-tcode.components.actions', compact('editUrl', 'deleteUrl'))->render();
            })
            ->rawColumns(['tcodes', 'action'])
            ->toJson();
    }


    /**
     * Search the tcodes and single roles based on the company id
     */
    public function searchByCompany(Request $request)
    {
        $companyId = $request->input('company_id');
        $tcodes = Tcode::whereHas('singleRoles', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->get();
        $singleRoles = SingleRole::where('company_id', $companyId)->get();

        return response()->json(['tcodes' => $tcodes, 'singleRoles' => $singleRoles]);
    }

    // DataTables JSON – paginate by single roles (NOT by each tcode row)
    public function datatable(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $draw   = (int)$request->input('draw');
        $length = (int)$request->input('length', 10);  // single roles per page
        $start  = (int)$request->input('start', 0);
        $search = $request->input('search.value');

        $base = SingleRole::query()
            ->leftJoin('ms_company', 'ms_company.company_code', '=', 'tr_single_roles.company_id')
            ->select('tr_single_roles.*', 'ms_company.nama as company_name')
            ->with(['tcodes' => function ($q) {
                $q->orderBy('code');
            }])
            ->orderBy('tr_single_roles.company_id');

        if ($userCompanyCode !== 'A000') {
            $base->where('tr_single_roles.company_id', $userCompanyCode);
        } elseif ($request->filled('company_id')) {
            $base->where('tr_single_roles.company_id', $request->company_id);
        }

        $recordsTotal = (clone $base)->count();

        if ($search) {
            $driver = DB::getDriverName();
            $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $base->where(function ($q) use ($search, $like) {
                $q->where('tr_single_roles.nama', $like, "%$search%")
                    ->orWhere('tr_single_roles.company_id', $like, "%$search%")
                    ->orWhere('ms_company.nama', $like, "%$search%")
                    ->orWhereHas('tcodes', function ($tq) use ($search, $like) {
                        $tq->where('tr_tcodes.code', $like, "%$search%")
                            ->orWhere('tr_tcodes.deskripsi', $like, "%$search%");
                    });
            });
        }

        $recordsFiltered = (clone $base)->count();

        if ($request->has('order')) {
            foreach ($request->input('order') as $ord) {
                $idx = (int)$ord['column'];
                $dir = $ord['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($idx) {
                    case 0: // company
                        $base->orderBy('ms_company.nama', $dir)->orderBy('tr_single_roles.nama');
                        break;
                    case 1: // single role
                        $base->orderBy('tr_single_roles.nama', $dir);
                        break;
                    case 2: // tcode column – fallback to role then tcode implicitly
                        $base->orderBy('tr_single_roles.nama', $dir);
                        break;
                    default:
                        $base->orderBy('tr_single_roles.nama');
                }
            }
        } else {
            $base->orderBy('ms_company.nama')->orderBy('tr_single_roles.nama');
        }

        $singleRoles = $base->skip($start)->take($length)->get();

        $data = [];
        foreach ($singleRoles as $sr) {
            $companyDisplay = $sr->company_name ?? '-';
            $canModify = $userCompanyCode === 'A000' || $sr->company_id === $userCompanyCode;

            $actionsHtml = $canModify
                ? '<a href="' . route('single-tcode.edit', $sr->id) . '" class="btn btn-primary btn-sm mb-1 w-100">Edit</a>'
                . '<form action="' . route('single-tcode.destroy', $sr->id) . '" method="POST" onsubmit="return confirm(\'Remove all tcodes for this Single Role?\')">'
                . csrf_field() . method_field('DELETE')
                . '<button class="btn btn-danger btn-sm w-100">Delete</button></form>'
                : '<span class="text-muted small">Read only</span>';

            if ($sr->tcodes->isEmpty()) {
                $data[] = [
                    'company'     => $companyDisplay,
                    'single_role' => $sr->nama,
                    'tcode'       => '-',
                    'description' => '-',
                    'group_key'   => $sr->id,
                    'actions'     => $actionsHtml,
                ];
                continue;
            }

            foreach ($sr->tcodes as $t) {
                $data[] = [
                    'company'     => $companyDisplay,
                    'single_role' => $sr->nama,
                    'tcode'       => $t->code,
                    'description' => $t->deskripsi ?: '-',
                    'group_key'   => $sr->id,
                    'actions'     => $actionsHtml,
                ];
            }
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
