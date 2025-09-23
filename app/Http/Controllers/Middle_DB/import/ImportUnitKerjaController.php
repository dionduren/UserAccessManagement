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
     * Fixes:
     *  - Preserve kompartemen cost_center from pure kompartemen rows (departemen_id NULL) with priority.
     *  - Allow later upgrade if a better (priority 1) row appears after a fallback (priority 2) row.
     *  - Infer missing kompartemen referenced only via departemen.
     *  - Keep departemen rows intact.
     */
    public function sync(Request $request)
    {
        set_time_limit(0);

        $user     = Auth::user()?->name ?? 'system';
        $refresh  = (bool)$request->get('refresh', true);

        DB::connection()->disableQueryLog();

        $summary = [
            'refreshed'              => false,
            'rows_source'            => 0,
            'companies_created'      => 0,
            'companies_existing'     => 0,
            'direktorat_cc'          => 0,
            'kompartemen_new'        => 0,
            'departemen_new'         => 0,
            'cc_new'                 => 0,
            'kompartemen_inferred'   => 0,
            'kompartemen_skipped_blank' => 0,
            'kompartemen_cc_upgraded'   => 0,
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
                $kompartemens = []; // kompartemen_id => [fields..., _cc_priority]
                $departemens  = [];

                $kompartemenSkippedBlank = 0;
                $kompCcUpgraded          = 0;

                foreach ($rows as $r) {

                    // Companies
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

                    // Direktorat (only pure level row)
                    if (
                        $r->direktorat_id && !isset($direktorats[$r->direktorat_id]) &&
                        $r->kompartemen_id == null && $r->departemen_id == null
                    ) {
                        $direktorats[$r->direktorat_id] = [
                            'company_id'  => $r->company,
                            'level'       => 'Direktorat',
                            'level_id'    => $r->direktorat_id,
                            'level_name'  => $r->direktorat,
                            'cost_center' => $r->cost_center ?: null,
                        ];
                    }

                    // Kompartemen with priority:
                    // priority 1 = pure kompartemen row (departemen_id NULL)
                    // priority 2 = derived from a row that also has departemen_id (fallback)
                    if ($r->kompartemen_id) {
                        $namaKomp   = $r->kompartemen ? trim($r->kompartemen) : null;
                        $ccIncoming = $r->cost_center ? trim($r->cost_center) : null;
                        $priority   = ($r->departemen_id === null) ? 1 : 2;

                        if (!isset($kompartemens[$r->kompartemen_id])) {
                            if ($namaKomp === '' || $namaKomp === '-' || $namaKomp === null) {
                                $kompartemenSkippedBlank++;
                            } else {
                                $kompartemens[$r->kompartemen_id] = [
                                    'kompartemen_id' => $r->kompartemen_id,
                                    'company_id'     => $r->company,
                                    'nama'           => $namaKomp,
                                    'cost_center'    => $ccIncoming ?: null,
                                    '_cc_priority'   => $ccIncoming ? $priority : 999, // 999 = unknown (no cost center yet)
                                ];
                            }
                        } else {
                            // Existing: maybe update name if placeholder was set previously (inference step happens later)
                            if (
                                !empty($namaKomp) &&
                                str_starts_with($kompartemens[$r->kompartemen_id]['nama'], 'KOMP-')
                            ) {
                                $kompartemens[$r->kompartemen_id]['nama'] = $namaKomp;
                            }

                            if ($ccIncoming) {
                                $currentPriority = $kompartemens[$r->kompartemen_id]['_cc_priority'];
                                $haveCurrentCC   = !empty($kompartemens[$r->kompartemen_id]['cost_center']);

                                // Overwrite rules:
                                //  - No current CC
                                //  - Better priority (lower number)
                                //  - Same priority but value differs and current was blank
                                if (
                                    !$haveCurrentCC ||
                                    $priority < $currentPriority ||
                                    ($priority === $currentPriority && !$haveCurrentCC)
                                ) {
                                    if ($haveCurrentCC && $priority < $currentPriority) {
                                        $kompCcUpgraded++;
                                    }
                                    $kompartemens[$r->kompartemen_id]['cost_center']  = $ccIncoming;
                                    $kompartemens[$r->kompartemen_id]['_cc_priority'] = $priority;
                                }
                            }
                        }
                    }

                    // Departemen
                    if ($r->departemen_id && !isset($departemens[$r->departemen_id])) {
                        $departemens[$r->departemen_id] = [
                            'departemen_id'  => $r->departemen_id,
                            'company_id'     => $r->company,
                            'kompartemen_id' => $r->kompartemen_id ?: null,
                            'nama'           => $r->departemen,
                            'cost_center'    => $r->cost_center ?: null,
                        ];
                    }
                }

                // Infer kompartemen missing but referenced by departemen
                $kompartemenInferred = 0;
                foreach ($departemens as $d) {
                    $kid = $d['kompartemen_id'];
                    if ($kid && !isset($kompartemens[$kid])) {
                        $kompartemens[$kid] = [
                            'kompartemen_id' => $kid,
                            'company_id'     => $d['company_id'],
                            'nama'           => 'KOMP-' . $kid, // placeholder (will be replaced if later pure row found)
                            'cost_center'    => null,
                            '_cc_priority'   => 999,
                        ];
                        $kompartemenInferred++;
                    }
                }

                // Insert new companies
                if ($newCompanies) {
                    Company::insert(array_values($newCompanies));
                    $summary['companies_created'] = count($newCompanies);
                }
                $summary['companies_existing'] = count($existingCompanies) - $summary['companies_created'];

                // Derive kompartemen cost centers (only if still empty)
                $kompDerivedCodes = [];
                $deriveKomCode = function ($deptCc) {
                    if (!$deptCc || $deptCc === '-') return null;
                    $clean = preg_replace('/[^A-Za-z0-9]/', '', $deptCc);
                    if ($clean === '') return null;
                    $prefix = substr(str_pad($clean, 5, '0'), 0, 5);
                    return $prefix . '00000';
                };

                // Pre-fill existing cost centers
                foreach ($kompartemens as $kompId => $k) {
                    if (!empty($k['cost_center'])) {
                        $kompDerivedCodes[$kompId] = $k['cost_center'];
                    }
                }

                // Derive from departemen where missing
                foreach ($departemens as $d) {
                    $kid = $d['kompartemen_id'];
                    if (!$kid) continue;
                    if (isset($kompDerivedCodes[$kid])) continue;
                    if (!empty($kompartemens[$kid]['cost_center'])) continue;
                    $candidate = $deriveKomCode($d['cost_center']);
                    if ($candidate) {
                        $kompDerivedCodes[$kid] = $candidate;
                    }
                }

                // Clean helper keys before insert
                foreach ($kompartemens as $id => $k) {
                    unset($kompartemens[$id]['_cc_priority']);
                }

                // Truncate old data
                DB::table('ms_kompartemen')->truncate();
                DB::table('ms_departemen')->truncate();
                DB::table('ms_cost_center')->truncate();

                $now = now();

                // Insert kompartemen
                if ($kompartemens) {
                    $insertK = [];
                    foreach ($kompartemens as $k) {
                        $insertK[] = array_merge($k, [
                            'cost_center' => $kompDerivedCodes[$k['kompartemen_id']] ?? $k['cost_center'] ?? '-',
                            'created_by'  => $user,
                            'updated_by'  => $user,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ]);
                    }
                    DB::table('ms_kompartemen')->insert($insertK);
                    $summary['kompartemen_new'] = count($insertK);
                }

                // Insert departemen
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

                // Cost centers: Direktorat
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

                // Cost centers: Kompartemen
                $ccKompartemen = [];
                foreach ($kompartemens as $kompId => $k) {
                    $rowSample = $rows->firstWhere('kompartemen_id', $kompId);
                    $parentDir = $rowSample?->direktorat_id;
                    $parentId  = $parentDir && isset($dirIdMap[$parentDir]) ? $dirIdMap[$parentDir] : null;
                    $kompCode  = $kompDerivedCodes[$kompId] ?? $k['cost_center'] ?? '-';

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

                // Cost centers: Departemen
                $ccDepartemen = [];
                foreach ($departemens as $deptId => $d) {
                    $parentKomp = $d['kompartemen_id'];
                    $parentId   = $parentKomp && isset($kompIdMap[$parentKomp])
                        ? $kompIdMap[$parentKomp]
                        : null;

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

                $summary['cc_new']                   = $summary['direktorat_cc'] + count($ccKompartemen) + count($ccDepartemen);
                $summary['kompartemen_inferred']     = $kompartemenInferred;
                $summary['kompartemen_skipped_blank'] = $kompartemenSkippedBlank;
                $summary['kompartemen_cc_upgraded']  = $kompCcUpgraded;
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

                $now      = now();
                $buffer   = [];
                $batch    = 1000;
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
