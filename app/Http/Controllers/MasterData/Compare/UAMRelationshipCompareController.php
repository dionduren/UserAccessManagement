<?php

namespace App\Http\Controllers\MasterData\Compare;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\middle_db\view\UAMSingleTcode;
use App\Models\middle_db\view\UAMCompositeSingle;
use App\Models\middle_db\view\UAMUserComposite;

class UAMRelationshipCompareController extends Controller
{
    // Composite Role - Single Role
    public function compositeSingle()
    {
        // Local pairs
        $local = DB::table('pt_composite_role_single_role as crsr')
            ->join('tr_composite_roles as cr', 'cr.id', '=', 'crsr.composite_role_id')
            ->join('tr_single_roles as sr', 'sr.id', '=', 'crsr.single_role_id')
            ->select('cr.company_id', 'cr.nama as left_val', 'sr.nama as right_val')
            ->get();

        // Middle pairs
        $middle = UAMCompositeSingle::query()
            ->select('composite_role as left_val', 'single_role as right_val')
            ->get();

        [$localOnly, $middleOnly] = $this->diffPairs($local, $middle);

        $scope      = 'composite_single';
        $leftLabel  = 'Composite Role';
        $rightLabel = 'Single Role';

        return view('master-data.compare.uam.relationship_missing', compact('localOnly', 'middleOnly', 'scope', 'leftLabel', 'rightLabel'));
    }
    // Single Role - Tcode
    public function singleTcode()
    {
        // Local pairs
        $local = DB::table('pt_single_role_tcode as srt')
            ->join('tr_single_roles as sr', 'sr.id', '=', 'srt.single_role_id')
            ->join('tr_tcodes as tc', 'tc.id', '=', 'srt.tcode_id')
            ->select('sr.nama as left_val', 'tc.code as right_val')
            ->get();

        // Middle pairs
        $middle = UAMSingleTcode::query()
            ->select('single_role as left_val', 'tcode as right_val')
            ->get();

        [$localOnly, $middleOnly] = $this->diffPairs($local, $middle);

        $scope      = 'single_tcode';
        $leftLabel  = 'Single Role';
        $rightLabel = 'Tcode';

        return view('master-data.compare.uam.relationship_missing', compact('localOnly', 'middleOnly', 'scope', 'leftLabel', 'rightLabel'));
    }


    // User - (Job Role) - Composite Role (LOCAL) vs User - Composite Role (MIDDLE)
    public function userComposite()
    {
        // Local: derive user -> job_role -> composite_role
        $local = DB::table('tr_ussm_job_role as ujr')
            ->join('tr_job_roles as jr', 'jr.job_role_id', '=', 'ujr.job_role_id')
            ->join('tr_composite_roles as cr', 'cr.jabatan_id', '=', 'jr.id')
            ->whereNull('ujr.deleted_at')
            ->select(DB::raw("NULL as company_id"), 'ujr.nik as left_val', 'cr.nama as right_val')
            ->get();

        // Middle: user -> composite role view
        $middle = UAMUserComposite::query()
            ->select('sap_user as left_val', 'composite_role as right_val')
            ->get();

        [$localOnly, $middleOnly] = $this->diffPairs($local, $middle);

        $scope      = 'user_composite';
        $leftLabel  = 'User';
        $rightLabel = 'Composite Role';

        return view('master-data.compare.uam.relationship_missing', compact('localOnly', 'middleOnly', 'scope', 'leftLabel', 'rightLabel'));
    }

    // Existing in both: Single Role - Tcode
    public function singleTcodeExist()
    {
        $local = DB::table('pt_single_role_tcode as srt')
            ->join('tr_single_roles as sr', 'sr.id', '=', 'srt.single_role_id')
            ->join('tr_tcodes as tc', 'tc.id', '=', 'srt.tcode_id')
            ->select('sr.company_id', 'sr.nama as left_val', 'tc.code as right_val')
            ->get();

        $middle = UAMSingleTcode::query()
            ->select('single_role as left_val', 'tcode as right_val')
            ->get();

        $rows = $this->intersectionPairs($local, $middle);
        $scope = 'single_tcode';
        $leftLabel = 'Single Role';
        $rightLabel = 'Tcode';
        return view('master-data.compare.uam.relationship_exist', compact('rows', 'scope', 'leftLabel', 'rightLabel'));
    }

    // Existing in both: Composite Role - Single Role
    public function compositeSingleExist()
    {
        $local = DB::table('pt_composite_role_single_role as crsr')
            ->join('tr_composite_roles as cr', 'cr.id', '=', 'crsr.composite_role_id')
            ->join('tr_single_roles as sr', 'sr.id', '=', 'crsr.single_role_id')
            ->select('cr.company_id', 'cr.nama as left_val', 'sr.nama as right_val')
            ->get();

        $middle = UAMCompositeSingle::query()
            ->select('composite_role as left_val', 'single_role as right_val')
            ->get();

        $rows = $this->intersectionPairs($local, $middle);
        $scope = 'composite_single';
        $leftLabel = 'Composite Role';
        $rightLabel = 'Single Role';
        return view('master-data.compare.uam.relationship_exist', compact('rows', 'scope', 'leftLabel', 'rightLabel'));
    }

    // Existing in both: User - Composite Role
    public function userCompositeExist()
    {
        $local = DB::table('tr_ussm_job_role as ujr')
            ->join('tr_job_roles as jr', 'jr.job_role_id', '=', 'ujr.job_role_id')
            ->join('tr_composite_roles as cr', 'cr.jabatan_id', '=', 'jr.id')
            ->whereNull('ujr.deleted_at')
            ->select(DB::raw("NULL as company_id"), 'ujr.nik as left_val', 'cr.nama as right_val')
            ->get();

        $middle = UAMUserComposite::query()
            ->select('sap_user as left_val', 'composite_role as right_val')
            ->get();

        $rows = $this->intersectionPairs($local, $middle);
        $scope = 'user_composite';
        $leftLabel = 'User';
        $rightLabel = 'Composite Role';
        return view('master-data.compare.uam.relationship_exist', compact('rows', 'scope', 'leftLabel', 'rightLabel'));
    }

    // Helpers

    private function norm(?string $v): string
    {
        return strtoupper(trim((string) $v));
    }

    /**
     * Build pair sets and compute differences
     * @return array{0: array<int, array>, 1: array<int, array>}
     */
    private function diffPairs($local, $middle): array
    {
        // Key = LEFT|RIGHT normalized
        $localMap = [];
        foreach ($local as $r) {
            $L = $this->norm($r->left_val ?? '');
            $R = $this->norm($r->right_val ?? '');
            if ($L === '' || $R === '') continue;
            $k = "{$L}|{$R}";
            $localMap[$k] = [
                'company' => $r->company_id ?? '',
                'left'    => $L,
                'right'   => $R,
            ];
        }

        $middleMap = [];
        foreach ($middle as $r) {
            $L = $this->norm($r->left_val ?? '');
            $R = $this->norm($r->right_val ?? '');
            if ($L === '' || $R === '') continue;
            $k = "{$L}|{$R}";
            $middleMap[$k] = [
                'company' => '',
                'left'    => $L,
                'right'   => $R,
            ];
        }

        // Differences
        $localOnlyKeys  = array_diff(array_keys($localMap), array_keys($middleMap));
        $middleOnlyKeys = array_diff(array_keys($middleMap), array_keys($localMap));

        $localOnly  = array_values(array_intersect_key($localMap, array_flip($localOnlyKeys)));
        $middleOnly = array_values(array_intersect_key($middleMap, array_flip($middleOnlyKeys)));

        // Sort for stable output
        $this->sortPairs($localOnly);
        $this->sortPairs($middleOnly);

        return [$localOnly, $middleOnly];
    }

    private function sortPairs(array &$rows): void
    {
        usort($rows, fn($a, $b) => [
            $a['company'] ?? '',
            $a['left'],
            $a['right'],
        ] <=> [
            $b['company'] ?? '',
            $b['left'],
            $b['right'],
        ]);
    }

    private function intersectionPairs($local, $middle): array
    {
        $localMap = [];
        foreach ($local as $r) {
            $L = $this->norm($r->left_val ?? '');
            $R = $this->norm($r->right_val ?? '');
            if ($L === '' || $R === '') continue;
            $k = "{$L}|{$R}";
            $localMap[$k] = [
                'company' => $r->company_id ?? '',
                'left'    => $L,
                'right'   => $R,
            ];
        }

        $middleKeys = [];
        foreach ($middle as $r) {
            $L = $this->norm($r->left_val ?? '');
            $R = $this->norm($r->right_val ?? '');
            if ($L === '' || $R === '') continue;
            $middleKeys["{$L}|{$R}"] = true;
        }

        $rows = [];
        foreach ($localMap as $k => $v) {
            if (isset($middleKeys[$k])) {
                $rows[] = $v;
            }
        }

        usort($rows, fn($a, $b) => [$a['company'] ?? '', $a['left'], $a['right']] <=> [$b['company'] ?? '', $b['left'], $b['right']]);
        return $rows;
    }
}
