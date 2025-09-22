<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\SingleRole;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $request->validate([
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_single_roles', 'nama')
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id);
                    }),
            ],
            'deskripsi' => 'nullable|string',
        ]);

        $singleRole = SingleRole::create($request->all());

        // Check if the request is an AJAX request
        if ($request->ajax()) {
            // Return HTML for the table row or a success message
            $view = view('master-data.single_roles.partials.actions', ['role' => $singleRole])->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role created successfully.');
    }

    public function edit($id)
    {
        $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;
        if ($userCompanyCode === 'A000') {
            $singleRole = SingleRole::findOrFail($id);

            return view('master-data.single_roles.edit', compact('singleRole', 'userCompanyCode'))->render();
        } else {
            return redirect()
                ->route('single-roles.index')
                ->with('error', 'You are not authorized to edit a Single Role.');
        }
    }

    public function update(Request $request, $id)
    {
        $singleRole = SingleRole::findOrFail($id);

        $request->validate([
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_single_roles', 'nama')
                    ->where('company_id', $request->company_id)
                    ->ignore($singleRole->id),
            ],
            'deskripsi' => 'nullable|string',
        ]);

        $singleRole->update($request->all());

        if ($request->ajax()) {
            // Pass the variable as `$role` to match the partial view expectation
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
        // Base query with relations
        $query = SingleRole::with(['tcodes', 'compositeRoles'])
            ->select('tr_single_roles.*');


        // Global search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('tr_single_roles.nama', 'like', "%{$searchValue}%")
                    ->orWhere('tr_single_roles.deskripsi', 'like', "%{$searchValue}%");
            });
        }

        // Clone before adding joins for accurate filtered count
        $recordsFiltered = (clone $query)->count();

        // Ordering (may add join)
        if ($request->filled('order.0.column')) {
            $orderableColumns = ['tr_single_roles.nama', 'tr_single_roles.deskripsi'];
            $columnIndex      = $request->input('order.0.column');
            $columnDirection  = $request->input('order.0.dir', 'asc');
            $columnName       = $orderableColumns[$columnIndex] ?? 'tr_single_roles.nama';

            $query->orderBy($columnName, $columnDirection);
        }

        // Pagination
        $singleRoles = $query
            ->skip(intval($request->start))
            ->take(intval($request->length))
            ->get();

        // Total records the user is allowed to see (before search)
        $totalQuery = SingleRole::query();
        $recordsTotal = $totalQuery->count();

        // Format rows
        $data = $singleRoles->map(function ($role) {
            return [
                'nama'      => $role->nama,
                'deskripsi' => $role->deskripsi,
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
