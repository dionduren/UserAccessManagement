<?php
// filepath: c:\Kerja\Project\2024\05. User Access Management\UserAccessManagement\app\Http\Controllers\CheckpointController.php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Periode;
use App\Models\CompositeRole;
use App\Models\JobRole;
use App\Services\CheckpointServiceOld as CheckpointService;
// use App\Services\CheckpointService;
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
            // ->where('company_code', '!=', 'Z000');
            ->whereNotIn('company_code', ['Z000', 'DA00']); // ✅ Exclude both Z000 and DA00

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
            // ->where('company_code', '!=', 'Z000');
            ->whereNotIn('company_code', ['Z000', 'DA00']); // ✅ Exclude both Z000 and DA00

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

    /**
     * Get job roles that have multiple composite roles assigned
     */
    public function jobRolesWithMultipleComposites(Request $request)
    {
        $companyCode = $request->input('company_code');

        if (!$companyCode) {
            return response()->json([]);
        }

        // Job roles with multiple composite roles
        $jobRoles = JobRole::where('tr_job_roles.company_id', $companyCode) // <-- prefix
            ->select('tr_job_roles.id', 'tr_job_roles.nama', 'tr_job_roles.company_id')
            ->with(['company:company_code,nama'])
            ->join('tr_composite_roles', 'tr_composite_roles.jabatan_id', '=', 'tr_job_roles.id')
            ->whereNull('tr_composite_roles.deleted_at')
            ->groupBy('tr_job_roles.id', 'tr_job_roles.nama', 'tr_job_roles.company_id')
            ->havingRaw('COUNT(DISTINCT tr_composite_roles.id) > 1')
            ->get()
            ->map(function ($jobRole) use ($companyCode) {
                // Get all composite roles for this job role (scoped to same company)
                $composites = CompositeRole::where('jabatan_id', $jobRole->id)
                    ->where('tr_composite_roles.company_id', $companyCode)     // <-- prefix
                    ->whereNull('tr_composite_roles.deleted_at')
                    ->pluck('nama')
                    ->toArray();

                return [
                    'id' => $jobRole->id,
                    'nama' => $jobRole->nama,
                    'company' => $jobRole->company,
                    'composite_count' => count($composites),
                    'composite_roles' => implode(', ', $composites),
                ];
            });

        return response()->json($jobRoles);
    }
}
