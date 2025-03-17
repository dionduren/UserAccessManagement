<?php

namespace App\Http\Controllers;

use App\Models\Periode;

use Yajra\DataTables\DataTables;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PeriodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $periodes = Periode::select('id', 'definisi', 'tanggal_create_periode');
            return DataTables::of($periodes)
                ->editColumn('tanggal_create_periode', function ($row) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', $row->tanggal_create_periode)->format('d M Y');
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('periode.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning" disabled>Edit</a>';
                    // return '<a target="_blank" class="btn btn-sm btn-outline-warning" disabled>Edit</a>';
                    // <button onclick="deletePeriode(' . $row->id . ')" class="btn btn-sm btn-danger" disabled>Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('master-data.periode.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master-data.periode.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'definisi' => 'required|string|max:255|unique:ms_periode,definisi',
        ]);

        $request->request->add(['tanggal_create_periode' => date('Y-m-d H:i:s')]);

        Periode::create($request->all());

        // Duplikat Data User Generic dari Periode sebelumnya
        $latestPeriode = Periode::latest()->first();

        if ($latestPeriode) {
            $periodeId = $latestPeriode->id;
            $userGenerics = \App\Models\UserGeneric::all();
            foreach ($userGenerics as $userGeneric) {
                $newUserGeneric = $userGeneric->replicate();
                $newUserGeneric->periode_id = $periodeId;
                $newUserGeneric->save();
            }
        }

        return redirect()->route('periode.index')->with('success', 'Periode  dengan definisi ' . $request->definisi . ' berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Periode $periode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Periode $periode)
    {
        return view('master-data.periode.edit', compact('periode'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Periode $periode)
    {
        $request->validate([
            'definisi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ms_periode', 'definisi')->ignore($periode->id),
            ],
        ]);

        $periode->update($request->all());

        return redirect()->route('periode.index')->with('success', 'Periode dengan definisi ' . $request->definisi . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Periode $periode)
    {
        //
    }
}
