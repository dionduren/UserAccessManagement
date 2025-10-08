<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\userGenericSystem;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;

class UserSystemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if (!$request->filled('periode')) {
                return DataTables::of(collect([]))->make(true);
            }

            $query = userGenericSystem::with(['periode', 'costCenter', 'company'])
                ->select(
                    'id',
                    'periode_id',
                    'group',
                    'user_code',
                    'user_type',
                    'user_profile',
                    'license_type',
                    'cost_code',
                    'last_login',
                    'valid_from',
                    'valid_to',
                    'flagged',
                    'uar_listed'
                )
                ->when(
                    $request->filled('periode'),
                    fn($q) =>
                    $q->where('periode_id', $request->input('periode'))
                );

            return DataTables::of($query)
                ->editColumn('valid_from', fn($r) => $r->valid_from ? Carbon::parse($r->valid_from)->format('d M Y') : '-')
                ->editColumn('valid_to', fn($r) => $r->valid_to ? Carbon::parse($r->valid_to)->format('d M Y') : '-')
                ->editColumn('last_login', fn($r) => $r->last_login ? $r->last_login->format('d M Y H:i') : '-')
                ->addColumn('periode', fn($r) => $r->periode?->definisi ?? '-')
                ->addColumn('cost_center', fn($r) => $r->costCenter->cost_center ?? '-')
                ->addColumn('company_name', fn($r) => $r->company->nama ?? '-')
                ->addColumn('action', function ($r) {
                    return '<a href="' . route('user-system.edit', $r->id) . '" class="btn btn-sm btn-outline-warning">Edit</a>
                        <button onclick="deleteUserSystem(' . $r->id . ')" class="btn btn-sm btn-outline-danger">Delete</button>
                        <a href="' . route('user-system.flagged-edit', $r->id) . '" class="btn btn-sm btn-outline-primary">Flagged</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $periodes = Periode::select('id', 'definisi')->get();
        return view('master-data.user_system.index', compact('periodes'));
    }

    public function edit($id)
    {
        $record = userGenericSystem::findOrFail($id);
        $periodes = Periode::select('id', 'definisi')->get();
        return view('master-data.user_system.edit', compact('record', 'periodes'));
    }

    public function update(Request $request, $id)
    {
        $record = userGenericSystem::findOrFail($id);

        $validated = $request->validate([
            'periode_id'   => 'required|exists:ms_periode,id',
            'group'        => 'nullable|string|max:50',
            'user_code'    => 'required|string|max:50',
            'user_profile' => 'nullable|string',
            'user_type'    => 'nullable|string|max:50',
            'cost_code'    => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:50',
            'valid_from'   => 'nullable|date',
            'valid_to'     => 'nullable|date',
            'last_login'   => 'nullable|date',
            'keterangan'   => 'nullable|string',
        ]);

        $record->update($validated);

        return redirect()->route('user-system.index')->with('success', 'Updated.');
    }

    public function destroy(userGenericSystem $userSystem)
    {
        $userSystem->delete();
        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }

    public function editFlagged($id)
    {
        $record = userGenericSystem::findOrFail($id);
        $periodes = Periode::select('id', 'definisi')->get();
        return view('master-data.user_system.edit-flagged', compact('record', 'periodes'));
    }

    public function updateFlag(Request $request, $id)
    {
        $request->validate([
            'flagged' => 'required|boolean',
        ]);
        $record = userGenericSystem::findOrFail($id);
        $record->flagged = $request->input('flagged');
        $record->save();
        return redirect()->route('user-system.index')->with('success', 'Flag updated.');
    }
}
