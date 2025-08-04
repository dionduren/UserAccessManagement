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
            'groupedData' => $this->getGroupedData()
        ];

        return view('dashboard.index', compact('data'));
    }

    private function getGroupedData()
    {
        $models = ['Kompartemen', 'Departemen', 'JobRole', 'CompositeRole', 'SingleRole'];
        $companies = Company::select('company_code', 'nama')->get();
        $groupedData = [];

        foreach ($models as $model) {
            $data = $groupedData[strtolower($model)] = app("App\\Models\\$model")::selectRaw('company_id, COUNT(*) as total')
                ->with('company:company_code,nama') // Load company relationship with selected fields
                ->groupBy('company_id')
                ->get()
                ->pluck('total', 'company_id')
                ->toArray();

            $groupedData[strtolower($model)] = $data;
        }

        // Group "empty" metrics per company
        $emptyMetrics = [
            'JobCompEmpty' => JobRole::doesntHave('compositeRole')->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),
            'compJobEmpty' => CompositeRole::doesntHave('jobRole')->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),
            'compSingleEmpty' => CompositeRole::doesntHave('singleRoles')->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),
            'singleCompEmpty' => SingleRole::doesntHave('compositeRoles')->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),
            'singleTcodeEmpty' => SingleRole::doesntHave('tcodes')->selectRaw('company_id, COUNT(*) as total')->groupBy('company_id')->get()->pluck('total', 'company_id')->toArray(),
        ];

        return [
            'companies' => $companies,
            'data' => $groupedData,
            'emptyMetrics' => $emptyMetrics
        ];
    }

    public function getJobRolesCompositeEmpty()
    {
        $jobRoles = JobRole::doesntHave('compositeRole')
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($jobRoles);
    }

    public function getCompositeRolesJobEmpty()
    {
        $compositeRoles = CompositeRole::doesntHave('jobRole')
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($compositeRoles);
    }

    public function getCompositeRolesSingleEmpty()
    {
        $compositeRoles = CompositeRole::doesntHave('singleRoles')
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($compositeRoles);
    }

    public function getSingleRolesCompositeEmpty()
    {
        $singleRoles = SingleRole::doesntHave('compositeRoles')
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($singleRoles);
    }

    public function getSingleRolesTcodeEmpty()
    {
        $singleRoles = SingleRole::doesntHave('tcodes')
            ->with('company:company_code,nama')
            ->get(['id', 'nama', 'company_id']);

        return response()->json($singleRoles);
    }

    public function getTcodesSingleEmpty()
    {
        $tcodes = Tcode::doesntHave('singleRoles')
            ->get(['id', 'code']);

        $company = Company::where('company_code', 'A000')->first(['company_code', 'nama']);

        $tcodes->transform(function ($item) use ($company) {
            $item = (object)[
                'id' => $item->id,
                'nama' => $item->code,
                'company_id' => 'A000',
                'company' => $company
            ];

            return $item;
        });

        return response()->json($tcodes);
    }
}
