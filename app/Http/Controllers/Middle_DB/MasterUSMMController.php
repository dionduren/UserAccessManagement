<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use App\Models\middle_db\MasterUSMM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterUSMMController extends Controller
{
    public function index()
    {
        return view('middle_db.usmm_master.index');
    }

    public function data(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = DB::table('mdb_usmm_master')
            // Exclude records where valid_to date is in the past (allow null or 00000000 = open-ended)
            ->where(function ($w) {
                $w->whereNull('valid_to')
                    ->orWhere('valid_to', '00000000')
                    ->orWhereRaw("to_date(valid_to, 'YYYYMMDD') >= current_date");
            })
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('sap_user_id', 'ILIKE', $like)
                        ->orWhere('full_name', 'ILIKE', $like)
                        ->orWhere('company', 'ILIKE', $like)
                        ->orWhere('department', 'ILIKE', $like);
                });
            })
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function activeGeneric()
    {
        return view('middle_db.usmm_master.active_generic');
    }

    public function activeGenericData(Request $request)
    {
        // 

        $q = trim((string) $request->get('q', ''));

        $rows = DB::table('mdb_usmm_master')
            // Exclude records where valid_to date is in the past (allow null or 00000000 = open-ended)
            ->where(function ($w) {
                $w->whereNull('valid_to')
                    ->orWhere('valid_to', '00000000')
                    ->orWhereRaw("to_date(valid_to, 'YYYYMMDD') >= current_date");
            })
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('sap_user_id', 'ILIKE', $like)
                        ->orWhere('full_name', 'ILIKE', $like)
                        ->orWhere('company', 'ILIKE', $like)
                        ->orWhere('department', 'ILIKE', $like);
                });
            })
            ->whereRaw("sap_user_id ~* '^[A-K]'")
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function activeNIK()
    {
        return view('middle_db.usmm_master.active_nik');
    }

    public function activeNIKData(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = DB::table('mdb_usmm_master')
            // Exclude records where valid_to date is in the past (allow null or 00000000 = open-ended)
            ->where(function ($w) {
                $w->whereNull('valid_to')
                    ->orWhere('valid_to', '00000000')
                    ->orWhereRaw("to_date(valid_to, 'YYYYMMDD') >= current_date");
            })
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('sap_user_id', 'ILIKE', $like)
                        ->orWhere('full_name', 'ILIKE', $like)
                        ->orWhere('company', 'ILIKE', $like)
                        ->orWhere('department', 'ILIKE', $like);
                });
            })
            ->whereRaw("sap_user_id ~* '^[0-9]'")
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /** View: User Tidak Aktif > 6 Bulan */
    public function inactive()
    {
        return view('middle_db.usmm_master.inactive');
    }

    /** Data: User Tidak Aktif > 6 Bulan (last_logon_date < current_date-6 months) */
    public function inactiveData()
    {
        $rows = DB::table('mdb_usmm_master')
            ->whereNotNull('last_logon_date')
            ->whereRaw("to_date(last_logon_date, 'YYYYMMDD') < (current_date - interval '6 months')")
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /** View: User Valid To < Today (if valid_to != 00000000) */
    public function expired()
    {
        return view('middle_db.usmm_master.expired');
    }

    /** Data: valid_to < today with valid_to != '00000000' */
    public function expiredData()
    {
        $rows = DB::table('mdb_usmm_master')
            ->where('valid_to', '<>', '00000000')
            ->whereNotNull('valid_to')
            ->whereRaw("to_date(valid_to, 'YYYYMMDD') < current_date")
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /** Sync dari SQL Server (FreeTDS) ke Postgres lokal */
    public function sync()
    {
        $res = MasterUSMM::syncFromExternal();
        return response()->json([
            'status'   => 'ok',
            'inserted' => $res['inserted'],
        ]);
    }

    public function all()
    {
        return view('middle_db.usmm_master.all_data');
    }

    public function allData(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = DB::table('mdb_usmm_master')
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('sap_user_id', 'ILIKE', $like)
                        ->orWhere('full_name', 'ILIKE', $like)
                        ->orWhere('company', 'ILIKE', $like)
                        ->orWhere('department', 'ILIKE', $like);
                });
            })
            ->orderBy('sap_user_id')
            ->get();

        return response()->json(['data' => $rows]);
    }
}
