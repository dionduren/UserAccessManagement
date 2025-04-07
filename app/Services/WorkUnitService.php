<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WorkUnitService
{
    public static function getNestedStructure($periodeId, $filters = [])
    {
        $rows = DB::table('ms_user_detail as ud')
            ->leftJoin('tr_nik_job_role as njr', function ($join) use ($periodeId) {
                $join->on('njr.nik', '=', 'ud.nik')
                    ->where('njr.periode_id', '=', $periodeId);
            })
            ->leftJoin('tr_job_roles as jr', 'jr.id', '=', 'njr.job_role_id')
            ->leftJoin('ms_company as c', 'c.id', '=', 'ud.company_id')
            ->leftJoin('ms_kompartemen as k', 'k.id', '=', 'ud.kompartemen_id')
            ->leftJoin('ms_departemen as d', 'd.id', '=', 'ud.departemen_id')
            ->leftJoin('tr_composite_roles as cr', 'cr.jabatan_id', '=', 'jr.id')
            ->leftJoin('pt_composite_role_single_role as cr_sr', 'cr.id', '=', 'cr_sr.composite_role_id')
            ->leftJoin('tr_single_roles as sr', 'sr.id', '=', 'cr_sr.single_role_id')
            ->leftJoin('pt_single_role_tcode as sr_tc', 'sr.id', '=', 'sr_tc.single_role_id')
            ->leftJoin('tr_tcodes as tc', 'tc.id', '=', 'sr_tc.tcode_id')
            ->selectRaw('
                ud.nik,
                ud.nama,
                c.name AS company,
                k.name AS kompartemen,
                d.name AS departemen,
                jr.id AS job_role_id,
                jr.nama_jabatan AS job_role,
                cr.id AS composite_role_id,
                cr.nama AS composite_role,
                sr.id AS single_role_id,
                sr.nama AS single_role,
                sr.deskripsi AS single_role_desc,
                tc.code AS tcode,
                tc.sap_module,
                tc.deskripsi AS tcode_desc
            ')
            ->whereNotNull('jr.id')
            ->orderBy('ud.nik')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $key = implode('|', [$row->company, $row->kompartemen, $row->departemen, $row->job_role]);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'company' => $row->company,
                    'kompartemen' => $row->kompartemen,
                    'departemen' => $row->departemen,
                    'job_role' => $row->job_role,
                    'composite_roles' => [],
                ];
            }

            $compRef = &$grouped[$key]['composite_roles'];
            $cr_id = $row->composite_role_id;

            if ($cr_id && !isset($compRef[$cr_id])) {
                $compRef[$cr_id] = [
                    'name' => $row->composite_role,
                    'single_roles' => [],
                ];
            }

            if ($cr_id) {
                $sr_id = $row->single_role_id;

                if (!isset($compRef[$cr_id]['single_roles'][$sr_id])) {
                    $compRef[$cr_id]['single_roles'][$sr_id] = [
                        'name' => $row->single_role,
                        'deskripsi' => $row->single_role_desc,
                        'tcodes' => [],
                    ];
                }

                if ($row->tcode) {
                    $compRef[$cr_id]['single_roles'][$sr_id]['tcodes'][] = [
                        'code' => $row->tcode,
                        'sap_module' => $row->sap_module,
                        'deskripsi' => $row->tcode_desc,
                    ];
                }
            }
        }

        return array_values(array_map(function ($item) {
            $item['composite_roles'] = array_values(array_map(function ($cr) {
                $cr['single_roles'] = array_values($cr['single_roles']);
                return $cr;
            }, $item['composite_roles']));
            return $item;
        }, $grouped));
    }
}
