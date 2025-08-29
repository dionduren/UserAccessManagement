<?php

namespace App\Http\Controllers\Relationship;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LocalUAMRelationshipController extends Controller
{
    public function index()
    {
        return view('relationship.uam.index');
    }

    /**
     * Return JSON (no server-side filtering here; client DataTables will handle column search).
     */
    public function data(Request $request)
    {
        $rows = DB::table('tr_composite_roles as cr')
            ->join('pt_composite_role_single_role as crsr', 'crsr.composite_role_id', '=', 'cr.id')
            ->join('tr_single_roles as sr', 'sr.id', '=', 'crsr.single_role_id')
            ->leftJoin('pt_single_role_tcode as srt', 'srt.single_role_id', '=', 'sr.id')
            ->leftJoin('tr_tcodes as tc', 'tc.id', '=', 'srt.tcode_id')
            ->selectRaw('
                cr.id as composite_role_id,
                cr.nama as composite_role,
                cr.deskripsi as composite_role_desc,
                sr.id as single_role_id,
                sr.nama as single_role,
                sr.deskripsi as single_role_desc,
                tc.code as tcode,
                tc.deskripsi as tcode_desc,
                cr.updated_at as updated_at
            ')
            ->orderBy('cr.nama')
            ->orderBy('sr.nama')
            ->orderBy('tc.code')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }
}
