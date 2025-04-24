<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\Periode;
use App\Models\Departemen;
use App\Models\Kompartemen;

use App\Services\WorkUnitService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkUnitReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        return view('report.unit_kerja.index', [
            'periodes' => Periode::select('id', 'definisi')->orderByDesc('id')->get(),
            'companies' => Company::select('company_code', 'nama')->orderBy('nama')->get(),
            'kompartemens' => Kompartemen::select('kompartemen_id', 'nama')->get(),
            'departemens' => Departemen::select('departemen_id', 'nama')->get(),
        ]);
    }

    public function groupedJson(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $filters = $request->only(['company_id', 'kompartemen_id', 'departemen_id']);

        $data = WorkUnitService::getNestedStructure($periodeId, $filters);


        return response()->json(['data' => $data]);
    }

    public function data(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $companyId = $request->input('company_id');
        $kompartemenId = $request->input('kompartemen_id');
        $departemenId = $request->input('departemen_id');

        $rows = DB::table('ms_user_detail as ud')
            ->leftJoin('tr_nik_job_role as njr', function ($join) use ($periodeId) {
                $join->on('njr.nik', '=', 'ud.nik')->where('njr.periode_id', $periodeId);
            })
            ->leftJoin('tr_job_roles as jr', 'jr.id', '=', 'njr.job_role_id')
            ->leftJoin('tr_composite_roles as cr', 'cr.jabatan_id', '=', 'jr.id')
            ->leftJoin('ms_company as c', 'c.company_code', '=', 'ud.company_id')
            ->leftJoin('ms_kompartemen as k', 'k.kompartemen_id', '=', 'ud.kompartemen_id')
            ->leftJoin('ms_departemen as d', 'd.departemen_id', '=', 'ud.departemen_id')
            ->select(
                'ud.nik',
                'ud.nama',
                'c.nama as company',
                'c.company_code as company_id',
                'k.nama as kompartemen',
                'k.kompartemen_id as kompartemen_id',
                'd.name as departemen',
                'd.departemen_id as departemen_id',
                'jr.nama as job_role',
                'jr.id as job_role_id',
                'cr.nama as composite_role',
                'cr.id as composite_role_id'
            )
            ->when($companyId, fn($q) => $q->where('c.id', $companyId))
            ->when($kompartemenId, fn($q) => $q->where('k.id', $kompartemenId))
            ->when($departemenId, fn($q) => $q->where('d.id', $departemenId))
            ->whereNotNull('jr.id')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $key = implode('|', [$row->company, $row->kompartemen, $row->departemen]);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'company' => $row->company,
                    'kompartemen' => $row->kompartemen,
                    'departemen' => $row->departemen,
                    'users' => [],
                ];
            }

            $grouped[$key]['users'][] = [
                'nik' => $row->nik,
                'nama' => $row->nama,
                'job_role' => $row->job_role,
                'composite_role' => $row->composite_role,
                'job_role_id' => $row->job_role_id,
                'composite_role_id' => $row->composite_role_id,
            ];
        }

        return response()->json(array_values($grouped));
    }
}
