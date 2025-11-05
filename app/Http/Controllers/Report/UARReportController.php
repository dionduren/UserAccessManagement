<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use App\Models\NIKJobRole;
use App\Models\PenomoranUAR;
use App\Models\Periode;
use App\Models\userGenericSystem;
use App\Models\UserGenericUnitKerja;
use App\Models\UserNIKUnitKerja;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class UARReportController extends Controller
{
    public function index(Request $request)
    {
        $userCompany = auth()->user()->loginDetail->company_code;

        if ($userCompany !== 'A000') {
            // Get all companies with the same first character as userCompany
            $firstChar = substr($userCompany, 0, 1);
            $companies = Company::select('company_code', 'nama')
                ->where('company_code', 'LIKE', $firstChar . '%')
                ->orderBy('company_code')
                ->get();
        } else {
            // A000 users see all companies
            $companies = Company::select('company_code', 'nama')
                ->orderBy('company_code')
                ->get();
        }

        $periodes = Periode::orderByDesc('id')->get(['id', 'definisi', 'created_at']);
        $selectedPeriodeId = $request->get('periode_id');
        if (!$selectedPeriodeId && $periodes->isNotEmpty()) {
            // default pilih periode terbaru (bisa diubah sesuai kebutuhan)
            $selectedPeriodeId = $periodes->first()->id;
        }
        $selectedPeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $jumlahAwalUser = 0;
        if ($selectedPeriode) {
            $jumlahAwalUser = NIKJobRole::where('periode_id', $selectedPeriode->id)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->count();
        }

        return view('report.uar.index', compact(
            'companies',
            'periodes',
            'selectedPeriodeId',
            'selectedPeriode',
            'jumlahAwalUser'
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
        $periodeId     = $request->get('periode_id');
        $companyId     = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId  = $request->departemen_id;

        if (!$periodeId || !($periode = Periode::find($periodeId))) {
            return response()->json([
                'data'        => [],
                'nomorSurat'  => '-',
                'cost_center' => '-',
                'message'     => 'Periode tidak valid / belum dipilih'
            ], 200);
        }

        $periodeYear  = $periode->created_at ? $periode->created_at->format('Y') : date('Y');
        $headerNomorSurat = 'PI-TIN-UAR';
        $nomorSurat   = 'XXX - Belum terdaftar';
        $cost_center  = '-';
        $dataUserSystem = [];

        // Add company filtering based on user permissions
        $userCompany = auth()->user()->loginDetail->company_code;

        // Validate company access for non-A000 users
        if ($userCompany !== 'A000' && $companyId) {
            $firstChar = substr($userCompany, 0, 1);
            $allowedCompanies = Company::where('company_code', 'LIKE', $firstChar . '%')
                ->pluck('company_code')
                ->toArray();

            if (!in_array($companyId, $allowedCompanies)) {
                return response()->json([
                    'data'             => [],
                    'nomorSurat'       => '-',
                    'composite_roles'  => [],
                    'single_roles'     => [],
                    'message'          => 'Access denied to selected company'
                ], 403);
            }
        }

        // Get nomor surat & cost center logic (same as before)
        if ($departemenId) {
            $penomoranUAR = PenomoranUAR::where('unit_kerja_id', $departemenId)
                ->whereNull('deleted_at')->latest()->first();
            if ($penomoranUAR) {
                $nomorSurat  = $penomoranUAR->number;
                $cost_center = $penomoranUAR->departemen?->cost_center ?? 'Belum terdaftar';
            } else {
                $nomorSurat  = 'XXX (Belum terdaftar)';
                $cost_center = Departemen::find($departemenId)?->cost_center ?? 'Belum terdaftar';
                // Log::info('Departemen ID not found for PenomoranUAR: ' . $departemenId);
                // Log::info('Departemen Cost Center: ' . (Departemen::find($departemenId) ?? 'Not Found'));
            }
        } elseif ($kompartemenId) {
            $penomoranUAR = PenomoranUAR::where('unit_kerja_id', $kompartemenId)
                ->whereNull('deleted_at')->latest()->first();
            if ($penomoranUAR) {
                $nomorSurat  = $penomoranUAR->number;
                $cost_center = $penomoranUAR->kompartemen?->cost_center ?? 'Belum terdaftar';

                if ($cost_center == 'A008200000') {
                    $dataUserSystem = userGenericSystem::where('cost_code', $cost_center)->where('periode_id', $periode->id)->get();
                }
            } else {
                $nomorSurat  = 'XXX (Belum terdaftar)';
                $cost_center = Kompartemen::find($kompartemenId)?->cost_center ?? 'Belum terdaftar';
            }
        }

        // Set header nomor surat based on company
        switch ($companyId) {
            case 'F000':
                $headerNomorSurat = "PSP-LTI-UAR-{$periodeYear}";
                break;
            case 'C000':
                $headerNomorSurat = "PK-LTI-UAR-{$periodeYear}";
                break;
            case 'E000':
                $headerNomorSurat = "PIM-TI-UAR-{$periodeYear}";
                break;
            case 'B000':
                $headerNomorSurat = "PKG_TI.02.01_UAR_{$periodeYear}";
                break;
            case 'G000':
                $headerNomorSurat = "REK-TI-UAR-{$periodeYear}";
                break;
            default: // A000 or any other
                $headerNomorSurat = "PI-TIN-UAR-{$periodeYear}";
                break;
        }

        // Format final nomor surat based on company
        if ($companyId === 'B000') {
            $nomorSurat = "{$nomorSurat}_{$headerNomorSurat}";
        } else {
            $nomorSurat = "{$headerNomorSurat}-{$nomorSurat}";
        }

        // Query UserNIKUnitKerja with NIKJobRole filter
        $userNIKQuery = UserNIKUnitKerja::query()
            ->with([
                'company',
                'kompartemen',
                'departemen'
            ])
            ->whereNull('deleted_at')
            ->where('periode_id', $periode->id)
            // Only include UserNIKUnitKerja that have corresponding NIKJobRole
            ->whereExists(function ($query) use ($periode) {
                $query->select(\DB::raw(1))
                    ->from('tr_ussm_job_role')
                    ->whereColumn('tr_ussm_job_role.nik', 'ms_nik_unit_kerja.nik')
                    ->where('tr_ussm_job_role.periode_id', $periode->id)
                    ->where('tr_ussm_job_role.is_active', true)
                    ->whereNull('tr_ussm_job_role.deleted_at');
            });

        // Apply filters for UserNIK
        if ($companyId) {
            $userNIKQuery->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $userNIKQuery->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $userNIKQuery->where('departemen_id', $departemenId);
        }

        // Query UserGenericUnitKerja with NIKJobRole filter
        $userGenericQuery = UserGenericUnitKerja::query()
            ->with([
                'userGeneric',
                'kompartemen',
                'departemen'
            ])
            ->whereNull('deleted_at')
            ->where('periode_id', $periode->id)
            // Only include UserGenericUnitKerja that have corresponding NIKJobRole
            ->whereExists(function ($query) use ($periode) {
                $query->select(\DB::raw(1))
                    ->from('tr_ussm_job_role')
                    ->whereColumn('tr_ussm_job_role.nik', 'ms_generic_unit_kerja.user_cc')
                    ->where('tr_ussm_job_role.periode_id', $periode->id)
                    ->where('tr_ussm_job_role.is_active', true)
                    ->whereNull('tr_ussm_job_role.deleted_at');
            });

        // Apply filters for UserGeneric - FIXED COMPANY FILTERING
        if ($companyId) {
            $userGenericQuery->where(function ($q) use ($companyId) {
                $q->whereHas('departemen', function ($subQ) use ($companyId) {
                    $subQ->where('company_id', $companyId);
                })->orWhere(function ($subQ) use ($companyId) {
                    $subQ->whereHas('kompartemen', function ($subSubQ) use ($companyId) {
                        $subSubQ->where('company_id', $companyId);
                    })->whereNull('departemen_id');
                });
            });
        }
        if ($kompartemenId) {
            $userGenericQuery->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $userGenericQuery->where('departemen_id', $departemenId);
        }

        $userNIKs = $userNIKQuery->get();
        $userGenerics = $userGenericQuery->get();

        $data = [];

        // Process UserNIK data
        foreach ($userNIKs as $userNIK) {
            // *** NEW: Get user's group from userNIK table first ***
            $userNIKRecord = \App\Models\userNIK::where('user_code', $userNIK->nik)
                ->where('periode_id', $periode->id)
                ->whereNull('deleted_at')
                ->first();

            $userGroup = $userNIKRecord?->group;

            // Find corresponding NIKJobRole with job role relationships
            $nikJobRoleQuery = NIKJobRole::with([
                'jobRole.kompartemen',
                'jobRole.departemen',
                'jobRole.company', // Make sure company is eager loaded
                'mdb_usmm'
            ])
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->where('periode_id', $periode->id)
                ->where('nik', $userNIK->nik);

            // Filter NIKJobRole by company if specified
            if ($companyId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }
            if ($kompartemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($kompartemenId) {
                    $q->where('kompartemen_id', $kompartemenId);
                });
            }
            if ($departemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($departemenId) {
                    $q->where('departemen_id', $departemenId);
                });
            }

            // *** NEW: Filter by matching group (userNIK.group) with JobRole company shortname ***
            if ($userGroup) {
                $nikJobRoleQuery->whereHas('jobRole.company', function ($q) use ($userGroup) {
                    $q->where('shortname', $userGroup);
                });
            }

            $nikJobRole = $nikJobRoleQuery->first();

            // Skip if no NIKJobRole found
            if (!$nikJobRole) {
                continue;
            }

            $jobRoleName = '-';
            $assignedJobRole = '-';
            $assignedUnitKerja = '-';

            $jobRole = $nikJobRole->jobRole;
            if ($jobRole) {
                $isDifferentUnitKerja = false;

                // Check if job role's unit kerja matches selected filters
                if ($departemenId && $jobRole->departemen_id != $departemenId) {
                    $isDifferentUnitKerja = true;
                } elseif ($kompartemenId && !$departemenId && $jobRole->kompartemen_id != $kompartemenId) {
                    $isDifferentUnitKerja = true;
                } elseif ($companyId && !$kompartemenId && !$departemenId && $jobRole->company_id != $companyId) {
                    $isDifferentUnitKerja = true;
                }

                // Set assigned job role details
                $assignedJobRole = $jobRole->nama ?? '-';
                $kompartemenName = $jobRole->kompartemen?->nama ?? '-';
                $departemenName = $jobRole->departemen?->nama ?? '-';
                $assignedUnitKerja = $kompartemenName . ' - ' . $departemenName;

                if ($isDifferentUnitKerja) {
                    $jobRoleName = '<span style="color: red;">[Job Role with Different Unit Kerja]</span>';
                } else {
                    $jobRoleName = $assignedJobRole;
                }
            }

            // Get MDB data if available
            $mdb = $nikJobRole?->mdb_usmm;
            $mdbData = $mdb ? [
                'sap_user_id' => $mdb->sap_user_id ?? null,
                'nama'        => $mdb->full_name ?? null,
                'nik'         => $mdb->masterDataKaryawan_nama->nik ?? null,
            ] : null;

            $data[] = [
                'company'       => $userNIK->company?->nama ?? '-',
                'kompartemen'   => $userNIK->kompartemen?->nama ?? '-',
                'departemen'    => $userNIK->departemen?->nama ?? '-',
                'user_nik'      => $nikJobRole->nik ?? $userNIK->nik ?? '-',
                'job_role'      => $jobRoleName,
                'assigned_job_role' => $assignedJobRole,
                'assigned_unit_kerja' => $assignedUnitKerja,
                'user_definisi' => $userNIK->nama ?? '-',
                'karyawan_nik'  => $userNIK->nik ?? '-',
                'mdb_usmm'      => $mdbData,
                'user_type'     => 'NIK'
            ];
        }

        // Process UserGeneric data with same filtering logic
        foreach ($userGenerics as $userGeneric) {
            // Find corresponding NIKJobRole with job role relationships
            $nikJobRoleQuery = NIKJobRole::with([
                'jobRole.kompartemen',
                'jobRole.departemen',
                'jobRole.company', // Make sure company is eager loaded
                'mdb_usmm'
            ])
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->where('periode_id', $periode->id)
                ->where('nik', $userGeneric->user_cc);

            // Filter NIKJobRole by company if specified
            if ($companyId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }
            if ($kompartemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($kompartemenId) {
                    $q->where('kompartemen_id', $kompartemenId);
                });
            }
            if ($departemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($departemenId) {
                    $q->where('departemen_id', $departemenId);
                });
            }

            // *** NEW: Filter by matching group (userGeneric.group) with JobRole company shortname ***
            $userGroup = $userGeneric->userGeneric?->group;
            if ($userGroup) {
                $nikJobRoleQuery->whereHas('jobRole.company', function ($q) use ($userGroup) {
                    $q->where('shortname', $userGroup);
                });
            }

            $nikJobRole = $nikJobRoleQuery->first();

            // Skip if no NIKJobRole found (this shouldn't happen due to whereExists, but safety check)
            if (!$nikJobRole) {
                continue;
            }

            $jobRoleName = '-';
            $assignedJobRole = '-';
            $assignedUnitKerja = '-';

            $jobRole = $nikJobRole->jobRole;
            if ($jobRole) {
                $isDifferentUnitKerja = false;

                // Check if job role's unit kerja matches selected filters
                if ($departemenId && $jobRole->departemen_id != $departemenId) {
                    $isDifferentUnitKerja = true;
                } elseif ($kompartemenId && !$departemenId && $jobRole->kompartemen_id != $kompartemenId) {
                    $isDifferentUnitKerja = true;
                } elseif ($companyId && !$kompartemenId && !$departemenId && $jobRole->company_id != $companyId) {
                    $isDifferentUnitKerja = true;
                }

                // Set assigned job role details
                $assignedJobRole = $jobRole->nama ?? '-';
                $kompartemenName = $jobRole->kompartemen?->nama ?? '-';
                $departemenName = $jobRole->departemen?->nama ?? '-';
                $assignedUnitKerja = $kompartemenName . ' - ' . $departemenName;

                if ($isDifferentUnitKerja) {
                    $jobRoleName = '<span style="color: red;">[Job Role with Different Unit Kerja]</span>';
                } else {
                    $jobRoleName = $assignedJobRole;
                }
            }

            // Get MDB data if available
            $mdb = $nikJobRole?->mdb_usmm;
            $mdbData = $mdb ? [
                'sap_user_id' => $mdb->sap_user_id ?? null,
                'nama'        => $mdb->full_name ?? null,
                'nik'         => $mdb->masterDataKaryawan_nama->nik ?? null,
            ] : null;

            // Get company name from userGeneric's kompartemen/departemen
            $companyName = '-';
            if ($userGeneric->departemen?->company) {
                $companyName = $userGeneric->departemen->company->nama;
            } elseif ($userGeneric->kompartemen?->company) {
                $companyName = $userGeneric->kompartemen->company->nama;
            }

            $data[] = [
                'company'       => $companyName,
                'kompartemen'   => $userGeneric->kompartemen?->nama ?? '-',
                'departemen'    => $userGeneric->departemen?->nama ?? '-',
                'user_nik'      => $nikJobRole->nik ?? $userGeneric->user_cc ?? '-',
                'job_role'      => $jobRoleName,
                'assigned_job_role' => $assignedJobRole,
                'assigned_unit_kerja' => $assignedUnitKerja,
                'user_definisi' => $userGeneric->userGeneric?->user_profile ?? '-',
                'karyawan_nik'  => $userGeneric->userGeneric?->mappingNIK?->personnel_number ?? '-',
                'mdb_usmm'      => $mdbData,
                'user_type'     => 'Generic'
            ];
        }

        return response()->json([
            'data'        => $data,
            'nomorSurat'  => $nomorSurat,
            'cost_center' => $cost_center,
            'user_system'   => $dataUserSystem,
        ]);
    }

    public function exportWord(Request $request)
    {
        // Enable PhpWord's built-in XML escaping for special chars like &
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $periodeId     = $request->get('periode_id');
        $companyId     = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId  = $request->departemen_id;

        if (!$periodeId || !($periode = Periode::find($periodeId))) {
            abort(422, 'Periode tidak valid');
        }

        $periodeYear  = $periode->created_at ? $periode->created_at->format('Y') : date('Y');

        $headerNomorSurat = 'PI-TIN-UAR';
        $nomorSurat   = 'XXX - Belum terdaftar';
        $cost_center  = '-';
        $dataUserSystem = [];

        // Get nomor surat & cost center logic (same as jobRolesData)
        if ($departemenId) {
            $penomoranUAR = PenomoranUAR::where('unit_kerja_id', $departemenId)
                ->whereNull('deleted_at')->latest()->first();
            if ($penomoranUAR) {
                $nomorSurat  = $penomoranUAR->number;
                $cost_center = $penomoranUAR->departemen?->cost_center ?? 'Belum terdaftar';
            } else {
                $nomorSurat  = 'XXX (Belum terdaftar)';
                $cost_center = Departemen::find($departemenId)?->cost_center ?? 'Belum terdaftar';
            }
        } elseif ($kompartemenId) {
            $penomoranUAR = PenomoranUAR::where('unit_kerja_id', $kompartemenId)
                ->whereNull('deleted_at')->latest()->first();
            if ($penomoranUAR) {
                $nomorSurat  = $penomoranUAR->number;
                $cost_center = $penomoranUAR->kompartemen?->cost_center ?? 'Belum terdaftar';

                if ($cost_center == 'A008200000') {
                    $dataUserSystem = userGenericSystem::where('cost_code', $cost_center)->where('periode_id', $periode->id)->get();
                }
            } else {
                $nomorSurat  = 'XXX (Belum terdaftar)';
                $cost_center = Kompartemen::find($kompartemenId)?->cost_center ?? 'Belum terdaftar';
            }
        }

        // Set header nomor surat based on company
        switch ($companyId) {
            case 'F000':
                $headerNomorSurat = "PSP-LTI-UAR-{$periodeYear}";
                break;
            case 'C000':
                $headerNomorSurat = "PK-LTI-UAR-{$periodeYear}";
                break;
            case 'E000':
                $headerNomorSurat = "PIM-TI-UAR-{$periodeYear}";
                break;
            case 'B000':
                $headerNomorSurat = "PKG_TI.02.01_UAR_{$periodeYear}";
                break;
            case 'G000':
                $headerNomorSurat = "REK-TI-UAR-{$periodeYear}";
                break;
            default: // A000 or any other
                $headerNomorSurat = "PI-TIN-UAR-{$periodeYear}";
                break;
        }

        // Format final nomor surat based on company
        if ($companyId === 'B000') {
            $finalNomorSurat = "{$nomorSurat}_{$headerNomorSurat}";
        } else {
            $finalNomorSurat = "{$headerNomorSurat}-{$nomorSurat}";
        }

        // ADOPTED LOGIC FROM jobRolesData - Query UserNIKUnitKerja with NIKJobRole filter
        $userNIKQuery = UserNIKUnitKerja::query()
            ->with([
                'company',
                'kompartemen',
                'departemen'
            ])
            ->whereNull('deleted_at')
            ->where('periode_id', $periode->id)
            // Only include UserNIKUnitKerja that have corresponding NIKJobRole
            ->whereExists(function ($query) use ($periode) {
                $query->select(\DB::raw(1))
                    ->from('tr_ussm_job_role')
                    ->whereColumn('tr_ussm_job_role.nik', 'ms_nik_unit_kerja.nik')
                    ->where('tr_ussm_job_role.periode_id', $periode->id)
                    ->where('tr_ussm_job_role.is_active', true)
                    ->whereNull('tr_ussm_job_role.deleted_at');
            });

        // Apply filters for UserNIK
        if ($companyId) {
            $userNIKQuery->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $userNIKQuery->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $userNIKQuery->where('departemen_id', $departemenId);
        }

        // Query UserGenericUnitKerja with NIKJobRole filter
        $userGenericQuery = UserGenericUnitKerja::query()
            ->with([
                'userGeneric',
                'kompartemen',
                'departemen'
            ])
            ->whereNull('deleted_at')
            ->where('periode_id', $periode->id)
            // Only include UserGenericUnitKerja that have corresponding NIKJobRole
            ->whereExists(function ($query) use ($periode) {
                $query->select(\DB::raw(1))
                    ->from('tr_ussm_job_role')
                    ->whereColumn('tr_ussm_job_role.nik', 'ms_generic_unit_kerja.user_cc')
                    ->where('tr_ussm_job_role.periode_id', $periode->id)
                    ->where('tr_ussm_job_role.is_active', true)
                    ->whereNull('tr_ussm_job_role.deleted_at');
            });

        // Apply filters for UserGeneric - FIXED COMPANY FILTERING
        if ($companyId) {
            $userGenericQuery->where(function ($q) use ($companyId) {
                $q->whereHas('departemen', function ($subQ) use ($companyId) {
                    $subQ->where('company_id', $companyId);
                })->orWhere(function ($subQ) use ($companyId) {
                    $subQ->whereHas('kompartemen', function ($subSubQ) use ($companyId) {
                        $subSubQ->where('company_id', $companyId);
                    })->whereNull('departemen_id');
                });
            });
        }
        if ($kompartemenId) {
            $userGenericQuery->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $userGenericQuery->where('departemen_id', $departemenId);
        }

        $userNIKs = $userNIKQuery->get();
        $userGenerics = $userGenericQuery->get();

        $combinedData = [];

        // Process UserNIK data (same logic as jobRolesData)
        foreach ($userNIKs as $userNIK) {
            // *** NEW: Get user's group from userNIK table first ***
            $userNIKRecord = \App\Models\userNIK::where('user_code', $userNIK->nik)
                ->where('periode_id', $periode->id)
                ->whereNull('deleted_at')
                ->first();

            $userGroup = $userNIKRecord?->group;

            // Find corresponding NIKJobRole with job role relationships
            $nikJobRoleQuery = NIKJobRole::with([
                'jobRole.kompartemen',
                'jobRole.departemen',
                'jobRole.company', // Make sure company is eager loaded
                'mdb_usmm'
            ])
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->where('periode_id', $periode->id)
                ->where('nik', $userNIK->nik);

            // Filter NIKJobRole by company if specified
            if ($companyId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }
            if ($kompartemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($kompartemenId) {
                    $q->where('kompartemen_id', $kompartemenId);
                });
            }
            if ($departemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($departemenId) {
                    $q->where('departemen_id', $departemenId);
                });
            }

            // *** NEW: Filter by matching group (userNIK.group) with JobRole company shortname ***
            if ($userGroup) {
                $nikJobRoleQuery->whereHas('jobRole.company', function ($q) use ($userGroup) {
                    $q->where('shortname', $userGroup);
                });
            }

            $nikJobRole = $nikJobRoleQuery->first();

            // Skip if no NIKJobRole found
            if (!$nikJobRole) {
                continue;
            }

            $jobRole = $nikJobRole->jobRole;
            $mdb = $nikJobRole?->mdb_usmm;

            // Extract data for Word export
            $userId    = $nikJobRole->nik ?? '-';
            $userName  = $userNIK->nama ?? '-';
            $nikValue  = $userNIK->nik ?? '-';

            $combinedData[] = [
                'user_id' => $userId,
                'user_name' => $userName,
                'job_role_name' => $jobRole->nama ?? '-',
                'nik_value' => $nikValue,
                'user_type' => 'NIK'
            ];
        }

        // Process UserGeneric data (same logic as jobRolesData)
        foreach ($userGenerics as $userGeneric) {
            // Find corresponding NIKJobRole with job role relationships
            $nikJobRoleQuery = NIKJobRole::with([
                'jobRole.kompartemen',
                'jobRole.departemen',
                'jobRole.company', // Make sure company is eager loaded
                'mdb_usmm'
            ])
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->where('periode_id', $periode->id)
                ->where('nik', $userGeneric->user_cc);

            // Filter NIKJobRole by company if specified
            if ($companyId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }
            if ($kompartemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($kompartemenId) {
                    $q->where('kompartemen_id', $kompartemenId);
                });
            }
            if ($departemenId) {
                $nikJobRoleQuery->whereHas('jobRole', function ($q) use ($departemenId) {
                    $q->where('departemen_id', $departemenId);
                });
            }

            // *** NEW: Filter by matching group (userGeneric.group) with JobRole company shortname ***
            $userGroup = $userGeneric->userGeneric?->group;
            if ($userGroup) {
                $nikJobRoleQuery->whereHas('jobRole.company', function ($q) use ($userGroup) {
                    $q->where('shortname', $userGroup);
                });
            }

            $nikJobRole = $nikJobRoleQuery->first();

            // Skip if no NIKJobRole found (this shouldn't happen due to whereExists, but safety check)
            if (!$nikJobRole) {
                continue;
            }

            $jobRole = $nikJobRole->jobRole;
            $mdb = $nikJobRole?->mdb_usmm;

            // Extract data for Word export
            $userId    = $nikJobRole->nik ?? '-';
            $userName  = $userGeneric->userGeneric?->user_profile ?? '-';
            $nikValue  = $userGeneric->userGeneric?->mappingNIK?->personnel_number ?? '-';

            $combinedData[] = [
                'user_id' => $userId,
                'user_name' => $userName,
                'job_role_name' => $jobRole->nama ?? '-',
                'nik_value' => $nikValue,
                'user_type' => 'Generic'
            ];
        }

        $jumlahAwalUser = count($combinedData);

        // Get unit kerja info for display
        $unitKerja = '-';
        $jabatanUnitKerja = '';
        $unitKerjaName = '';

        $latestPeriodeObj = $periode;
        $latestPeriode    = $periode->definisi;
        $latestPeriodeYear = $periode->created_at?->format('Y') ?? date('Y');

        // Persiapkan Identifier Unit Kerja & Nomor Dokumen UAR berdasarkan filter Unit kerja yang dipilih
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
            } elseif (preg_match('/^Dep\s+/', $displayName)) {
                $displayName = preg_replace('/^Dep\s+/', '', $displayName);
                $unitKerja = 'Departemen ' . $displayName;
            } else {
                $unitKerja = $displayName;
            }
            $unitKerja = $this->sanitizeForFilename($unitKerja);
            $unitKerjaName = $this->sanitizeForFilename($displayName);
            $jabatanUnitKerja = 'VP';
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
            $unitKerja = $this->sanitizeForFilename($unitKerja);
            $unitKerjaName = $this->sanitizeForFilename($displayName);
            $jabatanUnitKerja = 'SVP';
        } elseif ($companyId) {
            $company = Company::where('company_code', $companyId)->first();
            $displayName = $company ? $company->nama : '-';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName);
            $unitKerja = $this->sanitizeForFilename($displayName);
            $unitKerjaName = $this->sanitizeForFilename($displayName);
            $jabatanUnitKerja = 'Direktur';
            $cost_center = 'Tidak ada Cost Center untuk Level Perusahaan';
        }

        // Prepare PhpWord
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Set header for all pages
        $header = $section->addHeader();

        // Header Table (Logo + Title + Info)
        $headerTable = $header->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $headerTable->addRow();

        $headerTable->addCell(2000, ['valign' => 'center'])->addImage(
            public_path('logo_pupuk_indonesia.png'),
            ['width' => 80, 'height' => 40, 'alignment' => Jc::CENTER]
        );

        // FIX: Use multiple addText calls WITHOUT addTextBreak
        $titleCell = $headerTable->addCell(4500, ['gridSpan' => 2, 'valign' => 'center']);
        $titleCell->addText('REVIEW USER ID DAN OTORISASI', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);

        // Add a single row with a cell that spans 2 columns, containing a nested table for the 3 rows
        $nestedTable = $headerTable->addCell(2000, ['gridSpan' => 2, 'valign' => 'center'])->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
        ]);

        // Row 1: RAHASIA
        $nestedTable->addRow();
        $rahCell = $nestedTable->addCell(3250, [
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

        // Row 2: Nomor
        $nestedTable->addRow();
        $nestedTable->addCell(1000, [
            'valign' => 'center',
            'borderBottomSize' => 6,
            'borderBottomColor' => '000000',
            'borderRightSize' => 6,
            'borderRightColor' => '000000',
        ])->addText('Nomor', ['size' => 8]);
        $nestedTable->addCell(2250, [
            'valign' => 'center',
            'borderBottomSize' => 6,
            'borderBottomColor' => '000000',
        ])->addText($finalNomorSurat, ['size' => 8]);

        // Row 3: Periode
        $nestedTable->addRow();
        $nestedTable->addCell(1000, [
            'valign' => 'center',
            'borderBottomSize' => 6,
            'borderBottomColor' => '000000',
            'borderRightSize' => 6,
            'borderRightColor' => '000000',
        ])->addText('Periode', ['size' => 8]);
        $nestedTable->addCell(2250, [
            'valign' => 'center',
            'borderBottomSize' => 6,
            'borderBottomColor' => '000000',
        ])->addText($latestPeriode, ['size' => 8]);

        // Row 4: Hal. ke
        $nestedTable->addRow();
        $nestedTable->addCell(1000, [
            'valign' => 'center',
            'borderRightSize' => 6,
            'borderRightColor' => '000000',
        ])->addText('Hal. ke', ['size' => 8]);
        $nestedTable->addCell(2250, [
            'valign' => 'center',
        ])->addPreserveText('{PAGE} dari {NUMPAGES}', ['size' => 8], ['alignment' => Jc::START]);

        $header->addTextBreak(1);

        // Review Table
        $reviewTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $reviewTable->addRow();
        $reviewTable->addCell(12000, [
            'gridSpan' => 2,
            'bgColor' => 'D9E1F2',
            'valign' => 'center'
        ])->addText(
            'DOKUMEN REVIEW USER ID DAN OTORISASI',
            ['bold' => true, 'color' => '000000'],
            ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        );

        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Aset Informasi', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('User ID SAP', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Unit Kerja', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText($unitKerja ? $unitKerja : '-', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Cost Center', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText($cost_center ? $cost_center : '-', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah User', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText($jumlahAwalUser, ['italic' => true, 'size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah User Berubah', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah User yang Belum Terdata (Baru)', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah Akhir User', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        $section->addTextBreak(1);

        // Job Role Table
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(12000, [
            'gridSpan' => 8,
            'bgColor' => 'D9E1F2',
            'valign' => 'center'
        ])->addText(
            'SUMMARY USER ACCESS REVIEW',
            ['bold' => true, 'color' => '000000', 'size' => 8],
            ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        );
        $table->addRow();
        $table->addCell(750, ['bgColor' => 'D9E1F2'])->addText('No', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('User ID', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('Nama', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('Job Role', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(2000, ['bgColor' => 'D9E1F2'])->addText('NIK', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(1750, ['bgColor' => 'D9E1F2'])->addText('Tetap*', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(1750, ['bgColor' => 'D9E1F2'])->addText('Berubah*', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(2000, ['bgColor' => 'D9E1F2'])->addText('Keterangan', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);

        $no = 1;

        // Use combinedData from the new query logic
        foreach ($combinedData as $data) {
            $table->addRow();
            $table->addCell(750, ['valign' => 'center'])->addText(
                $no++,
                ['size' => 8],
                ['space' => ['after' => 0], 'alignment' => Jc::CENTER]
            );
            $table->addCell(3000, ['valign' => 'center'])->addText($data['user_id'], ['size' => 8], ['space' => ['after' => 0]]);
            $table->addCell(3000, ['valign' => 'center'])->addText(
                $data['user_name'],
                ['size' => 8],
                ['space' => ['after' => 0]]
            );
            $table->addCell(3000, ['valign' => 'center'])->addText(
                $data['job_role_name'],
                ['size' => 8],
                ['space' => ['after' => 0]]
            );
            $table->addCell(2000, ['valign' => 'center'])->addText(
                $data['nik_value'],
                ['size' => 8],
                ['space' => ['after' => 0]]
            );
            $table->addCell(1750, ['valign' => 'center'])->addText('', ['size' => 8], ['space' => ['after' => 0]]);
            $table->addCell(1750, ['valign' => 'center'])->addText('', ['size' => 8], ['space' => ['after' => 0]]);
            $table->addCell(2000, ['valign' => 'center'])->addText(
                'Apabila ada (perubahan job function/nama/nik/Penonaktifan)',
                ['size' => 8, 'color' => 'A6A6A6'],
                [
                    'space' => ['after' => 0],
                    'wrap' => true,
                ]
            );
        }

        // If no data row was added, add an empty row
        if ($no === 1) {
            $table->addRow();
            $table->addCell(750, ['valign' => 'center'])->addText('1.', ['size' => 8], ['alignment' => Jc::CENTER]);
            $table->addCell(3000, ['valign' => 'center'])->addText('-', ['size' => 8]);
            $table->addCell(3000, ['valign' => 'center'])->addText('-', ['size' => 8]);
            $table->addCell(3000, ['valign' => 'center'])->addText('-', ['size' => 8]);
            $table->addCell(2000, ['valign' => 'center'])->addText('-', ['size' => 8], ['alignment' => Jc::CENTER]);
            $table->addCell(1750, ['valign' => 'center'])->addText('-', ['size' => 8]);
            $table->addCell(1750, ['valign' => 'center'])->addText('-', ['size' => 8]);
            $table->addCell(2000, ['valign' => 'center'])->addText('-', ['size' => 8]);
        }

        // Catatan untuk kolom pilihan
        $section->addText(
            '*diisi dengan (X) apabila dipilih dan (-) apabila tidak dipilih',
            ['size' => 8, 'italic' => true, 'color' => '7F7F7F'],
            ['space' => ['after' => 0]]
        );

        // Summary User System
        if ($dataUserSystem && count($dataUserSystem) > 0) {
            $section->addTextBreak(1);

            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            $table->addRow();
            $table->addCell(12000, [
                'gridSpan' => 7,
                'bgColor' => 'D9E1F2',
                'valign' => 'center'
            ])->addText(
                'SUMMARY USER SYSTEM',
                ['bold' => true, 'color' => '000000', 'size' => 8],
                ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
            );
            $table->addRow();
            $table->addCell(750, ['bgColor' => 'D9E1F2'])->addText('No', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
            $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('User ID', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
            $table->addCell(4500, ['bgColor' => 'D9E1F2'])->addText('Deskripsi', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
            $table->addCell(4500, ['bgColor' => 'D9E1F2'])->addText('Last Login', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
            $table->addCell(1750, ['bgColor' => 'D9E1F2'])->addText('Tetap*', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
            $table->addCell(1750, ['bgColor' => 'D9E1F2'])->addText('Berubah*', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
            $table->addCell(2000, ['bgColor' => 'D9E1F2'])->addText('Keterangan', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);

            $no = 1;
            foreach ($dataUserSystem as $userSystem) {
                $table->addRow();
                $table->addCell(750, ['valign' => 'center'])->addText(
                    $no++,
                    ['size' => 8],
                    ['space' => ['after' => 0], 'alignment' => Jc::CENTER]
                );
                $table->addCell(3000, ['valign' => 'center'])->addText($userSystem->user_code, ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(4500, ['valign' => 'center'])->addText(
                    $this->sanitizeForFilename($userSystem->user_profile),
                    ['size' => 8],
                    ['space' => ['after' => 0]]
                );
                $table->addCell(4000, ['valign' => 'center'])->addText(
                    $this->sanitizeForFilename(
                        $userSystem->last_login
                            ? \Carbon\Carbon::parse($userSystem->last_login)->format('d F Y')
                            : '-'
                    ),
                    ['size' => 8],
                    ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
                );
                $table->addCell(1500, ['valign' => 'center'])->addText('', ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(1500, ['valign' => 'center'])->addText('', ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(2500, ['valign' => 'center'])->addText('', ['size' => 8, 'color' => 'A6A6A6'], ['space' => ['after' => 0]]);
            }
        }

        // Approval Table - FIX: Remove addTextBreak, use multiple addText
        $section->addTextBreak(1);

        $approvalTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);

        // Header row
        $approvalTable->addRow();
        $approvalTable->addCell(5000, ['bgColor' => 'D9E1F2', 'gridSpan' => 2])->addText('Persetujuan', ['bold' => true], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500, ['bgColor' => 'D9E1F2'])->addText('Tanda Tangan', ['bold' => true], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500, ['bgColor' => 'D9E1F2'])->addText('Tanggal', ['bold' => true], ['space' => ['after' => 0]]);

        // Disiapkan oleh
        $approvalTable->addRow();
        $approvalTable->addCell(null, ['gridSpan' => 4])->addText('Disiapkan oleh:', ['bold' => true, 'size' => 8], ['space' => ['after' => 0]]);

        // System Administrator
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText('System Administrator', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText(' ', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // Functional Modul
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText('Functional Modul ....', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // Diverifikasi oleh
        $approvalTable->addRow();
        $approvalTable->addCell(null, ['gridSpan' => 4])->addText('Diverifikasi oleh:', ['bold' => true, 'size' => 8], ['space' => ['after' => 0]]);

        // VP Operasional Sistem TI
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText('VP Operasional Sistem TI', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // VP Unit Kerja
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText($this->sanitizeForFilename($jabatanUnitKerja . ' ' . $unitKerjaName), ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // Output - Use final nomor surat for filename
        $sanitizedUnitKerja = $this->sanitizeForFilename($unitKerja);
        // $sanitizedNomorSurat = $this->sanitizeForFilename($finalNomorSurat);
        $fileName = $finalNomorSurat . '_' . $sanitizedUnitKerja . '.docx';
        $filePath = storage_path('app/public/' . $fileName);

        // Save using IOFactory
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filePath);

        // Set headers for download with properly encoded filename
        $safeFileName = rawurlencode($fileName);
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header("Content-Disposition: attachment; filename*=UTF-8''" . $safeFileName);

        // Output file and delete after send
        readfile($filePath);
        unlink($filePath);
        exit;
    }

    // Add this new method specifically for filename sanitization
    private function sanitizeForFilename($text)
    {
        if (is_null($text)) {
            return '';
        }

        // Remove control characters and normalize text
        $text = preg_replace('/[^\P{C}\n]+/u', '', (string) $text);

        // Replace problematic characters for both content and filenames
        $text = str_replace([
            '&',
            '<',
            '>',
            '"',
            "'",
            '/',
            '\\',
            '|',
            '?',
            '*',
            ':',
            ';',
            '[',
            ']',
            '{',
            '}',
            '(',
            ')',
            '=',
            '+',
            '@',
            '#',
            '$',
            '%',
            '^'
        ], [
            'dan',
            '',
            '',
            '',
            '',
            '-',
            '-',
            '-',
            '',
            '',
            '-',
            '-',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ], $text);

        // FIXED: Escape the backslash properly in the regex pattern
        $text = preg_replace('/[<>:"\/\\\|?*]/', '', $text);

        // Replace multiple hyphens/spaces with single ones
        $text = preg_replace('/[-\s]+/', ' ', $text);

        // Trim and limit length for filename safety
        $text = substr(trim($text), 0, 100);

        return $text;
    }
}
