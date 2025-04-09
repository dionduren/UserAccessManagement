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
        $groupedData = [];

        foreach ($models as $model) {
            $groupedData[strtolower($model)] = app("App\\Models\\$model")::selectRaw('company_id, COUNT(*) as total')
                ->with('company:company_code,nama') // Load company relationship with selected fields
                ->groupBy('company_id')
                ->get();
        }

        return $groupedData;
    }
}
