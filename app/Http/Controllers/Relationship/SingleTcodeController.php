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
        return view('relationship.single-tcode.index');
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
            'tcode_id' => 'required|array',
            'tcode_id.*' => 'exists:tr_tcodes,code',
        ]);

        $singleRole = SingleRole::findOrFail($validatedData['single_role_id']);

        // Convert codes to IDs before syncing
        $tcodeIds = Tcode::whereIn('code', $validatedData['tcode_id'])->pluck('id')->toArray();

        // Sync with source = 'upload' via withPivotValues
        $pivotData = [];
        foreach ($tcodeIds as $tcodeId) {
            $pivotData[$tcodeId] = [
                'source'     => 'upload',
                'created_by' => auth()->user()->name ?? null,
                'updated_by' => auth()->user()->name ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $singleRole->tcodes()->syncWithoutDetaching($pivotData);

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
        $singleRoles = SingleRole::all(); // add this if view needs it for a dropdown
        $tcodes = Tcode::all();
        $selectedTcodes = $singleRole->tcodes->pluck('code')->toArray();

        return view('relationship.single-tcode.edit', compact('singleRoles', 'singleRole', 'tcodes', 'selectedTcodes'));
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
        $tcodeCodes = $request->input('tcode_id', []);

        // Convert codes to IDs
        $tcodeIds = Tcode::whereIn('code', $tcodeCodes)->pluck('id')->toArray();

        // Build pivot data with source = 'upload' for new/updated rows
        $pivotData = [];
        foreach ($tcodeIds as $tcodeId) {
            $pivotData[$tcodeId] = [
                'source'     => 'upload',
                'updated_by' => auth()->user()->name ?? null,
                'updated_at' => now(),
            ];
        }

        // sync() replaces all; detaches removed tcodes and attaches/updates specified ones
        $singleRole->tcodes()->sync($pivotData);

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
        $draw   = (int)$request->input('draw');
        $length = (int)$request->input('length', 10);
        $start  = (int)$request->input('start', 0);
        $search = $request->input('search.value');

        $columns = $request->input('columns', []);
        $colSingleRole = $columns[0]['search']['value'] ?? null;
        $colTcode      = $columns[1]['search']['value'] ?? null;
        $colDesc       = $columns[2]['search']['value'] ?? null;

        // FIX: join on pivot.tcode_id = t.id (both bigint)
        $base = DB::table('tr_single_roles as sr')
            ->leftJoin('pt_single_role_tcode as pivot', 'pivot.single_role_id', '=', 'sr.id')
            ->leftJoin('tr_tcodes as t', 'pivot.tcode_id', '=', 't.id') // <-- changed from t.code
            ->selectRaw('
                sr.id as single_role_id,
                sr.nama as single_role,
                t.code as tcode,
                t.deskripsi as tcode_desc
            ');

        $recordsTotal = (clone $base)->count();

        $driver = DB::getDriverName();
        $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        if ($colSingleRole) {
            $base->where('sr.nama', $like, "%{$colSingleRole}%");
        }
        if ($colTcode) {
            $base->where('t.code', $like, "%{$colTcode}%");
        }
        if ($colDesc) {
            $base->where('t.deskripsi', $like, "%{$colDesc}%");
        }

        if ($search) {
            $base->where(function ($q) use ($like, $search) {
                $q->where('sr.nama', $like, "%{$search}%")
                    ->orWhere('t.code', $like, "%{$search}%")
                    ->orWhere('t.deskripsi', $like, "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        if ($request->has('order')) {
            foreach ($request->input('order') as $ord) {
                $idx = (int)$ord['column'];
                $dir = $ord['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($idx) {
                    case 0:
                        $base->orderBy('sr.nama', $dir)->orderBy('t.code');
                        break;
                    case 1:
                        if ($driver === 'pgsql') {
                            $base->orderByRaw("t.code IS NULL")->orderBy('t.code', $dir);
                        } else {
                            $base->orderByRaw("(t.code IS NULL) asc")->orderBy('t.code', $dir);
                        }
                        break;
                    case 2:
                        if ($driver === 'pgsql') {
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

        $rows = $base->skip($start)->take($length)->get();

        $data = [];
        foreach ($rows as $r) {
            $actionsHtml = '<a href="' . route('single-tcode.edit', $r->single_role_id) . '" class="btn btn-primary btn-sm mb-1 w-100">Edit</a>'
                . '<form action="' . route('single-tcode.destroy', $r->single_role_id) . '" method="POST" onsubmit="return confirm(\'Remove all tcodes for this Single Role?\')">'
                . csrf_field() . method_field('DELETE')
                . '<button class="btn btn-danger btn-sm w-100">Delete</button></form>';

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

    /**
     * Show bulk Tcode search form
     */
    public function bulkSearchIndex()
    {
        return view('relationship.single-tcode.bulk-search');
    }

    /**
     * Process bulk Tcode search and return single roles with highlighting
     */
    public function bulkSearchResults(Request $request)
    {
        $request->validate([
            'tcodes' => 'required|string',
            'include_company' => 'nullable|boolean',
            'modules' => 'nullable|string',
            'process_areas' => 'nullable|string', // ✅ NEW
            'objects' => 'nullable|string', // ✅ NEW
        ]);

        // Parse input (split by newline/enter)
        $inputTcodes = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $request->tcodes)),
            fn($t) => !empty($t)
        );

        if (empty($inputTcodes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No valid Tcodes provided'
            ]);
        }

        // Normalize input (uppercase, trim)
        $inputTcodes = array_map('strtoupper', $inputTcodes);
        $inputTcodes = array_unique($inputTcodes);

        $includeCompany = $request->boolean('include_company', false);

        // ✅ Parse module filter (comma or space separated)
        $moduleFilter = [];
        if ($request->filled('modules')) {
            $moduleFilter = array_filter(
                array_map('trim', preg_split('/[\s,]+/', strtoupper($request->modules))),
                fn($m) => !empty($m)
            );
            $moduleFilter = array_unique($moduleFilter);
        }

        // ✅ Parse process area filter
        $processAreaFilter = [];
        if ($request->filled('process_areas')) {
            $processAreaFilter = array_filter(
                array_map('trim', preg_split('/[\s,]+/', strtoupper($request->process_areas))),
                fn($p) => !empty($p)
            );
            $processAreaFilter = array_unique($processAreaFilter);
        }

        // ✅ Parse object filter
        $objectFilter = [];
        if ($request->filled('objects')) {
            $objectFilter = array_filter(
                array_map('trim', preg_split('/[\s,]+/', strtoupper($request->objects))),
                fn($o) => !empty($o)
            );
            $objectFilter = array_unique($objectFilter);
        }

        // Find single roles that have ANY of the input Tcodes
        $singleRoles = SingleRole::whereHas('tcodes', function ($q) use ($inputTcodes) {
            $q->whereIn('tr_tcodes.code', $inputTcodes)
                ->whereNull('tr_tcodes.deleted_at');
        })
            ->with(['tcodes' => function ($q) {
                $q->whereNull('tr_tcodes.deleted_at')
                    ->orderBy('tr_tcodes.code');
            }])
            ->whereNull('tr_single_roles.deleted_at')
            // ✅ Exclude "ZS-ALL-PIHC-TI"
            ->where('tr_single_roles.nama', '!=', 'ZS-ALL-PIHC-TI')
            // ✅ Conditionally exclude company single roles
            ->when(!$includeCompany, function ($q) {
                if (DB::getDriverName() === 'pgsql') {
                    $q->whereRaw("tr_single_roles.nama !~ '^ZS-[A-Z0-9]{4}-'");
                } else {
                    $q->where(function ($subQ) {
                        $subQ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-A000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-B000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-C000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-D000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-E000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-F000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-G000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-H000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-I000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-J000-%')
                            ->where('tr_single_roles.nama', 'NOT LIKE', 'ZS-K000-%');
                    });
                }
            })
            // ✅ Filter by modules (e.g., QM, PP, MD, MM)
            ->when(!empty($moduleFilter), function ($q) use ($moduleFilter, $includeCompany) {
                $q->where(function ($subQ) use ($moduleFilter, $includeCompany) {
                    foreach ($moduleFilter as $module) {
                        if ($includeCompany) {
                            // Pattern: ZS-XXXX-MODULE- or ZS-MODULE-
                            $subQ->orWhere(function ($orQ) use ($module) {
                                $orQ->where('tr_single_roles.nama', 'LIKE', "ZS-____-{$module}-%")
                                    ->orWhere('tr_single_roles.nama', 'LIKE', "ZS-{$module}-%");
                            });
                        } else {
                            // Pattern: ZS-MODULE- only
                            $subQ->orWhere('tr_single_roles.nama', 'LIKE', "ZS-{$module}-%");
                        }
                    }
                });
            })
            // ✅ NEW: Filter by process areas (position 3: ZS-MM-MD-MAT-CRT)
            ->when(!empty($processAreaFilter), function ($q) use ($processAreaFilter, $includeCompany) {
                $q->where(function ($subQ) use ($processAreaFilter, $includeCompany) {
                    foreach ($processAreaFilter as $processArea) {
                        if ($includeCompany) {
                            // Pattern: ZS-XXXX-XX-PROCESSAREA- or ZS-XX-PROCESSAREA-
                            // Examples: ZS-A000-MM-MD-, ZS-MM-MD-
                            $subQ->orWhere(function ($orQ) use ($processArea) {
                                $orQ->where('tr_single_roles.nama', 'LIKE', "ZS-____-__-{$processArea}-%")
                                    ->orWhere('tr_single_roles.nama', 'LIKE', "ZS-__-{$processArea}-%");
                            });
                        } else {
                            // Pattern: ZS-XX-PROCESSAREA- only
                            // Example: ZS-MM-MD-
                            $subQ->orWhere('tr_single_roles.nama', 'LIKE', "ZS-__-{$processArea}-%");
                        }
                    }
                });
            })
            // ✅ NEW: Filter by objects (position 4: ZS-MM-MD-MAT-CRT)
            ->when(!empty($objectFilter), function ($q) use ($objectFilter, $includeCompany) {
                $q->where(function ($subQ) use ($objectFilter, $includeCompany) {
                    foreach ($objectFilter as $object) {
                        if ($includeCompany) {
                            // Pattern: ZS-XXXX-XX-XX-OBJECT- or ZS-XX-XX-OBJECT-
                            // Examples: ZS-A000-MM-MD-MAT-, ZS-MM-MD-MAT-
                            $subQ->orWhere(function ($orQ) use ($object) {
                                $orQ->where('tr_single_roles.nama', 'LIKE', "ZS-____-__-__-{$object}-%")
                                    ->orWhere('tr_single_roles.nama', 'LIKE', "ZS-__-__-{$object}-%");
                            });
                        } else {
                            // Pattern: ZS-XX-XX-OBJECT- only
                            // Example: ZS-MM-MD-MAT-
                            $subQ->orWhere('tr_single_roles.nama', 'LIKE', "ZS-__-__-{$object}-%");
                        }
                    }
                });
            })
            ->get();

        // Build result with highlighting
        $results = $singleRoles->map(function ($singleRole) use ($inputTcodes) {
            $allTcodes = $singleRole->tcodes->map(function ($tcode) use ($inputTcodes) {
                return [
                    'id' => $tcode->id,
                    'code' => $tcode->code,
                    'deskripsi' => $tcode->deskripsi,
                    'matched' => in_array(strtoupper($tcode->code), $inputTcodes),
                ];
            });

            $matchedCount = $allTcodes->where('matched', true)->count();
            $unmatchedCount = $allTcodes->where('matched', false)->count();

            return [
                'id' => $singleRole->id,
                'nama' => $singleRole->nama,
                'deskripsi' => $singleRole->deskripsi,
                'tcodes' => $allTcodes->values()->toArray(),
                'matched_count' => $matchedCount,
                'unmatched_count' => $unmatchedCount,
                'total_tcodes' => $allTcodes->count(),
            ];
        });

        // Sort by matched count (most matches first)
        $results = $results->sortByDesc('matched_count')->values();

        // Find input Tcodes that weren't found in any Single Role
        $foundTcodes = $results->pluck('tcodes')->flatten(1)->where('matched', true)->pluck('code')->map('strtoupper')->unique()->values();
        $notFoundTcodes = array_diff($inputTcodes, $foundTcodes->toArray());

        return response()->json([
            'status' => 'ok',
            'input_tcodes' => array_values($inputTcodes),
            'total_input' => count($inputTcodes),
            'found_tcodes' => $foundTcodes->count(),
            'not_found_tcodes' => array_values($notFoundTcodes),
            'single_roles_found' => $results->count(),
            'include_company' => $includeCompany,
            'module_filter' => $moduleFilter,
            'process_area_filter' => $processAreaFilter, // ✅ NEW
            'object_filter' => $objectFilter, // ✅ NEW
            'results' => $results,
        ]);
    }
}
