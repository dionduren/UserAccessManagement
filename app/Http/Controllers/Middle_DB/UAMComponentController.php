<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\middle_db\CompositeRole;
use App\Models\middle_db\SingleRole;
use App\Models\middle_db\Tcode;

class UAMComponentController extends Controller
{
    public function compositeRole()
    {
        return view('middle_db.uam.composite_role.index');
    }


    public function compositeData()
    {
        $rows = DB::table('mdb_composite_role')
            ->select('id', 'composite_role', 'definisi', 'created_at')
            ->orderBy('composite_role')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function compositeSync(Request $request)
    {
        $like = $request->get('like', 'ZM-%');
        $exclude = $request->get('exclude', '%-AO');
        $r = CompositeRole::syncFromExternal($like, $exclude);

        return response()->json(['status' => 'ok', 'inserted' => $r['inserted']]);
    }


    public function singleRole()
    {
        return view('middle_db.uam.single_role.index');
    }

    public function singleData()
    {
        $rows = DB::table('mdb_single_role')
            ->select('id', 'single_role', 'definisi', 'created_at')
            ->orderBy('single_role')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function singleSync(Request $request)
    {
        $like1 = $request->get('like1', 'ZS-%');
        $like2 = $request->get('like2', '%-AO%');
        $r = SingleRole::syncFromExternal($like1, $like2);

        return response()->json(['status' => 'ok', 'inserted' => $r['inserted']]);
    }

    public function tcode()
    {
        return view('middle_db.uam.tcode.index');
    }

    public function tcodeData()
    {
        $rows = DB::table('mdb_tcode')
            ->select('id', 'tcode', 'definisi', 'created_at')
            ->orderBy('tcode')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function tcodeSync()
    {
        $r = Tcode::syncFromExternal();

        return response()->json(['status' => 'ok', 'inserted' => $r['inserted']]);
    }
}
