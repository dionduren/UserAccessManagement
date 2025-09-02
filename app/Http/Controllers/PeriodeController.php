<?php

namespace App\Http\Controllers;

use \App\Models\NIKJobRole;

use \App\Models\UserGenericUnitKerja;
use App\Http\Controllers\Controller;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

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
            'tanggal_create_periode' => 'required',
        ]);

        $request['tanggal_create_periode'] = Carbon::createFromFormat('Y-m-d\TH:i', $request['tanggal_create_periode'])
            ->format('Y-m-d H:i:s');

        Periode::create($request->all());

        $message = 'Periode dengan definisi ' . $request->definisi . ' berhasil dibuat.';

        // Duplikat Data User Generic dari Periode sebelumnya (n-1 dari latest)
        $previousPeriode = Periode::orderByDesc('id')->skip(1)->first();
        $latestPeriode = Periode::latest()->first();

        if ($previousPeriode) {
            // Duplikat Mapping User Generic - Unit Kerja dari periode sebelumnya
            $userGenericUnitKerja = UserGenericUnitKerja::where('periode_id', $previousPeriode->id)->get();

            foreach ($userGenericUnitKerja as $UserGenericUnitKerja) {
                $newuserGenericUnitKerja = $UserGenericUnitKerja->replicate();
                $newuserGenericUnitKerja->periode_id = $latestPeriode->id;
                $newuserGenericUnitKerja->save();
            }

            if ($userGenericUnitKerja->isEmpty()) {
                $message .= '<br><ul><li>Tidak ada data Mapping User Generic - Unit Kerja sebelumnya untuk diduplikat.</li>';
            } else {
                $message .= '<br><ul><li>Mapping User Generic - Unit Kerja berhasil diduplikat dari ' . $previousPeriode->definisi . '</li>';
            }

            // Duplikat Data User Generic - Job Role dari periode sebelumnya
            $USSMJobRoles = NIKJobRole::where('periode_id', $previousPeriode->id)->get();

            foreach ($USSMJobRoles as $USSMJobRole) {
                $newUSSMJobRole = $USSMJobRole->replicate();
                $newUSSMJobRole->periode_id = $latestPeriode->id;
                $newUSSMJobRole->save();
            }

            if ($USSMJobRoles->isEmpty()) {
                $message .= '<li>Tidak ada data Mapping User Cost Center - Job Role sebelumnya untuk diduplikat.</li></ul>';
            } else if ($USSMJobRoles->count() > 0) {
                $message .= '<li>Mapping User Cost Center - Job Role berhasil diduplikat dari ' . $previousPeriode->definisi . '</li></ul>';
            }
        } else {
            $message .= '<br><ul><li>Tidak ada data UAR sebelumnya untuk diduplikat.</li></ul>';
        }

        return redirect()->route('periode.index')->with('success', $message);
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
        $data = $request->validate([
            'definisi' => 'required|string',
            'tanggal_create_periode' => 'required',
            'is_active' => 'required|boolean',
        ]);

        // Convert "Y-m-d\TH:i" -> Carbon -> proper datetime (seconds added)
        $data['tanggal_create_periode'] = Carbon::createFromFormat('Y-m-d\TH:i', $data['tanggal_create_periode'])
            ->format('Y-m-d H:i:s');

        $periode->update($data);

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
