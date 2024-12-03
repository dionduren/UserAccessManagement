<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TcodeController extends Controller
{
    public function index()
    {
        $tcodes = Tcode::all();
        return view('tcodes.index', compact('tcodes'));
    }

    public function show($id)
    {
        $tcode = Tcode::findOrFail($id);
        return view('tcodes.show', compact('tcode'));
    }

    public function create()
    {
        return view('tcodes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:tr_tcodes,code',
            'sap_module' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);

        Tcode::create($request->all());

        return response()->json(['status' => 'success', 'message' => 'Tcode created successfully.']);
    }

    public function edit(Tcode $tcode)
    {
        return view('tcodes.edit', compact('tcode'));
    }

    public function update(Request $request, Tcode $tcode)
    {
        $request->validate([
            'code' => 'required|string|unique:tr_tcodes,code,' . $tcode->id,
            'sap_module' => 'nullable|string',
            'deskripsi' => 'nullable|string'
        ]);

        $tcode->update($request->all());

        return response()->json(['status' => 'success', 'message' => 'Tcode updated successfully.']);
    }

    public function destroy(Tcode $tcode)
    {
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
                return view('tcodes.partials.actions', ['tcode' => $tcode])->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
