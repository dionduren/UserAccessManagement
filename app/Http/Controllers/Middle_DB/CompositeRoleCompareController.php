<?php

namespace App\Http\Controllers\Middle_DB;

use App\Http\Controllers\Controller;
use App\Models\CompositeRole;
use App\Models\middle_db\MasterDataKaryawan;
use App\Models\middle_db\MasterUSMM;
use App\Models\middle_db\raw\GenericKaryawanMapping;
use App\Models\middle_db\view\GenericKaryawanMappingFiltered;
use App\Models\middle_db\raw\UAMRelationshipRAW;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompositeRoleCompareController extends Controller
{
    public function compare(Request $request)
    {
        $name = trim($request->query('name', ''));

        $singleRows         = [];
        $tcodeRows          = [];
        $summary            = null;
        $metaRows           = [];
        $assignedUsers      = collect();   // RAW
        $assignedUsersLocal = collect();   // LOCAL

        if ($name === '') {
            return view('middle_db.composite_role.compare', compact(
                'name',
                'singleRows',
                'tcodeRows',
                'summary',
                'metaRows',
                'assignedUsers',
                'assignedUsersLocal'
            ));
        }

        $composite = CompositeRole::with([
            'company',
            'kompartemen',
            'departemen',
            'jobRole.NIKJobRole.unitKerja:nik,nama',
            'jobRole.NIKJobRole.userGeneric:user_code,user_profile',
            'singleRoles'
        ])->where('nama', $name)->first();

        /* RAW relationship data */
        $midDBRows = UAMRelationshipRAW::where('composite_role', $name)->get();
        $sapUsers  = $midDBRows->pluck('sap_user')->filter()->unique()->values();

        /* Build enriched RAW user index */
        $karyawanByNik = $this->buildRawUserIndex($sapUsers);

        /* Assigned RAW users */
        $assignedUsers = $sapUsers->map(function ($sap) use ($karyawanByNik) {
            $emp = $karyawanByNik->get($sap);
            return [
                'sap_user'      => $sap,
                'nik'           => $emp->nik        ?? '-',  // added for table display
                'employee_name' => $emp->nama        ?? '-',
                'company'       => $emp->company     ?? '-',  // kept (not shown in view now)
                'departemen'    => $emp->departemen  ?? '-',
                'kompartemen'   => $emp->kompartemen ?? '-',
            ];
        });

        /* Assigned LOCAL users */
        if ($composite && $composite->jobRole) {
            $assignedUsersLocal = $composite->jobRole->NIKJobRole
                ->map(function ($row) {
                    $detail = $row->unitKerja;
                    $generic = $row->userGeneric;
                    return [
                        'nik'         => $detail ? $detail->nik : ($generic->user_code ?? null),
                        'nama'        => $detail ? $detail->nama : ($generic->user_profile ?? null),
                        'company'     => $row->jobRole->company->nama      ?? '-',
                        'kompartemen' => $row->jobRole->kompartemen->nama  ?? '-',
                        'departemen'  => $row->jobRole->departemen->nama   ?? '-',
                        'user_code'   => $generic->user_code    ?? null,
                        'user_profile' => $generic->user_profile ?? null,
                    ];
                })
                ->filter(fn($u) => $u['nik'])
                ->unique('nik')
                ->values();
        }

        /* Distinct meta (RAW) */
        $midDbCompanies        = $karyawanByNik->pluck('company')->filter()->unique()->values();
        $midDbKompartemenIds   = $karyawanByNik->pluck('kompartemen_id')->filter()->unique()->values();
        $midDbKompartemenNames = $karyawanByNik->pluck('kompartemen')->filter()->unique()->values();
        $midDbDepartemenIds    = $karyawanByNik->pluck('departemen_id')->filter()->unique()->values();
        $midDbDepartemenNames  = $karyawanByNik->pluck('departemen')->filter()->unique()->values();

        /* Local singles / tcodes */
        $localSingles = collect();
        $localTcodes  = collect();
        if ($composite) {
            $localSingles = $composite->singleRoles
                ->pluck('nama')->filter()->unique()->values();

            $localTcodes = DB::table('pt_composite_role_single_role as crsr')
                ->join('pt_single_role_tcode as srt', 'srt.single_role_id', '=', 'crsr.single_role_id')
                ->join('tr_tcodes as tc', 'tc.id', '=', 'srt.tcode_id')
                ->where('crsr.composite_role_id', $composite->id)
                ->pluck('tc.code')
                ->filter()
                ->unique()
                ->values();
        }

        $rawSingles = $midDBRows->pluck('single_role')->filter()->unique();
        $rawTcodes  = $midDBRows->pluck('tcode')->filter()->unique();

        $singleRows = $this->alignTwoSets($localSingles, $rawSingles);
        $tcodeRows  = $this->alignTwoSets($localTcodes,  $rawTcodes);

        $summary = [
            'local_single_count'  => $localSingles->count(),
            'raw_single_count'    => $rawSingles->count(),
            'local_tcode_count'   => $localTcodes->count(),
            'raw_tcode_count'     => $rawTcodes->count(),
            'raw_user_count'      => $assignedUsers->count(),
            'local_user_count'    => $assignedUsersLocal->count(), // NEW
        ];

        $metaRows = [
            [
                'label' => 'Company (Code / Name)',
                'local' => $composite
                    ? (($composite->company->company_code ?? $composite->company_id ?? '-') . ' / ' . ($composite->company->nama ?? '-'))
                    : '-',
                'raw' => $midDbCompanies->implode(', ') ?: '-',
            ],
            [
                'label' => 'Kompartemen (ID / Name)',
                'local' => $composite
                    ? (($composite->kompartemen->kompartemen_id ?? '-') . ' / ' . ($composite->kompartemen->nama ?? '-'))
                    : '-',
                'raw' => ($midDbKompartemenIds->implode(', ') ?: '-') . ' / ' . ($midDbKompartemenNames->implode(', ') ?: '-'),
            ],
            [
                'label' => 'Departemen (ID / Name)',
                'local' => $composite
                    ? (($composite->departemen->departemen_id ?? '-') . ' / ' . ($composite->departemen->nama ?? '-'))
                    : '-',
                'raw' => ($midDbDepartemenIds->implode(', ') ?: '-') . ' / ' . ($midDbDepartemenNames->implode(', ') ?: '-'),
            ],
            [
                'label' => 'Assigned Users (Count)',
                'local' => $assignedUsersLocal->count(),  // CHANGED from '-' to actual local count
                'raw'   => $assignedUsers->count(),
            ],
        ];

        return view('middle_db.composite_role.compare', compact(
            'name',
            'singleRows',
            'tcodeRows',
            'summary',
            'metaRows',
            'assignedUsers',
            'assignedUsersLocal'
        ));
    }

    /**
     * Build enriched RAW user index (sap_user_id => object with nama & org fields)
     */
    protected function buildRawUserIndex($sapUsers)
    {
        $index = collect();
        if ($sapUsers->isEmpty()) {
            return $index;
        }

        // Base from MasterDataKaryawan
        $index = MasterDataKaryawan::whereIn('nik', $sapUsers)->get()->keyBy('nik');

        $missing = $sapUsers->diff($index->keys());
        if ($missing->isEmpty()) {
            return $index;
        }

        $mappings = MasterUSMM::whereIn('sap_user_id', $missing)->get();
        if ($mappings->isEmpty()) {
            return $index;
        }

        // Synthetic entries from MasterUSMM (full_name)
        $mappings->each(function ($m) use (&$index) {
            if (!$index->has($m->sap_user_id)) {
                $index->put($m->sap_user_id, (object)[
                    'sap_user'       => $m->sap_user_id,
                    'nama'           => $m->full_name ?? '-',
                    'nik'            => '-',
                    'company'        => '-',
                    'kompartemen'    => '-',
                    'departemen'     => '-',
                    'kompartemen_id' => null,
                    'departemen_id'  => null,
                ]);
            }
        });
        // dd('After Synthetic Mapping: ', $index);

        // Additional mapping (GenericKaryawanMapping) if exists
        $gm = GenericKaryawanMappingFiltered::whereIn('sap_user_id', $mappings->pluck('sap_user_id'))->get();
        // $gm = GenericKaryawanMapping::whereIn('sap_user_id', $mappings->pluck('sap_user_id'))->get();
        if ($gm->isEmpty()) {
            return $index;
        }

        $personnelToSap = $gm->pluck('sap_user_id', 'personnel_number')->filter();
        if ($personnelToSap->isEmpty()) {
            return $index;
        }

        // Enrich org data via MasterDataKaryawan looked up by personnel_number
        $mdkByNik = MasterDataKaryawan::whereIn('nik', $personnelToSap->keys())->get()->keyBy('nik');
        // dd('mdkByNik', $mdkByNik);

        foreach ($personnelToSap as $personnelNumber => $sapId) {
            $mdk = $mdkByNik->get($personnelNumber);
            // dd('mdk', $mdk);
            if (!$mdk) {
                continue;
            }
            if ($index->has($sapId)) {
                $ex = $index->get($sapId);
                foreach (
                    [
                        'nik'            => $mdk->nik            ?? '-',
                        'company'        => $mdk->company        ?? '-',
                        'kompartemen'    => $mdk->kompartemen    ?? '-',
                        'departemen'     => $mdk->departemen     ?? '-',
                        'kompartemen_id' => $mdk->kompartemen_id ?? null,
                        'departemen_id'  => $mdk->departemen_id  ?? null,
                    ] as $k => $v
                ) {
                    if (!isset($ex->$k) || $ex->$k === '-' || $ex->$k === null) {
                        $ex->$k = $v;
                    }
                }
                if (!isset($ex->nama) || $ex->nama === '-' || $ex->nama === null) {
                    $ex->nama = $mdk->nama ?? $ex->nama ?? '-';
                }
                $index->put($sapId, $ex);
            } else {
                $index->put($sapId, (object)[
                    'sap_user'       => $sapId,
                    'nama'           => $mdk->nama ?? '-',
                    'nik'            => $mdk->nik ?? '-',
                    'company'        => $mdk->company ?? '-',
                    'kompartemen'    => $mdk->kompartemen ?? '-',
                    'departemen'     => $mdk->departemen ?? '-',
                    'kompartemen_id' => $mdk->kompartemen_id ?? null,
                    'departemen_id'  => $mdk->departemen_id ?? null,
                ]);
            }
        }

        return $index;
    }

    /**
     * Align two sets (local vs raw) into rows.
     */
    protected function alignTwoSets($localSet, $rawSet): array
    {
        $l = $localSet->filter()->unique()->values()->all();
        $r = $rawSet->filter()->unique()->values()->all();
        usort($l, fn($a, $b) => strnatcasecmp($a, $b));
        usort($r, fn($a, $b) => strnatcasecmp($a, $b));

        $out = [];
        $i = $j = 0;
        while ($i < count($l) && $j < count($r)) {
            $cmp = strnatcasecmp($l[$i], $r[$j]);
            if ($cmp === 0) {
                $out[] = ['local' => $l[$i], 'raw' => $r[$j]];
                $i++;
                $j++;
            } elseif ($cmp < 0) {
                $out[] = ['local' => $l[$i], 'raw' => '-'];
                $i++;
            } else {
                $out[] = ['local' => '-', 'raw' => $r[$j]];
                $j++;
            }
        }
        while ($i < count($l)) {
            $out[] = ['local' => $l[$i], 'raw' => '-'];
            $i++;
        }
        while ($j < count($r)) {
            $out[] = ['local' => '-', 'raw' => $r[$j]];
            $j++;
        }
        return $out;
    }
}
