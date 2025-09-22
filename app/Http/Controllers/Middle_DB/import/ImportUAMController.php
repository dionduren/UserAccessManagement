<?php

namespace App\Http\Controllers\Middle_DB\import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\CompositeAO;
use App\Models\SingleRole as LocalSingleRole;
use App\Models\middle_db\SingleRole as MiddleSingleRole;
use App\Models\middle_db\view\UAMSingleTcode;
use App\Models\middle_db\view\UAMCompositeAO;
use App\Models\Tcode as LocalTcode;
use App\Models\middle_db\Tcode as MiddleTcode;
use App\Models\CompositeRole as LocalCompositeRole;
use App\Models\middle_db\CompositeRole as MiddleCompositeRole;
use App\Models\middle_db\view\UAMCompositeSingle;

class ImportUAMController extends Controller
{
    /* =============================================================
     |  GENERIC INTERNAL HELPERS (Reusable for future CompositeRole)
     * ============================================================= */

    // Generic chunk iterator (works for views w/out id; uses offset/limit fallback if chunk() not preferred)
    private function chunkQuery($query, int $size, callable $cb): void
    {
        $query->chunk($size, function ($rows) use ($cb) {
            $cb($rows);
        });
    }

    // Insert batch helper
    private function flushInsert(string $table, array &$batch, int &$counter, array &$summary): void
    {
        if (!$batch) return;
        DB::table($table)->insert($batch);
        $summary['inserted'] = ($summary['inserted'] ?? 0) + count($batch);
        $counter += count($batch);
        $batch = [];
    }

    /**
     * Generic entity sync (incremental) for simple key/value entities.
     * Options:
     *  - sourceQuery (required)
     *  - localTable  (required)
     *  - keyField    (name inserted into local)
     *  - sourceKey   (callable($row): string)
     *  - sourceDesc  (callable($row): ?string)
     *  - uppercaseKey (bool) default true
     *  - overwrite (bool) update description always if different
     *  - existingMap (optional) pre-built existing rows [KEY => ['id'=>?, 'desc'=>?]]
     *  - extraColumns (array K=>V static)
     */
    private function syncFlatEntity(array $opts): array
    {
        $actor        = Auth::user()?->name ?? 'system';
        $now          = now();
        $batchSize    = $opts['batchSize'] ?? 1000;
        $uppercase    = $opts['uppercaseKey'] ?? true;
        $overwrite    = $opts['overwrite'] ?? false;
        $keyField     = $opts['keyField'] ?? 'nama';
        $descField    = $opts['descField'] ?? 'deskripsi';
        $sourceQuery  = $opts['sourceQuery'];
        $localTable   = $opts['localTable'];
        $sourceKeyFn  = $opts['sourceKey'];
        $sourceDescFn = $opts['sourceDesc'];
        $extraCols    = $opts['extraColumns'] ?? [];

        $summary = [
            'source_total' => (clone $sourceQuery)->count(),
            'processed'    => 0,
            'inserted'     => 0,
            'updated'      => 0,
            'skipped'      => 0,
            'errors'       => 0,
        ];
        if ($summary['source_total'] === 0) return $summary;

        // Build existing map if not provided
        $existingMap = $opts['existingMap'] ?? DB::table($localTable)
            ->select('id', $keyField . ' as key_val', $descField . ' as desc_val')
            ->get()
            ->reduce(function ($carry, $r) use ($uppercase) {
                $k = $uppercase ? strtoupper($r->key_val) : $r->key_val;
                $carry[$k] = ['id' => $r->id, 'desc' => $r->desc_val];
                return $carry;
            }, []);

        $batch = [];
        $insertCounter = 0;

        DB::beginTransaction();
        try {
            $this->chunkQuery($sourceQuery, $batchSize, function ($rows) use (&$summary, &$batch, $batchSize, &$insertCounter, $existingMap, $overwrite, $uppercase, $keyField, $descField, $extraCols, $sourceKeyFn, $sourceDescFn, $localTable, $actor, $now) {
                foreach ($rows as $row) {
                    $summary['processed']++;
                    $rawKey = $sourceKeyFn($row);
                    if ($rawKey === null || $rawKey === '') {
                        $summary['skipped']++;
                        continue;
                    }
                    $key = $uppercase ? strtoupper($rawKey) : $rawKey;
                    $desc = $sourceDescFn($row);

                    if (!isset($existingMap[$key])) {
                        $record = array_merge($extraCols, [
                            $keyField    => $key,
                            $descField   => $desc,
                            'created_by' => $actor,
                            'updated_by' => $actor,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $batch[] = $record;
                        if (count($batch) === $batchSize) {
                            $this->flushInsert($localTable, $batch, $insertCounter, $summary);
                        }
                        continue;
                    }

                    $current = $existingMap[$key];
                    $shouldUpdate = false;
                    if ($overwrite && $desc !== null && $desc !== $current['desc']) {
                        $shouldUpdate = true;
                    } elseif (($current['desc'] === null || $current['desc'] === '') && $desc !== null) {
                        $shouldUpdate = true;
                    }

                    if ($shouldUpdate && $current['id']) {
                        DB::table($localTable)
                            ->where('id', $current['id'])
                            ->update([
                                $descField   => $desc,
                                'updated_by' => $actor,
                                'updated_at' => $now,
                            ]);
                        $summary['updated']++;
                    } else {
                        $summary['skipped']++;
                    }
                }
            });

            $this->flushInsert($localTable, $batch, $insertCounter, $summary);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('syncFlatEntity failed', ['error' => $e->getMessage()]);
            $summary['errors']++;
            $summary['message'] = $e->getMessage();
        }

        return $summary;
    }

    /**
     * Generic pivot sync.
     * Options:
     *  - viewQuery (select key1, key2)
     *  - pivotTable
     *  - localMapA [KEY => id]
     *  - localMapB [KEY => id]
     *  - fullRefresh (bool)
     *  - makeKeyA / makeKeyB (callable for normalization)
     *  - colA / colB pivot FK names
     */
    private function syncPivotGeneric(array $opts): array
    {
        $actor       = Auth::user()?->name ?? 'system';
        $now         = now();
        $fullRefresh = $opts['fullRefresh'] ?? false;
        $viewQuery   = $opts['viewQuery'];
        $pivotTable  = $opts['pivotTable'];
        $colA        = $opts['colA'];
        $colB        = $opts['colB'];
        $localA      = $opts['localMapA']; // [UPPER(name)=>id]
        $localB      = $opts['localMapB'];
        $makeKeyA    = $opts['makeKeyA'] ?? fn($v) => strtoupper($v);
        $makeKeyB    = $opts['makeKeyB'] ?? fn($v) => strtoupper($v);
        $batchSize   = $opts['batchSize'] ?? 1000;

        $summary = [
            'source_total'        => (clone $viewQuery)->count(),
            'processed'           => 0,
            'inserted'            => 0,
            'skipped_missing_a'   => 0,
            'skipped_missing_b'   => 0,
            'skipped_exists'      => 0,
            'errors'              => 0,
            'full_refresh'        => $fullRefresh,
        ];
        if ($summary['source_total'] === 0) return $summary;

        DB::beginTransaction();
        try {
            if ($fullRefresh) {
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement("TRUNCATE TABLE {$pivotTable} RESTART IDENTITY CASCADE");
                } else {
                    DB::table($pivotTable)->truncate();
                }
            }

            $existingPairs = [];
            if (!$fullRefresh) {
                $existingPairs = DB::table($pivotTable)
                    ->select($colA, $colB)
                    ->get()
                    ->reduce(function ($c, $r) use ($colA, $colB) {
                        $c[$r->{$colA} . '||' . $r->{$colB}] = true;
                        return $c;
                    }, []);
            }

            $batch = [];
            $this->chunkQuery($viewQuery, $batchSize, function ($rows) use (&$summary, &$batch, $batchSize, $pivotTable, $colA, $colB, $localA, $localB, $makeKeyA, $makeKeyB, $existingPairs, $fullRefresh, $actor, $now) {
                foreach ($rows as $r) {
                    $summary['processed']++;
                    $rawA = $r->single_role ?? $r->composite_role ?? null; // flexible
                    $rawB = $r->tcode ?? $r->single_role_child ?? null;

                    if ($rawA === null || $rawB === null) {
                        $summary['skipped_missing_a']++;
                        continue;
                    }
                    $kA = $makeKeyA($rawA);
                    $kB = $makeKeyB($rawB);

                    $idA = $localA[$kA] ?? null;
                    if (!$idA) {
                        $summary['skipped_missing_a']++;
                        continue;
                    }
                    $idB = $localB[$kB] ?? null;
                    if (!$idB) {
                        $summary['skipped_missing_b']++;
                        continue;
                    }

                    $pairKey = $idA . '||' . $idB;
                    if (!$fullRefresh && isset($existingPairs[$pairKey])) {
                        $summary['skipped_exists']++;
                        continue;
                    }

                    $batch[] = [
                        $colA        => $idA,
                        $colB        => $idB,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'created_by' => $actor,
                        'updated_by' => $actor,
                    ];
                    $summary['inserted']++;

                    if (!$fullRefresh) {
                        $existingPairs[$pairKey] = true;
                    }

                    if (count($batch) === $batchSize) {
                        DB::table($pivotTable)->insert($batch);
                        $batch = [];
                    }
                }
            });

            if ($batch) {
                DB::table($pivotTable)->insert($batch);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('syncPivotGeneric failed', ['error' => $e->getMessage()]);
            $summary['errors']++;
            $summary['message'] = $e->getMessage();
        }

        return $summary;
    }

    /* ====================== SPECIFIC SYNC FUNCTIONS ====================== */

    // 1. FULL refresh Tcodes (truncate + load)
    public function sync_tcodes(Request $request)
    {
        @set_time_limit(0);
        $actor = Auth::user()?->name ?? 'system';

        $sourceQuery = MiddleTcode::query()->select('tcode', 'definisi')->orderBy('tcode');

        $summary = [
            'source_total' => (clone $sourceQuery)->count(),
            'inserted'     => 0,
            'errors'       => 0,
        ];
        if ($summary['source_total'] === 0) {
            return response()->json(['message' => 'Source empty', 'summary' => $summary], 422);
        }

        DB::beginTransaction();
        try {
            $localTable = (new LocalTcode)->getTable();
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("TRUNCATE TABLE {$localTable} RESTART IDENTITY CASCADE");
            } else {
                DB::table($localTable)->truncate();
            }

            $now = now();
            $batch = [];
            $batchSize = 1000;

            $this->chunkQuery($sourceQuery, $batchSize, function ($rows) use (&$batch, &$summary, $batchSize, $localTable, $now, $actor) {
                foreach ($rows as $r) {
                    $code = strtoupper(trim($r->tcode ?? ''));
                    if ($code === '') continue;

                    $batch[] = [
                        'code'        => $code,
                        'deskripsi'   => trim((string)$r->definisi) ?: null,
                        'created_by'  => $actor,
                        'updated_by'  => $actor,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                    if (count($batch) === $batchSize) {
                        DB::table($localTable)->insert($batch);
                        $summary['inserted'] += count($batch);
                        $batch = [];
                    }
                }
            });

            if ($batch) {
                DB::table($localTable)->insert($batch);
                $summary['inserted'] += count($batch);
            }

            DB::commit();
            return response()->json(['message' => 'TCODE full refresh completed', 'summary' => $summary]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('TCODE sync failed', ['error' => $e->getMessage()]);
            $summary['errors']++;
            return response()->json(['message' => 'TCODE sync failed', 'error' => $e->getMessage(), 'summary' => $summary], 500);
        }
    }

    // 2. Incremental Single Role sync (no soft delete logic)
    public function sync_single_roles(Request $request)
    {
        @set_time_limit(0);

        $overwrite = $request->boolean('overwrite', false);
        $all       = $request->boolean('all', false);

        $sourceQuery = MiddleSingleRole::query()
            ->select(['single_role', 'definisi'])
            ->when(!$all, fn($q) => $q->sapPattern())
            ->ordered();

        $summary = $this->syncFlatEntity([
            'sourceQuery' => $sourceQuery,
            'localTable'  => (new LocalSingleRole)->getTable(),
            'keyField'    => 'nama',
            'descField'   => 'deskripsi',
            'sourceKey'   => fn($r) => $r->single_role,
            'sourceDesc'  => fn($r) => $r->definisi,
            'uppercaseKey' => true,
            'overwrite'   => $overwrite,
        ]);

        return response()->json(['message' => 'Single Role sync done', 'summary' => $summary, 'overwrite' => $overwrite]);
    }

    // 3. Sync AO (Composite AO) - description lookup from middle single role
    public function sync_ao(Request $request)
    {
        @set_time_limit(0);

        $sourceQuery = UAMCompositeAO::query()
            ->where('single_role', 'LIKE', '%-AO')
            ->select(['composite_role', 'single_role'])
            ->orderBy('composite_role')
            ->orderBy('single_role');

        $total = (clone $sourceQuery)->count();
        if ($total === 0) {
            return response()->json(['message' => 'No AO rows', 'summary' => ['source_total' => 0]]);
        }

        // Build description map from middle single role
        $aoNames = (clone $sourceQuery)->distinct()->pluck('single_role');
        $descMap = MiddleSingleRole::whereIn('single_role', $aoNames)
            ->pluck('definisi', 'single_role');

        // Existing composite AO map
        $localTable = (new CompositeAO)->getTable();
        $existing = DB::table($localTable)
            ->select('id', 'composite_role', 'nama', 'deskripsi')
            ->get()
            ->reduce(function ($c, $r) {
                $c[$r->composite_role . '||' . $r->nama] = $r;
                return $c;
            }, []);

        $actor = Auth::user()?->name ?? 'system';
        $now = now();
        $batchSize = 1000;
        $batch = [];
        $summary = [
            'source_total' => $total,
            'processed'    => 0,
            'inserted'     => 0,
            'updated'      => 0,
            'skipped'      => 0,
            'desc_hits'    => 0,
            'errors'       => 0,
        ];

        DB::beginTransaction();
        try {
            $this->chunkQuery($sourceQuery, $batchSize, function ($rows) use (&$summary, &$batch, $batchSize, $existing, $descMap, $localTable, $actor, $now) {
                foreach ($rows as $row) {
                    $summary['processed']++;
                    $comp = trim($row->composite_role ?? '');
                    $ao   = trim($row->single_role ?? '');
                    if ($comp === '' || $ao === '') {
                        $summary['skipped']++;
                        continue;
                    }
                    $key = $comp . '||' . $ao;
                    $desc = $descMap[$ao] ?? null;
                    if ($desc) $summary['desc_hits']++;

                    if (!isset($existing[$key])) {
                        $batch[] = [
                            'composite_role' => $comp,
                            'nama'           => $ao,
                            'deskripsi'      => $desc,
                            'created_by'     => $actor,
                            'updated_by'     => $actor,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ];
                        $summary['inserted']++;
                        if (count($batch) === $batchSize) {
                            DB::table($localTable)->insert($batch);
                            $batch = [];
                        }
                        continue;
                    }

                    $existRow = $existing[$key];
                    if ((!$existRow->deskripsi || $existRow->deskripsi === '') && $desc) {
                        DB::table($localTable)->where('id', $existRow->id)->update([
                            'deskripsi'  => $desc,
                            'updated_by' => $actor,
                            'updated_at' => $now,
                        ]);
                        $summary['updated']++;
                    } else {
                        $summary['skipped']++;
                    }
                }
            });

            if ($batch) {
                DB::table($localTable)->insert($batch);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AO sync failed', ['error' => $e->getMessage()]);
            $summary['errors']++;
            return response()->json(['message' => 'AO sync failed', 'error' => $e->getMessage(), 'summary' => $summary], 500);
        }

        return response()->json(['message' => 'AO sync completed', 'summary' => $summary]);
    }

    // 4. Sync SingleRole - Tcode pivot
    public function sync_single_role_tcodes(Request $request)
    {
        @set_time_limit(0);
        $full = $request->boolean('full_refresh', false);

        $viewQuery = UAMSingleTcode::query()
            ->select(['single_role', 'tcode'])
            ->orderBy('single_role')
            ->orderBy('tcode');

        $singleRoleMap = LocalSingleRole::pluck('id', 'nama')
            ->mapWithKeys(fn($id, $n) => [strtoupper($n) => $id])
            ->toArray();
        $tcodeMap = LocalTcode::pluck('id', 'code')
            ->mapWithKeys(fn($id, $c) => [strtoupper($c) => $id])
            ->toArray();

        $summary = $this->syncPivotGeneric([
            'viewQuery'   => $viewQuery,
            'pivotTable'  => 'pt_single_role_tcode',
            'localMapA'   => $singleRoleMap,
            'localMapB'   => $tcodeMap,
            'colA'        => 'single_role_id',
            'colB'        => 'tcode_id',
            'fullRefresh' => $full,
        ]);

        return response()->json(['message' => 'Single Role - Tcode sync done', 'summary' => $summary]);
    }

    // Index page (buttons UI)
    public function index()
    {
        return view('imports.uam.index');
    }

    // 5. Incremental Composite Role sync (similar to single roles)
    public function sync_composite_roles(Request $request)
    {
        @set_time_limit(0);
        $overwrite = $request->boolean('overwrite', false);
        $all       = $request->boolean('all', false);

        $sourceQuery = MiddleCompositeRole::query()
            ->select(['composite_role', 'definisi'])
            ->when(!$all, fn($q) => $q->sapPattern())
            ->ordered();

        $summary = $this->syncFlatEntity([
            'sourceQuery'  => $sourceQuery,
            'localTable'   => (new LocalCompositeRole)->getTable(),
            'keyField'     => 'nama',
            'descField'    => 'deskripsi',
            'sourceKey'    => fn($r) => $r->composite_role,
            'sourceDesc'   => fn($r) => $r->definisi,
            'uppercaseKey' => true,
            'overwrite'    => $overwrite,
            'extraColumns' => []
        ]);

        return response()->json([
            'message'   => 'Composite Role sync done',
            'overwrite' => $overwrite,
            'summary'   => $summary
        ]);
    }

    // 6. Composite Role - Single Role pivot sync
    public function sync_composite_role_single_roles(Request $request)
    {
        @set_time_limit(0);
        $full = $request->boolean('full_refresh', false);

        $viewQuery = UAMCompositeSingle::query()
            ->select(['composite_role', 'single_role'])
            ->where('single_role', 'NOT LIKE', '%-AO') // exclude AO variants
            ->orderBy('composite_role')
            ->orderBy('single_role');

        $compositeMap = LocalCompositeRole::pluck('id', 'nama')
            ->mapWithKeys(fn($id, $n) => [strtoupper($n) => $id])->toArray();

        $singleRoleMap = LocalSingleRole::pluck('id', 'nama')
            ->mapWithKeys(fn($id, $n) => [strtoupper($n) => $id])->toArray();

        $summary = $this->syncPivotGeneric([
            'viewQuery'   => $viewQuery,
            'pivotTable'  => 'pt_composite_role_single_role',
            'localMapA'   => $compositeMap,
            'localMapB'   => $singleRoleMap,
            'colA'        => 'composite_role_id',
            'colB'        => 'single_role_id',
            'fullRefresh' => $full,
            'makeKeyA'    => fn($v) => strtoupper($v),
            'makeKeyB'    => fn($v) => strtoupper($v),
        ]);

        return response()->json([
            'message' => 'Composite Role - Single Role sync done',
            'summary' => $summary
        ]);
    }

    // 7. Sync ALL (sequential)
    public function sync_all(Request $request)
    {
        @set_time_limit(0);

        $overwriteSingle     = $request->boolean('overwrite_single', false);
        $overwriteComposite  = $request->boolean('overwrite_composite', false);
        $fullSingleTcode     = $request->boolean('full_single_tcode', false);
        $fullCompositeSingle = $request->boolean('full_composite_single', false);
        $allPatterns         = $request->boolean('all_patterns', false);

        $results = [];

        // Tcodes (full refresh)
        $results['tcodes'] = json_decode($this->sync_tcodes(new Request())->getContent(), true);

        // Single Roles
        $reqSingle = new Request([
            'overwrite' => $overwriteSingle,
            'all'       => $allPatterns
        ]);
        $results['single_roles'] = json_decode($this->sync_single_roles($reqSingle)->getContent(), true);

        // Composite Roles
        $reqComposite = new Request([
            'overwrite' => $overwriteComposite,
            'all'       => $allPatterns
        ]);
        $results['composite_roles'] = json_decode($this->sync_composite_roles($reqComposite)->getContent(), true);

        // AO
        $results['ao'] = json_decode($this->sync_ao(new Request())->getContent(), true);

        // SingleRole - Tcodes pivot
        $reqSRT = new Request([
            'full_refresh' => $fullSingleTcode
        ]);
        $results['single_role_tcodes'] = json_decode($this->sync_single_role_tcodes($reqSRT)->getContent(), true);

        // Composite Role - Single Role pivot
        $reqCRSR = new Request([
            'full_refresh' => $fullCompositeSingle
        ]);
        $results['composite_role_single_roles'] = json_decode($this->sync_composite_role_single_roles($reqCRSR)->getContent(), true);

        return response()->json([
            'message' => 'All sync processes completed',
            'results' => $results
        ]);
    }

    /* ======================= FUTURE EXTENSIONS =====================
       For CompositeRole & CompositeRole-SingleRole relationship:
       - Use syncFlatEntity() for composite role list.
       - Use syncPivotGeneric() for composite_role <-> single_role pivot.
       Just prepare source queries & local maps similarly.
       ================================================================ */
}
