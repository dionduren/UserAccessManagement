<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Traits\AuditsActivity;
use App\Models\Tcode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TcodeController extends Controller
{
    use AuditsActivity;
    public function index()
    {
        $tcodes = Tcode::all();
        return view('master-data.tcodes.index', compact('tcodes'));
    }

    public function show($id)
    {
        $tcode = Tcode::findOrFail($id);
        return view('master-data.tcodes.show', compact('tcode'));
    }

    public function create()
    {
        return view('master-data.tcodes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:tr_tcodes,code',
            'sap_module' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);

        $request->merge(['source' => 'upload']);

        $tcode = Tcode::create($request->all());

        // Audit trail
        $this->auditCreate($tcode);

        return response()->json(['status' => 'success', 'message' => 'Tcode created successfully.']);
    }

    public function edit(Tcode $tcode)
    {
        return view('master-data.tcodes.edit', compact('tcode'));
    }

    public function update(Request $request, Tcode $tcode)
    {
        $request->validate([
            'code' => [
                'required',
                'string',
                Rule::unique('tr_tcodes', 'code')->ignore($tcode->code, 'code'),
            ],
            'sap_module' => 'nullable|string',
            'deskripsi' => 'nullable|string'
        ]);

        // Store original data for audit
        $originalData = $tcode->toArray();

        $tcode->update($request->all());

        // Audit trail
        $this->auditUpdate($tcode, $originalData);

        return response()->json(['status' => 'success', 'message' => 'Tcode updated successfully.']);
    }

    public function destroy(Tcode $tcode)
    {
        // Audit trail
        $this->auditDelete($tcode);

        $tcode->delete();

        return redirect()->route('tcodes.index')->with('status', 'Tcode deleted successfully.');
    }

    public function getTcodes(Request $request)
    {
        $query = Tcode::query();

        // Filter by global search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('sap_module', 'like', "%{$search}%");
            });
        }

        // Handle sorting
        if ($request->has('order.0.column')) {
            $columns = ['code', 'deskripsi', 'sap_module'];
            $columnIndex = $request->input('order.0.column');
            $sortDirection = $request->input('order.0.dir', 'asc');
            $query->orderBy($columns[$columnIndex] ?? 'code', $sortDirection);
        }

        // Return paginated data
        return DataTables::of($query)
            ->addColumn('actions', function ($tcode) {
                return view('master-data.tcodes.partials.actions', ['tcode' => $tcode])->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
