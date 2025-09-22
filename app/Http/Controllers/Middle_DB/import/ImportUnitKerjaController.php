<?php

namespace App\Http\Controllers\Middle_DB\import;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\CostCenter;
use App\Models\middle_db\UnitKerja;
use App\Models\middle_db\MasterDataKaryawan;
use App\Models\MasterDataKaryawanLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportUnitKerjaController extends Controller
{
    public function index()
    {
        return view('imports.unit_kerja.index');
    }

    /**
     * Replace (rebuild) hierarchy from middle DB table mdb_unit_kerja.
     */
    public function sync(Request $request)
    {
        set_time_limit(0);

        $user     = Auth::user()?->name ?? 'system';
        $refresh  = (bool)$request->get('refresh', true);

        DB::connection()->disableQueryLog();

        $summary = [
            'refreshed'          => false,
            'rows_source'        => 0,
            'companies_created'  => 0,
            'companies_existing' => 0,
            'direktorat_cc'      => 0,
            'kompartemen_new'    => 0,
            'departemen_new'     => 0,
            'cc_new'             => 0,
        ];

        try {
            if ($refresh) {
                $ext = UnitKerja::syncFromExternal();
                $summary['refreshed']   = true;
                $summary['rows_source'] = $ext['inserted'] ?? 0;
            }

            $rows = UnitKerja::orderBy('company')
                ->orderBy('direktorat_id')
                ->orderBy('kompartemen_id')
                ->orderBy('departemen_id')
                ->get();

            if ($rows->isEmpty()) {
                return response()->json(['message' => 'No UnitKerja rows found'], 200);
            }

            DB::transaction(function () use ($rows, $user, &$summary) {

                $existingCompanies = Company::pluck('company_code')->flip()->toArray();
                $newCompanies = [];

                $direktorats  = [];
                $kompartemens = [];
                $departemens  = [];


                foreach ($rows as $r) {
                    // if ($r->kompartemen_id == '50000690') {
                    //     dd($r);
                    // }

                    // Fungsi pengumpulan data company
                    if (!isset($existingCompanies[$r->company])) {
                        $newCompanies[$r->company] = [
                            'company_code' => $r->company,
                            'nama'         => $r->company,
                            'created_by'   => $user,
                            'updated_by'   => $user,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                        $existingCompanies[$r->company] = true;
                    }

                    // Fungsi pengumpulan data direktorat
                    if ($r->direktorat_id && !isset($direktorats[$r->direktorat_id]) && $r->departemen_id == null && $r->kompartemen_id == null) {
                        $direktorats[$r->direktorat_id] = [
                            'company_id' => $r->company,
                            'level'      => 'Direktorat',
                            'level_id'   => $r->direktorat_id,
                            'level_name' => $r->direktorat,
                            'cost_center' => $r->cost_center ?: null, // fallback
                        ];
                    }

                    // Fungsi pengumpulan data kompartemen
                    if ($r->kompartemen_id && !isset($kompartemens[$r->kompartemen_id]) && $r->departemen_id == null) {
                        $kompartemens[$r->kompartemen_id] = [
                            'kompartemen_id' => $r->kompartemen_id,
                            'company_id'     => $r->company,
                            'nama'           => $r->kompartemen,
                            'cost_center'    => $r->cost_center ?: null, // fallback
                        ];
                    }

                    // Fungsi pengumpulan data departemen
                    if ($r->departemen_id && !isset($departemens[$r->departemen_id])) {
                        $departemens[$r->departemen_id] = [
                            'departemen_id'  => $r->departemen_id,
                            'company_id'     => $r->company,
                            'kompartemen_id' => $r->kompartemen_id ?: null,
                            'nama'           => $r->departemen,
                            'cost_center'    => $r->cost_center ?: null, // fallback
                        ];
                    }
                }

                // Insert apabila ada company baru
                if ($newCompanies) {
                    Company::insert(array_values($newCompanies));
                    $summary['companies_created'] = count($newCompanies);
                }
                $summary['companies_existing'] = count($existingCompanies) - $summary['companies_created'];

                // Persiapan Fungsi Cost Center untuk Kompartemen tanpa Cost Center
                // Rule: ambil 5 karakter alfanumerik pertama dari cost center departemen 
                $kompDerivedCodes = [];

                $deriveKomCode = function ($deptCc) {
                    if (!$deptCc || $deptCc === '-') return null;
                    $clean = preg_replace('/[^A-Za-z0-9]/', '', $deptCc);
                    if ($clean === '') return null;
                    $prefix = substr(str_pad($clean, 5, '0'), 0, 5);
                    return $prefix . '00000'; // 5 chars + 5 zeros
                };

                // 1. Prefill with existing kompartemen cost_center (only if not empty)
                foreach ($kompartemens as $kompId => $k) {
                    if (!empty($k['cost_center'])) {
                        $kompDerivedCodes[$kompId] = $k['cost_center'];
                    }
                }

                // 2. Derive ONLY for kompartemen that do NOT have a cost_center
                foreach ($departemens as $d) {
                    if (empty($d['kompartemen_id'])) {
                        continue;
                    }
                    $kid = $d['kompartemen_id'];

                    // Skip if kompartemen already has its own cost_center (existing) or already derived
                    if (!empty($kompartemens[$kid]['cost_center']) || isset($kompDerivedCodes[$kid])) {
                        continue;
                    }

                    $candidate = $deriveKomCode($d['cost_center']);
                    if ($candidate) {
                        $kompDerivedCodes[$kid] = $candidate;
                    }
                }

                // Hapus data lama
                DB::table('ms_kompartemen')->truncate();
                DB::table('ms_departemen')->truncate();
                DB::table('ms_cost_center')->truncate();

                $now = now();

                if ($kompartemens) {
                    $insertK = [];
                    foreach ($kompartemens as $k) {

                        $insertK[] = array_merge($k, [
                            'cost_center' => $kompDerivedCodes[$k['kompartemen_id']],
                            'created_by'  => $user,
                            'updated_by'  => $user,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ]);
                    }
                    DB::table('ms_kompartemen')->insert($insertK);
                    $summary['kompartemen_new'] = count($insertK);
                }

                if ($departemens) {
                    $insertD = [];
                    foreach ($departemens as $d) {
                        $insertD[] = array_merge($d, [
                            'created_by' => $user,
                            'updated_by' => $user,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                    DB::table('ms_departemen')->insert($insertD);
                    $summary['departemen_new'] = count($insertD);
                }

                // 1. Direktorat cost centers
                $ccDirektorat = [];
                foreach ($direktorats as $dirId => $d) {
                    $code = $d['cost_center'] ?: '-';
                    $ccDirektorat[] = [
                        'company_id'  => $d['company_id'],
                        'parent_id'   => $d['company_id'],
                        'level'       => 'Direktorat',
                        'level_id'    => $dirId,
                        'level_name'  => $d['level_name'],
                        'cost_center' => $code,
                        'cost_code'   => $code,
                        'created_by'  => $user,
                        'updated_by'  => $user,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }

                if ($ccDirektorat) {
                    DB::table('ms_cost_center')->insert($ccDirektorat);
                    $summary['direktorat_cc'] = count($ccDirektorat);
                }

                $dirIdMap = DB::table('ms_cost_center')
                    ->where('level', 'Direktorat')
                    ->pluck('id', 'level_id')
                    ->toArray();

                // 2. Kompartemen cost centers
                $ccKompartemen = [];
                foreach ($kompartemens as $kompId => $k) {

                    $rowSample = $rows->firstWhere('kompartemen_id', $kompId);
                    $parentDir = $rowSample?->direktorat_id;
                    $parentId  = $parentDir && isset($dirIdMap[$parentDir]) ? $dirIdMap[$parentDir] : null;

                    // Use derived code if available else '-'
                    $kompCode = $kompDerivedCodes[$kompId] ?? '-';

                    $ccKompartemen[] = [
                        'company_id'  => $k['company_id'],
                        'parent_id'   => $parentId,
                        'level'       => 'Kompartemen',
                        'level_id'    => $kompId,
                        'level_name'  => $k['nama'],
                        'cost_center' => $kompCode,
                        'cost_code'   => $kompCode,
                        'created_by'  => $user,
                        'updated_by'  => $user,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }

                if ($ccKompartemen) {
                    DB::table('ms_cost_center')->insert($ccKompartemen);
                }

                $kompIdMap = DB::table('ms_cost_center')
                    ->where('level', 'Kompartemen')
                    ->pluck('id', 'level_id')
                    ->toArray();

                // 3. Departemen cost centers
                $ccDepartemen = [];
                foreach ($departemens as $deptId => $d) {
                    $parentKomp = $d['kompartemen_id'];
                    $parentId   = $parentKomp && isset($kompIdMap[$parentKomp])
                        ? $kompIdMap[$parentKomp]
                        : ($rows->firstWhere('departemen_id', $deptId)?->direktorat_id
                            && isset($dirIdMap[$rows->firstWhere('departemen_id', $deptId)->direktorat_id])
                            ? $dirIdMap[$rows->firstWhere('departemen_id', $deptId)->direktorat_id]
                            : null
                        );

                    $code = $d['cost_center'] ?: '-';
                    $ccDepartemen[] = [
                        'company_id'  => $d['company_id'],
                        'parent_id'   => $parentId,
                        'level'       => 'Departemen',
                        'level_id'    => $deptId,
                        'level_name'  => $d['nama'],
                        'cost_center' => $code,
                        'cost_code'   => $code,
                        'created_by'  => $user,
                        'updated_by'  => $user,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }
                if ($ccDepartemen) {
                    DB::table('ms_cost_center')->insert($ccDepartemen);
                }

                $summary['cc_new'] = $summary['direktorat_cc'] + count($ccKompartemen) + count($ccDepartemen);
            });

            return response()->json([
                'message' => 'Sync completed',
                'summary' => $summary
            ], 200);
        } catch (\Throwable $e) {
            Log::error('UnitKerja sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Sync failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function karyawanSync(Request $request)
    {
        set_time_limit(0);
        DB::connection()->disableQueryLog();

        $user    = Auth::user()?->name ?? 'system';
        $refresh = (bool)$request->get('refresh', true);

        $summary = [
            'refreshed'      => false,
            'rows_source'    => 0,
            'rows_inserted'  => 0,
        ];

        try {
            if ($refresh) {
                $ext = MasterDataKaryawan::syncFromExternal(); // fills middle table
                $summary['refreshed']   = true;
                $summary['rows_source'] = $ext['inserted'] ?? 0;
            } else {
                $summary['rows_source'] = MasterDataKaryawan::count();
            }

            $rows = MasterDataKaryawan::orderBy('company')
                ->orderBy('direktorat_id')
                ->orderBy('kompartemen_id')
                ->orderBy('departemen_id')
                ->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'message' => 'No middle DB karyawan rows found',
                    'summary' => $summary
                ], 200);
            }

            DB::transaction(function () use ($rows, $user, &$summary) {
                DB::table((new MasterDataKaryawanLocal)->getTable())->truncate();

                $now     = now();
                $buffer  = [];
                $batch   = 1000;
                $inserted = 0;

                foreach ($rows as $r) {
                    $buffer[] = [
                        'nik'            => $r->nik,
                        'nama'           => $r->nama,
                        'company'        => $r->company,
                        'direktorat_id'  => $r->direktorat_id,
                        'direktorat'     => $r->direktorat,
                        'kompartemen_id' => $r->kompartemen_id,
                        'kompartemen'    => $r->kompartemen,
                        'departemen_id'  => $r->departemen_id,
                        'departemen'     => $r->departemen,
                        'atasan'         => $r->atasan,
                        'cost_center'    => $r->cost_center,
                        'created_by'     => $user,
                        'updated_by'     => $user,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];

                    if (count($buffer) === $batch) {
                        MasterDataKaryawanLocal::insert($buffer);
                        $inserted += $batch;
                        $buffer = [];
                    }
                }

                if ($buffer) {
                    MasterDataKaryawanLocal::insert($buffer);
                    $inserted += count($buffer);
                }

                $summary['rows_inserted'] = $inserted;
            });

            return response()->json([
                'message' => 'Karyawan sync completed',
                'summary' => $summary
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Karyawan sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Sync failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
