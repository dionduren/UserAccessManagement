<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;

use App\Models\middle_db\UnitKerja;

use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index()
    {
        return view('middle_db.unit_kerja.index');
    }

    public function data()
    {
        $rows = UnitKerja::query()
            ->select([
                'id',
                'company',
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
        $result = UnitKerja::syncFromExternal();
        return response()->json([
            'status'   => 'ok',
            'inserted' => $result['inserted'],
        ]);
    }
}
