<?php
// filepath: app/Http/Controllers/CheckpointController.php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Periode;
use App\Services\CheckpointService;
use Illuminate\Http\Request;

class CheckpointController extends Controller
{
    public function __construct(private readonly CheckpointService $service) {}

    public function index(Request $request)
    {
        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);
        $selectedPeriode = $request->query('periode_id', $periodes->first()?->id);

        $userCompanyCode = optional(auth()->user()->loginDetail)->company_code;

        $companiesQuery = Company::query()
            ->where('company_code', '!=', 'Z000');

        if ($userCompanyCode && $userCompanyCode !== 'A000') {
            // Get all companies with the same first character as userCompany
            $firstChar = substr($userCompanyCode, 0, 1);
            $companiesQuery->where('company_code', 'LIKE', $firstChar . '%');
        }

        $companies = $companiesQuery
            ->orderBy('company_code')
            ->get(['company_code', 'nama', 'shortname']);

        $matrix = $this->service->getProgress($selectedPeriode, $companies);

        return view('checkpoints.index', [
            'periodes'        => $periodes,
            'selectedPeriode' => $selectedPeriode,
            'companies'       => $companies,
            'steps'           => $this->service->steps(),
            'matrix'          => $matrix,
        ]);
    }

    public function refresh(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|integer|exists:ms_periode,id',
        ]);

        $userCompanyCode = optional(auth()->user()->loginDetail)->company_code;

        $companiesQuery = Company::query()
            ->where('company_code', '!=', 'Z000');

        if ($userCompanyCode && $userCompanyCode !== 'A000') {
            $companiesQuery->where('company_code', $userCompanyCode);
        }

        $companies = $companiesQuery
            ->orderBy('company_code')
            ->get(['company_code', 'nama', 'shortname']);

        $this->service->refresh($validated['periode_id'], $companies);

        return redirect()
            ->route('checkpoints.index', ['periode_id' => $validated['periode_id']])
            ->with('success', 'Checkpoint progress updated.');
    }
}
