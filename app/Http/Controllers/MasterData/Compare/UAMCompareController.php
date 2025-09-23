<?php

namespace App\Http\Controllers\MasterData\Compare;

use App\Http\Controllers\Controller;

use App\Models\CompositeRole as LocalCompositeRole;
use App\Models\SingleRole as LocalSingleRole;
use App\Models\Tcode as LocalTcode;
use App\Models\middle_db\CompositeRole as MidCompositeRole;
use App\Models\middle_db\SingleRole as MidSingleRole;
use App\Models\middle_db\Tcode as MidTcode;
use App\Models\CompositeAO; // ADD
use App\Exports\CompareDiffExport;

use Maatwebsite\Excel\Facades\Excel;

class UAMCompareController extends Controller
{
    public function compositeRole()
    {
        $middle = MidCompositeRole::sapPattern()->ordered()->get(['composite_role', 'definisi']);
        $local  = LocalCompositeRole::select('company_id', 'nama', 'deskripsi')->get();

        $middleIds = $middle->pluck('composite_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $local->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->composite_role)));

        $localMissing = [];
        foreach ($local as $row) {
            $key = strtoupper(trim($row->nama));
            if ($localOnly->contains($key)) {
                $localMissing[] = [
                    'company' => $row->company_id ?? '',
                    // 'level'   => 'Composite Role',
                    'id'      => $row->nama,
                    'value'   => $row->deskripsi ?? $row->nama,
                ];
            }
        }

        $middleMissing = [];
        foreach ($middleOnly as $key) {
            $r = $middleIndex->get($key);
            $middleMissing[] = [
                'company' => '',
                // 'level'   => 'Composite Role',
                'id'      => $r->composite_role ?? $key,
                'value'   => $r->definisi ?? ($r->composite_role ?? $key),
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'composite_role';
        return view('master-data.compare.uam.missing', compact('localMissing', 'middleMissing', 'scope'));
    }

    public function singleRole()
    {
        // Middle (authoritative)
        $middle = MidSingleRole::sapPattern()->ordered()->get(['single_role', 'definisi']);

        // Local Single Roles
        $localSingles = LocalSingleRole::select('nama', 'deskripsi')->get();

        // Local AO (treated as additional single roles)
        $localAOs = CompositeAO::select('nama', 'deskripsi')->get();

        // Combine (priority: real SingleRole description over AO; fill missing desc from AO if empty)
        $combined = [];
        foreach ($localSingles as $sr) {
            $key = strtoupper(trim($sr->nama));
            if ($key === '') continue;
            $combined[$key] = [
                'nama' => $sr->nama,
                'deskripsi' => $sr->deskripsi
            ];
        }
        foreach ($localAOs as $ao) {
            $key = strtoupper(trim($ao->nama));
            if ($key === '') continue;
            if (!isset($combined[$key])) {
                $combined[$key] = [
                    'nama' => $ao->nama,
                    'deskripsi' => $ao->deskripsi
                ];
            } else {
                // If existing description empty, fill from AO
                if (empty($combined[$key]['deskripsi']) && !empty($ao->deskripsi)) {
                    $combined[$key]['deskripsi'] = $ao->deskripsi;
                }
            }
        }
        $localCombined = collect(array_values($combined));

        $middleIds = $middle->pluck('single_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $localCombined->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->single_role)));

        $localMissing = [];
        foreach ($localCombined as $row) {
            $key = strtoupper(trim($row['nama']));
            if ($localOnly->contains($key)) {
                $localMissing[] = [
                    'company' => '',
                    'id'      => $row['nama'],
                    'value'   => $row['deskripsi'] ?? $row['nama'],
                ];
            }
        }

        $middleMissing = [];
        foreach ($middleOnly as $key) {
            $r = $middleIndex->get($key);
            $middleMissing[] = [
                'company' => '',
                'id'      => $r->single_role ?? $key,
                'value'   => $r->definisi ?? ($r->single_role ?? $key),
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'single_role';
        return view('master-data.compare.uam.missing', compact('localMissing', 'middleMissing', 'scope'));
    }

    public function tcode()
    {
        $middle = MidTcode::ordered()->get(['tcode', 'definisi']);
        $local  = LocalTcode::select('code', 'deskripsi')->get();

        $middleIds = $middle->pluck('tcode')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $local->pluck('code')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->tcode)));

        $localMissing = [];
        foreach ($local as $row) {
            $key = strtoupper(trim($row->code));
            if ($localOnly->contains($key)) {
                $localMissing[] = [
                    'company' => '',
                    // 'level'   => 'Tcode',
                    'id'      => $row->code,
                    'value'   => $row->deskripsi ?? $row->code,
                ];
            }
        }

        $middleMissing = [];
        foreach ($middleOnly as $key) {
            $r = $middleIndex->get($key);
            $middleMissing[] = [
                'company' => '',
                // 'level'   => 'Tcode',
                'id'      => $r->tcode ?? $key,
                'value'   => $r->definisi ?? ($r->tcode ?? $key),
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'tcode';
        return view('master-data.compare.uam.missing', compact('localMissing', 'middleMissing', 'scope'));
    }

    public function compositeRoleExist()
    {
        $middle = MidCompositeRole::sapPattern()->ordered()->get(['composite_role', 'definisi']);
        $local  = LocalCompositeRole::select('company_id', 'nama', 'deskripsi')->get();

        $middleIds = $middle->pluck('composite_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $local->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $bothIds = $localIds->intersect($middleIds);

        $localIndex = $local->keyBy(fn($r) => strtoupper(trim($r->nama)));
        $rows = [];
        foreach ($bothIds as $id) {
            $lr = $localIndex->get($id);
            $rows[] = [
                'company' => $lr->company_id ?? '',
                // 'level'   => 'Composite Role',
                'id'      => $lr->nama,
                'value'   => $lr->deskripsi ?? $lr->nama,
            ];
        }
        $this->sort($rows);
        $scope = 'composite_role';
        return view('master-data.compare.uam.exist', compact('rows', 'scope'));
    }

    public function singleRoleExist()
    {
        $middle = MidSingleRole::sapPattern()->ordered()->get(['single_role', 'definisi']);

        $localSingles = LocalSingleRole::select('nama', 'deskripsi')->get();
        $localAOs     = CompositeAO::select('nama', 'deskripsi')->get();

        $combined = [];
        foreach ($localSingles as $sr) {
            $key = strtoupper(trim($sr->nama));
            if ($key === '') continue;
            $combined[$key] = [
                'nama' => $sr->nama,
                'deskripsi' => $sr->deskripsi
            ];
        }
        foreach ($localAOs as $ao) {
            $key = strtoupper(trim($ao->nama));
            if ($key === '') continue;
            if (!isset($combined[$key])) {
                $combined[$key] = [
                    'nama' => $ao->nama,
                    'deskripsi' => $ao->deskripsi
                ];
            } else {
                if (empty($combined[$key]['deskripsi']) && !empty($ao->deskripsi)) {
                    $combined[$key]['deskripsi'] = $ao->deskripsi;
                }
            }
        }
        $localCombined = collect(array_values($combined));

        $middleIds = $middle->pluck('single_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $localCombined->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $bothIds = $localIds->intersect($middleIds);

        $localIndex = $localCombined->keyBy(fn($r) => strtoupper(trim($r['nama'])));

        $rows = [];
        foreach ($bothIds as $id) {
            $lr = $localIndex->get($id);
            $rows[] = [
                'company' => '',
                'id'      => $lr['nama'],
                'value'   => $lr['deskripsi'] ?? $lr['nama'],
            ];
        }

        $this->sort($rows);
        $scope = 'single_role';
        return view('master-data.compare.uam.exist', compact('rows', 'scope'));
    }

    public function tcodeExist()
    {
        $middle = MidTcode::ordered()->get(['tcode', 'definisi']);
        $local  = LocalTcode::select('code', 'deskripsi')->get();

        $middleIds = $middle->pluck('tcode')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $local->pluck('code')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $bothIds = $localIds->intersect($middleIds);

        $localIndex = $local->keyBy(fn($r) => strtoupper(trim($r->code)));
        $rows = [];
        foreach ($bothIds as $id) {
            $lr = $localIndex->get($id);
            $rows[] = [
                'company' => '',
                // 'level'   => 'Tcode',
                'id'      => $lr->code,
                'value'   => $lr->deskripsi ?? $lr->code,
            ];
        }
        $this->sort($rows);
        $scope = 'tcode';
        return view('master-data.compare.uam.exist', compact('rows', 'scope'));
    }

    private function sort(array &$arr): void
    {
        usort($arr, fn($a, $b) => [
            $a['company'] ?? '',
            // $a['level'],
            $a['id']
        ] <=> [
            $b['company'] ?? '',
            // $b['level'],
            $b['id']
        ]);
    }

    public function export(string $scope, string $side)
    {
        // side: local | middle
        $scope = strtolower($scope);
        $side  = strtolower($side);

        switch ($scope) {
            case 'composite_role':
                $middle = MidCompositeRole::sapPattern()->ordered()->get(['composite_role', 'definisi']);
                $local  = LocalCompositeRole::select('company_id', 'nama', 'deskripsi')->get();
                $middleIds = $middle->pluck('composite_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
                $localIds  = $local->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
                $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->composite_role)));
                $localIndex  = $local->keyBy(fn($r) => strtoupper(trim($r->nama)));
                if ($side === 'local') {
                    $only = $localIds->diff($middleIds);
                    $rows = [];
                    foreach ($only as $k) {
                        $r = $localIndex->get($k);
                        $rows[] = [
                            'company' => $r->company_id ?? '',
                            'id'      => $r->nama,
                            'value'   => $r->deskripsi ?? $r->nama,
                        ];
                    }
                } else { // middle
                    $only = $middleIds->diff($localIds);
                    $rows = [];
                    foreach ($only as $k) {
                        $r = $middleIndex->get($k);
                        $rows[] = [
                            'company' => '',
                            'id'      => $r->composite_role ?? $k,
                            'value'   => $r->definisi ?? ($r->composite_role ?? $k),
                        ];
                    }
                }
                $title = ($side === 'local' ? 'LOCAL_ONLY' : 'MIDDLE_ONLY') . '_COMPOSITE_ROLE';
                break;

            case 'single_role':
                $middle = MidSingleRole::sapPattern()->ordered()->get(['single_role', 'definisi']);
                $localSingles = LocalSingleRole::select('nama', 'deskripsi')->get();
                $localAOs     = CompositeAO::select('nama', 'deskripsi')->get();
                $combined = [];
                foreach ($localSingles as $sr) {
                    $k = strtoupper(trim($sr->nama));
                    if ($k === '') continue;
                    $combined[$k] = ['nama' => $sr->nama, 'deskripsi' => $sr->deskripsi];
                }
                foreach ($localAOs as $ao) {
                    $k = strtoupper(trim($ao->nama));
                    if ($k === '') continue;
                    if (!isset($combined[$k])) $combined[$k] = ['nama' => $ao->nama, 'deskripsi' => $ao->deskripsi];
                    else if (empty($combined[$k]['deskripsi']) && !empty($ao->deskripsi)) $combined[$k]['deskripsi'] = $ao->deskripsi;
                }
                $localCombined = collect(array_values($combined));
                $middleIds = $middle->pluck('single_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
                $localIds  = $localCombined->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
                $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->single_role)));
                $localIndex  = $localCombined->keyBy(fn($r) => strtoupper(trim($r['nama'])));
                if ($side === 'local') {
                    $only = $localIds->diff($middleIds);
                    $rows = [];
                    foreach ($only as $k) {
                        $r = $localIndex->get($k);
                        $rows[] = [
                            'company' => '',
                            'id' => $r['nama'],
                            'value' => $r['deskripsi'] ?? $r['nama'],
                        ];
                    }
                } else {
                    $only = $middleIds->diff($localIds);
                    $rows = [];
                    foreach ($only as $k) {
                        $r = $middleIndex->get($k);
                        $rows[] = [
                            'company' => '',
                            'id' => $r->single_role ?? $k,
                            'value' => $r->definisi ?? ($r->single_role ?? $k),
                        ];
                    }
                }
                $title = ($side === 'local' ? 'LOCAL_ONLY' : 'MIDDLE_ONLY') . '_SINGLE_ROLE';
                break;

            case 'tcode':
                $middle = MidTcode::ordered()->get(['tcode', 'definisi']);
                $local  = LocalTcode::select('code', 'deskripsi')->get();
                $middleIds = $middle->pluck('tcode')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
                $localIds  = $local->pluck('code')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
                $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->tcode)));
                $localIndex  = $local->keyBy(fn($r) => strtoupper(trim($r->code)));
                if ($side === 'local') {
                    $only = $localIds->diff($middleIds);
                    $rows = [];
                    foreach ($only as $k) {
                        $r = $localIndex->get($k);
                        $rows[] = [
                            'company' => '',
                            'id' => $r->code,
                            'value' => $r->deskripsi ?? $r->code,
                        ];
                    }
                } else {
                    $only = $middleIds->diff($localIds);
                    $rows = [];
                    foreach ($only as $k) {
                        $r = $middleIndex->get($k);
                        $rows[] = [
                            'company' => '',
                            'id' => $r->tcode ?? $k,
                            'value' => $r->definisi ?? ($r->tcode ?? $k),
                        ];
                    }
                }
                $title = ($side === 'local' ? 'LOCAL_ONLY' : 'MIDDLE_ONLY') . '_TCODE';
                break;

            default:
                abort(404, 'Invalid scope');
        }

        $this->sort($rows);
        $file = $title . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CompareDiffExport($rows, $title), $file);
    }
}
