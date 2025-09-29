<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\SingleRole;
use App\Models\Kompartemen;
use App\Models\CompositeRole;
use App\Models\userGeneric;
use App\Models\userNIK;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // Refactored Dashboard Controller with Company Donut Chart and Grouped Data

    public function index()
    {
        $user = Auth::user();
        $companyCode = optional($user->loginDetail)->company_code ?? 'A000';
        $group = Company::where('company_code', $companyCode)->value('shortname');

        // DASHBOARD PI - A000 (global)
        if ($companyCode === 'A000') {
            $data = [
                'company'         => Company::all(),
                'kompartemen'     => Kompartemen::count(),
                'departemen'      => Departemen::count(),
                'jobRole'         => JobRole::count(),
                'compositeRole'   => CompositeRole::count(),
                // Single Role & Tcode are GLOBAL (no company relation)
                'singleRole'      => SingleRole::count(),
                'tcode'           => Tcode::count(),
                'JobComp'         => JobRole::has('compositeRole')->count(),
                'JobCompEmpty'    => JobRole::doesntHave('compositeRole')->count(),
                'compJob'         => CompositeRole::has('jobRole')->count(),
                'compJobEmpty'    => CompositeRole::doesntHave('jobRole')->count(),
                'compSingle'      => CompositeRole::has('singleRoles')->count(),
                'compSingleEmpty' => CompositeRole::doesntHave('singleRoles')->count(),
                'singleComp'      => SingleRole::has('compositeRoles')->count(),
                'singleCompEmpty' => SingleRole::doesntHave('compositeRoles')->count(),
                'singleTcode'     => SingleRole::has('tcodes')->count(),
                'singleTcodeEmpty' => SingleRole::doesntHave('tcodes')->count(),
                'tcodeSing'       => Tcode::has('singleRoles')->count(),
                'tcodeSingEmpty'  => Tcode::doesntHave('singleRoles')->count(),
                'nikJob'         => userNIK::has('NIKJobRole')->count(),
                'nikJobEmpty'    => userNIK::doesntHave('NIKJobRole')->count(),
                'genericJob'    => userGeneric::has('NIKJobRole')->count(),
                'genericJobEmpty' => userGeneric::doesntHave('NIKJobRole')->count(),
                'groupedData'     => $this->getGroupedData(), // company-scoped models only
                'groupedDataUAR'  => [
                    'nikJob'          => $this->createGetGroupedDataUAR('nikJob'),
                    'nikJobEmpty'     => $this->createGetGroupedDataUAR('nikJobEmpty'),
                    'genericJob'      => $this->createGetGroupedDataUAR('genericJob'),
                    'genericJobEmpty' => $this->createGetGroupedDataUAR('genericJobEmpty'),
                ],
            ];
            return view('dashboard.index', compact('data'));
        }

        // DASHBOARD NON A000 (company filtered where applicable; SingleRole & Tcode remain global)
        $data = [
            'company'         => Company::where('company_code', $companyCode)->get(),
            'kompartemen'     => Kompartemen::where('company_id', $companyCode)->count(),
            'departemen'      => Departemen::where('company_id', $companyCode)->count(),
            'jobRole'         => JobRole::where('company_id', $companyCode)->count(),
            'compositeRole'   => CompositeRole::where('company_id', $companyCode)->count(),
            // GLOBAL
            'singleRole'      => SingleRole::count(),
            'tcode'           => Tcode::count(),
            'JobComp'         => JobRole::where('company_id', $companyCode)->has('compositeRole')->count(),
            'JobCompEmpty'    => JobRole::where('company_id', $companyCode)->doesntHave('compositeRole')->count(),
            'compJob'         => CompositeRole::where('company_id', $companyCode)->has('jobRole')->count(),
            'compJobEmpty'    => CompositeRole::where('company_id', $companyCode)->doesntHave('jobRole')->count(),
            'compSingle'      => CompositeRole::where('company_id', $companyCode)->has('singleRoles')->count(),
            'compSingleEmpty' => CompositeRole::where('company_id', $companyCode)->doesntHave('singleRoles')->count(),
            // GLOBAL relationships
            'singleComp'      => SingleRole::has('compositeRoles')->count(),
            'singleCompEmpty' => SingleRole::doesntHave('compositeRoles')->count(),
            'singleTcode'     => SingleRole::has('tcodes')->count(),
            'singleTcodeEmpty' => SingleRole::doesntHave('tcodes')->count(),
            'nikJob'         => userNIK::where('group', $group)->has('NIKJobRole')->count(),
            'nikJobEmpty'    => userNIK::where('group', $group)->doesntHave('NIKJobRole')->count(),
            'genericJob'    => userGeneric::where('group', $group)->has('NIKJobRole')->count(),
            'genericJobEmpty' => userGeneric::where('group', $group)->doesntHave('NIKJobRole')->count(),
            'groupedData'     => $this->getGroupedData($companyCode),
            'groupedDataUAR'  => [
                'nikJob'    => $this->createGetGroupedDataUAR('nikJob', $companyCode),
                'nikJobEmpty'    => $this->createGetGroupedDataUAR('nikJobEmpty', $companyCode),
                'genericJob' => $this->createGetGroupedDataUAR('genericJob', $companyCode),
                'genericJobEmpty' => $this->createGetGroupedDataUAR('genericJobEmpty', $companyCode),
            ],
        ];

        return view('dashboard.index_company', [
            'data' => $data,
            'companyCode' => $companyCode,
        ]);
    }

    private function getGroupedData(?string $companyCode = null)
    {
        // Only models that STILL have company_id
        $models = ['Kompartemen', 'Departemen', 'JobRole', 'CompositeRole'];
        $filter = $companyCode && $companyCode !== 'A000';

        $companiesQuery = Company::select('company_code', 'nama');
        if ($filter) {
            $companiesQuery->where('company_code', $companyCode);
        }
        $companies = $companiesQuery->get();

        $groupedData = [];
        foreach ($models as $model) {
            $q = app("App\\Models\\$model")::selectRaw('company_id, COUNT(*) as total')
                ->groupBy('company_id');
            if ($filter) {
                $q->where('company_id', $companyCode);
            }
            $groupedData[strtolower($model)] = $q->pluck('total', 'company_id')->toArray();
        }

        // Empty metrics for company-scoped models
        $emptyMetrics = [
            'JobCompEmpty'     => JobRole::doesntHave('compositeRole')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->pluck('total', 'company_id')->toArray(),
            'compJobEmpty'     => CompositeRole::doesntHave('jobRole')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->pluck('total', 'company_id')->toArray(),
            'compSingleEmpty'  => CompositeRole::doesntHave('singleRoles')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->pluck('total', 'company_id')->toArray(),
            'nikJobEmpty'     => userNIK::doesntHave('NIKJobRole')
                ->when($filter, fn($q) => $q->where('group', $companyCode))
                ->selectRaw('"group" as company_id, COUNT(*) as total')->groupBy('company_id')->pluck('total', 'company_id')->toArray(),
            'genericJobEmpty' => userGeneric::doesntHave('NIKJobRole')
                ->when($filter, fn($q) => $q->where('group', $companyCode))
                ->selectRaw('"group" as company_id, COUNT(*) as total')->groupBy('company_id')->pluck('total', 'company_id')->toArray(),
        ];

        // Global (no company_id) SingleRole & Tcode empties kept separately
        $globalNoCompany = [
            'singleCompEmpty'  => SingleRole::doesntHave('compositeRoles')->count(),
            'singleTcodeEmpty' => SingleRole::doesntHave('tcodes')->count(),
        ];

        return [
            'companies'     => $companies,
            'data'          => $groupedData,
            'emptyMetrics'  => $emptyMetrics,
            'globalMetrics' => $globalNoCompany,
        ];
    }

    public function createGetGroupedDataUAR(string $metric, ?string $companyCode = null)
    {
        $user = Auth::user();
        $companyCode = $companyCode ?? optional($user->loginDetail)->company_code ?? 'A000';
        $filter = $companyCode && $companyCode !== 'A000';
        $convertedGroup = $filter
            ? (Company::where('company_code', $companyCode)->value('shortname') ?? $companyCode)
            : null;

        $definitions = [
            'nikJob' => [
                'model'     => userNIK::class,
                'relation'  => 'NIKJobRole',
                'aggregate' => 'has',
            ],
            'nikJobEmpty' => [
                'model'     => userNIK::class,
                'relation'  => 'NIKJobRole',
                'aggregate' => 'doesntHave',
            ],
            'genericJob' => [
                'model'     => userGeneric::class,
                'relation'  => 'NIKJobRole',
                'aggregate' => 'has',
            ],
            'genericJobEmpty' => [
                'model'     => userGeneric::class,
                'relation'  => 'NIKJobRole',
                'aggregate' => 'doesntHave',
            ],
        ];

        if (! isset($definitions[$metric])) {
            return [];
        }

        $definition = $definitions[$metric];
        $query = $definition['model']::query()
            ->selectRaw('"group" as shortname, COUNT(*) as total')
            ->groupBy('shortname');

        $definition['aggregate'] === 'has'
            ? $query->has($definition['relation'])
            : $query->doesntHave($definition['relation']);

        if ($filter) {
            $query->where('group', $convertedGroup);
        }

        return $query->pluck('total', 'shortname')->toArray();
    }

    public function getJobRolesCompositeEmpty(Request $request)
    {
        $companyCode = $request->query('company_code') ?? optional(Auth::user()->loginDetail)->company_code;
        $jobRoles = JobRole::doesntHave('compositeRole')
            ->when($companyCode && $companyCode !== 'A000', fn($q) => $q->where('company_id', $companyCode))
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($jobRoles);
    }

    public function getCompositeRolesJobEmpty(Request $request)
    {
        $companyCode = $request->query('company_code') ?? optional(Auth::user()->loginDetail)->company_code;
        $compositeRoles = CompositeRole::doesntHave('jobRole')
            ->when($companyCode && $companyCode !== 'A000', fn($q) => $q->where('company_id', $companyCode))
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($compositeRoles);
    }

    public function getCompositeRolesSingleEmpty(Request $request)
    {
        $companyCode = $request->query('company_code') ?? optional(Auth::user()->loginDetail)->company_code;
        $compositeRoles = CompositeRole::doesntHave('singleRoles')
            ->when($companyCode && $companyCode !== 'A000', fn($q) => $q->where('company_id', $companyCode))
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($compositeRoles);
    }

    public function getSingleRolesCompositeEmpty(Request $request)
    {
        // Now global
        $singleRoles = SingleRole::doesntHave('compositeRoles')
            ->get(['id', 'nama']);
        return response()->json($singleRoles);
    }

    public function getSingleRolesTcodeEmpty(Request $request)
    {
        $singleRoles = SingleRole::doesntHave('tcodes')
            ->get(['id', 'nama']);
        return response()->json($singleRoles);
    }

    public function getTcodesSingleEmpty(Request $request)
    {
        $tcodes = Tcode::doesntHave('singleRoles')
            ->get(['id', 'nama'])   // use 'nama' (model has no 'code' column)
            ->map(fn($t) => ['id' => $t->id, 'nama' => $t->nama])
            ->values();
        return response()->json($tcodes);
    }

    public function getNikJobEmpty(Request $request)
    {
        $authCompany = optional(Auth::user()->loginDetail)->company_code ?? 'A000';
        $selectedCompany = $authCompany === 'A000'
            ? ($request->query('company_code') ?? 'A000')
            : $authCompany;

        $query = userNIK::query()
            ->with(['Company:company_code,nama,shortname'])
            ->doesntHave('NIKJobRole');

        if ($selectedCompany !== 'A000') {
            $groupShortname = Company::where('company_code', $selectedCompany)->value('shortname') ?? $selectedCompany;
            $query->where('group', $groupShortname);
        }

        $nikJobsEmpty = $query
            ->get(['id', 'user_code', 'group'])
            ->map(fn($item) => [
                'id'      => $item->id,
                'nama'    => $item->user_code,
                'company' => [
                    'nama'       => $item->Company->nama ?? '-',
                    'shortname'  => $item->Company->shortname ?? $item->group,
                ],
            ])
            ->values();

        return response()->json($nikJobsEmpty);
    }

    public function getGenericJobEmpty(Request $request)
    {
        $authCompany = optional(Auth::user()->loginDetail)->company_code ?? 'A000';
        $selectedCompany = $authCompany === 'A000'
            ? ($request->query('company_code') ?? 'A000')
            : $authCompany;

        $query = userGeneric::query()
            ->with(['Company:company_code,nama,shortname'])
            ->doesntHave('NIKJobRole');

        if ($selectedCompany !== 'A000') {
            $groupShortname = Company::where('company_code', $selectedCompany)->value('shortname') ?? $selectedCompany;
            $query->where('group', $groupShortname);
        }

        $genericJobsEmpty = $query
            ->get(['id', 'user_code', 'group'])
            ->map(fn($item) => [
                'id'      => $item->id,
                'nama'    => $item->user_code,
                'company' => [
                    'nama'      => $item->Company->nama ?? '-',
                    'shortname' => $item->Company->shortname ?? $item->group,
                ],
            ])
            ->values();

        return response()->json($genericJobsEmpty);
    }
}
