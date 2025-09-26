<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Models\UserNIKUnitKerja;
use App\Models\userGeneric;
use App\Models\JobRole;
use App\Models\Periode;
use App\Models\TempUploadSession;

use App\Imports\USSMJobRolePreviewImport;
use App\Services\USSMJobRoleService;
use App\Exports\USSMJobRoleTemplateExport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // add
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class USSMJobRoleController extends Controller
{
    public function uploadForm()
    {
        $periodes = Periode::orderBy('id')->get();
        return view('imports.upload.ussm_job_role', compact('periodes'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'periode_id' => 'required|exists:ms_periode,id',
        ]);

        try {
            $import = new USSMJobRolePreviewImport();

            // Try named sheet first
            $import->onlySheets('UPLOAD_TEMPLATE');
            Excel::import($import, $request->file('excel_file'));

            // Fallback to first sheet if nothing was read
            if ($import->rows->isEmpty()) {
                $import = new USSMJobRolePreviewImport();
                $import->onlySheets(0);
                Excel::import($import, $request->file('excel_file'));
            }

            $data = $import->rows->map(fn($row) => $row->toArray())->toArray();

            $parsedData = [];
            $periodeId = $request->input('periode_id');

            $seenDefinisi = [];
            foreach ($data as $row) {
                $row['periode_id'] = $periodeId;
                $row['_row_errors'] = [];
                $row['_row_warnings'] = [];

                if (empty($row['nik'])) {
                    $row['_row_errors'][] = 'NIK wajib diisi.';
                }
                if (empty($row['job_role_id'])) {
                    $row['_row_errors'][] = 'Job Role ID kosong.';
                }

                $row['_row_issues_count'] = count($row['_row_errors']) + count($row['_row_warnings']);
                $parsedData[] = $row;
            }

            usort($parsedData, fn($a, $b) => ($b['_row_issues_count'] ?? 0) <=> ($a['_row_issues_count'] ?? 0));

            TempUploadSession::create([
                'module' => 'ussm_job_role_import',
                'data' => $parsedData,
                'periode_id' => $periodeId,
            ]);

            return redirect()->route('ussm-job-role.previewPage');
        } catch (\Exception $e) {
            Log::error('Excel Preview Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error parsing file: ' . $e->getMessage());
        }
    }

    public function previewPage()
    {
        return view('imports.preview.ussm_job_role');
    }

    public function getPreviewData()
    {
        $session = TempUploadSession::where('module', 'ussm_job_role_import')->latest()->first();
        $data = $session ? $session->data : [];

        if (!$data) {
            return response()->json(['error' => 'No preview data found'], 400);
        }

        return DataTables::of(collect($data))
            ->addColumn('nik_owner', function ($row) {
                // Try to get userGeneric->user_profile or userNIK->nama
                $userProfile = null;
                $userGeneric = userGeneric::where('user_code', $row['nik'] ?? null)->first();
                if ($userGeneric && !empty($userGeneric->user_profile)) {
                    $userProfile = $userGeneric->user_profile;
                } else {
                    $userNIK = UserNIKUnitKerja::where('nik', $row['nik'] ?? null)->first();
                    $userProfile = $userNIK ? $userNIK->nama : '-';
                }
                return $userProfile ?: '-';
            })
            ->addColumn('job_role_name', function ($row) {
                $jobRole = JobRole::where('job_role_id', $row['job_role_id'] ?? null)->first();
                return $jobRole ? $jobRole->nama : '-';
            })
            ->addColumn('unit_kerja', function ($row) {
                // 1. If nik exists in userNIK, get UserNIKUnitKerja->kompartemen->nama
                $userNIK = UserNIKUnitKerja::where('nik', $row['nik'] ?? null)->first();
                if ($userNIK && $userNIK->kompartemen && !empty($userNIK->kompartemen->nama)) {
                    return $userNIK->kompartemen->nama;
                }
                // 2. Else, search in userGeneric into userGenericUnitKerja to get kompartemen->nama
                $userGeneric = userGeneric::where('user_code', $row['nik'] ?? null)->first();
                if ($userGeneric && $userGeneric->userGenericUnitKerja && $userGeneric->userGenericUnitKerja->kompartemen && !empty($userGeneric->userGenericUnitKerja->kompartemen->nama)) {
                    return $userGeneric->userGenericUnitKerja->kompartemen->nama;
                }
                return '-';
            })
            ->addColumn('validation_message', function ($row) {
                $msg = '';
                if (!empty($row['_row_warnings'])) {
                    $msg .= "Warnings:<br>- " . implode("<br>- ", $row['_row_warnings']) . "<br>";
                }
                if (!empty($row['_row_errors'])) {
                    $msg .= "Errors:<br>- " . implode("<br>- ", $row['_row_errors']);
                }
                return $msg ?: '';
            })
            ->addColumn('status_sort', function ($row) {
                $hasError = !empty($row['_row_errors']);
                $hasWarning = !empty($row['_row_warnings']);
                // 2 = both, 1 = error only, 0 = warning only, -1 = none
                if ($hasError && $hasWarning) return 3;
                if ($hasError) return 2;
                if ($hasWarning) return 1;
                return 0;
            })
            ->addColumn('row_class', function ($row) {
                $hasError = !empty($row['_row_errors']);
                $hasWarning = !empty($row['_row_warnings']);
                if ($hasError && $hasWarning) return 'row-orange';
                if ($hasError) return 'row-red';
                if ($hasWarning) return 'row-yellow';
                return '';
            })
            ->rawColumns(['validation_message'])
            ->make(true);
    }

    public function confirmImport(Request $request)
    {
        $session = TempUploadSession::where('module', 'ussm_job_role_import')->latest()->first();
        $data = $session ? $session->data : [];

        if (!$data) {
            return redirect()->route('ussm-job-role.upload')->with('error', 'No data available to import.');
        }

        try {
            $response = new StreamedResponse(function () use ($data, $session) {
                @ini_set('output_buffering', 'off');
                @ini_set('zlib.output_compression', '0');
                @set_time_limit(0);
                @ob_implicit_flush(true);
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }

                $send = function (array $payload) {
                    echo json_encode($payload) . "\n";
                    if (ob_get_level() > 0) {
                        @ob_flush();
                    }
                    flush();
                };

                $service = new USSMJobRoleService();
                $processed = 0;
                $total = count($data);
                $lastUpdate = microtime(true);

                // Track results
                $uploadedCount = 0;
                $skipped = []; // each item as "NIK - job_role_id"

                $send(['progress' => 0]);

                foreach ($data as $row) {
                    try {
                        $result = $service->handleRow($row);

                        if (!empty($result['uploaded'])) {
                            $uploadedCount++;
                        } else {
                            $nik = $result['nik'] ?? ($row['nik'] ?? '-');
                            $jr  = $result['job_role_id'] ?? ($row['job_role_id'] ?? '-');
                            $skipped[] = "{$nik} - {$jr}";
                        }
                    } catch (\Exception $e) {
                        // Treat unexpected errors as skipped
                        \Log::error('Row import failed', ['row' => $row, 'error' => $e->getMessage()]);
                        $skipped[] = ($row['nik'] ?? '-') . ' - ' . ($row['job_role_id'] ?? '-');
                    }

                    $processed++;
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $total) {
                        $send(['progress' => (int) round($processed / max(1, $total) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                }

                $send([
                    'success'        => true,
                    'message'        => 'Data imported successfully',
                    'uploaded_count' => $uploadedCount,
                    'skipped_count'  => count($skipped),
                    'skipped_items'  => $skipped,
                    'redirect'       => route('ussm-job-role.upload')
                ]);
            });

            $session?->delete();

            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('Cache-Control', 'no-cache, no-transform');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Connection', 'keep-alive');

            return $response;
        } catch (\Exception $e) {
            \Log::error('Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'details' => [
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $companyCode = Auth::user()?->loginDetail?->company_code ?? 'A000';

        return Excel::download(new USSMJobRoleTemplateExport($companyCode), 'User_ID_Job_Role_Template.xlsx');
    }

    // private function validateName($name, &$seenSet = [])
    // {
    //     $trimmed = trim($name);
    //     if (!$trimmed) return "Definisi kosong";

    //     if (mb_strlen($trimmed) > 100) return "Definisi terlalu panjang";

    //     $words = preg_split('/\s+/', $trimmed);
    //     if (count($words) < 2) return "Definisi harus terdiri dari minimal 2 kata";
    //     if (count($words) > 4) return "Definisi terlalu panjang (maksimal 4 kata)";

    //     $dupKey = mb_strtolower($trimmed);
    //     if (in_array($dupKey, $seenSet)) return "Definisi duplikat: $trimmed";
    //     $seenSet[] = $dupKey;

    //     $blacklist = ["function", "null", "undefined"];
    //     if (in_array($dupKey, $blacklist)) return "Definisi \"$trimmed\" tidak valid";

    //     return null; // valid
    // }
}
