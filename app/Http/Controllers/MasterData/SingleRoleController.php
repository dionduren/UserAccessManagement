<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\SingleRole;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class SingleRoleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        return view('master-data.single_roles.index', compact('userCompanyCode'));
    }

    // Show the details of a Single Role
    public function show($id)
    {
        $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;
        $singleRole = SingleRole::with(['compositeRoles', 'tcodes'])->findOrFail($id);
        return view('master-data.single_roles.show', compact('singleRole', 'userCompanyCode'));
    }

    public function create()
    {
        $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;
        if ($userCompanyCode === 'A000') {
            return view('master-data.single_roles.create', compact('userCompanyCode'));
        } else {
            return redirect()
                ->route('single-roles.index')
                ->with('error', 'You are not authorized to create a Single Role.');
        }
    }

    // Store a new Single Role
    public function store(Request $request)
    {
        $rules = [
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_single_roles', 'nama')
                    ->where(fn($q) => $q->whereNull('deleted_at')),
            ],
            'deskripsi' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                $existing = SingleRole::where('nama', $request->nama)
                    ->whereNull('deleted_at')
                    ->first();

                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                    'meta'    => $existing ? [
                        'role_id' => $existing->id,
                        'links' => [
                            'single_tcode_edit'       => route('single-tcode.edit', $existing->id),
                            'composite_single_index'  => route('composite-single.index', ['search_single_role' => $request->nama]),
                            'single_tcode_index'      => route('single-tcode.index'),
                        ],
                    ] : null,
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $request->merge(['source' => 'upload']);
        $singleRole = SingleRole::create($request->all());

        if ($request->ajax()) {
            $view = view('master-data.single_roles.partials.actions', ['role' => $singleRole])->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role created successfully.');
    }

    public function edit($id)
    {
        $singleRole = SingleRole::findOrFail($id);
        return view('master-data.single_roles.edit', compact('singleRole'))->render();
    }

    public function update(Request $request, $id)
    {
        $singleRole = SingleRole::findOrFail($id);

        $rules = [
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_single_roles', 'nama')
                    ->ignore($singleRole->id)
                    ->where(fn($q) => $q->whereNull('deleted_at')),
            ],
            'deskripsi'  => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                $existing = SingleRole::where('nama', $request->nama)
                    ->where('id', '!=', $singleRole->id)
                    ->whereNull('deleted_at')
                    ->first();

                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                    'meta'    => ($existing ?: $singleRole) ? [
                        'role_id' => ($existing ? $existing->id : $singleRole->id),
                        'links' => [
                            'single_tcode_edit'       => route('single-tcode.edit', ($existing ? $existing->id : $singleRole->id)),
                            'composite_single_index'  => route('composite-single.index', ['search_single_role' => $request->nama]),
                            'single_tcode_index'      => route('single-tcode.index'),
                        ],
                    ] : null,
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $singleRole->update($request->all());

        if ($request->ajax()) {
            $view = view('master-data.single_roles.partials.actions', ['role' => $singleRole])->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role updated successfully.');
    }

    public function destroy(SingleRole $singleRole)
    {
        $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;

        if ($userCompanyCode === 'A000') {
            $singleRole->delete();
            return redirect()->route('single-roles.index')->with('status', 'Single role deleted successfully.');
        } else {
            return redirect()
                ->route('single-roles.index')
                ->with('error', 'You are not authorized to delete a Single Role.');
        }
    }

    public function getSingleRoles(Request $request)
    {
        $query = SingleRole::with(['tcodes', 'compositeRoles'])
            ->select('tr_single_roles.*');

        // Column-specific search
        $columns = $request->input('columns', []);
        $colNama      = $columns[0]['search']['value'] ?? null;
        $colDeskripsi = $columns[1]['search']['value'] ?? null;
        $colSource    = $columns[2]['search']['value'] ?? null;

        if ($colNama) {
            $query->where('tr_single_roles.nama', 'like', "%{$colNama}%");
        }
        if ($colDeskripsi) {
            $query->where('tr_single_roles.deskripsi', 'like', "%{$colDeskripsi}%");
        }
        if ($colSource) {
            $query->where('tr_single_roles.source', 'like', "%{$colSource}%");
        }

        // Global search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('tr_single_roles.nama', 'like', "%{$searchValue}%")
                    ->orWhere('tr_single_roles.deskripsi', 'like', "%{$searchValue}%")
                    ->orWhere('tr_single_roles.source', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        if ($request->filled('order.0.column')) {
            $orderableColumns = ['tr_single_roles.nama', 'tr_single_roles.deskripsi', 'tr_single_roles.source'];
            $columnIndex      = $request->input('order.0.column');
            $columnDirection  = $request->input('order.0.dir', 'asc');
            $columnName       = $orderableColumns[$columnIndex] ?? 'tr_single_roles.nama';
            $query->orderBy($columnName, $columnDirection);
        }

        $singleRoles = $query
            ->skip(intval($request->start))
            ->take(intval($request->length))
            ->get();

        $recordsTotal = SingleRole::count();

        $data = $singleRoles->map(function ($role) {
            return [
                'nama'      => $role->nama,
                'deskripsi' => $role->deskripsi,
                'source'    => $role->source,
                'actions'   => view('master-data.single_roles.partials.actions', ['role' => $role])->render(),
            ];
        });

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
