<?php

namespace App\Http\Controllers\Middle_DB\import;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use App\Models\UserNIKUnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportUserNIKUnitKerjaController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);
        return view('imports.nik_unit_kerja.index', compact('periodes'));
    }

    // Data for DataTables (only NIKs not yet in ms_nik_unit_kerja for the given periode)
    public function data(Request $request)
    {
        $periodeId = (int) $request->query('periode_id');
        if (!$periodeId) {
            return response()->json(['data' => []]);
        }

        $nukTable = (new UserNIKUnitKerja)->getTable();

        $query = DB::table('tr_user_ussm_nik as u')
            ->whereNull('u.deleted_at');

        if (Schema::hasTable($nukTable)) {
            $query->leftJoin($nukTable . ' as nuk', function ($j) use ($periodeId) {
                $j->on('nuk.nik', '=', 'u.user_code')
                    ->where('nuk.periode_id', '=', $periodeId)
                    ->whereNull('nuk.deleted_at');
            })
                ->whereNull('nuk.id');
        }

        $rows = $query
            ->leftJoin('mdb_master_data_karyawan as md', 'md.nik', '=', 'u.user_code')
            ->selectRaw("
                u.user_code as nik,
                md.nama,
                md.company as company_id,
                md.kompartemen_id,
                md.kompartemen,
                md.departemen_id,
                md.departemen,
                md.atasan,
                md.cost_center
            ")
            ->orderBy('u.user_code')
            ->get();

        // Format for DataTables
        return response()->json([
            'data' => $rows,
        ]);
    }

    // Import selected NIKs into ms_nik_unit_kerja using MasterDataKaryawan info
    public function import(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:ms_periode,id'],
            'niks'       => ['required', 'array', 'min:1'],
            'niks.*'     => ['string', 'max:255'],
        ]);

        $nukTable = (new UserNIKUnitKerja)->getTable();
        if (!Schema::hasTable($nukTable)) {
            return response()->json([
                'message' => "Target table '{$nukTable}' not found. Run migrations.",
            ], 422);
        }

        $periodeId = (int) $validated['periode_id'];
        $niks = array_values(array_unique($validated['niks']));

        $mdRows = DB::table('mdb_master_data_karyawan')
            ->whereIn('nik', $niks)
            ->get()
            ->keyBy('nik');

        DB::beginTransaction();
        try {
            $inserted = 0;
            foreach ($niks as $nik) {
                $exists = DB::table($nukTable)
                    ->where('periode_id', $periodeId)
                    ->where('nik', $nik)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $md = $mdRows->get($nik);

                // Only fill allowed/defined columns
                $payload = [
                    'periode_id'     => $periodeId,
                    'nama'           => $md->nama ?? null,
                    'nik'            => $nik,
                    'company_id'     => $md->company ?? null,
                    'direktorat_id'  => $md->direktorat_id ?? null,
                    'kompartemen_id' => $md->kompartemen_id ?? null,
                    'departemen_id'  => $md->departemen_id ?? null,
                    'atasan'         => $md->atasan ?? null,
                    'cost_center'    => $md->cost_center ?? null,
                    // error_* fields are optional; omit unless you map them
                ];

                UserNIKUnitKerja::create($payload);
                $inserted++;
            }

            DB::commit();

            return response()->json([
                'message'  => 'Import completed',
                'inserted' => $inserted,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
