<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SingleRole;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SingleRoleController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('single_roles.index', compact('companies'));
    }

    // Show the details of a Single Role
    public function show($id)
    {
        $singleRole = SingleRole::with(['compositeRoles', 'company', 'tcodes'])->findOrFail($id);
        return view('single_roles.show', compact('singleRole'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('single_roles.create', compact('companies'));
    }


    // Store a new Single Role
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
            'nama' => [
                'required',
                'string',
                Rule::unique('tr_single_roles', 'nama')
                    ->where('company_id', $request->company_id)
            ],
            'deskripsi' => 'nullable|string',
        ]);

        $singleRole = SingleRole::create($request->all());

        // Check if the request is an AJAX request
        if ($request->ajax()) {
            // Return HTML for the table row or a success message
            $view = view('single_roles.partials.actions', compact('singleRole'))->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role created successfully.');
    }

    public function edit($id)
    {
        $singleRole = SingleRole::findOrFail($id);
        $companies = Company::all();

        // Render the view and pass the data to it
        return view('single_roles.edit', compact('singleRole', 'companies'))->render();
    }


    public function update(Request $request, $id)
    {
        $singleRole = SingleRole::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:ms_company,id',
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
            $view = view('single_roles.partials.actions', ['role' => $singleRole])->render();
            return response()->json(['status' => 'success', 'html' => $view]);
        }

        return redirect()->route('single-roles.index')->with('status', 'Single Role updated successfully.');
    }

    public function destroy(SingleRole $singleRole)
    {
        $singleRole->delete();

        return redirect()->route('single-roles.index')->with('status', 'Single role deleted successfully.');
    }

    public function getSingleRoles(Request $request)
    {
        $query = SingleRole::with(['company', 'tcodes', 'compositeRoles']);

        // Filter by `company_id`
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Global search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('nama', 'like', "%{$searchValue}%")
                    ->orWhere('deskripsi', 'like', "%{$searchValue}%")
                    ->orWhereHas('company', function ($companyQuery) use ($searchValue) {
                        $companyQuery->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Join with `ms_company` for proper ordering by company name
        if ($request->filled('order.0.column')) {
            $orderableColumns = ['company', 'nama', 'deskripsi']; // Map columns by index
            $columnIndex = $request->input('order.0.column');
            $columnDirection = $request->input('order.0.dir', 'asc');
            $columnName = $orderableColumns[$columnIndex] ?? 'nama'; // Default to 'nama'

            if ($columnName === 'company') {
                $query->leftJoin('ms_company', 'ms_company.id', '=', 'tr_single_roles.company_id')
                    ->select('tr_single_roles.*', 'ms_company.name as company_name')
                    ->orderBy('company_name', $columnDirection);
            } else {
                $query->orderBy($columnName, $columnDirection);
            }
        }

        // Get filtered and paginated data
        $recordsFiltered = $query->count();
        $singleRoles = $query->skip($request->start)->take($request->length)->get();

        // Format data for DataTable
        $data = $singleRoles->map(function ($role) {
            return [
                'company' => $role->company->name ?? 'N/A',
                'nama' => $role->nama,
                'deskripsi' => $role->deskripsi,
                'actions' => view('single_roles.partials.actions', ['role' => $role])->render(),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => SingleRole::count(), // Total number of records
            'recordsFiltered' => $recordsFiltered, // Total number of filtered records
            'data' => $data,
        ]);
    }
}
