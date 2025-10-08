<?php

namespace App\Http\Controllers\Report;

use \PhpOffice\PhpWord\IOFactory;

use App\Exports\ArrayExport;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use App\Models\middle_db\CompositeRole as MiddleCompositeRole;
use App\Models\middle_db\SingleRole as MiddleSingleRole;

use App\Models\PenomoranUAM;
use App\Models\Periode;

use App\Models\SingleRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class UAMReportController extends Controller
{
    public function index(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code;
        $companies = $userCompany !== 'A000'
            ? Company::select('company_code', 'nama')->where('company_code', $userCompany)->get()
            : Company::select('company_code', 'nama')->get();

        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi']);
        $selectedPeriodeId = $request->input('periode_id') ?? $periodes->first()?->id;
        $activePeriode = $selectedPeriodeId
            ? $periodes->firstWhere('id', (int) $selectedPeriodeId)
            : null;

        $unitKerja = '';
        $unitKerjaName = '';
        $cost_center = '';

        if ($departemenId) {
            $departemen = Departemen::find($departemenId);
            $unitKerja = 'Departemen';
            $unitKerjaName = $departemen ? $departemen->nama : '';
            $cost_center = $departemen ? $departemen->cost_center : '';
        } elseif ($kompartemenId) {
            $kompartemen = Kompartemen::find($kompartemenId);
            $unitKerja = 'Kompartemen';
            $unitKerjaName = $kompartemen ? $kompartemen->nama : '';
            $cost_center = $kompartemen ? $kompartemen->cost_center : '';
        } elseif ($companyId) {
            $company = Company::where('company_code', $companyId)->first();
            $unitKerja = 'Company';
            $unitKerjaName = $company ? $company->nama : '';
        }

        // Count users
        $query = JobRole::query()
            ->with(['compositeRole' => function ($q) {
                $q->whereNull('deleted_at');
            }]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $query->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }


        return view('report.uam.index', compact(
            'companies',
            'unitKerja',
            'unitKerjaName',
            'activePeriode',
            'periodes',
            'selectedPeriodeId'
        ));
    }

    public function getKompartemen(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemen = Kompartemen::where('company_id', $companyId)
            ->select('kompartemen_id', 'nama')
            ->orderBy('nama')
            ->get();
        return response()->json($kompartemen);
    }

    public function getDepartemen(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;

        if ($kompartemenId) {
            $departemen = Departemen::where('company_id', $companyId)
                ->where('kompartemen_id', $kompartemenId)
                ->select('departemen_id', 'nama')
                ->orderBy('nama')
                ->get();
        } else {
            $departemen = Departemen::where('company_id', $companyId)
                ->whereNull('kompartemen_id')
                ->select('departemen_id', 'nama')
                ->orderBy('nama')
                ->get();
        }
        return response()->json($departemen);
    }

    private function unwrapComposite($rel)
    {
        if ($rel instanceof \Illuminate\Support\Collection) {
            return $rel->first();
        }
        return $rel; // already a model or null
    }


    public function jobRolesData(Request $request)
    {
        $companyId      = $request->company_id;
        $kompartemenId  = $request->kompartemen_id;
        $departemenId   = $request->departemen_id;
        $periodeId      = $request->periode_id;

        $periode        = $periodeId ? Periode::find($periodeId) : Periode::latest()->first();
        $periodeYear    = $periode ? $periode->created_at?->format('Y') : now()->format('Y');
        $nomorSurat     = 'XXX - Belum terdaftar';
        $usedMDBSingles = false;

        $query = JobRole::query()
            ->with([
                'company',
                'kompartemen',
                'departemen',
                'compositeRole' => function ($q) {
                    $q->whereNull('deleted_at')
                        ->with(['singleRoles' => function ($qq) {
                            $qq->whereNull('tr_single_roles.deleted_at');
                        }, 'ao']); // assume rel authorizationObject exists
                }
            ]);

        if ($companyId)     $query->where('company_id', $companyId);
        if ($kompartemenId) $query->where('kompartemen_id', $kompartemenId);
        if ($departemenId)  $query->where('departemen_id', $departemenId);

        // Determine nomor surat (unchanged logic)
        if ($departemenId) {
            $penomoranUAM = PenomoranUAM::where('unit_kerja_id', $departemenId)
                ->whereNull('deleted_at')->latest()->first();
            $nomorSurat = $penomoranUAM->number ?? 'XXX (Belum terdaftar)';
        } elseif ($kompartemenId) {
            $penomoranUAM = PenomoranUAM::where('unit_kerja_id', $kompartemenId)
                ->whereNull('deleted_at')->latest()->first();
            $nomorSurat = $penomoranUAM->number ?? 'XXX (Belum terdaftar)';
        }

        $jobRoles = $query->get();
        $data = [];

        $nomorSurat = "PI-TIN-UAM-{$periodeYear}-{$nomorSurat}";

        // Collect composite names (for possible middle-db fallback)
        $compositeNames = $jobRoles
            ->filter(fn($jr) => $jr->compositeRole && !empty($jr->compositeRole->nama))
            ->pluck('compositeRole.nama')
            ->unique()
            ->values();

        // Middle-db composite descriptions (fallback only)
        $middleDescriptions = [];
        if ($compositeNames->isNotEmpty()) {
            $middleDescriptions = MiddleCompositeRole::whereIn('composite_role', $compositeNames)
                ->pluck('definisi', 'composite_role')
                ->toArray();
        }

        // Middle-db AO fallback (single_role = composite_role-AO)
        $aoByComposite = [];
        if ($compositeNames->isNotEmpty()) {
            $aoQueryNames = $compositeNames->map(fn($n) => $n . '-AO')->all();
            $aoRows = MiddleSingleRole::whereIn('single_role', $aoQueryNames)->get(['single_role', 'definisi']);
            foreach ($aoRows as $row) {
                // BUG FIX: Str::of(...)->beforeLast() returns Stringable (illegal offset as array key). Cast to string.
                $comp = (string) Str::of($row->single_role)->beforeLast('-AO');
                if ($comp === '') continue;
                $aoByComposite[$comp] = trim(
                    '<strong>' . e($row->single_role) . '</strong>' .
                        ($row->definisi ? '<br>' . e($row->definisi) : '')
                );
            }
        }

        // Build rows (Job Role mapping) LOCAL FIRST
        $compositeRoles = [];
        foreach ($jobRoles as $jobRole) {
            $compModel = $this->unwrapComposite($jobRole->compositeRole);
            if (!$compModel) continue;

            $compositeName = $compModel->nama ?? null;
            if (!$compositeName) continue;

            $localDesc  = $compModel->deskripsi;
            $mdbDesc    = $middleDescriptions[$compositeName] ?? null;
            $descUsed   = $localDesc ?: $mdbDesc;
            $usedMDB    = !$localDesc && $mdbDesc;

            $displayCompositeName = $compositeName . ($usedMDB ? ' <span style="color:red;"><strong><i>{MDB}</i></strong></span>' : '');
            $compositeLabel = '<strong>' . $displayCompositeName . '</strong>' . ($descUsed ? '<br>' . e($descUsed) : '');

            $aoLocalRel = $compModel->ao ?? null;
            if ($aoLocalRel) {
                $aoText = '<strong>' . e($aoLocalRel->nama ?? '-') . '</strong>' .
                    (($aoLocalRel->deskripsi ?? null) ? '<br>' . e($aoLocalRel->deskripsi) : '');
            } else {
                $aoText = $aoByComposite[$compositeName] ?? '-';
                if ($aoText !== '-' && !$aoLocalRel) {
                    $aoText .= ' <span style="color:red;"><strong><i>{MDB}</i></strong></span>';
                }
            }

            $data[] = [
                'company'              => $jobRole->company?->nama ?? '-',
                'kompartemen'          => $jobRole->kompartemen?->nama ?? '-',
                'departemen'           => $jobRole->departemen?->nama ?? '-',
                'job_role'             => $jobRole->nama ?? '-',
                'composite_role'       => $compositeLabel,
                'authorization_object' => $aoText,
                'source'               => $compModel->source ? $compModel->source : 'ERR',
            ];

            $compositeRoles[$compModel->id] = $compModel;
        }

        // Build compositeRolesWithSingles (local singles first, fallback to middle)
        $compositeRolesWithSingles = [];
        foreach ($compositeRoles as $comp) {
            $comp = $this->unwrapComposite($comp);
            if (!$comp) continue;
            $compNameRaw = $comp->nama;
            $localSingles = $comp->singleRoles ?? collect();
            $mappedSingles = collect();

            if ($localSingles && $localSingles->count() > 0) {
                // LOCAL singles
                $mappedSingles = $localSingles->map(fn($r) => [
                    'id'          => $r->id,
                    'nama'        => $r->nama,
                    'nama_display' => $r->nama, // local no tag
                    'deskripsi'   => $r->deskripsi,
                    'source'      => $r->source ? $r->source : 'CLOUD',
                ]);
            } else {
                // Fallback to middle-db only if local empty
                $mdbComposite = MiddleCompositeRole::where('composite_role', $compNameRaw)->first();
                if ($mdbComposite && method_exists($mdbComposite, 'singleRoles')) {
                    $mdbSingles = $mdbComposite->singleRoles()->get(['mdb_single_role.single_role', 'mdb_single_role.definisi']);
                    $mappedSingles = $mdbSingles->map(fn($r) => [
                        'id'          => null,
                        'nama'        => $r->single_role,
                        'nama_display' => $r->single_role . ' <span style="color:red;"><strong><i>{MDB}</i></strong></span>',
                        'deskripsi'   => $r->definisi,
                        'source'      => 'MDB',
                    ]);
                    $usedMDBSingles = true;
                }
            }

            $compDisplay = $compNameRaw;
            if ($usedMDBSingles && ($comp->deskripsi === null || $comp->deskripsi === '')) {
                // Mark composite if singles are from MDB and no local desc
                $compDisplay .= ' <span style="color:red;"><strong><i>{MDB}</i></strong></span>';
            }

            $compositeRolesWithSingles[] = [
                'id'           => $comp->id,
                'nama'         => $compNameRaw,
                'nama_display' => $compDisplay,
                'single_roles' => $mappedSingles->toArray(),
                'source'       => $comp->source ? $comp->source : 'CLOUD',
            ];
        }

        // Unique single roles (LOCAL tcodes first, fallback to middle)
        $uniqueSingleRoles = [];
        foreach ($compositeRolesWithSingles as $cr) {
            foreach ($cr['single_roles'] as $sr) {
                $key = $sr['id'] ?? $sr['nama'];
                if (isset($uniqueSingleRoles[$key])) continue;

                $srId      = $sr['id'];
                $srNameRaw = $sr['nama'];
                $tcodes    = [];
                $usedMDBT  = false;

                // LOCAL first if we have local id
                if ($srId) {
                    $singleRoleModel = SingleRole::find($srId);
                    if ($singleRoleModel && method_exists($singleRoleModel, 'tcodes')) {
                        $tcodesLocal = $singleRoleModel->tcodes()
                            ->whereNull('tr_tcodes.deleted_at')
                            ->get(['tr_tcodes.code', 'tr_tcodes.deskripsi']);
                        $tcodes = $tcodesLocal->map(fn($t) => [
                            'tcode'         => $t->code,
                            'tcode_display' => $t->code,
                            'deskripsi'     => $t->deskripsi,
                            'source'        => $t->source ? $t->source : 'CLOUD',
                        ])->toArray();
                    }
                }

                // Fallback middle if no local tcodes (or no local ID)
                if (empty($tcodes)) {
                    $mdbSingle = MiddleSingleRole::where('single_role', $srNameRaw)->first();
                    if ($mdbSingle && method_exists($mdbSingle, 'tcodes')) {
                        $mdbTcodes = $mdbSingle->tcodes()->get(['mdb_tcode.tcode', 'mdb_tcode.definisi']);
                        if ($mdbTcodes->count()) {
                            $tcodes = $mdbTcodes->map(fn($t) => [
                                'tcode'         => $t->tcode,
                                'tcode_display' => $t->tcode . ' <span style="color:red;"><strong><i>{MDB}</i></strong></span>',
                                'deskripsi'     => $t->definisi,
                                'source'        => 'MDB',
                            ])->toArray();
                            $usedMDBT = true;
                        }
                    }
                }

                $nameDisplay = $sr['nama_display'] ?? $srNameRaw;
                // If no local id and came from middle fallback ensure display tag
                if (!$srId && str_contains($nameDisplay, '{MDB}') === false && ($sr['source'] ?? null) === 'MDB') {
                    $nameDisplay .= ' <span style="color:red;"><strong><i>{MDB}</i></strong></span>';
                }

                $uniqueSingleRoles[$key] = [
                    'id'           => $srId,
                    'nama'         => $srNameRaw,
                    'nama_display' => $nameDisplay,
                    'deskripsi'    => $sr['deskripsi'] ?? null,
                    'source'       => $sr['source'] ?? 'MDB',
                    'tcodes'       => $tcodes,
                ];
            }
        }

        return response()->json([
            'data'             => $data,
            'nomorSurat'       => $nomorSurat,
            'composite_roles'  => $compositeRolesWithSingles,
            'single_roles'     => array_values($uniqueSingleRoles),
        ]);
    }

    // public function exportCompositeExcel(Request $request)
    // {
    //     // Fetch data as in jobRolesData
    //     // Build $exportData as array: [No, Composite Role, Single Role, Deskripsi]
    //     $companyId = $request->company_id;
    //     $kompartemenId = $request->kompartemen_id;
    //     $departemenId = $request->departemen_id;

    //     // ...repeat compositeRolesWithSingles logic...
    //     $exportData = [];
    //     foreach ($compositeRolesWithSingles as $idx => $cr) {
    //         foreach ($cr['single_roles'] as $sr) {
    //             $exportData[] = [
    //                 'No' => $idx + 1,
    //                 'Composite Role' => $cr['nama'],
    //                 'Single Role' => $sr['nama'],
    //                 'Deskripsi' => $sr['deskripsi'],
    //             ];
    //         }
    //     }

    //     // For brevity, you can copy logic from jobRolesData and build $exportData

    //     return Excel::download(new ArrayExport($exportData), 'composite_single_roles.xlsx');
    // }

    public function exportSingleExcel(Request $request)
    {
        $uniqueSingleRoles = [];
        $uniqueTcodeCount = [];

        $query = JobRole::query()
            ->with([
                'compositeRole' => function ($q) {
                    $q->whereNull('deleted_at');
                }
            ]);

        $jobRoles = $query->get();

        // Build compositeRolesWithSingles
        $compositeRoles = [];
        foreach ($jobRoles as $jobRole) {
            $comp = $this->unwrapComposite($jobRole->compositeRole);
            if ($comp) {
                $compositeRoles[$comp->id] = $comp;
            }
        }

        $compositeRolesWithSingles = [];
        foreach ($compositeRoles as $compositeRole) {
            $singleRoles = $compositeRole->singleRoles()
                ->whereNull('tr_single_roles.deleted_at')
                ->get(['tr_single_roles.id', 'tr_single_roles.nama', 'tr_single_roles.deskripsi']);
            $compositeRolesWithSingles[] = [
                'id' => $compositeRole->id,
                'nama' => $compositeRole->nama,
                'single_roles' => $singleRoles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'nama' => $role->nama,
                        'deskripsi' => $role->deskripsi,
                    ];
                })->toArray(),
            ];
        }


        // Compile unique single roles
        $uniqueSingleRoles = [];
        foreach ($compositeRolesWithSingles as $cr) {
            foreach ($cr['single_roles'] as $sr) {
                if (!isset($uniqueSingleRoles[$sr['id']])) {
                    $singleRoleModel = SingleRole::find($sr['id']);
                    $tcodes = [];
                    if ($singleRoleModel && method_exists($singleRoleModel, 'tcodes')) {
                        $tcodes = $singleRoleModel->tcodes()
                            ->whereNull('tr_tcodes.deleted_at')
                            ->get(['tr_tcodes.code', 'tr_tcodes.deskripsi'])
                            ->map(function ($t) {
                                return [
                                    'tcode' => $t->code,
                                    'deskripsi' => $t->deskripsi,
                                ];
                            })->toArray();
                    }
                    $uniqueSingleRoles[$sr['id']] = [
                        'id' => $sr['id'],
                        'nama' => $sr['nama'],
                        'deskripsi' => $sr['deskripsi'],
                        'tcodes' => $tcodes,
                    ];
                }
            }
        }

        // Build export data with header
        $exportData = [];
        $exportData[] = [
            'No',
            'Single Role',
            'Deskripsi Single Role',
            'Tcode',
            'Deskripsi Tcode'
        ];

        $rowNumber = 1;
        foreach (array_values($uniqueSingleRoles) as $sr) { // use array_values here
            if (count($sr['tcodes']) > 0) {
                foreach ($sr['tcodes'] as $tc) {
                    $exportData[] = [
                        $rowNumber,
                        $sr['nama'],
                        $sr['deskripsi'] ?? '',
                        $tc['tcode'],
                        $tc['deskripsi'] ?? '',
                    ];
                    $rowNumber++;
                }
            } else {
                $exportData[] = [
                    $rowNumber,
                    $sr['nama'],
                    $sr['deskripsi'] ?? '',
                    '',
                    '',
                ];
                $rowNumber++;
            }
        }

        return Excel::download(new ArrayExport($exportData), 'single_role_tcodes.xlsx');
    }

    public function exportCompositeWithoutAO(Request $request)
    {
        $companyId     = $request->query('company_id');
        $kompartemenId = $request->query('kompartemen_id');
        $departemenId  = $request->query('departemen_id');

        $jobRoles = JobRole::query()
            ->with([
                'company:company_code,nama',
                'kompartemen:kompartemen_id,nama',
                'departemen:departemen_id,nama',
                'compositeRole' => fn($q) => $q->whereNull('deleted_at')->with('ao'),
            ])
            ->whereNull('deleted_at')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($kompartemenId, fn($q) => $q->where('kompartemen_id', $kompartemenId))
            ->when($departemenId, fn($q) => $q->where('departemen_id', $departemenId))
            ->get()
            ->filter(function ($jobRole) {
                $composite = $jobRole->compositeRole;
                return $composite && !$composite->ao;
            });

        $rows = $jobRoles->map(function ($jobRole, $index) {
            $composite = $jobRole->compositeRole;

            return [
                'No'               => $index + 1,
                'Company'          => $jobRole->company->nama ?? '-',
                'Kompartemen'      => $jobRole->kompartemen->nama ?? '-',
                'Departemen'       => $jobRole->departemen->nama ?? '-',
                'Job Role ID'      => $jobRole->job_role_id,
                'Job Role Name'    => $jobRole->nama ?? '-',
                'Composite Role'   => $composite->nama ?? '-',
                'Composite Source' => $composite->source ?? '-',
            ];
        })->prepend([
            'No',
            'Company',
            'Kompartemen',
            'Departemen',
            'Job Role ID',
            'Job Role Name',
            'Composite Role',
            'Composite Source',
        ])->values()->all();

        return Excel::download(new ArrayExport($rows), 'job_role_composite_without_ao.xlsx');
    }

    public function exportWord(Request $request)
    {
        $companyId    = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId  = $request->departemen_id;
        $periodeId     = $request->periode_id;

        $unitKerja = '-';
        $jabatanUnitKerja = '';
        $unitKerjaName = '';
        $periodeObj         = $periodeId ? Periode::find($periodeId) : Periode::latest()->first();
        $latestPeriode      = $periodeObj ? $periodeObj->definisi : '-';
        $latestPeriodeYear  = $periodeObj ? $periodeObj->created_at?->format('Y') : now()->format('Y');
        $nomorSurat         = 'XXX';
        // $maxSingleRoles = 150; // Maximum single roles viewed per document
        $maxTcodes = 600; // Maximum tcodes viewed per document

        // Rename unit kerja untuk display info di judul & tabel word

        if ($departemenId) {
            $departemen = Departemen::find($departemenId);
            $displayName = $departemen ? $departemen->nama : '';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName);
            if (preg_match('/^Dept\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Dept\.\s*/', '', $displayName);
                $unitKerja = 'Departemen ' . $displayName;
            } elseif (preg_match('/^Dep\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Dep\.\s*/', '', $displayName);
                $unitKerja = 'Departemen ' . $displayName;
            } else {
                $unitKerja = $displayName;
            }
            $unitKerja = $this->sanitizeForDocx($unitKerja);
            $unitKerjaName = $this->sanitizeForDocx($displayName);
            $jabatanUnitKerja = 'VP';
            $penomoranUAM = PenomoranUAM::where('unit_kerja_id', $departemenId)
                ->whereNull('deleted_at')
                ->latest()
                ->first();
            $nomorSurat = $penomoranUAM->number ?? 'XXX (Belum terdaftar)';
            $cost_center = $penomoranUAM && $penomoranUAM->departemen ? $penomoranUAM->departemen->cost_center : '';
        } elseif ($kompartemenId) {
            $kompartemen = Kompartemen::find($kompartemenId);
            $displayName = $kompartemen ? $kompartemen->nama : '';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName);
            if (preg_match('/^Komp\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Komp\.\s*/', '', $displayName);
                $unitKerja = 'Kompartemen ' . $displayName;
            } elseif (preg_match('/^Fungs\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Fungs\.\s*/', '', $displayName);
                $unitKerja = 'Fungsional ' . $displayName;
            } else {
                $unitKerja = $displayName;
            }
            $unitKerja = $this->sanitizeForDocx($unitKerja);
            $unitKerjaName = $this->sanitizeForDocx($displayName);
            $jabatanUnitKerja = 'SVP';
            $penomoranUAM = PenomoranUAM::where('unit_kerja_id', $kompartemenId)
                ->whereNull('deleted_at')
                ->latest()
                ->first();
            $nomorSurat = $penomoranUAM->number ?? 'XXX (Belum terdaftar)';
            $cost_center = $penomoranUAM && $penomoranUAM->kompartemen ? $penomoranUAM->kompartemen->cost_center : '';
        } elseif ($companyId) {
            $company = Company::where('company_code', $companyId)->first();
            $displayName = $company ? $company->nama : '-';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName);
            $unitKerja = $this->sanitizeForDocx($displayName);
            $unitKerjaName = $this->sanitizeForDocx($displayName);
            $jabatanUnitKerja = 'Direktur';
            $cost_center = 'Tidak ada Cost Center untuk Level Perusahaan';
        }

        // Fetch job roles and users
        $query = JobRole::query()
            ->with([
                'company',
                'kompartemen',
                'departemen',
                'compositeRole' => function ($q) {
                    $q->whereNull('deleted_at');
                }

            ])
            ->whereNull('deleted_at');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $query->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $jobRoles = $query->get();

        // Calculate unique single roles and unique tcodes
        $compositeRoles = [];
        foreach ($jobRoles as $jobRole) {
            $comp = $this->unwrapComposite($jobRole->compositeRole);
            if ($comp) {
                $compositeRoles[$comp->id] = $comp;
            }
        }

        $compositeRolesWithSingles = [];
        foreach ($compositeRoles as $compositeRole) {
            $compositeRole = $this->unwrapComposite($compositeRole);
            if (!$compositeRole) continue;
            $singleRoles = $compositeRole->singleRoles()
                ->whereNull('tr_single_roles.deleted_at')
                ->get(['tr_single_roles.id', 'tr_single_roles.nama', 'tr_single_roles.deskripsi']);
            $compositeRolesWithSingles[] = [
                'id' => $compositeRole->id,
                'nama' => $compositeRole->nama,
                'single_roles' => $singleRoles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'nama' => $role->nama,
                        'deskripsi' => $role->deskripsi,
                    ];
                })->toArray(),
            ];
        }

        // Compile unique single roles
        $uniqueSingleRoles = [];
        foreach ($compositeRolesWithSingles as $cr) {
            foreach ($cr['single_roles'] as $sr) {
                if (!isset($uniqueSingleRoles[$sr['id']])) {
                    $singleRoleModel = SingleRole::find($sr['id']);
                    $tcodes = [];
                    if ($singleRoleModel && method_exists($singleRoleModel, 'tcodes')) {
                        $tcodes = $singleRoleModel->tcodes()
                            ->whereNull('tr_tcodes.deleted_at')
                            ->get(['tr_tcodes.code', 'tr_tcodes.deskripsi'])
                            ->map(function ($t) {
                                return [
                                    'tcode' => $t->code,
                                    'deskripsi' => $t->deskripsi,
                                ];
                            })->toArray();
                    }
                    $uniqueSingleRoles[$sr['id']] = [
                        'id' => $sr['id'],
                        'nama' => $sr['nama'],
                        'deskripsi' => $sr['deskripsi'],
                        'tcodes' => $tcodes,
                    ];
                }
            }
        }
        // Calculate total unique tcodes for display limit
        $uniqueTcodeCount = array_reduce(array_values($uniqueSingleRoles), function ($carry, $sr) {
            return $carry + (isset($sr['tcodes']) && is_array($sr['tcodes']) ? count($sr['tcodes']) : 0);
        }, 0);


        // Initialize PhpWord and create main section
        $phpWord = new PhpWord();

        // COVER PAGE SECTION (no header/footer)
        $coverSection = $phpWord->addSection([
            'headerHeight' => 0,
            'footerHeight' => 0,
            'marginTop' => 1200,
            'marginBottom' => 1200,
            'marginLeft' => 1200,
            'marginRight' => 1200,
            'colsNum' => 1,
            'breakType' => 'continuous',
            'titlePg' => true,
        ]);


        // Add cover page title
        $coverTable = $coverSection->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 20]);

        // Add empty rows for spacing
        // for ($i = 0; $i < 6; $i++) $coverTable->addRow(300);

        $coverTable->addRow(7500, [
            'valign' => 'center',
        ]);
        $coverTable->addCell(9000, ['gridSpan' => 3, 'valign' => 'center'])->addText(
            'USER ACCESS MATRIX <w:br/> ' . mb_strtoupper(($unitKerja ? "$unitKerja" : '-'), 'UTF-8') . '<w:br/><w:br/>',
            ['bold' => true, 'size' => 20],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        // Name row
        $coverTable->addRow();
        $coverTable->addCell(10200, ['gridSpan' => 3])->addText('DISUSUN OLEH', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        // Signature row
        $coverTable->addRow(1000, ['exactHeight' => true]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addRow(250, ['exactHeight' => true]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        // Position row
        $coverTable->addRow(250, ['exactHeight' => true]);
        $coverTable->addCell(3400)->addText('Staf Operasional TI', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('Junior Officer Tata Kelola TI', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('Key User', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        // Header row: "DISETUJUI OLEH"
        // Name row
        $coverTable->addRow();
        $coverTable->addCell(9000, ['gridSpan' => 3])->addText('DISETUJUI OLEH', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        // Signature row
        $coverTable->addRow(1000, ['exactHeight' => true]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        // Name row
        $coverTable->addRow(250, ['exactHeight' => true]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        // $coverTable->addCell(3400)->addText('Abdul Muhyi Marakarma', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        // $coverTable->addCell(3400)->addText('Sony Candra Dirganto', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);

        // Position row
        $coverTable->addRow(250, ['exactHeight' => true]);
        $coverTable->addCell(3400)->addText('VP Operasional Sistem TI', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('VP Strategi dan Tata Kelola TI', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText($jabatanUnitKerja . ' ' . $unitKerjaName, ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        $coverTable->addRow(250, ['exactHeight' => true]);
        $coverTable->addCell(3400);
        $coverTable->addCell(3400);
        $coverTable->addCell(3400);

        // Add a new table for document info (logo, nomor, tanggal, disclaimer)
        $docInfoTable = $coverSection->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 20]);
        $docInfoTable = $coverSection->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $docInfoTable = $coverSection->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);

        // Row 1: Logo + No Dok + Nomor Surat
        $docInfoTable->addRow(500, ['exactHeight' => true]);
        $docInfoTable->addCell(3400, ['valign' => 'center', 'vMerge' => 'restart'])->addImage(
            public_path('logo_pupuk_indonesia.png'),
            ['width' => 120, 'height' => 60, 'alignment' => Jc::CENTER]
        );
        $docInfoTable->addCell(1650, [
            'valign' => 'center',
            'borderRightSize' => 6,
            'borderRightColor' => '000000',
            'marginLeft' => 200, // add left margin
        ])->addText(
            "NO DOKUMEN:",
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::START, 'spaceAfter' => 0]
        );
        $docInfoTable->addCell(5150, [
            'valign' => 'center',
        ])->addText(
            "PI-TIN-UAM-" . $latestPeriodeYear . "-" . $nomorSurat,
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );

        // Row 2: Empty + Tahun Terbit + Disclaimer
        $docInfoTable->addRow(1200, ['exactHeight' => true]);
        $docInfoTable->addCell(null, ['vMerge' => 'continue']);
        $docInfoTable->addCell(6000, [
            'valign' => 'center',
            'gridSpan' => 2,
            'vMerge' => 'restart',
        ])->addText(
            "PupukIndonesia@" . Carbon::today()->year . " Dokumen ini milik PT Pupuk Indonesia (Persero). Segala informasi yang tercantum dalam dokumen ini bersifat rahasia dan terbatas, serta tidak diperkenankan untuk didistribusikan kembali, baik dalam bentuk cetakan maupun elektronik, tanpa persetujuan dari PT Pupuk Indonesia (Persero).",
            ['size' => 8],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );

        // Row 3: Watermark + Empty
        $docInfoTable->addRow(500, ['exactHeight' => true]);
        // White background with a bordered rectangle (nested table) containing the text RAHASIA
        $rahCell = $docInfoTable->addCell(3400, [
            'valign' => 'center',
            'vMerge' => 'restart',
        ]);
        $rahBox = $rahCell->addTable([
            'borderSize'      => 6,
            'borderColor'     => '000000',
            'cellMarginTop'   => 80,
            'cellMarginBottom' => 80,
            'cellMarginLeft'  => 80,
            'cellMarginRight' => 80,
        ]);
        // Make nested table occupy full parent cell width
        $rahBox->getStyle()->setUnit(\PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT);
        $rahBox->getStyle()->setWidth(100 * 50); // 100%
        // Optional: remove internal left/right margins to truly maximize space
        $rahBox->getStyle()->setCellMarginLeft(0);
        $rahBox->getStyle()->setCellMarginRight(0);

        $rahBox->addRow();
        $rahBox->addCell(null, [
            'valign' => 'center',
            'bgColor' => 'FF0000',
            'width'  => 100 * 50,
            'unit'   => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
        ])->addText(
            'RAHASIA',
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        $docInfoTable->addCell(null, [
            'vMerge'   => 'continue',
            'gridSpan' => 2,
        ]);


        // Main section with header/footer
        $section = $phpWord->addSection();
        $header = $section->addHeader();

        // Header Table (Logo + Title + No Dok + Nomor Surat)
        $headerTable = $header->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMarginLeft' => 50, 'cellMarginTop' => 25, 'cellMarginRight' => 50, 'cellMarginBottom' => 25]);

        // Row 1: Logo + Title + No Dok + Nomor Surat
        $headerTable->addRow(500, ['exactHeight' => true]);
        $headerTable->addCell(3000, ['valign' => 'center', 'vMerge' => 'restart',])->addImage(
            public_path('logo_pupuk_indonesia.png'),
            ['width' => 80, 'height' => 50, 'alignment' => Jc::CENTER]
        );
        $headerTable->addCell(6000, ['valign' => 'center', 'vMerge' => 'restart'])->addText(
            'TEKNOLOGI INFORMASI',
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        // Nested bordered box with red background "RAHASIA" (mirrors cover style)
        $rahCell = $headerTable->addCell(4200, [
            'valign'   => 'center',
            'gridSpan' => 2,
        ]);
        $rahBox = $rahCell->addTable([
            'borderSize'       => 6,
            'borderColor'      => '000000',
            'cellMarginTop'    => 80,
            'cellMarginBottom' => 80,
            'cellMarginLeft'   => 80,
            'cellMarginRight'  => 80,
        ]);
        $rahBox->getStyle()->setUnit(\PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT);
        $rahBox->getStyle()->setWidth(100 * 50);
        $rahBox->getStyle()->setCellMarginLeft(0);
        $rahBox->getStyle()->setCellMarginRight(0);

        $rahBox->addRow();
        $rahBox->addCell(null, [
            'valign'  => 'center',
            'bgColor' => 'FF0000',
            'width'   => 100 * 50,
            'unit'    => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
        ])->addText(
            'RAHASIA',
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        // Row 2: Colspan Logo + Colspan TEKNOLOGI INFORMASI + No Dok + Nomor Surat
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(1200, ['valign' => 'center'])->addText('No Dok', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center'])->addText('PI-TIN-UAM-' . $latestPeriodeYear . '-' . $nomorSurat, ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        // Row 3: Rowspan + Judul  + Rev. Ke + 0
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(6000, ['valign' => 'center', 'vMerge' => 'restart'])->addText(
            'USER ACCESS MATRIX <w:br/> ' . mb_strtoupper(($unitKerja ? "$unitKerja" : '-'), 'UTF-8'),
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $headerTable->addCell(1200, ['valign' => 'center'])->addText('Rev. Ke', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center'])->addText('0', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        // Row 4: Rowspan + Rowspan Tanggal + Tanggal Sekarang
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(1200, ['valign' => 'center'])->addText('Tanggal', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center'])->addText(\Carbon\Carbon::today()->locale('id')->isoFormat('DD MMMM YYYY'), ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        // Row 5: Rowspan + Rowspan + Halaman Ke + Halaman
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(1200, ['valign' => 'center',])->addText('Hal. ke', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center',])->addPreserveText('{PAGE} dari {NUMPAGES}', ['size' => 9], ['alignment' => Jc::START, 'spaceBefore' => 0, 'spaceAfter' => 0]);

        $header->addTextBreak(1);

        $section->addText(
            '1. TABEL MAPPING JOB FUNCTION DAN COMPOSITE ROLE',
            ['bold' => true, 'size' => 12],
            ['space' => ['after' => 0]]
        );

        $section->addTextBreak(1);

        // Job Role Table
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);

        $table->addRow();
        $table->addCell(750)->addText('No', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $table->addCell(3750)->addText('Job Role', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $table->addCell(3750)->addText('Composite Role', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $table->addCell(3750)->addText('Authorization Object', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        $no = 1;
        // Loop through jobRoles and add rows to the table
        foreach ($jobRoles as $jobRole) {
            $comp = $this->unwrapComposite($jobRole->compositeRole);
            if ($comp instanceof \Illuminate\Support\Collection) {
                $comp = $comp->first();
            }

            $compName = $comp?->nama;
            $compDesc = $comp?->deskripsi;
            $compParts = [];
            if ($compName) $compParts[] = $compName;
            if ($compDesc) $compParts[] = $compDesc;
            $compText = $compParts ? implode("\n", $compParts) : '-';

            $ao = $comp?->ao ?? null;
            if ($ao instanceof \Illuminate\Support\Collection) {
                $ao = $ao->first();
            }
            $aoName = $ao?->nama;
            $aoDesc = $ao?->deskripsi;
            $aoParts = [];
            if ($aoName) $aoParts[] = $aoName;
            if ($aoDesc) $aoParts[] = $aoDesc;
            $aoText = $aoParts ? implode("\n", $aoParts) : '-';

            $table->addRow();
            $table->addCell(750, ['valign' => 'top'])->addText(
                $no++,
                ['size' => 8],
                ['space' => ['after' => 0], 'alignment' => Jc::CENTER]
            );
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($jobRole->nama ?? '-'), ['size' => 8], ['spaceAfter' => 0]);
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($compText), ['size' => 8], ['spaceAfter' => 0]);
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($aoText), ['size' => 8], ['spaceAfter' => 0]);
        }

        // If no data row was added, add an empty row
        // if ($no === 1) {
        //     $table->addRow();
        //     $table->addCell(750, ['valign' => 'center'])->addText('1.', ['size' => 8], ['alignment' => Jc::CENTER]);
        //     $table->addCell(3000, ['valign' => 'center'])->addText('-', ['size' => 8]);
        //     $table->addCell(3000, ['valign' => 'center'])->addText('-', ['size' => 8]);
        //     $table->addCell(3000, ['valign' => 'center'])->addText('-', ['size' => 8]);
        //     $table->addCell(1000, ['valign' => 'center'])->addText('-', ['size' => 8], ['alignment' => Jc::CENTER]);
        //     $table->addCell(1750, ['valign' => 'center'])->addText('-', ['size' => 8]);
        //     $table->addCell(1750, ['valign' => 'center'])->addText('-', ['size' => 8]);
        //     $table->addCell(2000, ['valign' => 'center'])->addText('-', ['size' => 8]);
        // }


        $section->addTextBreak(1);

        $section->addText(
            '2. TABEL MAPPING COMPOSITE ROLE DAN SINGLE ROLE',
            ['bold' => true, 'size' => 12],
            ['spaceAfter' => 0]
        );

        $section->addTextBreak(1);

        // Composite Role - Single Role Table
        $compositeTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $compositeTable->addRow();
        $compositeTable->addCell(750)->addText('No', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $compositeTable->addCell(5625)->addText('Composite Role', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $compositeTable->addCell(5625)->addText('Single Roles', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $compositeTable->addCell(5625)->addText('Deskripsi', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        foreach ($compositeRolesWithSingles as $idx => $cr) {
            $singleRoles = $cr['single_roles'];
            foreach ($singleRoles as $srIdx => $sr) {
                $compositeTable->addRow();
                // Only show No and Composite Role for the first single role, with rowspan
                if ($srIdx === 0) {
                    $compositeTable->addCell(750, ['vMerge' => 'restart', 'valign' => 'top', 'rowSpan' => count($singleRoles)])
                        ->addText($idx + 1, ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
                    $compositeTable->addCell(5625, ['vMerge' => 'restart', 'valign' => 'top', 'rowSpan' => count($singleRoles)])
                        ->addText($this->sanitizeForDocx($cr['nama']), ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
                } else {
                    $compositeTable->addCell(750, ['vMerge' => 'continue']);
                    $compositeTable->addCell(5625, ['vMerge' => 'continue']);
                }
                $compositeTable->addCell(5625, ['valign' => 'top'])->addText($this->sanitizeForDocx($sr['nama']), ['size' => 8], ['spaceAfter' => 0]);
                $compositeTable->addCell(5625, ['valign' => 'top'])->addText($this->sanitizeForDocx($sr['deskripsi']), ['size' => 8], ['spaceAfter' => 0]);
            }
        }

        $section->addTextBreak(1);

        $section->addText(
            '3. Lampiran',
            ['bold' => true, 'size' => 12],
            ['spaceAfter' => 0]
        );

        $section->addText(
            'Mapping Single Role dan Tcode',
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        // $section->addTextBreak(1);

        // Single Role - Tcode Table
        // if (count($uniqueSingleRoles) > $maxSingleRoles || $uniqueTcodeCount > $maxTcodes) {
        if ($uniqueTcodeCount > $maxTcodes) {
            // Add a comment or summary instead of generating the table
            $section->addText('Data terlalu banyak untuk ditampilkan. Silakan unduh file Excel untuk detail lengkap.', ['italic' => true, 'color' => 'FF0000']);
        } else {
            $singleRoleTcodeTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            $singleRoleTcodeTable->addRow(200, ['exactHeight' => true]);
            $singleRoleTcodeTable->addCell(750, ['valign' => 'center'])->addText('No', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
            $singleRoleTcodeTable->addCell(3750, ['valign' => 'center'])->addText('Single Role', ['bold' => true, 'size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
            $singleRoleTcodeTable->addCell(3250, ['valign' => 'center'])->addText('Tcode', ['bold' => true, 'size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
            $singleRoleTcodeTable->addCell(4250, ['valign' => 'center'])->addText('Deskripsi Tcode', ['bold' => true, 'size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);

            $no = 1;
            foreach (array_values($uniqueSingleRoles) as $sr) {
                $tcodes = $sr['tcodes'];
                if (count($tcodes) > 0) {
                    foreach ($tcodes as $tcIdx => $tc) {
                        $singleRoleTcodeTable->addRow(200, ['exactHeight' => true]);
                        if ($tcIdx === 0) {
                            $singleRoleTcodeTable->addCell(750, ['vMerge' => 'restart', 'valign' => 'top', 'rowSpan' => count($tcodes)])
                                ->addText($no, ['size' => 8], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
                            $singleRoleTcodeTable->addCell(3750, ['vMerge' => 'restart', 'valign' => 'top', 'rowSpan' => count($tcodes)])
                                ->addText($this->sanitizeForDocx($sr['nama']), ['size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                        } else {
                            $singleRoleTcodeTable->addCell(750, ['vMerge' => 'continue']);
                            $singleRoleTcodeTable->addCell(3750, ['vMerge' => 'continue']);
                        }
                        $singleRoleTcodeTable->addCell(3250, ['valign' => 'top'])->addText($this->sanitizeForDocx($tc['tcode']), ['size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                        $singleRoleTcodeTable->addCell(4250, ['valign' => 'top'])->addText($this->sanitizeForDocx($tc['deskripsi']), ['size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                    }
                    $no++;
                } else {
                    $singleRoleTcodeTable->addRow(200, ['exactHeight' => true]);
                    $singleRoleTcodeTable->addCell(750, ['valign' => 'top'])->addText($no, ['size' => 8], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
                    $singleRoleTcodeTable->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($sr['nama']), ['size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                    $singleRoleTcodeTable->addCell(3250, ['valign' => 'top'])->addText('-', ['size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                    $singleRoleTcodeTable->addCell(4250, ['valign' => 'top'])->addText('-', ['size' => 8], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                    $no++;
                }
            }
        }

        // Output
        $fileName = 'PI-TIN-UAM-' . $latestPeriodeYear . '-' . $nomorSurat . '_' . $unitKerja . ' ' . $latestPeriodeYear .  '.docx';
        $filePath = storage_path('app/public/' . $fileName);

        // Save using IOFactory
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    private function sanitizeForDocx($string)
    {
        // Remove control characters except newline and tab
        $string = preg_replace('/[^\P{C}\n\t]+/u', '', $string);
        // Convert to UTF-8 if not already
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        // Encode XML special chars
        $string = htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $string = str_replace('&', 'dan', $string);
        return $string;
    }
}
