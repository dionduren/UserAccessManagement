<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\SingleRole;
use App\Models\Kompartemen;
use Illuminate\Http\Request;
use App\Models\CompositeRole;
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

        // DASHBOARD PI - A000
        if ($companyCode === 'A000') {
            $data = [
                'company' => Company::all(),
                'kompartemen' => Kompartemen::count(),
                'departemen' => Departemen::count(),
                'jobRole' => JobRole::count(),
                'compositeRole' => CompositeRole::count(),
                'singleRole' => SingleRole::count(),
                'tcode' => Tcode::count(),
                'JobComp' => JobRole::has('compositeRole')->count(),
                'JobCompEmpty' => JobRole::doesntHave('compositeRole')->count(),
                'compJob' => CompositeRole::has('jobRole')->count(),
                'compJobEmpty' => CompositeRole::doesntHave('jobRole')->count(),
                'compSingle' => CompositeRole::has('singleRoles')->count(),
                'compSingleEmpty' => CompositeRole::doesntHave('singleRoles')->count(),
                'singleComp' => SingleRole::has('compositeRoles')->count(),
                'singleCompEmpty' => SingleRole::doesntHave('compositeRoles')->count(),
                'singleTcode' => SingleRole::has('tcodes')->count(),
                'singleTcodeEmpty' => SingleRole::doesntHave('tcodes')->count(),
                'tcodeSing' => Tcode::has('singleRoles')->count(),
                'tcodeSingEmpty' => Tcode::doesntHave('singleRoles')->count(),
                'groupedData' => $this->getGroupedData(), // all companies
            ];

            return view('dashboard.index', compact('data'));
        }

        // DASHBOARD NON A000
        $data = [
            'company' => Company::where('company_code', $companyCode)->get(),
            'kompartemen' => Kompartemen::where('company_id', $companyCode)->count(),
            'departemen' => Departemen::where('company_id', $companyCode)->count(),
            'jobRole' => JobRole::where('company_id', $companyCode)->count(),
            'compositeRole' => CompositeRole::where('company_id', $companyCode)->count(),
            'singleRole' => SingleRole::where('company_id', $companyCode)->count(),
            'tcode' => Tcode::count(), // global if tcodes are shared
            'JobComp' => JobRole::where('company_id', $companyCode)->has('compositeRole')->count(),
            'JobCompEmpty' => JobRole::where('company_id', $companyCode)->doesntHave('compositeRole')->count(),
            'compJob' => CompositeRole::where('company_id', $companyCode)->has('jobRole')->count(),
            'compJobEmpty' => CompositeRole::where('company_id', $companyCode)->doesntHave('jobRole')->count(),
            'compSingle' => CompositeRole::where('company_id', $companyCode)->has('singleRoles')->count(),
            'compSingleEmpty' => CompositeRole::where('company_id', $companyCode)->doesntHave('singleRoles')->count(),
            'singleComp' => SingleRole::where('company_id', $companyCode)->has('compositeRoles')->count(),
            'singleCompEmpty' => SingleRole::where('company_id', $companyCode)->doesntHave('compositeRoles')->count(),
            'singleTcode' => SingleRole::where('company_id', $companyCode)->has('tcodes')->count(),
            'singleTcodeEmpty' => SingleRole::where('company_id', $companyCode)->doesntHave('tcodes')->count(),
            // 'tcodeSing' => Tcode::whereHas('singleRoles', function ($q) use ($companyCode) {
            //     $q->where('single_roles.company_id', $companyCode);
            // })->count(),
            // 'tcodeSingEmpty' => Tcode::whereDoesntHave('singleRoles', function ($q) use ($companyCode) {
            //     $q->where('single_roles.company_id', $companyCode);
            // })->count(),
            'groupedData' => $this->getGroupedData($companyCode), // filtered
        ];

        return view('dashboard.index_company', [
            'data' => $data,
            'companyCode' => $companyCode,
        ]);
    }

    private function getGroupedData(?string $companyCode = null)
    {
        $filter = $companyCode && $companyCode !== 'A000';

        $companiesQuery = Company::select('company_code', 'nama');
        if ($filter) {
            $companiesQuery->where('company_code', $companyCode);
        }
        $companies = $companiesQuery->get();

        $models = ['Kompartemen', 'Departemen', 'JobRole', 'CompositeRole', 'SingleRole'];
        $groupedData = [];

        foreach ($models as $model) {
            $q = app("App\\Models\\$model")::selectRaw('company_id, COUNT(*) as total')
                ->with('company:company_code,nama')
                ->groupBy('company_id');

            if ($filter) {
                $q->where('company_id', $companyCode);
            }

            $groupedData[strtolower($model)] = $q->get()->pluck('total', 'company_id')->toArray();
        }

        $emptyMetrics = [
            'JobCompEmpty' => JobRole::doesntHave('compositeRole')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),

            'compJobEmpty' => CompositeRole::doesntHave('jobRole')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),

            'compSingleEmpty' => CompositeRole::doesntHave('singleRoles')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),

            'singleCompEmpty' => SingleRole::doesntHave('compositeRoles')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),

            'singleTcodeEmpty' => SingleRole::doesntHave('tcodes')
                ->when($filter, fn($q) => $q->where('company_id', $companyCode))
                ->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),
        ];

        return [
            'companies' => $companies,
            'data' => $groupedData,
            'emptyMetrics' => $emptyMetrics,
        ];
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
        $companyCode = $request->query('company_code') ?? optional(Auth::user()->loginDetail)->company_code;
        $singleRoles = SingleRole::doesntHave('compositeRoles')
            ->when($companyCode && $companyCode !== 'A000', fn($q) => $q->where('company_id', $companyCode))
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($singleRoles);
    }

    public function getSingleRolesTcodeEmpty(Request $request)
    {
        $companyCode = $request->query('company_code') ?? optional(Auth::user()->loginDetail)->company_code;
        $singleRoles = SingleRole::doesntHave('tcodes')
            ->when($companyCode && $companyCode !== 'A000', fn($q) => $q->where('company_id', $companyCode))
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($singleRoles);
    }

    public function getTcodesSingleEmpty(Request $request)
    {
        $companyCode = $request->query('company_code') ?? optional(Auth::user()->loginDetail)->company_code ?? 'A000';

        $tcodes = Tcode::whereDoesntHave('singleRoles', function ($q) use ($companyCode) {
            // If A000, this behaves like global "no single role at all"
            if ($companyCode !== 'A000') {
                $q->where('single_roles.company_id', $companyCode);
            }
        })
            ->get(['id', 'code']);

        $company = Company::where('company_code', $companyCode)->first(['company_code', 'nama']);

        $tcodes->transform(function ($item) use ($company, $companyCode) {
            return (object)[
                'id' => $item->id,
                'nama' => $item->code,
                'company_id' => $companyCode,
                'company' => $company,
            ];
        });

        return response()->json($tcodes);
    }
}
