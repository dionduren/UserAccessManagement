<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\middle_db\MasterDataKaryawan;
use App\Models\middle_db\raw\DuplicateNameFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicateNameController extends Controller
{
    /**
     * Halaman baru: menampilkan SEMUA baris MasterDataKaryawan
     * yang memiliki nama duplikat (nama muncul > 1 kali).
     * Tidak lagi dikelompokkan; user pilih sendiri mana yang mau dimasukkan ke filter.
     */
    public function index()
    {
        return view('middle_db.master_data_karyawan.duplicates');
    }

    /**
     * Data JSON (non-aggregated) untuk DataTable.
     * Mengambil setiap baris yang namanya termasuk dalam set nama dengan COUNT(*) > 1.
     * Optional filter per company (?per_company=1) agar duplikasi dihitung per company.
     */
    public function data(Request $request)
    {
        $perCompany = (int)$request->query('per_company', 0) === 1;

        // Subquery nama duplikat
        if ($perCompany) {
            // Duplikat dalam konteks (company, nama)
            $dupNames = MasterDataKaryawan::select('company', 'nama')
                ->groupBy('company', 'nama')
                ->havingRaw('COUNT(*) > 1');
            $base = MasterDataKaryawan::query()
                ->joinSub($dupNames, 'd', function ($j) {
                    $j->on('master_data_karyawan.company', '=', 'd.company')
                        ->on('master_data_karyawan.nama', '=', 'd.nama');
                });
        } else {
            // Duplikat berdasarkan nama saja (lintas company)
            $dupNames = MasterDataKaryawan::select('nama')
                ->groupBy('nama')
                ->havingRaw('COUNT(*) > 1');
            $base = MasterDataKaryawan::query()
                ->whereIn('nama', $dupNames->pluck('nama'));
        }

        // Ambil nik yang sudah ada di filter untuk penandaan
        $filteredNik = DuplicateNameFilter::pluck('nik')->toBase();

        $rows = $base
            ->select([
                'nik',
                'nama',
                'company',
                'departemen',
                'kompartemen',
            ])
            ->orderBy('nama')
            ->orderBy('company')
            ->orderBy('nik')
            ->get()
            ->map(function ($r) use ($filteredNik) {
                return [
                    'nik'         => $r->nik,
                    'nama'        => $r->nama,
                    'company'     => $r->company,
                    'departemen'  => $r->departemen,
                    'kompartemen' => $r->kompartemen,
                    'in_filter'   => $filteredNik->contains($r->nik),
                ];
            });

        return response()->json(['data' => $rows]);
    }

    /**
     * Simpan beberapa baris terpilih (berdasarkan daftar NIK) ke DuplicateNameFilter.
     * Body: { niks: ["123","456"], require_same_name: (optional) }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'niks' => 'required|array|min:1',
            'niks.*' => 'string',
        ]);

        $niks = collect($data['niks'])->filter()->unique()->values();
        if ($niks->isEmpty()) {
            return response()->json(['status' => 'ok', 'inserted' => 0, 'existing' => 0, 'message' => 'No NIK provided']);
        }

        // Ambil baris master untuk NIK terpilih
        $masters = MasterDataKaryawan::whereIn('nik', $niks)->get(['nik', 'nama', 'company']);
        if ($masters->isEmpty()) {
            return response()->json(['status' => 'ok', 'inserted' => 0, 'existing' => 0, 'message' => 'No matching master rows']);
        }

        $inserted = 0;
        $existing = 0;
        $now = now();
        $user = auth()->user() ? auth()->user()->username : 'system';

        foreach ($masters as $m) {
            DuplicateNameFilter::create([
                'company_id' => $m->company,
                'nik'        => $m->nik,
                'nama'       => $m->nama,
                'created_by' => $user,
                'updated_by' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
        }

        return response()->json([
            'status'   => 'ok',
            'inserted' => $inserted,
            'existing' => $existing,
            'message'  => 'Selected duplicates processed'
        ]);
    }
}
