<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WorkUnitService
{
    public static function getGroupedData($periodeId, $filters = [])
    {
        $query = DB::table('ms_user_detail as ud')
            ->leftJoin('tr_nik_job_role as njr', function ($join) use ($periodeId) {
                $join->on('njr.nik', '=', 'ud.nik')
                    ->where('njr.periode_id', '=', $periodeId);
            })
            ->leftJoin('tr_job_roles as jr', 'jr.job_role_id', '=', 'njr.job_role_id')
            ->leftJoin('ms_company as c', 'c.company_code', '=', 'ud.company_id')
            ->leftJoin('ms_kompartemen as k', 'k.kompartemen_id', '=', 'ud.kompartemen_id')
            ->leftJoin('ms_departemen as d', 'd.departemen_id', '=', 'ud.departemen_id')
            ->leftJoin('tr_composite_roles as cr', 'cr.jabatan_id', '=', 'jr.id')
            ->leftJoin('pt_composite_role_single_role as cr_sr', 'cr.id', '=', 'cr_sr.composite_role_id')
            ->leftJoin('tr_single_roles as sr', 'sr.id', '=', 'cr_sr.single_role_id')
            ->leftJoin('pt_single_role_tcode as sr_tc', 'sr.id', '=', 'sr_tc.single_role_id')
            ->leftJoin('tr_tcodes as tc', 'tc.id', '=', 'sr_tc.tcode_id')
            ->selectRaw('
                ud.nik,
                ud.nama,
                c.nama AS company,
                k.nama AS kompartemen,
                d.nama AS departemen,
                jr.id AS job_role_id,
                jr.nama AS job_role,
                cr.id AS composite_role_id,
                cr.nama AS composite_role,
                sr.id AS single_role_id,
                sr.nama AS single_role,
                sr.deskripsi AS single_role_desc,
                tc.code AS tcode,
                tc.sap_module,
                tc.deskripsi AS tcode_desc
            ');

        // 🛡 Filters
        if (!empty($filters['company_id'])) {
            $query->where('ud.company_id', $filters['company_id']);
        }
        if (!empty($filters['kompartemen_id'])) {
            $query->where('ud.kompartemen_id', $filters['kompartemen_id']);
        }
        if (!empty($filters['departemen_id'])) {
            $query->where('ud.departemen_id', $filters['departemen_id']);
        }

        return $query->whereNotNull('jr.id')
            ->orderBy('company')
            ->orderBy('kompartemen')
            ->orderBy('departemen')
            ->orderBy('job_role')
            ->orderBy('single_role')
            ->get()
            ->toArray();
    }
}
