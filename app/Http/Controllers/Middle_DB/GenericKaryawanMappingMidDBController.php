<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use App\Models\middle_db\view\GenericKaryawanMappingFiltered;

use Illuminate\Http\Request;

class GenericKaryawanMappingMidDBController extends Controller
{
    public function index()
    {
        // Sesuaikan dengan lokasi blade yang sebelumnya dibuat
        return view('middle_db.generic_karyawan_mapping.index');
    }

    public function data(Request $request)
    {
        $rows = GenericKaryawanMappingFiltered::query()
            ->orderBy('user_full_name')
            ->orderBy('company')
            ->orderBy('personnel_number')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                 => $r->id,
                    'company'            => $r->company,
                    'sap_user_id'        => $r->sap_user_id,
                    'user_full_name'     => $r->user_full_name,
                    'personnel_number'   => $r->personnel_number,
                    'employee_full_name' => $r->employee_full_name,
                    'duplicate_name'     => $r->duplicate_name,
                    'filtered_in'        => $r->filtered_in,
                ];
            });

        return response()->json(['data' => $rows]);
    }
}
