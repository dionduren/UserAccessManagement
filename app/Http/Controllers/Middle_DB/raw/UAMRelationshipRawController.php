<?php

namespace App\Http\Controllers\Middle_DB\raw;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\middle_db\raw\UAMRelationshipRAW;
use Illuminate\Support\Facades\DB;

class UAMRelationshipRawController extends Controller
{
    public function index()
    {
        return view('middle_db.raw.uam_relationship.index');
    }

    public function data(Request $request)
    {
        $rows = DB::table('mdb_uam_relationship_raw')
            ->select(
                'id',
                'sap_user',
                'composite_role',
                'composite_role_desc',
                'single_role',
                'single_role_desc',
                'tcode',
                'tcode_desc',
                'created_at'
            )
            ->orderBy('sap_user')
            ->orderBy('composite_role')
            ->orderBy('single_role')
            ->orderBy('tcode')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function sync(Request $request)
    {
        $like = $request->get('like', 'Z%');
        $result = UAMRelationshipRAW::syncFromExternal($like);

        return response()->json([
            'status'   => 'ok',
            'inserted' => $result['inserted'],
        ]);
    }
}
