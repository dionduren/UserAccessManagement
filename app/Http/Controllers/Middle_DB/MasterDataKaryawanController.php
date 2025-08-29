<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\middle_db\MasterDataKaryawan;
use App\Models\middle_db\raw\DuplicateNameFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataKaryawanController extends Controller
{
    public function index()
    {
        return view('middle_db.master_data_karyawan.index');
    }

    public function data()
    {
        $rows = MasterDataKaryawan::query()
            ->select([
                'id',
                'company',
                'nik',
                'nama',
                'direktorat',
                'direktorat_id',
                'kompartemen_id',
                'kompartemen',
                'departemen_id',
                'departemen',
                'cost_center'
            ])
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function sync()
    {
        $result = MasterDataKaryawan::syncFromExternal();
        return response()->json([
            'status'   => 'ok',
            'inserted' => $result['inserted'],
        ]);
    }
}
