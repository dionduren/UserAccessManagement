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

        $companies = Company::orderBy('company_code')->get(['company_code', 'nama', 'shortname']);

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

        $companies = Company::orderBy('company_code')->get(['company_code', 'nama', 'shortname']);
        $this->service->refresh($validated['periode_id'], $companies);

        return redirect()
            ->route('checkpoints.index', ['periode_id' => $validated['periode_id']])
            ->with('success', 'Checkpoint progress updated.');
    }
}
