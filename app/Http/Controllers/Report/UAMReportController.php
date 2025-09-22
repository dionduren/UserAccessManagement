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
        $companies = Company::select('company_code', 'nama')->get();

        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        $latestPeriode = Periode::latest()->first();
        if (!$latestPeriode) {
            $latestPeriode = null;
        }

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
            'latestPeriode'
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

    public function jobRolesData(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;
        $latestPeriode = Periode::latest()->first();
        $latestPeriodeYear = $latestPeriode ? date('Y', strtotime($latestPeriode->created_at)) : null;
        $nomorSurat = 'XXX - Belum terdaftar';

        $query = JobRole::query()
            ->with([
                'company',
                'kompartemen',
                'departemen',
                'compositeRole' => function ($q) {
                    $q->whereNull('deleted_at');
                }
            ]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $query->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        if ($departemenId) {
            $penomoranUAM = PenomoranUAM::where('unit_kerja_id', $departemenId)
                ->whereNull('deleted_at')
                ->latest()
                ->first();
            $nomorSurat = $penomoranUAM->number ?? 'XXX (Belum terdaftar)';
        } elseif ($kompartemenId) {
            $penomoranUAM = PenomoranUAM::where('unit_kerja_id', $kompartemenId)
                ->whereNull('deleted_at')
                ->latest()
                ->first() ?? null;
            $nomorSurat = $penomoranUAM->number ?? 'XXX (Belum terdaftar)';
        }

        $jobRoles = $query->get();
        $data = [];

        $nomorSurat = "PI-TIN-UAM-{$latestPeriodeYear}-{$nomorSurat}";

        // Prefetch middle-db composite role descriptions by name to avoid N+1
        $middleDescriptions = [];
        $compositeNames = $jobRoles
            ->filter(function ($jr) {
                return $jr->compositeRole && !empty($jr->compositeRole->nama);
            })
            ->pluck('compositeRole.nama')
            ->filter()
            ->unique()
            ->values();

        if ($compositeNames->isNotEmpty()) {
            $middleDescriptions = MiddleCompositeRole::whereIn('composite_role', $compositeNames)
                ->pluck('definisi', 'composite_role')
                ->toArray();
        }

        // Prefetch AO per composite role from middle-db (AO Single Role rows)
        $aoByComposite = [];
        if ($compositeNames->isNotEmpty()) {
            $aoQueryNames = $compositeNames->map(fn($name) => $name . '-AO')->all();
            $aoRows = MiddleSingleRole::whereIn('single_role', $aoQueryNames)
                ->get(['single_role', 'definisi']); // only fields available per dd()

            foreach ($aoRows as $row) {
                // Map back to composite name by removing "-AO"
                $comp = (string) Str::of($row->single_role)->beforeLast('-AO');
                if ($comp === '') {
                    continue;
                }
                // Build display text: "AO Single Role - definisi"
                $aoByComposite[$comp] = trim(($row->single_role ? '<strong>' . $row->single_role . '</strong>' : '-') . ($row->definisi ? '<br>' . $row->definisi : '-'));
            }
        }

        // Build data based on jobRole and its compositeRole relationship
        $compositeRoles = [];
        foreach ($jobRoles as $jobRole) {
            if ($jobRole->compositeRole && $jobRole->compositeRole->count() > 0) {
                $compositeName = $jobRole->compositeRole->nama ?? null;
                $effectiveDescription = $compositeName && isset($middleDescriptions[$compositeName])
                    ? $middleDescriptions[$compositeName]
                    : ($jobRole->compositeRole->deskripsi ?? '-');

                // Mark composite name as (MDB) when description comes from middle_db
                $isCompositeFromMDB = $compositeName && isset($middleDescriptions[$compositeName]);
                $displayCompositeName = $compositeName
                    ? ($compositeName . ($isCompositeFromMDB ? ' <span style="color: green;"><strong><italic>{MDB}</italic></strong></span>' : ''))
                    : '-';

                // Compose composite role label as "nama <br> deskripsi"
                $compositeLabel = '<strong>' . $displayCompositeName . '</strong>'
                    . ($effectiveDescription ? '<br>' . $effectiveDescription : '-');

                // Use prebuilt AO text from the AO Single Role row
                $aoText = $compositeName && isset($aoByComposite[$compositeName])
                    ? $aoByComposite[$compositeName]
                    : '-';

                $data[] = [
                    'company' => $jobRole->company ? $jobRole->company->nama : '-',
                    'kompartemen' => $jobRole->kompartemen ? $jobRole->kompartemen->nama : '-',
                    'departemen' => $jobRole->departemen ? $jobRole->departemen->nama : '-',
                    'job_role' => $jobRole->nama ?? '-',
                    'composite_role' => $compositeLabel,
                    'authorization_object' => $aoText,
                ];

                $compositeRoles[$jobRole->compositeRole->id] = $jobRole->compositeRole;
            }
        }

        // Fetch single roles for each composite role (MDB-first, then local)
        $compositeRolesWithSingles = [];
        foreach ($compositeRoles as $compositeRole) {
            $mappedSingles = collect();
            $compositeDisplayName = $compositeRole->nama;

            // Try middle_db first by composite name
            $usedMDBComposite = false;
            $compositeDisplayNameRaw = $compositeRole->nama; // raw
            $compositeDisplayNameHtml = $compositeDisplayNameRaw;
            if (!empty($compositeRole->nama)) {
                $mdbComposite = MiddleCompositeRole::where('composite_role', $compositeRole->nama)->first();
                if ($mdbComposite) {
                    $usedMDBComposite = true;
                    $mdbSingles = $mdbComposite->singleRoles()->get(['mdb_single_role.single_role', 'mdb_single_role.definisi']);
                    $mappedSingles = $mdbSingles->map(function ($r) {
                        $raw = $r->single_role;
                        return [
                            'id' => null, // no local id
                            'nama' => $raw, // RAW for lookups
                            'nama_display' => $raw . ' <span style="color: green;"><strong><italic>{MDB}</italic></strong></span>', // UI only
                            'deskripsi' => $r->definisi,
                        ];
                    });
                    // mark composite display as MDB (keep raw untouched)
                    $compositeDisplayNameHtml = $compositeDisplayNameRaw . ' <span style="color: green;"><strong><italic>{MDB}</italic></strong></span>';
                }
            }
            // If not found in middle_db, fall back to local
            if (!$usedMDBComposite) {
                $singleRoles = $compositeRole->singleRoles()
                    ->whereNull('tr_single_roles.deleted_at')
                    ->get(['tr_single_roles.id', 'tr_single_roles.nama', 'tr_single_roles.deskripsi']);

                $mappedSingles = $singleRoles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'nama' => $role->nama,         // RAW
                        'nama_display' => $role->nama, // UI == raw (no MDB)
                        'deskripsi' => $role->deskripsi,
                    ];
                });
                $compositeDisplayNameHtml = $compositeDisplayNameRaw; // no MDB
            }

            $compositeRolesWithSingles[] = [
                'id' => $compositeRole->id,
                'nama' => $compositeDisplayNameRaw,         // RAW
                'nama_display' => $compositeDisplayNameHtml, // UI
                'single_roles' => $mappedSingles->toArray(),
            ];
        }

        // Compile unique single roles (MDB-first for tcodes, then local)
        $uniqueSingleRoles = [];
        foreach ($compositeRolesWithSingles as $cr) {
            foreach ($cr['single_roles'] as $sr) {
                $key = $sr['id'] ?? $sr['nama']; // use id when available, otherwise RAW name
                if (!isset($uniqueSingleRoles[$key])) {
                    $tcodes = [];

                    // Try middle_db first by RAW SR name
                    $srNameLookup = $sr['nama'] ?? '';
                    $mdbSingle = $srNameLookup
                        ? MiddleSingleRole::where('single_role', $srNameLookup)->first()
                        : null;

                    if ($mdbSingle && method_exists($mdbSingle, 'tcodes')) {
                        $tcodes = $mdbSingle->tcodes()
                            ->get(['mdb_tcode.tcode', 'mdb_tcode.definisi'])
                            ->map(function ($t) {
                                return [
                                    'tcode' => $t->tcode, // RAW for lookups
                                    'tcode_display' => $t->tcode . ' <span style="color: green;"><strong><italic>{MDB}</italic></strong></span>',
                                    'deskripsi' => $t->definisi,
                                ];
                            })->toArray();
                    } elseif (!empty($sr['id'])) {
                        // Fall back to local tcodes by local SR id
                        $singleRoleModel = \App\Models\SingleRole::find($sr['id']);
                        if ($singleRoleModel && method_exists($singleRoleModel, 'tcodes')) {
                            $tcodes = $singleRoleModel->tcodes()
                                ->whereNull('tr_tcodes.deleted_at')
                                ->get(['tr_tcodes.code', 'tr_tcodes.deskripsi'])
                                ->map(function ($t) {
                                    return [
                                        'tcode' => $t->code,          // RAW
                                        'tcode_display' => $t->code,  // UI == raw
                                        'deskripsi' => $t->deskripsi,
                                    ];
                                })->toArray();
                        }
                    }

                    // Determine display name for SR (use provided display, or build it)
                    $srNameDisplay = $sr['nama_display'] ?? $sr['nama'] ?? '-';
                    if ($mdbSingle && empty($sr['nama_display'])) {
                        $srNameDisplay = ($sr['nama'] ?? '-') . ' <span style="color: green;"><strong><italic>{MDB}</italic></strong></span>';
                    }

                    $uniqueSingleRoles[$key] = [
                        'id' => $sr['id'] ?? null,
                        'nama' => $sr['nama'] ?? '-',               // RAW
                        'nama_display' => $srNameDisplay,           // UI
                        'deskripsi' => $sr['deskripsi'] ?? null,
                        'tcodes' => $tcodes,                        // each has tcode + tcode_display
                    ];
                }
            }
        }

        // Now return after finishing the loops
        return response()->json([
            'data' => $data,
            'nomorSurat' => $nomorSurat,
            'composite_roles' => $compositeRolesWithSingles,             // each has nama + nama_display
            'single_roles' => array_values($uniqueSingleRoles),          // each has nama + nama_display + tcodes
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
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;
        $company = Company::where('company_code', $companyId)->first();
        $kompartemen = Kompartemen::where('kompartemen_id', $kompartemenId)->first();
        $departemen = Departemen::where('departemen_id', $departemenId)->first();
        $uniqueSingleRoles = [];
        $uniqueTcodeCount = [];

        $query = JobRole::query()
            ->with([
                'company',
                'kompartemen',
                'departemen',
                'compositeRole' => function ($q) {
                    $q->whereNull('deleted_at');
                }
            ]);

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

        // Build compositeRolesWithSingles
        $compositeRoles = [];
        foreach ($jobRoles as $jobRole) {
            if ($jobRole->compositeRole && $jobRole->compositeRole->count() > 0) {
                $compositeRoles[$jobRole->compositeRole->id] = $jobRole->compositeRole;
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
                        'tcodes' => $tcodes,
                    ];
                }
            }
        }

        // Build export data with header
        $exportData = [];
        $exportData[] = [
            'No',
            'Perusahaan',
            'Kompartemen_id',
            'Kompartemen',
            'Departemen_id',
            'Departemen',
            'Single Role',
            'Tcode',
            'Deskripsi Tcode'
        ];

        $rowNumber = 1;
        foreach (array_values($uniqueSingleRoles) as $sr) { // use array_values here
            if (count($sr['tcodes']) > 0) {
                foreach ($sr['tcodes'] as $tc) {
                    $exportData[] = [
                        $rowNumber,
                        $company ? $company->company_code : '-',
                        $kompartemen ? $kompartemen->kompartemen_id : '-',
                        $kompartemen ? $kompartemen->nama : '-',
                        $departemen ? $departemen->departemen_id : '-',
                        $departemen ? $departemen->nama : '-',
                        $sr['nama'],
                        $tc['tcode'],
                        $tc['deskripsi'],
                    ];
                    $rowNumber++;
                }
            } else {
                $exportData[] = [
                    $rowNumber,
                    $company ? $company->company_code : '-',
                    $kompartemen ? $kompartemen->kompartemen_id : '-',
                    $kompartemen ? $kompartemen->nama : '-',
                    $departemen ? $departemen->departemen_id : '-',
                    $departemen ? $departemen->nama : '-',
                    $sr['nama'],
                    '-',
                    '-',
                ];
                $rowNumber++;
            }
        }

        return Excel::download(new ArrayExport($exportData), 'single_role_tcodes.xlsx');
    }

    public function exportWord(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        $unitKerja = '-';
        $jabatanUnitKerja = '';
        $unitKerjaName = '';
        $latestPeriodeObj = Periode::latest()->first();
        $latestPeriode = $latestPeriodeObj ? $latestPeriodeObj->definisi : '-';
        $latestPeriodeYear = $latestPeriodeObj ? date('Y', strtotime($latestPeriodeObj->created_at)) : null;
        $nomorSurat = 'XXX';
        // $maxSingleRoles = 150; // Maximum single roles viewed per document
        $maxTcodes = 600; // Maximum tcodes viewed per document

        // Initialize PhpWord

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
            if ($jobRole->compositeRole && $jobRole->compositeRole->count() > 0) {
                $compositeRoles[$jobRole->compositeRole->id] = $jobRole->compositeRole;
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

        $coverTable->addRow(8000, [
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
        $coverTable->addCell(3400)->addText('Abdul Muhyi Marakarma', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
        $coverTable->addCell(3400)->addText('Sony Candra Dirganto', ['size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
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
        $docInfoTable->addRow(500, ['exactHeight' => true]);
        $docInfoTable->addCell(3400, ['valign' => 'center', 'vMerge' => 'restart',])->addImage(
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
        $docInfoTable->addRow(1200, ['exactHeight' => true]);
        $docInfoTable->addCell(null, ['vMerge' => 'continue']);
        $docInfoTable->addCell(6000, [
            'valign' => 'center',
            'gridSpan' => 2,
        ])->addText(
            "PupukIndonesia@" . Carbon::today()->year . " Dokumen ini milik PT Pupuk Indonesia (Persero). Segala informasi yang tercantum dalam dokumen ini bersifat rahasia dan terbatas, serta tidak diperkenankan untuk didistribusikan kembali, baik dalam bentuk cetakan maupun elektronik, tanpa persetujuan dari PT Pupuk Indonesia (Persero).",
            ['size' => 8],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );

        // Main section with header/footer
        $section = $phpWord->addSection();
        $header = $section->addHeader();

        // Header Table (Logo + Title + No Dok + Nomor Surat)
        $headerTable = $header->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMarginLeft' => 50, 'cellMarginTop' => 25, 'cellMarginRight' => 50, 'cellMarginBottom' => 25]);

        // Row 1: Logo + Title + No Dok + Nomor Surat
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(3000, ['valign' => 'center', 'vMerge' => 'restart',])->addImage(
            public_path('logo_pupuk_indonesia.png'),
            ['width' => 80, 'height' => 50, 'alignment' => Jc::CENTER]
        );
        $headerTable->addCell(6000, ['valign' => 'center'])->addText(
            'TEKNOLOGI INFORMASI',
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $headerTable->addCell(1200, ['valign' => 'center'])->addText('No Dok', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center'])->addText('PI-TIN-UAM-' . $latestPeriodeYear . '-' . $nomorSurat, ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        // Row 2: Rowspan + Judul  + Rev. Ke + 0
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(6000, ['valign' => 'center', 'vMerge' => 'restart'])->addText(
            'USER ACCESS MATRIX <w:br/> ' . mb_strtoupper(($unitKerja ? "$unitKerja" : '-'), 'UTF-8'),
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $headerTable->addCell(1200, ['valign' => 'center'])->addText('Rev. Ke', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center'])->addText('0', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        // Row 3: Rowspan + Rowspan Tanggal + Tanggal Sekarang
        $headerTable->addRow(250, ['exactHeight' => true]);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(null, ['vMerge' => 'continue']);
        $headerTable->addCell(1200, ['valign' => 'center'])->addText('Tanggal', ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $headerTable->addCell(3000, ['valign' => 'center'])->addText(\Carbon\Carbon::today()->locale('id')->isoFormat('DD MMMM YYYY'), ['size' => 9], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        // Row 4: Rowspan + Rowspan + Halaman Ke + Halaman
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
            $table->addRow();
            $table->addCell(750, ['valign' => 'top'])->addText(
                $no++,
                ['size' => 8],
                ['space' => ['after' => 0], 'alignment' => Jc::CENTER]
            );
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($jobRole->nama ?? '-'), ['size' => 8], ['spaceAfter' => 0]);
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($jobRole->compositeRole->nama ? $jobRole->compositeRole->nama . '<br>' . $jobRole->compositeRole->deskripsi : '-'), ['size' => 8], ['spaceAfter' => 0]);
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($jobRole->compositeRole->authorizationObject ? $jobRole->compositeRole->authorizationObject . '<br>' . $jobRole->compositeRole->authorizationObject->deskripsi : '-'), ['size' => 8], ['spaceAfter' => 0]);
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
