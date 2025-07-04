<?php

namespace App\Http\Controllers\IOExcel;

use \App\Models\UserGenericUnitKerja;

use App\Http\Controllers\Controller;
use App\Imports\UserGenericPreviewImport;
use App\Models\Periode;

use App\Models\TempUploadSession;
use App\Models\UserGeneric;

use App\Services\UserGenericService;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class UserGenericImportController extends Controller
{
    public function uploadForm()
    {
        $periodes = Periode::orderBy('id')->get();
        return view('imports.upload.user_generic', compact('periodes'));
    }

    public function preview(Request $request)
    {

        // Get all existing PIC names from the database (UserGeneric model)
        // $seenPicNames = UserGeneric::query()
        //     ->whereNotNull('pic')
        //     ->pluck('pic')
        //     ->map(fn($name) => mb_strtolower(trim($name)))
        //     ->unique()
        //     ->values()
        //     ->toArray();

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'periode_id' => 'required|exists:ms_periode,id',
        ]);

        try {
            $import = new UserGenericPreviewImport();
            Excel::import($import, $request->file('excel_file'));
            $data = $import->rows->map(fn($row) => $row->toArray())->toArray();

            $parsedData = [];
            $periodeId = $request->input('periode_id');

            // First pass: add error/warning info except duplicate pic
            foreach ($data as $index => $row) {
                $row['periode_id'] = $periodeId;
                $row['_row_errors'] = [];
                $row['_row_warnings'] = [];

                // Warning if no mapping between User Cost Center and Unit Kerja
                if (!empty($row['user_code'])) {
                    $unitKerja = UserGenericUnitKerja::where('user_cc', $row['user_code'])->first();
                    if (!$unitKerja) {
                        $row['_row_warnings'][] = 'Belum ada Mapping User Cost Center dengan Unit Kerja';
                    }
                }

                // Validate user_code
                if (empty($row['user_code'])) {
                    $row['_row_errors'][] = 'User Code tidak boleh kosong.';
                }

                // Validate user_type
                if (empty($row['user_type'])) {
                    $row['_row_errors'][] = 'User Type tidak boleh kosong.';
                }

                // Validate cost_code
                // if (empty($row['cost_code'])) {     
                //     $row['_row_errors'][] = 'Cost Code is required.';
                // }   

                // Error: license_type is empty
                if (empty($row['license_type'])) {
                    $row['_row_errors'][] = 'License Type tidak boleh kosong.';
                }

                // Validate group
                if (empty($row['group'])) {
                    $row['_row_errors'][] = 'Group Company tidak boleh kosong.';
                }

                // Validate pic
                // if (!empty($row['pic'])) {
                //     $picWarning = $this->validateName($row['pic'], $seenPicNames);
                //     if ($picWarning) {
                //         $row['_row_warnings'][] = $picWarning;
                //     }
                // } else {
                //     $row['_row_warnings'][] = 'Tidak ada PIC yang terdaftar.';
                // }

                // Validate unit_kerja
                // if (empty($row['unit_kerja'])) {
                //     $row['_row_warnings'][] = 'Unit Kerja kosong.';
                // }

                // // Validate job_role_name
                // if (empty($row['job_role_name'])) {
                //     $row['_row_warnings'][] = 'Job Role Name kosong.';
                // }

                // // Validate kompartemen_id
                // if (empty($row['kompartemen_id'])) {
                //     $row['_row_errors'][] = 'Kompartemen ID is required.';
                // } else {
                //     // Check if kompartemen_id exists in the database
                //     $kompartemen = \App\Models\Kompartemen::find($row['kompartemen_id']);
                //     if (!$kompartemen) {
                //         $row['_row_errors'][] = 'Kompartemen ID does not exist.';
                //     } else {
                //         $row['kompartemen_name'] = $kompartemen->name; // Set name if exists
                //     }
                // }

                // // Validate departemen_id
                // if (empty($row['departemen_id'])) {
                //     $row['_row_errors'][] = 'Departemen ID is required.';
                // } else {
                //     // Check if departemen_id exists in the database
                //     $departemen = \App\Models\Departemen::find($row['departemen_id']);
                //     if (!$departemen) {
                //         $row['_row_errors'][] = 'Departemen ID does not exist.';
                //     } else {
                //         $row['departemen_name'] = $departemen->name; // Set name if exists
                //     }
                // }

                // // Validate job_role_id
                // if (empty($row['job_role_id'])) {
                //     $row['_row_errors'][] = 'Job Role ID is required.';
                // } else {
                //     // Check if job_role_id exists in the database
                //     $jobRole = \App\Models\JobRole::find($row['job_role_id']);
                //     if (!$jobRole) {
                //         $row['_row_errors'][] = 'Job Role ID does not exist.';
                //     } else {
                //         $row['job_role_name'] = $jobRole->name; // Set name if exists
                //     }
                // }

                // _row_issues_count will be updated after duplicate check

                foreach (['last_login', 'valid_from', 'valid_to'] as $dateField) {
                    if (isset($row[$dateField]) && !empty($row[$dateField])) {
                        $reformatted = $this->reformatDate($row[$dateField]);
                        if ($reformatted !== null) {
                            $row[$dateField] = $reformatted;
                        }
                    }
                }

                $parsedData[] = $row;

                foreach ($parsedData as &$row) {
                    $row['_row_issues_count'] = count($row['_row_errors']) + count($row['_row_warnings']);
                }
                unset($row);
            }

            // Sort: error rows first, then warning, then normal
            usort($parsedData, function ($a, $b) {
                return ($b['_row_issues_count'] ?? 0) <=> ($a['_row_issues_count'] ?? 0);
            });

            TempUploadSession::create([
                'module' => 'user_generic_import',
                'data' => $parsedData,
                'periode_id' => $periodeId,
            ]);

            return redirect()->route('user-generic.previewPage');
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
        // Just render the preview page, JS will fetch data via getPreviewData
        return view('imports.preview.user_generic');
    }

    public function getPreviewData()
    {
        $session = TempUploadSession::where('module', 'user_generic_import')->latest()->first();
        $data = $session ? $session->data : [];
        foreach ($data as &$row) {
            // Get unit kerja info if available
            if (!empty($row['user_code'])) {
                // Find the UserGenericUnitKerja record by user_cc = user_code
                $unitKerja = UserGenericUnitKerja::where('user_cc', $row['user_code'])->first();
                if ($unitKerja) {
                    // Get kompartemen_id and departemen_id from the found record
                    $row['kompartemen_id'] = $unitKerja->kompartemen_id;
                    $row['departemen_id'] = $unitKerja->departemen_id;

                    // Get kompartemen name via relationship if available
                    $row['kompartemen_name'] = optional($unitKerja->kompartemen)->nama;

                    // Get departemen name via relationship if available
                    $row['departemen_name'] = optional($unitKerja->departemen)->nama;
                } else {
                    $row['kompartemen_id'] = null;
                    $row['kompartemen_name'] = null;
                    $row['departemen_id'] = null;
                    $row['departemen_name'] = null;
                }
            } else {
                $row['kompartemen_id'] = null;
                $row['kompartemen_name'] = null;
                $row['departemen_id'] = null;
                $row['departemen_name'] = null;
            }
        }
        unset($row);
        if (!$data) {
            return response()->json(['error' => 'No preview data found'], 400);
        }
        return DataTables::of(collect($data))->make(true);
    }

    public function confirmImport(Request $request)
    {
        $session = TempUploadSession::where('module', 'user_generic_import')->latest()->first();
        $data = $session ? $session->data : [];

        if (!$data) {
            return redirect()->route('user-generic.upload')->with('error', 'No data available to import.');
        }

        try {
            $response = new StreamedResponse(function () use ($data, $session) {
                $processed = 0;
                $total = count($data);
                $lastUpdate = microtime(true);

                echo json_encode(['progress' => 0]) . "\n";
                ob_flush();
                flush();

                foreach ($data as $row) {
                    try {
                        $service = new UserGenericService();
                        $service->handleRow($row);
                    } catch (\Exception $e) {
                        Log::error('Row import failed', ['row' => $row, 'error' => $e->getMessage()]);
                    }

                    $processed++;
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $total) {
                        echo json_encode(['progress' => round(($processed / $total) * 100)]) . "\n";
                        ob_flush();
                        flush();
                        $lastUpdate = microtime(true);
                    }
                }

                echo json_encode(['success' => 'Data imported successfully']) . "\n";
                ob_flush();
                flush();
            });

            // Optionally delete the session after import
            $session?->delete();
            $response->headers->set('Content-Type', 'text/event-stream');
            return $response;
        } catch (\Exception $e) {
            Log::error('Error occurred', [
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

    // private function validateName($name, &$seenSet = [])
    // {
    //     $trimmed = trim($name);
    //     if (!$trimmed) return "Nama PIC kosong";

    //     if (mb_strlen($trimmed) > 100) return "Nama terlalu panjang";

    //     $words = preg_split('/\s+/', $trimmed);
    //     if (count($words) < 2) return "Nama lengkap (depan + belakang) diperlukan";
    //     if (count($words) > 4) return "Kemungkinan bukan nama PIC";

    //     // if (preg_match('/[^A-Za-zÀ-ž\'’-]/', $trimmed)) return "Invalid characters in name";

    //     $dupKey = mb_strtolower($trimmed);
    //     if (in_array($dupKey, $seenSet)) return "Nama duplikat: $trimmed";
    //     $seenSet[] = $dupKey;

    //     $blacklist = ["function", "null", "undefined"];
    //     if (in_array($dupKey, $blacklist)) return "Nama \"$trimmed\" tidak valid";

    //     return null; // valid
    // }

    /**
     * Reformat date from dd.mm.yyyy to Y-m-d (PostgreSQL date format).
     *
     * @param string|null $date
     * @return string|null
     */
    private function reformatDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Match dd.mm.yyyy or d.m.yyyy
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', trim($date), $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }

        return null; // Invalid format
    }
}
