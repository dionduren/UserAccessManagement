<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\SingleRole;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CompositeSingleController extends Controller
{

    // Display a listing of the resource.
    public function index()
    {
        return view('relationship.composite-single.index');
    }

    // Show the form for creating a new resource.
    public function create()
    {
        $companies = Company::all();
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
        $companies = Company::all();
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

    public function jsonIndex(Request $request)
    {
        $compositeSingles = CompositeRole::with('singleRoles')
            ->has('singleRoles')
            ->get();

        return DataTables::of($compositeSingles)
            ->addColumn('singleRoles', function ($compositeSingle) {
                return $compositeSingle->singleRoles->pluck('nama')->implode(', ');
            })
            ->addColumn('action', function ($compositeSingle) {
                return [
                    'edit_url' => route('composite-single.edit', $compositeSingle->id),
                    'delete_url' => route('composite-single.destroy', $compositeSingle->id),
                ];
            })
            ->toJson();
    }

    public function searchByCompany(Request $request)
    {
        $companyId = $request->input('company_id');
        $compositeRoles = CompositeRole::where('company_id', $companyId)->get();
        $singleRoles = SingleRole::where('company_id', $companyId)->get();

        return response()->json(['compositeRoles' => $compositeRoles, 'singleRoles' => $singleRoles]);
    }
}
