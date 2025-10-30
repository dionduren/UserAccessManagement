<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\CompositeAO;
use App\Models\CompositeRole;
use App\Models\SingleRole;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CompositeSingleController extends Controller
{
    // Display a listing of the resource.
    public function index(Request $request)
    {
        $user  = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $companies = $userCompanyCode === 'A000'
            ? Company::orderBy('nama')->get()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('relationship.composite-single.index', [
            'companies'        => $companies,
            'userCompanyCode'  => $userCompanyCode,
            'selectedCompany'  => $request->get('company_id')
        ]);
    }

    // Show the form for creating a new resource.
    public function create()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('relationship.composite-single.create', compact('companies'));
    }


    // Store a new composite-single relationship
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'composite_role_id' => 'required|exists:tr_composite_roles,id',
            'single_role_id' => 'required|array',
            'single_role_id.*' => 'exists:tr_single_roles,id',
        ]);


        $validatedData->merge(['source' => 'upload']);

        $compositeRole = CompositeRole::findOrFail($validatedData['composite_role_id']);
        $compositeRole->singleRoles()->syncWithoutDetaching($validatedData['single_role_id']);

        return redirect()->route('composite-single.index')->with('success', 'Relationship created successfully.');
    }

    // Display the specified resource.
    public function show($id)
    {
        $compositeSingle = CompositeRole::with('singleRoles')->findOrFail($id);
        return view('relationship.composite-single.show', compact('compositeSingle'));
    }

    // Edit form
    public function edit($id)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        $compositeSingle = CompositeRole::with('singleRoles')->findOrFail($id);
        $selectedSingleRoles = $compositeSingle->singleRoles->pluck('id')->toArray();

        $compositeRoles = CompositeRole::all();
        $singleRoles = SingleRole::all();

        return view('relationship.composite-single.edit', compact(
            'companies',
            'compositeSingle',
            'compositeRoles',
            'singleRoles',
            'selectedSingleRoles'
        ));
    }

    // Update a composite-single relationship
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'composite_role_id' => 'required|exists:tr_composite_roles,id',
            'single_role_id' => 'required|array',
            'single_role_id.*' => 'exists:tr_single_roles,id',
        ]);

        $compositeRole = CompositeRole::findOrFail($id);
        $compositeRole->singleRoles()->sync($validatedData['single_role_id']);

        return redirect()->route('composite-single.index')->with('success', 'Relationship updated successfully.');
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $compositeRole = CompositeRole::findOrFail($id);
        $compositeRole->singleRoles()->detach();

        return redirect()->route('composite-single.index')->with('success', 'Relationship deleted successfully.');
    }

    // EXTRA FUNCTIONS

    // public function jsonIndex(Request $request)
    // {
    //     $compositeSingles = CompositeRole::with('singleRoles')->get();

    //     return DataTables::of($compositeSingles)
    //         ->addColumn('singleRoles', function ($compositeSingle) {
    //             return $compositeSingle->singleRoles->pluck('nama')->implode(', ');
    //         })
    //         ->addColumn('action', function ($compositeSingle) {
    //             return [
    //                 'edit_url' => route('composite-single.edit', $compositeSingle->id),
    //                 'delete_url' => route('composite-single.destroy', $compositeSingle->id),
    //             ];
    //         })
    //         ->rawColumns(['action']) // allow HTML in action column if needed
    //         ->toJson();
    // }

    // public function jsonIndex(Request $request)
    // {
    //     $compositeSingles = CompositeRole::with('singleRoles')->get();

    //     return \Yajra\DataTables\DataTables::of($compositeSingles)
    //         ->addColumn('singleRoles', function ($compositeSingle) {
    //             $names = $compositeSingle->singleRoles->pluck('nama');
    //             $lis = $names->map(fn($n) => '<li>' . e($n) . '</li>')->implode('');
    //             $count = $names->count();
    //             return '<ul class="mb-0 single-role-list" data-count="' . $count . '">' . $lis . '</ul>';
    //         })
    //         ->addColumn('singleRoles_text', function ($compositeSingle) {
    //             return $compositeSingle->singleRoles->pluck('nama')->implode(', ');
    //         })
    //         ->addColumn('action', function ($compositeSingle) {
    //             return [
    //                 'edit_url'   => route('composite-single.edit', $compositeSingle->id),
    //                 'delete_url' => route('composite-single.destroy', $compositeSingle->id),
    //             ];
    //         })
    //         ->rawColumns(['singleRoles'])
    //         ->make(true);
    // }

    public function searchByCompany(Request $request)
    {
        $companyId = $request->input('company_id');
        $compositeRoles = CompositeRole::where('company_id', $companyId)->get();
        $singleRoles = SingleRole::where('company_id', $companyId)->get();

        return response()->json(['compositeRoles' => $compositeRoles, 'singleRoles' => $singleRoles]);
    }

    // DataTables JSON – paginate by composite roles (NOT by single-role rows)
    public function datatable(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $draw   = (int)$request->input('draw');
        $length = (int)$request->input('length', 10);
        $start  = (int)$request->input('start', 0);
        $search = $request->input('search.value');

        // Base query (one row per composite role) - ADD PROPER SOFT DELETE FILTERS
        $base = CompositeRole::query()
            ->whereNull('tr_composite_roles.deleted_at') // ✅ Non-deleted composite roles
            ->leftJoin('ms_company', 'ms_company.company_code', '=', 'tr_composite_roles.company_id')
            ->whereNull('ms_company.deleted_at') // ✅ Non-deleted companies
            ->select('tr_composite_roles.*', 'ms_company.nama as company_name')
            ->with(['singleRoles' => function ($q) {
                $q->whereNull('tr_single_roles.deleted_at') // ✅ Non-deleted single roles
                    ->orderBy('nama');
            }])
            ->orderBy('tr_composite_roles.company_id');

        // Security / company scoping
        if ($userCompanyCode !== 'A000') {
            $base->where('tr_composite_roles.company_id', $userCompanyCode);
        } elseif ($request->filled('company_id')) {
            $base->where('tr_composite_roles.company_id', $request->company_id);
        }

        // Total BEFORE search filter (company scope already applied)
        $recordsTotal = (clone $base)->count();

        // Global search filter
        if ($search) {
            $driver = DB::getDriverName();
            $like   = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

            $base->where(function ($q) use ($search, $like) {
                $q->where('tr_composite_roles.nama', $like, "%$search%")
                    ->orWhere('tr_composite_roles.company_id', $like, "%$search%")
                    ->orWhere('ms_company.nama', $like, "%$search%")
                    ->orWhereHas('singleRoles', function ($sq) use ($search, $like) {
                        $sq->whereNull('tr_single_roles.deleted_at') // ✅ Ensure search only in non-deleted single roles
                            ->where(function ($innerQ) use ($search, $like) {
                                $innerQ->where('tr_single_roles.nama', $like, "%$search%")
                                    ->orWhere('tr_single_roles.deskripsi', $like, "%$search%");
                            });
                    });
            });
        }

        // Individual column search filters
        $columns = $request->input('columns', []);
        $driver = DB::getDriverName();
        $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        foreach ($columns as $index => $column) {
            $columnSearch = $column['search']['value'] ?? '';
            if (!empty($columnSearch)) {
                switch ($index) {
                    case 0: // Company column
                        $base->where(function ($q) use ($columnSearch, $like) {
                            $q->where('tr_composite_roles.company_id', $like, "%$columnSearch%")
                                ->orWhere('ms_company.nama', $like, "%$columnSearch%");
                        });
                        break;
                    case 1: // Composite Role column
                        $base->where('tr_composite_roles.nama', $like, "%$columnSearch%");
                        break;
                    case 2: // Single Role column
                        $base->whereHas('singleRoles', function ($sq) use ($columnSearch, $like) {
                            $sq->whereNull('tr_single_roles.deleted_at') // ✅ Non-deleted single roles only
                                ->where('tr_single_roles.nama', $like, "%$columnSearch%");
                        });
                        break;
                    case 3: // Description column
                        $base->whereHas('singleRoles', function ($sq) use ($columnSearch, $like) {
                            $sq->whereNull('tr_single_roles.deleted_at') // ✅ Non-deleted single roles only
                                ->where('tr_single_roles.deskripsi', $like, "%$columnSearch%");
                        });
                        break;
                }
            }
        }

        // Count AFTER filtering
        $recordsFiltered = (clone $base)->count();

        // Ordering (applied at composite level)
        if ($request->has('order')) {
            foreach ($request->input('order') as $ord) {
                $idx = (int)$ord['column'];
                $dir = $ord['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($idx) {
                    case 0:
                        $base->orderBy('ms_company.nama', $dir)->orderBy('tr_composite_roles.nama');
                        break;
                    case 1:
                        $base->orderBy('tr_composite_roles.nama', $dir);
                        break;
                    case 2:
                        // Approximate "single role" ordering: order by first single role name via composite name fallback
                        $base->orderBy('tr_composite_roles.nama', $dir);
                        break;
                    default:
                        // Fallback stable
                        $base->orderBy('tr_composite_roles.nama');
                }
            }
        } else {
            // Default order (company, composite)
            $base->orderBy('ms_company.nama')->orderBy('tr_composite_roles.nama');
        }

        // Pagination (by composites)
        $composites = $base->skip($start)->take($length)->get();

        // Build flattened row set (expanded single roles)
        $data = [];
        foreach ($composites as $comp) {
            $companyDisplay = ($comp->company_name ?? $comp->company_id ?? '-');
            $canModify = $userCompanyCode === 'A000' || $comp->company_id === $userCompanyCode;

            $actionsHtml = $canModify
                ? '<a href="' . route('composite-single.edit', $comp->id) . '" class="btn btn-primary btn-sm mb-1 w-100">Edit</a>'
                . '<form action="' . route('composite-single.destroy', $comp->id) . '" method="POST" onsubmit="return confirm(\'Delete all links for this composite role?\')">'
                . csrf_field() . method_field('DELETE')
                . '<button class="btn btn-danger btn-sm w-100">Delete Relationship</button></form>'
                : '<span class="text-muted small">Read only</span>';

            // If no single roles, still show a placeholder row
            if ($comp->singleRoles->isEmpty()) {
                $data[] = [
                    'company'        => $companyDisplay,
                    'composite_role' => $comp->nama,
                    'single_role'    => '-',
                    'description'    => '-',
                    'group_key'      => $comp->id,
                    'actions'        => $actionsHtml,
                ];
                continue;
            }

            foreach ($comp->singleRoles as $sr) {
                $data[] = [
                    'company'        => $companyDisplay,
                    'composite_role' => $comp->nama,
                    'single_role'    => $sr->nama,
                    'description'    => $sr->deskripsi ?: '-',
                    'group_key'      => $comp->id,
                    'actions'        => $actionsHtml,
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

    public function index_ao(Request $request)
    {
        $user  = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $companies = $userCompanyCode === 'A000'
            ? Company::orderBy('company_code')->get()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('relationship.composite-ao.index', [
            'companies'        => $companies,
            'userCompanyCode'  => $userCompanyCode,
            'selectedCompany'  => $request->get('company_id')
        ]);
    }

    public function datatable_ao(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        $draw   = (int)$request->input('draw');
        $length = (int)$request->input('length', 10);
        $start  = (int)$request->input('start', 0);
        $search = $request->input('search.value');

        // Base query: join to composite role (by name) to derive company, then to company table
        $base = CompositeAO::query()
            ->leftJoin('tr_composite_roles', function ($join) {
                $join->on('tr_composite_roles.nama', '=', 'tr_composite_ao.composite_role')
                    ->whereNull('tr_composite_roles.deleted_at'); // ✅ Non-deleted composite roles
            })
            ->leftJoin('ms_company', function ($join) {
                $join->on('ms_company.company_code', '=', 'tr_composite_roles.company_id')
                    ->whereNull('ms_company.deleted_at'); // ✅ Non-deleted companies
            })
            ->select([
                'tr_composite_ao.id',
                'tr_composite_ao.composite_role',
                'tr_composite_ao.nama as ao_name',
                'tr_composite_ao.deskripsi',
                'tr_composite_roles.company_id',
                'ms_company.nama as company_name',
            ]);

        // Scope by user company
        if ($userCompanyCode !== 'A000') {
            $base->where('tr_composite_roles.company_id', $userCompanyCode);
        } elseif ($request->filled('company_id')) {
            $base->where('tr_composite_roles.company_id', $request->company_id);
        }

        $recordsTotal = (clone $base)->count();

        if ($search) {
            $driver = DB::getDriverName();
            $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $base->where(function ($q) use ($search, $like) {
                $q->where('tr_composite_ao.composite_role', $like, "%$search%")
                    ->orWhere('tr_composite_ao.nama', $like, "%$search%")
                    ->orWhere('tr_composite_ao.deskripsi', $like, "%$search%")
                    ->orWhere('tr_composite_roles.company_id', $like, "%$search%")
                    ->orWhere('ms_company.nama', $like, "%$search%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        // Ordering
        if ($request->has('order')) {
            foreach ($request->input('order') as $ord) {
                $idx = (int)$ord['column'];
                $dir = $ord['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($idx) {
                    case 0: // company
                        $base->orderBy('ms_company.nama', $dir)->orderBy('tr_composite_ao.composite_role');
                        break;
                    case 1: // composite_role
                        $base->orderBy('tr_composite_ao.composite_role', $dir);
                        break;
                    case 2: // ao name
                        $base->orderBy('tr_composite_ao.nama', $dir);
                        break;
                    default:
                        $base->orderBy('tr_composite_ao.id', $dir);
                }
            }
        } else {
            $base->orderBy('ms_company.nama')->orderBy('tr_composite_ao.composite_role')->orderBy('tr_composite_ao.nama');
        }

        $rows = $base->skip($start)->take($length)->get();

        $data = [];
        foreach ($rows as $r) {
            $canModify = $userCompanyCode === 'A000' || $r->company_id === $userCompanyCode;
            $actions = $canModify
                ? '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $r->id . '">Delete</button>'
                : '<span class="text-muted small">Read only</span>';

            $data[] = [
                'company'        => $r->company_name ?? $r->company_id ?? '-BELUM TERDAFTAR-',
                'composite_role' => $r->composite_role,
                'ao_name'        => $r->ao_name,
                'description'    => $r->deskripsi ?: '-',
                'actions'        => $actions,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function destroy_ao($id)
    {
        $ao = CompositeAO::findOrFail($id);
        $ao->delete();
        return response()->json(['status' => 'ok']);
    }
}
