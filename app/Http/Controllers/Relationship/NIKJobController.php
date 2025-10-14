<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\Periode;
use App\Models\userNIK;
use App\Models\NIKJobRole;
use App\Exports\UserNIKWithoutJobRoleExport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class NIKJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodes = Periode::select('id', 'definisi')->get();
        return view('relationship.nik_job_role.index', compact('periodes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $periodes = Periode::select('id', 'definisi')->get();
        $userNIKs = userNIK::select('id', 'user_code')->get();
        $companies  = Company::all();  // or ->select('id','name')->get();

        return view('relationship.nik_job_role.create', compact('periodes', 'companies', 'userNIKs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'periode_id' => 'required|exists:ms_periode,id',
        //     'job_role_id' => 'required|exists:tr_job_roles,id',
        //     'user_code' => 'required|exists:tr_user_ussm_nik,user_code',
        // ]);

        // NIKJobRole::create([
        //     'periode_id' => $request->input('periode_id'),
        //     'job_role_id' => $request->input('job_role_id'),
        //     'nik' => $request->input('user_code'),
        // ]);

        // return redirect()->route('nik-job.index')->with('success', 'NIK Job Role created successfully.');
        $request->validate([
            'periode_id' => 'required|exists:ms_periode,id',
            'job_role_id' => 'required|exists:tr_job_roles,job_role_id',
            'nik' => 'required|exists:tr_user_ussm_nik,user_code',
        ]);

        // Check for existing assignment in this period
        $exists = NIKJobRole::where([
            'periode_id' => $request->periode_id,
            'nik' => $request->user_code,
            'job_role_id' => $request->job_role_id,
        ])->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'This user already has this job role for the selected period']);
        }

        NIKJobRole::create([
            'periode_id' => $request->periode_id,
            'nik' => $request->user_code,
            'job_role_id' => $request->job_role_id,
            'is_active' => true,
            // 'last_update' => now(),
            'created_by' => auth()->user()->name
        ]);

        return redirect()->route('nik-job.index')
            ->with('success', 'Job Role assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $nikJobRole = NIKJobRole::findOrFail($id);
        // It is assumed that you have defined the following relationships:
        // - $nikJobRole->periode
        // - $nikJobRole->userNIK
        // - $nikJobRole->jobRole and within it:
        //      ->company, ->kompartemen, and ->departemen

        return view('relationship.nik_job_role.show', compact('nikJobRole'));
    }

    public function modalShow($id)
    {
        $nikJobRole = NIKJobRole::with([
            'periode',
            'userNIK.unitKerja',
            'jobRole.company',
            'jobRole.kompartemen',
            'jobRole.departemen'
        ])->findOrFail($id);

        // Return only the modal content view
        return view('relationship.nik_job_role.show', compact('nikJobRole'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $companies = [];
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        if ($userCompany) {

            if ($userCompany && $userCompany !== 'A000') {
                $companies = Company::where('company_code', $userCompany)->get();
            } else {
                $companies = Company::all();
            }

            // Find the record or fail with a 404.
            $nikJobRole = NIKJobRole::findOrFail($id);

            // Get all required data for the dropdowns.
            $periodes  = Periode::select('id', 'definisi')->get();
            $userNIKs  = userNIK::select('id', 'user_code')->get();
            // $companies = Company::all(); // or select only id and name

            // Pass the NIKJobRole record along with dropdown data.
            return view('relationship.nik_job_role.edit', compact('nikJobRole', 'periodes', 'companies', 'userNIKs', 'userCompany'));
        }
        return redirect()->route('login')->with('error', 'Unauthorized access.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'periode_id'   => 'required|exists:ms_periode,id',
            'job_role_id'  => 'required|exists:tr_job_roles,job_role_id',
            'nik'    => 'required|exists:tr_user_ussm_nik,user_code', // adjust if needed
        ]);

        $nikJobRole = NIKJobRole::findOrFail($id);
        $nikJobRole->update([
            'periode_id'  => $request->input('periode_id'),
            'job_role_id' => $request->input('job_role_id'),
            'nik'         => $request->input('nik'),
        ]);

        return redirect()->route('nik-job.index')
            ->with('success', 'NIK Job Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NIKJobRole $nIKJobRole)
    {
        //
    }


    /**
     * Get NIK Job Roles based on Periode ID from request input
     */
    public function getNIKJobRolesByPeriodeId(Request $request)
    {
        $periodeId = $request->input('periode_id');

        $nikJobRoles = NIKJobRole::select('id', 'nik', 'job_role_id', 'periode_id')
            ->with([
                'jobRole' => function ($query) {
                    $query->select('id', 'job_role_id', 'nama', 'company_id', 'kompartemen_id');
                },
                'jobRole.company' => function ($query) {
                    $query->select('company_code', 'nama');
                },
                'jobRole.kompartemen' => function ($query) {
                    $query->select('kompartemen_id', 'nama');
                },
                'periode' => function ($query) {
                    $query->select('id', 'definisi');
                },
                'unitKerja' => function ($query) {
                    $query->select('nik', 'nama');
                }
            ])
            ->where('periode_id', $periodeId)
            ->whereHas('unitKerja') // Only records with related userDetail
            ->get();

        return DataTables::of($nikJobRoles)
            ->addColumn('nama', function ($row) {
                return $row->unitKerja ? $row->unitKerja->nama : '-';
            })
            ->addColumn('job_role', function ($row) {
                return $row->jobRole ? $row->jobRole->nama : '-';
            })
            ->addColumn('company', function ($row) {
                return ($row->jobRole && $row->jobRole->company) ? $row->jobRole->company->nama : '-';
            })
            ->addColumn('kompartemen', function ($row) {
                return ($row->jobRole && $row->jobRole->kompartemen) ? $row->jobRole->kompartemen->nama : '-';
            })
            ->addColumn('periode', function ($row) {
                return $row->periode ? $row->periode->definisi : '-';
            })
            ->addColumn('action', function ($row) {
                return '
            <a href="' . route('nik-job.show', $row->id) . '" target="_blank" class="btn btn-sm btn-primary me-1">
            <i class="bi bi-info-circle-fill"></i> Detail
            </a>
            <a href="' . route('nik-job.edit', $row->id) . '" target="_blank" class="btn btn-sm btn-warning me-1">
            <i class="bi bi-pencil-fill"></i> Edit
            </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Display a listing of the resource without job roles.
     */
    public function indexWithoutJobRole(Request $request)
    {
        $periodes = Periode::select('id', 'definisi')->get();
        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companyShortname = Company::where('company_code', $userCompany)->value('shortname');

        if ($request->ajax()) {
            if (!$request->filled('periode')) {
                return DataTables::of(collect([]))->make(true);
            }

            $periode = $request->input('periode');

            $query = userNIK::query()
                ->select([
                    'tr_user_ussm_nik.id',
                    'tr_user_ussm_nik.group',
                    'tr_user_ussm_nik.user_code',
                    'user_details.nama as nama',
                    'kompartemen.nama as kompartemen',
                    'departemen.nama as departemen',
                ])
                ->where('group', $companyShortname) // Filter by user's company
                // Aggregate any wrong job_role_id(s) for this user+periode (comma-separated)
                ->selectSub(function ($sub) use ($periode) {
                    $sub->from('tr_ussm_job_role as jr')
                        ->leftJoin('tr_job_roles as j', function ($join) {
                            $join->on('jr.job_role_id', '=', 'j.job_role_id')
                                ->whereNull('j.deleted_at'); // treat soft-deleted as missing
                        })
                        ->selectRaw("string_agg(DISTINCT jr.job_role_id::text, ',' ORDER BY jr.job_role_id::text)")
                        ->whereColumn('jr.nik', 'tr_user_ussm_nik.user_code')
                        ->where('jr.periode_id', $periode)
                        ->whereNull('jr.deleted_at')
                        ->whereNull('j.job_role_id');
                }, 'wrong_job_role_id')
                ->leftJoin('ms_master_data_karyawan as user_details', 'tr_user_ussm_nik.user_code', '=', 'user_details.nik')
                ->leftJoin('ms_kompartemen as kompartemen', 'user_details.kompartemen_id', '=', 'kompartemen.kompartemen_id')
                ->leftJoin('ms_departemen as departemen', 'user_details.departemen_id', '=', 'departemen.departemen_id')
                ->where('tr_user_ussm_nik.periode_id', $periode)
                ->where(function ($q) use ($periode) {
                    // Include users with no assignment in this period
                    $q->whereNotExists(function ($q1) use ($periode) {
                        $q1->selectRaw(1)
                            ->from('tr_ussm_job_role as jr')
                            ->whereColumn('jr.nik', 'tr_user_ussm_nik.user_code')
                            ->where('jr.periode_id', $periode)
                            ->whereNull('jr.deleted_at');
                    })
                        // OR users that have at least one invalid assignment (job_role_id not found in JobRole)
                        ->orWhereExists(function ($q2) use ($periode) {
                            $q2->selectRaw(1)
                                ->from('tr_ussm_job_role as jr2')
                                ->leftJoin('tr_job_roles as j', function ($join) {
                                    $join->on('jr2.job_role_id', '=', 'j.job_role_id')
                                        ->whereNull('j.deleted_at');
                                })
                                ->whereColumn('jr2.nik', 'tr_user_ussm_nik.user_code')
                                ->where('jr2.periode_id', $periode)
                                ->whereNull('jr2.deleted_at')
                                ->whereNull('j.job_role_id');
                        });
                });

            return DataTables::of($query)->make(true);
        }

        return view('relationship.nik_job_role.no-relationship', compact('periodes'));
    }

    public function exportWithoutJobRole(Request $request)
    {
        $periodeId = (int) $request->get('periode');
        $userCompany = auth()->user()->loginDetail->company_code ?? null;
        $companyShortname = Company::where('company_code', $userCompany)->value('shortname');

        if (!$periodeId) {
            return redirect()->back()->with('error', 'Periode harus dipilih untuk export');
        }

        // Get periode name for filename
        $periode = Periode::find($periodeId);
        $periodeName = $periode ? $periode->definisi : 'Unknown';

        $filename = 'NIK_Without_Job_Role_' . $periodeName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new UserNIKWithoutJobRoleExport($periodeId, $companyShortname),
            $filename
        );
    }
}
