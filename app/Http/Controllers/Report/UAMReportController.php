<?php

namespace App\Http\Controllers\Report;

use \Carbon\Carbon;

use App\Exports\ArrayExport;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use App\Models\PenomoranUAM;
use App\Models\Periode;

use App\Models\SingleRole;

use Illuminate\Http\Request;
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

        // Build data based on jobRole and its compositeRole relationship
        $compositeRoles = [];
        foreach ($jobRoles as $jobRole) {
            if ($jobRole->compositeRole && $jobRole->compositeRole->count() > 0) {
                $data[] = [
                    'company' => $jobRole->company ? $jobRole->company->nama : '-',
                    'kompartemen' => $jobRole->kompartemen ? $jobRole->kompartemen->nama : '-',
                    'departemen' => $jobRole->departemen ? $jobRole->departemen->nama : '-',
                    'job_role' => $jobRole->nama ?? '-',
                    'composite_role' => $jobRole->compositeRole->nama ?? '-',
                    'composite_role_description' => $jobRole->compositeRole->deskripsi ?? '-',
                ];
                // Collect unique composite roles
                $compositeRoles[$jobRole->compositeRole->id] = $jobRole->compositeRole;
            }
        }

        // Fetch single roles for each composite role
        $compositeRolesWithSingles = [];
        foreach ($compositeRoles as $compositeRole) {
            // Specify table name for id and nama to avoid ambiguity
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

        return response()->json([
            'data' => $data,
            'nomorSurat' => $nomorSurat,
            'composite_roles' => $compositeRolesWithSingles,
            'single_roles' => array_values($uniqueSingleRoles), // add this line
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

        $uniqueSingleRoles = [];
        $uniqueTcodes = [];
        foreach ($compositeRolesWithSingles as $cr) {
            foreach ($cr['single_roles'] as $sr) {
                if (!isset($uniqueSingleRoles[$sr['id']])) {
                    $singleRoleModel = SingleRole::find($sr['id']);
                    $tcodes = [];
                    if ($singleRoleModel && method_exists($singleRoleModel, 'tcodes')) {
                        $tcodes = $singleRoleModel->tcodes()
                            ->whereNull('tr_tcodes.deleted_at')
                            ->get(['tr_tcodes.code', 'tr_tcodes.deskripsi'])
                            ->map(function ($t) use (&$uniqueTcodes) {
                                $uniqueTcodes[$t->code] = true;
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
                } else {
                    // Also collect tcodes for already added single roles
                    $singleRoleModel = SingleRole::find($sr['id']);
                    if ($singleRoleModel && method_exists($singleRoleModel, 'tcodes')) {
                        $singleRoleModel->tcodes()
                            ->whereNull('tr_tcodes.deleted_at')
                            ->get(['tr_tcodes.code'])
                            ->each(function ($t) use (&$uniqueTcodes) {
                                $uniqueTcodes[$t->code] = true;
                            });
                    }
                }
            }
        }
        $uniqueSingleRoleCount = count($uniqueSingleRoles);
        $uniqueTcodeCount = count($uniqueTcodes);

        // Prepare PhpWord
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

        // Header Table (Logo + Title + Info)
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

        // HEADER KOLOM TENGAH - JUDUL DOKUMEN
        // $nestedTitleCell = $headerTable->addCell(4500, ['valign' => 'top']);
        // // Create a nested table for the title section
        // $titleTable = $nestedTitleCell->addTable([
        //     'borderSize' => 0,
        //     'borderColor' => 'FFFFFF',
        // ]);
        // $titleTable->addRow(375);
        // $titleTable->addCell(4500, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        // ])->addText(
        //     'TEKNOLOGI INFORMASI',
        //     ['bold' => true, 'size' => 10],
        //     ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        // );
        // Make the second cell span 3 rows to match the height of HEADER KOLOM KANAN row 2+3+4
        // $titleTable->addRow();
        // $titleTable->addCell(4500, [
        //     'valign' => 'center',
        //     'rowSpan' => 3, // span 3 rows
        //     'borderRightColor' => '000000',
        // ])->addText(
        //     'USER ACCESS MATRIX <w:br/> ' . mb_strtoupper(($unitKerja ? "$unitKerja" : '-'), 'UTF-8'),
        //     ['bold' => true, 'size' => 10],
        //     ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        // );

        // // HEADER KOLOM KANAN

        // $nestedTable = $headerTable->addCell(2000, ['gridSpan' => 2, 'valign' => 'center',])->addTable([
        //     'borderSize' => 0,
        //     'borderColor' => 'FFFFFF',
        // ]);
        // // Row 1: Nomor
        // $nestedTable->addRow();
        // $nestedTable->addCell(1000, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        //     'borderRightSize' => 6,
        //     'borderRightColor' => '000000',
        // ])->addText('No Dok', ['size' => 8]);
        // $nestedTable->addCell(2250, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        // ])->addText('PI-TIN-UAM-' . $latestPeriodeYear . '-' . $nomorSurat, ['size' => 8]);

        // // Row 2: Rev. Ke
        // $nestedTable->addRow();
        // $nestedTable->addCell(1000, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        //     'borderRightSize' => 6,
        //     'borderRightColor' => '000000',
        // ])->addText('Rev. ke', ['size' => 8]);
        // $nestedTable->addCell(2250, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        // ])->addText('0', ['size' => 8]);

        // // Row 3: Tanggal
        // $nestedTable->addRow();
        // $nestedTable->addCell(1000, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        //     'borderRightSize' => 6,
        //     'borderRightColor' => '000000',
        // ])->addText('Periode', ['size' => 8]);
        // $nestedTable->addCell(2250, [
        //     'valign' => 'center',
        //     'borderBottomSize' => 6,
        //     'borderBottomColor' => '000000',
        // ])->addText(\Carbon\Carbon::today()->locale('id')->isoFormat('DD MMMM YYYY'), ['size' => 8]);

        // // Row 4: Hal. ke
        // $nestedTable->addRow();
        // $nestedTable->addCell(1000, [
        //     'valign' => 'center',
        //     'borderRightSize' => 6,
        //     'borderRightColor' => '000000',
        // ])->addText('Hal. ke', ['size' => 8]);
        // $nestedTable->addCell(2250, [
        //     'valign' => 'center',
        // ])->addPreserveText('{PAGE} dari {NUMPAGES}', ['size' => 8], ['alignment' => Jc::START]);

        // (No need to add the header table to the section body)
        $header->addTextBreak(1);
        // $section->addTextBreak(1);

        // // Review Table
        // $reviewTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        // // First row with cellMargin
        // $reviewTable->addRow();
        // $reviewTable->addCell(12000, [
        //     'gridSpan' => 2,
        //     'bgColor' => 'D9E1F2',
        //     'valign' => 'center'
        // ])->addText(
        //     'DOKUMEN REVIEW USER ID DAN OTORISASI',
        //     ['bold' => true, 'color' => '000000'],
        //     ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        // );

        // // Next rows without cellMargin
        // $reviewTable->addRow();
        // $reviewTable->addCell(4000)->addText('Nomor Surat', ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addCell(8000)->addText('PI-TIN-UAM-' . $latestPeriodeYear . '-' . $nomorSurat, ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addRow();
        // $reviewTable->addCell(4000)->addText('Unit Kerja', ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addCell(8000)->addText($unitKerja ? "$unitKerja" : '-', ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addRow();
        // $reviewTable->addCell(4000)->addText('Jumlah Unique Single Role', ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addCell(8000)->addText($uniqueSingleRoleCount, ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addRow();
        // $reviewTable->addCell(4000)->addText('Jumlah Unique Tcode', ['size' => 8], ['space' => ['after' => 0]]);
        // $reviewTable->addCell(8000)->addText($uniqueTcodeCount, ['size' => 8], ['space' => ['after' => 0]]);

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
        $table->addCell(3750)->addText('Deskripsi', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

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
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($jobRole->compositeRole->nama ?? '-'), ['size' => 8], ['spaceAfter' => 0]);
            $table->addCell(3750, ['valign' => 'top'])->addText($this->sanitizeForDocx($jobRole->compositeRole->deskripsi ?? '-'), ['size' => 8], ['spaceAfter' => 0]);
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

        // // Approval Table (Persetujuan)
        // $section->addTextBreak(1);

        // $approvalTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);

        // // Header row
        // $approvalTable->addRow();
        // $approvalTable->addCell(5000, ['bgColor' => 'D9E1F2', 'gridSpan' => 2])->addText('Persetujuan', ['bold' => true], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500, ['bgColor' => 'D9E1F2'])->addText('Tanda Tangan', ['bold' => true], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500, ['bgColor' => 'D9E1F2'])->addText('Tanggal', ['bold' => true], ['space' => ['after' => 0]]);

        // // Disiapkan oleh
        // $approvalTable->addRow();
        // $approvalTable->addCell(null, ['gridSpan' => 4])->addText('Disiapkan oleh:', ['bold' => true, 'size' => 8], ['space' => ['after' => 0]]);

        // // // System Administrator
        // $approvalTable->addRow();
        // $approvalTable->addCell(2500)->addText('System Administrator', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('Deny Pratama', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // // // Functional Modul Sales & Distribution (SD)
        // $approvalTable->addRow();
        // $approvalTable->addCell(2500)->addText('Functional Modul ....', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // // Diverifikasi oleh
        // $approvalTable->addRow();
        // $approvalTable->addCell(null, ['gridSpan' => 4])->addText('Diverifikasi oleh:', ['bold' => true, 'size' => 8], ['space' => ['after' => 0]]);

        // // VP Operasional Sistem TI
        // $approvalTable->addRow();
        // $approvalTable->addCell(2500)->addText('VP Operasional Sistem TI', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('Abdul Muhyi Marakarma', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // // VP Dept. Strategi & Evaluasi Kinerja
        // $approvalTable->addRow();
        // $approvalTable->addCell(2500)->addText($jabatanUnitKerja . ' ' . $unitKerjaName, ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        // $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // Output
        $fileName = 'PI-TIN-UAM-' . $latestPeriodeYear . '-' . $nomorSurat . '_' . $unitKerja . ' ' . $latestPeriodeYear .  '.docx';
        $filePath = storage_path('app/public/' . $fileName);

        // Save using IOFactory
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
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
