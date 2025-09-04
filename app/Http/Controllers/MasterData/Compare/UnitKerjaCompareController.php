<?php

namespace App\Http\Controllers\MasterData\Compare;

use App\Http\Controllers\Controller;
use App\Models\middle_db\UnitKerja;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\CostCenter;

class UnitKerjaCompareController extends Controller
{
    public function company()
    {
        $middle = UnitKerja::select('company')->get();
        $local  = Company::all();

        $middleIds = $middle->pluck('company')->filter()->map(fn($v) => trim($v))->unique();
        $localIds  = $local->pluck('company_code')->filter()->map(fn($v) => trim($v))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $localMissing = [];
        foreach ($local->whereIn('company_code', $localOnly) as $c) {
            $localMissing[] = [
                'company' => $c->company_code,
                'level'   => 'Company',
                'id'      => $c->company_code,
                'value'   => $c->nama
            ];
        }

        $middleMissing = [];
        foreach ($middleOnly as $id) {
            $middleMissing[] = [
                'company' => $id,
                'level'   => 'Company',
                'id'      => $id,
                'value'   => $id
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'company';
        return view('master-data.compare.unit_kerja', compact('localMissing', 'middleMissing', 'scope'));
    }

    public function kompartemen()
    {
        $middle = UnitKerja::select('kompartemen_id', 'kompartemen', 'company')->get();
        $local  = Kompartemen::all();

        $middleIds = $middle->pluck('kompartemen_id')->filter()->map(fn($v) => trim($v))->unique();
        $localIds  = $local->pluck('kompartemen_id')->filter()->map(fn($v) => trim($v))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->groupBy(fn($r) => trim($r->kompartemen_id));

        $localMissing = [];
        foreach ($local->whereIn('kompartemen_id', $localOnly) as $k) {
            $localMissing[] = [
                'company' => $k->company_id,
                'level'   => 'Kompartemen',
                'id'      => $k->kompartemen_id,
                'value'   => $k->nama
            ];
        }

        $middleMissing = [];
        foreach ($middleOnly as $id) {
            $row = optional($middleIndex[$id])->first();
            $middleMissing[] = [
                'company' => $row->company ?? '',
                'level'   => 'Kompartemen',
                'id'      => $id,
                'value'   => $row->kompartemen ?? $id
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'kompartemen';
        return view('master-data.compare.unit_kerja', compact('localMissing', 'middleMissing', 'scope'));
    }

    public function departemen()
    {
        $middle = UnitKerja::select('departemen_id', 'departemen', 'company')->get();
        $local  = Departemen::all();

        $middleIds = $middle->pluck('departemen_id')->filter()->map(fn($v) => trim($v))->unique();
        $localIds  = $local->pluck('departemen_id')->filter()->map(fn($v) => trim($v))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->groupBy(fn($r) => trim($r->departemen_id));

        $localMissing = [];
        foreach ($local->whereIn('departemen_id', $localOnly) as $d) {
            $localMissing[] = [
                'company' => $d->company_id,
                'level'   => 'Departemen',
                'id'      => $d->departemen_id,
                'value'   => $d->nama
            ];
        }

        $middleMissing = [];
        foreach ($middleOnly as $id) {
            $row = optional($middleIndex[$id])->first();
            $middleMissing[] = [
                'company' => $row->company ?? '',
                'level'   => 'Departemen',
                'id'      => $id,
                'value'   => $row->departemen ?? $id
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'departemen';
        return view('master-data.compare.unit_kerja', compact('localMissing', 'middleMissing', 'scope'));
    }

    public function costCenter()
    {
        $middle = UnitKerja::select('cost_center', 'company', 'kompartemen', 'departemen')->get();
        $localCostCenters = CostCenter::all();
        $localDepartemen  = Departemen::all();
        $localKompartemen = Kompartemen::all();
        $localCompanies   = Company::all();

        $departemenByCC = $localDepartemen->keyBy('cost_center');
        $kompMap        = $localKompartemen->keyBy('kompartemen_id');
        $compMap        = $localCompanies->keyBy('company_code');

        $middleIds = $middle->pluck('cost_center')->filter()->map(fn($v) => trim($v))->unique();
        $localIds  = $localCostCenters->pluck('cost_center')->filter()->map(fn($v) => trim($v))->unique();

        $localOnly  = $localIds->diff($middleIds);
        $middleOnly = $middleIds->diff($localIds);

        $middleIndex = $middle->groupBy(fn($r) => trim($r->cost_center));

        $localMissing = [];
        foreach ($localCostCenters->whereIn('cost_center', $localOnly) as $cc) {
            $dep  = $departemenByCC->get($cc->cost_center);
            $komp = $dep ? $kompMap->get($dep->kompartemen_id) : null;
            $comp = $dep ? $compMap->get($dep->company_id) : null;
            $parts = array_filter([
                $comp->company_code ?? null,
                $komp->nama ?? null,
                $dep->nama ?? null
            ]);
            $val = implode(' - ', $parts) ?: $cc->cost_center;
            $localMissing[] = [
                'company' => $comp->company_code ?? '',
                'level'   => 'Cost Center',
                'id'      => $cc->cost_center,
                'value'   => $val
            ];
        }

        $middleMissing = [];
        foreach ($middleOnly as $id) {
            $row = optional($middleIndex[$id])->first();
            if ($row) {
                $parts = array_filter([
                    $row->company,
                    $row->kompartemen ?? null,
                    $row->departemen ?? null
                ]);
                $val = implode(' - ', $parts) ?: $id;
                $compCode = $row->company;
            } else {
                $val = $id;
                $compCode = '';
            }
            $middleMissing[] = [
                'company' => $compCode,
                'level'   => 'Cost Center',
                'id'      => $id,
                'value'   => $val
            ];
        }

        $this->sort($localMissing);
        $this->sort($middleMissing);

        $scope = 'cost_center';
        return view('master-data.compare.unit_kerja', compact('localMissing', 'middleMissing', 'scope'));
    }

    private function sort(array &$arr): void
    {
        usort($arr, fn($a, $b) => [
            $a['company'] ?? '',
            $a['level'],
            $a['id']
        ] <=> [
            $b['company'] ?? '',
            $b['level'],
            $b['id']
        ]);
    }
}
