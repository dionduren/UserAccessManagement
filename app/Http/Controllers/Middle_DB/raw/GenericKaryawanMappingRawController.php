<?php

namespace App\Http\Controllers\Middle_DB\raw;

use App\Http\Controllers\Controller;
use App\Models\middle_db\raw\GenericKaryawanMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenericKaryawanMappingRawController extends Controller
{
    public function index()
    {
        return view('middle_db.raw.generic_karyawan_mapping.index');
    }

    public function data()
    {
        $rows = DB::table('mdb_usmm_generic_karyawan_mapping')
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function sync(Request $request)
    {
        $res = GenericKaryawanMapping::syncFromExternal();

        return response()->json([
            'status'   => 'ok',
            'inserted' => $res['inserted'],
        ]);
    }
}
