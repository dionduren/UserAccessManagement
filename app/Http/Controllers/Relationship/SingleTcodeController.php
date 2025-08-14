<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\SingleRole;

use Yajra\DataTables\Facades\DataTables;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SingleTcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $singleTcodes = Tcode::with('singleRoles')->get();

        return view('relationship.single-tcode.index', compact('singleTcodes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        $singleRoles = SingleRole::all();
        $tcodes = Tcode::all();

        return view('relationship.single-tcode.create', compact('companies', 'singleRoles', 'tcodes'));
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
        $companies = Company::with('singleRoles')->get();
        $singleRole = SingleRole::with('tcodes')->findOrFail($id);
        $tcodes = Tcode::all();
        $selectedTcodes = $singleRole->tcodes->pluck('code')->toArray();

        return view('relationship.single-tcode.edit', compact(
            'singleRole',
            'companies',
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


    /**
     * Search the tcodes and single roles based on the company id
     */
    public function searchByCompany(Request $request)
    {
        $companyId = $request->input('company_id');
        $tcodes = Tcode::whereHas('singleRoles', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->get();
        $singleRoles = SingleRole::where('company_id', $companyId)->get();

        return response()->json(['tcodes' => $tcodes, 'singleRoles' => $singleRoles]);
    }
}
