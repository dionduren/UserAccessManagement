<?php

namespace App\Http\Controllers\MasterData\Compare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// Local models
use App\Models\userGeneric;
use App\Models\userNIK;

// Middle DB models
use App\Models\middle_db\MasterUSMM;

class USMMCompareController extends Controller
{
    // ---------- PAGE (INDEX) METHODS ----------

    // Full comparison (Generic)
    public function genericIndex()
    {
        return view('master-data.compare.usmm.generic');
    }

    // Full comparison (NIK)
    public function nikIndex()
    {
        return view('master-data.compare.usmm.nik');
    }


    // (Keep your existing data endpoints below)
    public function genericCompareData(Request $request)
    {
        // Validate inputs
        $request->validate([
            'company' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:255',
        ]);

        $company = trim((string)$request->get('company', ''));
        $search  = trim((string)$request->get('search', ''));

        // Middle DB (A-K, active filter on string-style valid_to)
        // Note: This regex pattern is hardcoded and safe (no user input)
        $mdbQuery = MasterUSMM::query()
            ->whereRaw("sap_user_id ~* '^[A-K]'");

        $this->applyActiveFilter($mdbQuery, 'valid_to', false); // false = string style

        if ($company !== '') {
            $mdbQuery->where('company', $company);
        }
        if ($search !== '') {
            $mdbQuery->where(function ($q) use ($search) {
                $q->where('sap_user_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        $mdbRows = $mdbQuery->get([
            'company',
            'sap_user_id',
            'full_name',
            'valid_from',
            'valid_to',
        ]);

        // Local GENERIC (DATE valid_to)
        $localQuery = userGeneric::query()
            ->whereNull('deleted_at')
            ->where('periode_id', function ($query) {
                $query->selectRaw('MAX(periode_id)')
                    ->from('user_generics')
                    ->whereNull('deleted_at');
            });
        $this->applyActiveFilter($localQuery, 'valid_to', true); // true = date column

        if ($search !== '') {
            $localQuery->where(function ($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%");
            });
        }

        $localRows = $localQuery->get([
            'id',
            'group',
            'user_code',
            'user_profile',
            'valid_from',
            'valid_to',
            'periode_id',
        ]);

        $mdbIndex   = $this->buildIndex($mdbRows, 'sap_user_id');
        $localIndex = $this->buildIndex($localRows, 'user_code');

        // Local => Middle mapping
        $fieldMap = [
            'group'       => 'company',
            'user_code'   => 'sap_user_id',
            'user_profile' => 'full_name',
            'valid_from'  => 'valid_from',
            'valid_to'    => 'valid_to',
            // periode_id only displayed (local only) – not compared
        ];

        [$rows, $summary] = $this->alignTwoSets($localIndex, $mdbIndex, $fieldMap);

        return response()->json(['summary' => $summary, 'data' => $rows]);
    }

    public function nikCompareData(Request $request)
    {
        // Validate inputs
        $request->validate([
            'company' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:255',
        ]);

        $company = trim((string)$request->get('company', ''));
        $search  = trim((string)$request->get('search', ''));

        // Middle DB (digit first, string-style valid_to)
        // Note: This regex pattern is hardcoded and safe (no user input)
        $mdbQuery = MasterUSMM::query()
            ->whereRaw("sap_user_id ~* '^[0-9]'");

        $this->applyActiveFilter($mdbQuery, 'valid_to', false);

        if ($company !== '') {
            $mdbQuery->where('company', $company);
        }
        if ($search !== '') {
            $mdbQuery->where(function ($q) use ($search) {
                $q->where('sap_user_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $mdbRows = $mdbQuery->get([
            'company',
            'sap_user_id',
            'full_name',
            'valid_from',
            'valid_to',
        ]);

        // Local NIK (assume DATE valid_to)
        $localQuery = userNIK::query()->whereNull('deleted_at');
        $this->applyActiveFilter($localQuery, 'valid_to', true);

        if ($search !== '') {
            $localQuery->where(function ($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%");
            });
        }

        $localRows = $localQuery->get([
            'id',
            'group',
            'user_code',
            'user_profile',
            'valid_from',
            'valid_to',
            'periode_id',
        ]);

        $mdbIndex   = $this->buildIndex($mdbRows, 'sap_user_id');
        $localIndex = $this->buildIndex($localRows, 'user_code');

        $fieldMap = [
            'group'       => 'company',
            'user_code'   => 'sap_user_id',
            'user_profile' => 'full_name',
            'valid_from'  => 'valid_from',
            'valid_to'    => 'valid_to',
        ];

        [$rows, $summary] = $this->alignTwoSets($localIndex, $mdbIndex, $fieldMap);

        return response()->json(['summary' => $summary, 'data' => $rows]);
    }

    /**
     * Apply "active" filter:
     * If $isDate = true: (valid_to IS NULL OR valid_to >= current_date)
     * Else (string): (valid_to IS NULL OR valid_to='00000000' OR (valid_to ~ '^[0-9]{8}$' AND to_date(valid_to,'YYYYMMDD') >= current_date))
     * 
     * SECURITY NOTE: The $column parameter is controlled internally (not from user input)
     * and is only called with 'valid_to' or similar column names. The whereRaw query
     * uses hardcoded regex patterns and PostgreSQL functions - no user input is interpolated.
     */
    private function applyActiveFilter($query, string $column, bool $isDate): void
    {
        if ($isDate) {
            $query->where(function ($q) use ($column) {
                $q->whereNull($column)
                    ->orWhere($column, '>=', now()->toDateString());
            });
        } else {
            $query->where(function ($q) use ($column) {
                $q->whereNull($column)
                    ->orWhere($column, '00000000')
                    // Safe: $column is internal, regex and date format are hardcoded
                    ->orWhereRaw("(" . $column . " ~ '^[0-9]{8}$' AND to_date(" . $column . ",'YYYYMMDD') >= current_date)");
            });
        }
    }

    /**
     * Build an associative index for fast compare.
     * @param \Illuminate\Support\Collection $rows
     * @param string $keyName
     * @return array [ key => array(row...), ... ]
     */
    protected function buildIndex($rows, string $keyName): array
    {
        $index = [];
        foreach ($rows as $r) {
            $key = (string) data_get($r, $keyName);
            if ($key === '') {
                continue;
            }
            $index[$key] = Arr::undot($r->toArray());
        }
        return $index;
    }

    /**
     * Align two sets with field name mapping (localField => middleField).
     * Produces diff entries keyed by local field name.
     * Summary includes per-field diff counts.
     *
     * @param array $localByKey
     * @param array $mdbByKey
     * @param array $fieldMap [localField => middleField]
     * @return array [rows, summary]
     */
    protected function alignTwoSets(array $localByKey, array $mdbByKey, array $fieldMap = []): array
    {
        $allKeys = array_values(array_unique(array_merge(array_keys($localByKey), array_keys($mdbByKey))));
        sort($allKeys);

        $rows       = [];
        $onlyLocal  = 0;
        $onlyMdb    = 0;
        $both       = 0;
        $fieldDiffCounts = array_fill_keys(array_keys($fieldMap), 0);

        foreach ($allKeys as $key) {
            $local = $localByKey[$key] ?? null;
            $mdb   = $mdbByKey[$key] ?? null;

            $inLocal = $local !== null;
            $inMdb   = $mdb !== null;

            $diffs = [];

            if ($inLocal && $inMdb) {
                $both++;
                foreach ($fieldMap as $localField => $mdbField) {
                    $lv = data_get($local, $localField);
                    $mv = data_get($mdb, $mdbField);

                    // Normalize simple scalars (trim strings)
                    if (is_string($lv)) $lv = trim($lv);
                    if (is_string($mv)) $mv = trim($mv);

                    if ($lv !== $mv) {
                        $diffs[$localField] = [
                            'local_field'  => $localField,
                            'middle_field' => $mdbField,
                            'local'        => $lv,
                            'middle'       => $mv,
                        ];
                        $fieldDiffCounts[$localField]++;
                    }
                }
            } elseif ($inLocal && !$inMdb) {
                $onlyLocal++;
            } elseif (!$inLocal && $inMdb) {
                $onlyMdb++;
            }

            $rows[] = [
                'key'       => $key,
                'in_local'  => $inLocal,
                'in_mdb'    => $inMdb,
                'local'     => $local,
                'middle'    => $mdb,
                'diffs'     => $diffs,
            ];
        }

        $summary = [
            'total_local'   => count($localByKey),
            'total_mdb'     => count($mdbByKey),
            'only_in_local' => $onlyLocal,
            'only_in_mdb'   => $onlyMdb,
            'in_both'       => $both,
            'field_diff_counts' => $fieldDiffCounts,
        ];

        return [$rows, $summary];
    }
}
