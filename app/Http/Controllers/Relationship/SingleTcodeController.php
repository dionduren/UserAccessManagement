<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Tcode;
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
        $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;

        return view('relationship.single-tcode.index', [
            'userCompanyCode' => $userCompanyCode
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $singleRoles = SingleRole::all();
        $tcodes = Tcode::all();

        return view('relationship.single-tcode.create', compact('singleRoles', 'tcodes'));
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

        $validatedData->merge(['source' => 'upload']);

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

        $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;

        if ($userCompanyCode !== 'A000') {
            return redirect()
                ->route('single-tcode.index')
                ->withErrors(['error' => 'You are not authorized to edit this single role.']);
        }

        $tcodes = Tcode::all();
        $selectedTcodes = $singleRole->tcodes->pluck('code')->toArray();

        return view('relationship.single-tcode.edit', compact(
            'singleRole',
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

    // DataTables JSON – server-side (row = single_role + optional tcode)
    public function datatable(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $draw   = (int)$request->input('draw');
        $length = (int)$request->input('length', 10);
        $start  = (int)$request->input('start', 0);
        $search = $request->input('search.value');

        // Base (flattened) query: one row per SingleRole–Tcode (or single role with null tcode)
        $base = DB::table('tr_single_roles as sr')
            ->leftJoin('pt_single_role_tcode as pivot', 'pivot.single_role_id', '=', 'sr.id')
            ->leftJoin('tr_tcodes as t', 'pivot.tcode_id', '=', 't.id') // FIX: pivot.tcode_id references tr_tcodes.id
            ->selectRaw('
                sr.id as single_role_id,
                sr.nama as single_role,
                t.code as tcode,
                t.deskripsi as tcode_desc
            ');

        // Total rows (before search)
        $recordsTotal = (clone $base)->count();

        if ($search) {
            $driver = DB::getDriverName();
            $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $base->where(function ($q) use ($like, $search) {
                $q->where('sr.nama', $like, "%{$search}%")
                    ->orWhere('t.code', $like, "%{$search}%")
                    ->orWhere('t.deskripsi', $like, "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        // Ordering
        if ($request->has('order')) {
            foreach ($request->input('order') as $ord) {
                $idx = (int)$ord['column'];
                $dir = $ord['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($idx) {
                    case 0: // single_role
                        $base->orderBy('sr.nama', $dir)->orderBy('t.code');
                        break;
                    case 1: // tcode
                        // Order by tcode with NULLS last (pgsql) / emulate for others
                        if (DB::getDriverName() === 'pgsql') {
                            $base->orderByRaw("t.code IS NULL")->orderBy('t.code', $dir);
                        } else {
                            $base->orderByRaw("(t.code IS NULL) asc")->orderBy('t.code', $dir);
                        }
                        break;
                    case 2: // description
                        if (DB::getDriverName() === 'pgsql') {
                            $base->orderByRaw("t.deskripsi IS NULL")->orderBy('t.deskripsi', $dir);
                        } else {
                            $base->orderByRaw("(t.deskripsi IS NULL) asc")->orderBy('t.deskripsi', $dir);
                        }
                        break;
                    default:
                        $base->orderBy('sr.nama')->orderBy('t.code');
                }
            }
        } else {
            $base->orderBy('sr.nama')->orderBy('t.code');
        }

        // Pagination
        $rows = $base->skip($start)->take($length)->get();

        $data = [];
        foreach ($rows as $r) {
            $canModify = $userCompanyCode === 'A000';
            $actionsHtml = $canModify
                ? '<a href="' . route('single-tcode.edit', $r->single_role_id) . '" class="btn btn-primary btn-sm mb-1 w-100">Edit</a>'
                . '<form action="' . route('single-tcode.destroy', $r->single_role_id) . '" method="POST" onsubmit="return confirm(\'Remove all tcodes for this Single Role?\')">'
                . csrf_field() . method_field('DELETE')
                . '<button class="btn btn-danger btn-sm w-100">Delete</button></form>'
                : '<span class="text-muted small">Read only</span>';

            $data[] = [
                'single_role' => $r->single_role,
                'tcode'       => $r->tcode ?: '-',
                'description' => $r->tcode_desc ?: '-',
                'group_key'   => $r->single_role_id,
                'actions'     => $actionsHtml,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
