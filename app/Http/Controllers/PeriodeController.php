<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\NIKJobRole;
use App\Models\userGeneric;
use App\Models\UserGenericUnitKerja;

use App\Models\userNIK;
use App\Models\UserNIKUnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PeriodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user_company = auth()->user()?->loginDetail?->company_code ?? null;

        if ($request->ajax()) {
            $periodes = Periode::select('id', 'definisi', 'tanggal_create_periode');
            return DataTables::of($periodes)
                ->editColumn('tanggal_create_periode', function ($row) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', $row->tanggal_create_periode)->format('d M Y');
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('periode.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning me-2" >Edit</a>'
                        // return '<a target="_blank" class="btn btn-sm btn-outline-warning" disabled>Edit</a>';
                        . '<button onclick=\'deletePeriode(' . $row->id . ', ' . json_encode($row->definisi) . ')\' class="btn btn-sm btn-danger">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('master-data.periode.index', compact('user_company'));
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
                $message .= '<li>Tidak ada data Mapping User Generic - Job Role sebelumnya untuk diduplikat.</li></ul>';
            } else if ($USSMJobRoles->count() > 0) {
                $message .= '<li>Mapping User Generic - Job Role berhasil diduplikat dari ' . $previousPeriode->definisi . '</li></ul>';
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
    public function destroy(Request $request, Periode $periode)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $force = $request->boolean('force', false);

        // Opsional: larang hapus periode aktif kecuali pakai force
        if ($periode->is_active && !$force) {
            return response()->json([
                'message' => 'Periode aktif tidak boleh dihapus tanpa konfirmasi lanjutan.',
                'need_force' => true
            ], 422);
        }

        $pid = $periode->id;

        $summary = [
            'periode_id' => $pid,
            'periode_definisi' => $periode->definisi,
            'deleted' => [
                'user_generic' => 0,
                'user_nik' => 0,
                'user_generic_unit_kerja' => 0,
                'user_nik_unit_kerja' => 0,
                'nik_job_role' => 0,
                'periode' => 0,
            ]
        ];

        DB::beginTransaction();
        try {
            $summary['deleted']['nik_job_role'] = NIKJobRole::where('periode_id', $pid)->delete();
            $summary['deleted']['user_generic_unit_kerja'] = UserGenericUnitKerja::where('periode_id', $pid)->delete();
            $summary['deleted']['user_nik_unit_kerja'] = UserNIKUnitKerja::where('periode_id', $pid)->delete();
            $summary['deleted']['user_generic'] = userGeneric::where('periode_id', $pid)->delete();
            $summary['deleted']['user_nik'] = userNIK::where('periode_id', $pid)->delete();

            $periode->delete();
            $summary['deleted']['periode'] = 1;

            DB::commit();

            return response()->json([
                'message' => 'Periode & data terkait berhasil dihapus.',
                'summary' => $summary
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal hapus periode',
                'error' => $e->getMessage(),
                'summary' => $summary
            ], 500);
        }
    }
}
