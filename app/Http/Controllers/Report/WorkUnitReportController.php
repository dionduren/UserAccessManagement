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
}
