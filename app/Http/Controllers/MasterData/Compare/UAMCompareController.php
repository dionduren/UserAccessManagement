<?php

namespace App\Http\Controllers\MasterData\Compare;

use App\Http\Controllers\Controller;
use App\Models\CompositeRole as LocalCompositeRole;
use App\Models\SingleRole as LocalSingleRole;
use App\Models\Tcode as LocalTcode;
use App\Models\middle_db\CompositeRole as MidCompositeRole;
use App\Models\middle_db\SingleRole as MidSingleRole;
use App\Models\middle_db\Tcode as MidTcode;

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
        $middle = MidSingleRole::sapPattern()->ordered()->get(['single_role', 'definisi']);
        $local  = LocalSingleRole::select('company_id', 'nama', 'deskripsi')->get();

        $middleIds = $middle->pluck('single_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $local->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->keyBy(fn($r) => strtoupper(trim($r->single_role)));

        $localMissing = [];
        foreach ($local as $row) {
            $key = strtoupper(trim($row->nama));
            if ($localOnly->contains($key)) {
                $localMissing[] = [
                    'company' => $row->company_id ?? '',
                    // 'level'   => 'Single Role',
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
                // 'level'   => 'Single Role',
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
                    'company' => $row->company_id ?? '',
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
        $local  = LocalSingleRole::select('company_id', 'nama', 'deskripsi')->get();

        $middleIds = $middle->pluck('single_role')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();
        $localIds  = $local->pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->unique();

        $bothIds = $localIds->intersect($middleIds);

        $localIndex = $local->keyBy(fn($r) => strtoupper(trim($r->nama)));
        $rows = [];
        foreach ($bothIds as $id) {
            $lr = $localIndex->get($id);
            $rows[] = [
                'company' => $lr->company_id ?? '',
                // 'level'   => 'Single Role',
                'id'      => $lr->nama,
                'value'   => $lr->deskripsi ?? $lr->nama,
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
                'company' => $lr->company_id ?? '',
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
}
