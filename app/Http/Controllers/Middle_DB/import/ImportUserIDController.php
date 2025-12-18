<?php


namespace App\Http\Controllers\Middle_DB\import;

use App\Http\Controllers\Controller;
use App\Models\middle_db\MasterUSMM;
use App\Models\userGeneric;
use App\Models\userNIK;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sync & split USMM master users into:
 *  - tr_user_generic (letters / generic IDs)  user_type='Generic'
 *  - tr_user_ussm_nik (numeric IDs)           user_type='NIK'
 *
 * Rules (default):
 *  - Active only: valid_to is NULL / 00000000 / >= today.
 *  - Rows with sap_user_id starting with a digit go to userNIK else userGeneric.
 *  - Truncate both local target tables before insert (full replace).
 *  - Optional refresh of middle table via MasterUSMM::syncFromExternal.
 *
 * Query params:
 *  refresh=1          -> re-pull external into mdb_usmm_master first
 *  include_expired=1  -> do NOT filter by valid_to / active (imports everything)
 *  batch=2000         -> custom batch size
 */
class ImportUserIDController extends Controller
{

    public function index()
    {
        $periodes = Periode::orderByDesc('is_active')
            ->orderBy('tanggal_create_periode')
            ->get(['id', 'definisi', 'is_active']);
        return view('imports.user_id.index', compact('periodes'));
    }

    public function sync(Request $request)
    {
        @set_time_limit(0);
        DB::connection()->disableQueryLog();

        $periodeId = (int)$request->get('periode_id');
        if (!$periodeId) {
            return response()->json(['message' => 'periode_id wajib diisi'], 422);
        }
        $periode = Periode::find($periodeId);
        if (!$periode) {
            return response()->json(['message' => 'Periode tidak ditemukan'], 422);
        }

        $refresh        = $request->boolean('refresh', false);
        $includeExpired = $request->boolean('include_expired', false);
        $batchSize      = (int)$request->get('batch', 1000);
        if ($batchSize < 100) $batchSize = 100;
        if ($batchSize > 5000) $batchSize = 5000;

        $actor = Auth::user()?->name ?? 'system';

        $summary = [
            'periode_id'          => $periodeId,
            'periode_definisi'    => $periode->definisi,
            'refreshed'           => false,
            'source_total'        => 0,
            'source_after_filter' => 0,
            'deleted_generic'     => 0,
            'deleted_nik'         => 0,
            'insert_generic'      => 0,
            'insert_nik'          => 0,
            'skipped_expired'     => 0,
            'errors'              => 0,
        ];

        try {
            if ($refresh) {
                $ext = MasterUSMM::syncFromExternal();
                $summary['refreshed'] = true;
                $summary['source_total'] = $ext['inserted'] ?? 0;
            } else {
                $summary['source_total'] = MasterUSMM::count();
            }

            // Base query
            $q = DB::table('mdb_usmm_master');

            if (!$includeExpired) {
                // Keep: valid_to NULL / '00000000' / >= today
                $q->where(function ($w) {
                    $w->whereNull('valid_to')
                        ->orWhere('valid_to', '00000000')
                        // SECURITY: Safe - no user input, uses hardcoded date format and PostgreSQL function
                        ->orWhereRaw("to_date(valid_to,'YYYYMMDD') >= current_date");
                });
            }

            $rows = $q->orderBy('sap_user_id')->get();
            $summary['source_after_filter'] = $rows->count();

            if ($rows->isEmpty()) {
                return response()->json([
                    'message' => 'No rows to import',
                    'summary' => $summary
                ], 200);
            }

            DB::transaction(function () use ($rows, $actor, $batchSize, $periodeId, &$summary) {

                // Hapus hanya data periode terkait
                $summary['deleted_generic'] = DB::table((new userGeneric)->getTable())
                    ->where('periode_id', $periodeId)->delete();
                $summary['deleted_nik'] = DB::table((new userNIK)->getTable())
                    ->where('periode_id', $periodeId)->delete();

                $now = now();

                $batchGeneric = [];
                $batchNIK     = [];

                $flushGeneric = function () use (&$batchGeneric, &$summary) {
                    if ($batchGeneric) {
                        userGeneric::insert($batchGeneric);
                        $summary['insert_generic'] += count($batchGeneric);
                        $batchGeneric = [];
                    }
                };
                $flushNIK = function () use (&$batchNIK, &$summary) {
                    if ($batchNIK) {
                        userNIK::insert($batchNIK);
                        $summary['insert_nik'] += count($batchNIK);
                        $batchNIK = [];
                    }
                };

                foreach ($rows as $r) {

                    // Expired counting (only if excluded earlier? just skip counting double)
                    if (!($r->valid_to === null || $r->valid_to === '00000000')) {
                        $isExpired = false;
                        if (preg_match('/^\d{8}$/', $r->valid_to ?? '')) {
                            $isExpired = (strtotime(substr($r->valid_to, 0, 4) . '-' . substr($r->valid_to, 4, 2) . '-' . substr($r->valid_to, 6, 2)) < strtotime(date('Y-m-d')));
                        }
                        if ($isExpired) {
                            // If query already filtered expired ones, this path rarely executes.
                            // Keep counter for visibility (only counts those that slipped through when include_expired=1)
                            $summary['skipped_expired']++;
                        }
                    }

                    $sapId = trim((string)$r->sap_user_id);
                    if ($sapId === '') continue;

                    // Classification: numeric first char => NIK else Generic
                    $isNik = ctype_digit(substr($sapId, 0, 1));

                    // Convert date fields
                    $validFrom = $this->toDate($r->valid_from);
                    $validTo   = $this->toDate($r->valid_to);
                    $lastLogin = $this->toDateTime($r->last_logon_date, $r->last_logon_time);

                    $base = [
                        'periode_id'    => $periodeId,
                        'group'        => $r->company ?: null,
                        'user_code'    => $sapId,
                        'license_type' => $r->contractual_user_type ?: null,
                        'valid_from'   => $validFrom,
                        'valid_to'     => $validTo,
                        'created_by'   => $actor,
                        'updated_by'   => $actor,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];

                    if ($isNik) {
                        $batchNIK[] = array_merge($base, [
                            'user_type' => 'NIK',
                            // Extra NIK model fields not present will default null
                        ]);
                        if (count($batchNIK) >= $batchSize) $flushNIK();
                    } else {
                        $batchGeneric[] = array_merge($base, [
                            'user_type'    => 'Generic',
                            'user_profile' => $r->full_name ?: null,
                            'last_login'   => $lastLogin,
                        ]);
                        if (count($batchGeneric) >= $batchSize) $flushGeneric();
                    }
                }

                // Flush remaining
                $flushGeneric();
                $flushNIK();
            });

            return response()->json([
                'message' => 'UserID sync & split completed',
                'summary' => $summary
            ], 200);
        } catch (\Throwable $e) {
            $summary['errors']++;
            Log::error('ImportUserID sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Sync failed',
                'error'   => $e->getMessage(),
                'summary' => $summary
            ], 500);
        }
    }

    private function toDate(?string $yyyymmdd): ?string
    {
        if (!$yyyymmdd || $yyyymmdd === '00000000') return null;
        if (!preg_match('/^\d{8}$/', $yyyymmdd)) return null;
        return substr($yyyymmdd, 0, 4) . '-' . substr($yyyymmdd, 4, 2) . '-' . substr($yyyymmdd, 6, 2);
    }

    private function toDateTime(?string $date, ?string $time): ?string
    {
        $d = $this->toDate($date);
        if (!$d) return null;
        if (!$time || !preg_match('/^\d{6}$/', $time)) return $d . ' 00:00:00';
        $hh = substr($time, 0, 2);
        $mm = substr($time, 2, 2);
        $ss = substr($time, 4, 2);
        return $d . " {$hh}:{$mm}:{$ss}";
    }
}
