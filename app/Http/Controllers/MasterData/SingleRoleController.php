<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\SingleRole;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SingleRoleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('master-data.single_roles.index', compact('companies'));
    }

    // Show the details of a Single Role
    public function show($id)
    {
        $singleRole = SingleRole::with(['compositeRoles', 'company', 'tcodes'])->findOrFail($id);
        return view('master-data.single_roles.show', compact('singleRole'));
    }

    public function create()
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('master-data.single_roles.create', compact('companies'));
    }


    // Store a new Single Role
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:ms_company,company_code',
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
        $singleRole = SingleRole::findOrFail($id);

        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;
        $companies = $userCompanyCode === 'A000'
            ? Company::all()
            : Company::where('company_code', $userCompanyCode)->get();

        return view('master-data.single_roles.edit', compact('singleRole', 'companies'))->render();
    }


    public function update(Request $request, $id)
    {
        $singleRole = SingleRole::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:ms_company,company_code',
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
        $singleRole->delete();

        return redirect()->route('single-roles.index')->with('status', 'Single role deleted successfully.');
    }

    // public function getSingleRoles(Request $request)
    // {
    //     $query = SingleRole::with(['company', 'tcodes', 'compositeRoles']);

    //     // Filter by `company_id`
    //     if ($request->filled('company_id')) {
    //         $query->where('company_id', $request->company_id);
    //     }

    //     // Global search
    //     if ($request->filled('search.value')) {
    //         $searchValue = $request->input('search.value');
    //         $query->where(function ($q) use ($searchValue) {
    //             $q->where('nama', 'like', "%{$searchValue}%")
    //                 ->orWhere('deskripsi', 'like', "%{$searchValue}%")
    //                 ->orWhereHas('company', function ($companyQuery) use ($searchValue) {
    //                     $companyQuery->where('name', 'like', "%{$searchValue}%");
    //                 });
    //         });
    //     }
    // }

    //     // Join with `ms_company` for proper ordering by company name
    //     if ($request->filled('order.0.column')) {
    //         $orderableColumns = ['company', 'nama', 'deskripsi']; // Map columns by index
    //         $columnIndex = $request->input('order.0.column');
    //         $columnDirection = $request->input('order.0.dir', 'asc');
    //         $columnName = $orderableColumns[$columnIndex] ?? 'nama'; // Default to 'nama'

    //         if ($columnName === 'company') {
    //             $query->leftJoin('ms_company', 'ms_company.company_code', '=', 'tr_single_roles.company_id')
    //                 ->select('tr_single_roles.*', 'ms_company.nama as company_name')
    //                 ->orderBy('company_name', $columnDirection);
    //         } else {
    //             $query->orderBy($columnName, $columnDirection);
    //         }
    //     }

    //     // Get filtered and paginated data
    //     $recordsFiltered = $query->count();
    //     $singleRoles = $query->skip($request->start)->take($request->length)->get();

    //     // Format data for DataTable
    //     $data = $singleRoles->map(function ($role) {
    //         return [
    //             'company' => $role->company->nama ?? 'N/A',
    //             'nama' => $role->nama,
    //             'deskripsi' => $role->deskripsi,
    //             'actions' => view('master-data.single_roles.partials.actions', ['role' => $role])->render(),
    //         ];
    //     });

    //     return response()->json([
    //         'draw' => intval($request->draw),
    //         'recordsTotal' => SingleRole::count(), // Total number of records
    //         'recordsFiltered' => $recordsFiltered, // Total number of filtered records
    //         'data' => $data,
    //     ]);
    // }

    public function getSingleRoles(Request $request)
    {
        $user = auth()->user();
        $userCompanyCode = $user->loginDetail->company_code ?? null;

        // Base query with relations
        $query = SingleRole::with(['company', 'tcodes', 'compositeRoles'])
            ->select('tr_single_roles.*');

        // Company scope:
        // - If user is not A000, force their company
        // - If user is A000 (admin) allow optional company_id filter from request
        if ($userCompanyCode !== 'A000') {
            $query->where('tr_single_roles.company_id', $userCompanyCode);
        } elseif ($request->filled('company_id')) {
            $query->where('tr_single_roles.company_id', $request->company_id);
        }

        // Global search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('tr_single_roles.nama', 'like', "%{$searchValue}%")
                    ->orWhere('tr_single_roles.deskripsi', 'like', "%{$searchValue}%")
                    ->orWhereHas('company', function ($companyQuery) use ($searchValue) {
                        $companyQuery->where('nama', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Clone before adding joins for accurate filtered count
        $recordsFiltered = (clone $query)->count();

        // Ordering (may add join)
        if ($request->filled('order.0.column')) {
            $orderableColumns = ['company', 'tr_single_roles.nama', 'tr_single_roles.deskripsi'];
            $columnIndex      = $request->input('order.0.column');
            $columnDirection  = $request->input('order.0.dir', 'asc');
            $columnName       = $orderableColumns[$columnIndex] ?? 'tr_single_roles.nama';

            if ($columnName === 'company') {
                $query->leftJoin('ms_company', 'ms_company.company_code', '=', 'tr_single_roles.company_id')
                    ->addSelect('ms_company.nama as company_name')
                    ->orderBy('company_name', $columnDirection);
            } else {
                $query->orderBy($columnName, $columnDirection);
            }
        }

        // Pagination
        $singleRoles = $query
            ->skip(intval($request->start))
            ->take(intval($request->length))
            ->get();

        // Total records the user is allowed to see (before search)
        $totalQuery = SingleRole::query();
        if ($userCompanyCode !== 'A000') {
            $totalQuery->where('company_id', $userCompanyCode);
        } elseif ($request->filled('company_id')) {
            $totalQuery->where('company_id', $request->company_id);
        }
        $recordsTotal = $totalQuery->count();

        // Format rows
        $data = $singleRoles->map(function ($role) {
            return [
                'company'   => $role->company->nama ?? 'N/A',
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
