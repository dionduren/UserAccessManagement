<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Exports\SingleRoleTcodeTemplateExport;
use App\Models\SingleRole;
use App\Models\Tcode;
use App\Imports\TcodeSingleRoleImport;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // added
use Illuminate\Support\Facades\DB;   // added
use Illuminate\Database\QueryException; // added

use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SingleRoleTcodeController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.tcode_single_role');
    }

    public function downloadTemplate()
    {
        return Excel::download(new SingleRoleTcodeTemplateExport(), 'single_role_tcode_template.xlsx');
    }

    // Preview the data from the uploaded Excel file
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $filePath = $request->file('excel_file');

        try {
            $data = Excel::toCollection(new TcodeSingleRoleImport, $filePath)->first();

            $errors = [];
            $parsedData = [];
            foreach ($data as $index => $row) {
                $tcode = trim($row['tcode'] ?? '');
                $singleRole = trim($row['single_role'] ?? '');

                if ($tcode == null || $singleRole == null) {
                    Log::info('Skipping row due to missing required fields', [
                        'row_index' => $index + 1,
                        'tcode' => $row['tcode'] ?? 'null',
                        'single_role' => $row['single_role'] ?? 'null'
                    ]);
                    continue; // Skip to the next row
                }

                // Custom validation for each row (adjust rules as needed)
                $validator = Validator::make($row->toArray(), [
                    'single_role' => 'required',
                    'single_role_description' => 'nullable',
                    'tcode' => 'required',
                    'tcode_description' => 'nullable',
                    'sap_module' => 'nullable|string'
                ]);

                if ($validator->fails()) {
                    $errorDetails = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->all(),
                    ];
                    $errors[$index + 1] = $validator->errors()->all();

                    // Log the validation errors with details
                    Log::error('Validation failed for Tcode-Single data', $errorDetails);
                } else {
                    $parsedData[] = [
                        'single_role' => $row['single_role'],
                        'single_role_description' => $row['single_role_description'] ?? 'None',
                        'tcode' => $row['tcode'],
                        'tcode_description' => $row['tcode_description'] ?? 'None',
                        'sap_module' => $row['sap_module'] ?? 'None'
                    ];
                }
            }


            if (!empty($errors)) {
                return redirect()->back()->with('validationErrors', $errors);
            }
            session(['parsedData' => $parsedData]);

            return view('imports.preview.tcode_single_role');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getPreviewData(Request $request)
    {
        $data = session('parsedData'); // Retrieve preview data from the session

        if (!$data) {
            return response()->json(['error' => 'No data available for preview.'], 400);
        }

        // Transform the session data to be compatible with DataTables
        $formattedData = collect($data)->map(function ($row, $key) {
            return [
                'id' => $key + 1, // Assign a unique ID
                'single_role' => $row['single_role'] ?? null,
                'single_role_description' => $row['single_role_description'] ?? null,
                'tcode' => $row['tcode'] ?? null,
                'tcode_description' => $row['tcode_description'] ?? null
            ];
        });

        // Return the data to DataTables
        return DataTables::of($formattedData)->make(true);
    }


    // Confirm and process the import
    public function confirmImport(Request $request)
    {
        @set_time_limit(0);

        $data = session('parsedData');

        if (!$data) {
            return response()->json(['error' => 'No data available for import. Please upload a file first.'], 400);
        }

        try {
            $dataArray = $data instanceof Collection ? $data->toArray() : $data;
            $totalRows = count($dataArray);
            $processedRows = 0;

            $response = new StreamedResponse(function () use ($dataArray, $totalRows, &$processedRows) {
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

                $lastUpdate = microtime(true);
                $send(['progress' => 0]);

                $warnings = [];
                $seenPairs = []; // de-dupe within same upload
                $actor = Auth::user()?->name ?? 'system';

                // Align PG sequence before inserting into pivot (prevents PK collisions)
                $this->ensurePgSequence('pt_single_role_tcode', 'id');

                foreach ($dataArray as $idx => $row) {
                    $singleName = trim((string)($row['single_role'] ?? ''));
                    $singleDesc = isset($row['single_role_description']) ? (string)$row['single_role_description'] : null;
                    $tcodeCode  = trim((string)($row['tcode'] ?? ''));
                    $tcodeDesc  = isset($row['tcode_description']) ? (string)$row['tcode_description'] : null;

                    if ($singleName === '' || $tcodeCode === '') {
                        Log::warning('Skipping invalid row.', ['row_index' => $idx + 1, 'row' => $row]);
                        continue;
                    }

                    // Upsert SingleRole without overriding existing source
                    $singleRole = SingleRole::firstOrNew(['nama' => $singleName]);
                    $singleRole->deskripsi = $singleDesc;
                    if (!$singleRole->exists) {
                        $singleRole->source = 'upload';
                    }
                    $singleRole->save();

                    // Upsert Tcode without overriding existing source
                    $tCode = Tcode::firstOrNew(['code' => $tcodeCode]);
                    $tCode->deskripsi = $tcodeDesc;
                    if (!$tCode->exists) {
                        $tCode->source = 'upload';
                    }
                    $tCode->save();

                    // De-dupe within the same file
                    $pairKey = strtoupper($singleRole->nama) . '|' . strtoupper($tCode->code);
                    if (isset($seenPairs[$pairKey])) {
                        $warnings[] = "Row " . ($idx + 1) . ": Duplicate mapping in file skipped.";
                    } else {
                        $seenPairs[$pairKey] = true;

                        // Skip if mapping already exists (from import/sync/upload)
                        $exists = DB::table('pt_single_role_tcode')
                            ->where('single_role_id', $singleRole->id)
                            ->where('tcode_id', $tCode->id)
                            ->exists();

                        if ($exists) {
                            $warnings[] = "Row " . ($idx + 1) . ": Mapping already exists (skipped).";
                        } else {
                            // Safe insert to pivot with audit fields; ignore residual conflicts
                            try {
                                DB::table('pt_single_role_tcode')->insertOrIgnore([
                                    'single_role_id' => $singleRole->id,
                                    'tcode_id'       => $tCode->id,
                                    'source'         => 'upload',
                                    'created_at'     => now(),
                                    'updated_at'     => now(),
                                    'created_by'     => $actor,
                                    'updated_by'     => $actor,
                                ]);
                            } catch (QueryException $e) {
                                if ((string)$e->getCode() === '23505') {
                                    // Sequence drift fallback: re-align and retry once
                                    $this->ensurePgSequence('pt_single_role_tcode', 'id');
                                    DB::table('pt_single_role_tcode')->insertOrIgnore([
                                        'single_role_id' => $singleRole->id,
                                        'tcode_id'       => $tCode->id,
                                        'source'         => 'upload',
                                        'created_at'     => now(),
                                        'updated_at'     => now(),
                                        'created_by'     => $actor,
                                        'updated_by'     => $actor,
                                    ]);
                                    $warnings[] = "Row " . ($idx + 1) . ": Duplicate pivot detected, auto-recovered.";
                                    Log::warning('Duplicate pivot ignored on upload (SingleRole-Tcode)', [
                                        'single_role_id' => $singleRole->id,
                                        'tcode_id'       => $tCode->id,
                                        'error'          => $e->getMessage(),
                                    ]);
                                } else {
                                    throw $e;
                                }
                            }
                        }
                    }

                    $processedRows++;
                    if (microtime(true) - $lastUpdate >= 1 || $processedRows === $totalRows) {
                        $send(['progress' => (int) round($processedRows / max(1, $totalRows) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                }

                $send(['success' => 'Data imported successfully!', 'warnings' => $warnings]);
            });

            session()->forget('parsedData');

            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('Cache-Control', 'no-cache, no-transform');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Connection', 'keep-alive');

            return $response;
        } catch (\Exception $e) {
            Log::error('Error during import', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Error during import: ' . $e->getMessage()], 500);
        }
    }

    // Align Postgres sequence to avoid PK collisions on pivot inserts
    private function ensurePgSequence(string $table, string $pk = 'id'): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence(?, ?),
                COALESCE((SELECT MAX($pk) FROM $table), 0) + 1,
                false
            )
        ", [$table, $pk]);
    }
}
