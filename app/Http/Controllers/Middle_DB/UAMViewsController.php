<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UAMViewsController extends Controller
{
    /* ===== Relationship Pages ===== */
    public function userComposite()
    {
        return view('middle_db.view.user_composite.index');
    }
    public function compositeSingle()
    {
        return view('middle_db.view.composite_single.index');
    }
    public function singleTcode()
    {
        return view('middle_db.view.single_tcode.index');
    }
    public function compositeSingleAO()
    {
        return view('middle_db.view.composite_ao.index');
    }

    /* ===== Master Pages ===== */
    public function compositeMaster()
    {
        return view('middle_db.view.composite_master.index');
    }
    public function singleMaster()
    {
        return view('middle_db.view.single_master.index');
    }
    public function tcodeMaster()
    {
        return view('middle_db.view.tcode_master.index');
    }

    /* ===== Simple Data Endpoints (unchanged) ===== */
    public function userCompositeData()
    {
        $rows = DB::table('v_uam_user_composite')
            ->select('sap_user', 'composite_role')
            ->orderBy('sap_user')->orderBy('composite_role')->get();
        return response()->json(['data' => $rows]);
    }

    public function compositeMasterData()
    {
        $rows = DB::table('v_uam_composite_master')
            ->select('composite_role', 'composite_role_desc')
            ->orderBy('composite_role')->get();
        return response()->json(['data' => $rows]);
    }
    public function singleMasterData()
    {
        $rows = DB::table('v_uam_single_master')
            ->select('single_role', 'single_role_desc')
            ->orderBy('single_role')->get();
        return response()->json(['data' => $rows]);
    }
    public function tcodeMasterData()
    {
        $rows = DB::table('v_uam_tcode_master')
            ->select('tcode', 'tcode_desc')
            ->orderBy('tcode')->get();
        return response()->json(['data' => $rows]);
    }

    /* ===== Composite - Single (pagination by composite) ===== */
    public function compositeSingleData(Request $request)
    {
        $draw    = (int)$request->input('draw');
        $start   = (int)$request->input('start', 0);
        $length  = (int)$request->input('length', 10);
        $comp    = trim($request->input('comp', ''));
        $single  = trim($request->input('single', ''));

        $recordsTotal = DB::table('v_uam_composite_single')->distinct()->count('composite_role');

        $distinct = DB::table('v_uam_composite_single as vcs')
            ->select('vcs.composite_role')
            ->distinct();

        if ($comp !== '') {
            $distinct->where('vcs.composite_role', 'LIKE', "%{$comp}%");
        }
        if ($single !== '') {
            $distinct->whereExists(function ($q) use ($single) {
                $q->select(DB::raw(1))
                    ->from('v_uam_composite_single as v2')
                    ->whereColumn('v2.composite_role', 'vcs.composite_role')
                    ->where('v2.single_role', 'LIKE', "%{$single}%");
            });
        }

        $recordsFiltered = (clone $distinct)->count();
        $distinct->orderBy('vcs.composite_role', 'asc');

        $pageCompositeRoles = $distinct->skip($start)->take($length)->pluck('composite_role')->toArray();

        if (!$pageCompositeRoles) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => []
            ]);
        }

        $pairsQ = DB::table('v_uam_composite_single')
            ->whereIn('composite_role', $pageCompositeRoles)
            ->orderBy('composite_role')
            ->orderBy('single_role');

        if ($single !== '') {
            $pairsQ->where('single_role', 'LIKE', "%{$single}%");
        }

        $pairs = $pairsQ->get();
        $data = [];
        foreach ($pairs as $p) {
            $data[] = [
                'composite_role' => $p->composite_role,
                'single_role' => $p->single_role,
                'group_key' => $p->composite_role,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /* ===== Single - Tcode (pagination by single_role) ===== */
    public function singleTcodeData(Request $request)
    {
        $draw    = (int)$request->input('draw');
        $start   = (int)$request->input('start', 0);    // offset in distinct single_role list
        $length  = (int)$request->input('length', 10);  // singles per page
        $single  = trim($request->input('single', ''));
        $tcode   = trim($request->input('tcode', ''));

        // Total distinct single roles (unfiltered)
        $recordsTotal = DB::table('v_uam_single_tcode')->distinct()->count('single_role');

        // Distinct singles with filters
        $distinct = DB::table('v_uam_single_tcode as vst')
            ->select('vst.single_role')
            ->distinct();

        if ($single !== '') {
            $distinct->where('vst.single_role', 'LIKE', "%{$single}%");
        }
        if ($tcode !== '') {
            $distinct->whereExists(function ($q) use ($tcode) {
                $q->select(DB::raw(1))
                    ->from('v_uam_single_tcode as v2')
                    ->whereColumn('v2.single_role', 'vst.single_role')
                    ->where('v2.tcode', 'LIKE', "%{$tcode}%");
            });
        }

        $recordsFiltered = (clone $distinct)->count();
        $distinct->orderBy('vst.single_role', 'asc');

        $pageSingles = $distinct->skip($start)->take($length)->pluck('single_role')->toArray();

        if (!$pageSingles) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => []
            ]);
        }

        // Fetch tcodes for those singles (apply tcode filter to reduce shown rows)
        $pairsQ = DB::table('v_uam_single_tcode')
            ->whereIn('single_role', $pageSingles)
            ->orderBy('single_role')
            ->orderBy('tcode');

        if ($tcode !== '') {
            $pairsQ->where('tcode', 'LIKE', "%{$tcode}%");
        }

        $pairs = $pairsQ->get();

        $data = [];
        foreach ($pairs as $p) {
            $data[] = [
                'single_role' => $p->single_role,
                'tcode'       => $p->tcode,
                'group_key'   => $p->single_role,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,      // total distinct singles (unfiltered)
            'recordsFiltered' => $recordsFiltered,   // filtered distinct singles
            'data'            => $data,
        ]);
    }

    public function compositeSingleAOData(Request $request)
    {
        $draw    = (int)$request->input('draw');
        $start   = (int)$request->input('start', 0);
        $length  = (int)$request->input('length', 10);
        $comp    = trim($request->input('comp', ''));
        $single  = trim($request->input('single', ''));

        $baseView = 'v_uam_composite_single_ao';

        // Total distinct AO composites
        $recordsTotal = DB::table($baseView)->distinct()->count('composite_role');

        $distinct = DB::table($baseView . ' as vcs')
            ->select('vcs.composite_role')
            ->distinct();

        if ($comp !== '') {
            $distinct->where('vcs.composite_role', 'LIKE', "%{$comp}%");
        }
        if ($single !== '') {
            $distinct->whereExists(function ($q) use ($single, $baseView) {
                $q->select(DB::raw(1))
                    ->from($baseView . ' as v2')
                    ->whereColumn('v2.composite_role', 'vcs.composite_role')
                    ->where('v2.single_role', 'LIKE', "%{$single}%");
            });
        }

        $recordsFiltered = (clone $distinct)->count();
        $distinct->orderBy('vcs.composite_role');

        $pageCompositeRoles = $distinct->skip($start)->take($length)->pluck('composite_role')->toArray();

        if (!$pageCompositeRoles) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => [],
            ]);
        }

        $pairsQ = DB::table($baseView)
            ->whereIn('composite_role', $pageCompositeRoles)
            ->orderBy('composite_role')
            ->orderBy('single_role');

        if ($single !== '') {
            $pairsQ->where('single_role', 'LIKE', "%{$single}%");
        }

        $pairs = $pairsQ->get();

        $data = [];
        foreach ($pairs as $p) {
            $data[] = [
                'composite_role' => $p->composite_role,
                'single_role'    => $p->single_role,
                'group_key'      => $p->composite_role,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
