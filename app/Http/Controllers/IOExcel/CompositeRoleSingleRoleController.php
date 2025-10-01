<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\SingleRole;
use App\Imports\CompositeRoleSingleRoleImport;
use App\Exports\CompositeSingleRoleTemplateExport;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CompositeRoleSingleRoleController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.composite_role_single_role');
    }

    // Preview the data from the uploaded Excel file
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $filePath = $request->file('excel_file');
        // $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data into a collection
            $data = Excel::toCollection(new CompositeRoleSingleRoleImport, $filePath)->first();

            // Validate and parse each row
            $errors = [];
            $parsedData = [];
            foreach ($data as $index => $row) {
                // Custom validation for each row (adjust rules as needed)
                $validator = Validator::make($row->toArray(), [
                    'company_code' => 'required|string',
                    'composite_role' => 'required|string',
                    'composite_role_description' => 'nullable|string',
                    'single_role' => 'nullable|string',
                    'single_role_description' => 'nullable|string'
                ]);

                if ($validator->fails()) {
                    $errorDetails = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->all(),
                    ];
                    $errors[$index + 1] = $validator->errors()->all();

                    // Log the validation errors with details
                    Log::error('Validation failed for Composite-Single data', $errorDetails);
                } else {
                    // Find the company name based on the company code
                    $company = Company::where('company_code', $row['company_code'])->first();
                    $companyName = $company ? $company->nama : 'N/A';

                    // Store validated data along with derived company name for preview
                    $parsedData[] = [
                        'company_code' => $row['company_code'],
                        'company_name' => $companyName,
                        'composite_role' => $row['composite_role'],
                        'composite_role_description' => $row['composite_role_description'] ?? 'None',
                        'single_role' => $row['single_role'],
                        'single_role_description' => $row['single_role_description'] ?? 'None'
                    ];
                }
            }

            if (!empty($errors)) {
                return redirect()->back()->with('validationErrors', $errors);
            }
            session(['parsedData' => $parsedData]);

            return view('imports.preview.composite_role_single_role');
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
                'company_code' => $row['company_code'] ?? null,
                'company_name' => $row['company_name'] ?? null,
                'composite_role' => $row['composite_role'] ?? null,
                'composite_role_description' => $row['composite_role_description'] ?? null,
                'single_role' => $row['single_role'] ?? null,
                'single_role_description' => $row['single_role_description'] ?? null
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
            $warnings = [];

            $response = new StreamedResponse(function () use ($dataArray, $totalRows, &$processedRows, &$warnings) {
                @ini_set('output_buffering', 'off');
                @ini_set('zlib.output_compression', '0');
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

                foreach ($dataArray as $index => $row) {
                    // if (!isset($row['company_code'], $row['single_role'], $row['composite_role'])) {
                    //     Log::warning('Skipping invalid row.', ['row' => $row]);
                    //     continue;
                    // }

                    // $company = Company::where('company_code', $row['company_code'])->first();
                    // if (!$company) {
                    //     Log::warning('Company not found for row', ['row' => $row]);
                    //     continue;
                    // }

                    $companyCodeRaw = $row['company_code'] ?? null;
                    $compositeRaw   = $row['composite_role'] ?? null;
                    $singleRaw      = $row['single_role'] ?? null;

                    $companyCode = $companyCodeRaw === null ? '' : trim((string) $companyCodeRaw);
                    $compositeName = $compositeRaw === null ? '' : trim((string) $compositeRaw);
                    $singleName = $singleRaw === null ? '' : trim((string) $singleRaw);

                    $compDescRaw   = $row['composite_role_description'] ?? null;
                    $singleDescRaw = $row['single_role_description'] ?? null;

                    $compDesc   = $compDescRaw === null ? null : trim((string) $compDescRaw);
                    $compDesc   = $compDesc === '' ? null : $compDesc;
                    $singleDesc = $singleDescRaw === null ? null : trim((string) $singleDescRaw);
                    $singleDesc = $singleDesc === '' ? null : $singleDesc;

                    // CASE 1: no composite + no company => update/create single-role description only
                    if ($compositeName === '' && $companyCode === '') {
                        if ($singleName === '') {
                            Log::warning('Skipping row; only single role description provided without name.', ['row' => $row]);
                            $warnings[] = "Row " . ($index + 1) . ": Single role name missing while updating description.";
                            continue;
                        }

                        $singleRole = SingleRole::firstOrNew(['nama' => $singleName]);
                        $singleRole->deskripsi = $singleDesc;
                        if (! $singleRole->exists) {
                            $singleRole->source = 'upload';
                        }
                        $singleRole->save();

                        $processedRows++;
                        if (microtime(true) - $lastUpdate >= 1 || $processedRows === $totalRows) {
                            $send(['progress' => (int) round($processedRows / max(1, $totalRows) * 100)]);
                            $lastUpdate = microtime(true);
                        }
                        continue;
                    }

                    // CASE 2: composite present but company missing => skip (nothing meaningful to update)
                    if ($compositeName !== '' && $companyCode === '') {
                        Log::warning('Skipping row; composite role has no company code.', ['row' => $row]);
                        $warnings[] = "Row " . ($index + 1) . ": Company code is required for composite role '{$compositeName}'.";
                        continue;
                    }

                    if ($compositeName === '') {
                        Log::warning('Skipping row; composite role name missing.', ['row' => $row]);
                        $warnings[] = "Row " . ($index + 1) . ": Composite role name is blank.";
                        continue;
                    }

                    $company = Company::where('company_code', $companyCode)->first();
                    if (! $company) {
                        Log::warning('Company not found for row', ['row' => $row]);
                        $warnings[] = "Row " . ($index + 1) . ": Company code '{$companyCode}' not found.";
                        continue;
                    }

                    $compositeRole = CompositeRole::firstOrNew(['nama' => $compositeName]);

                    $compDirty = false;
                    if ($compositeRole->company_id !== $companyCode) {
                        $compositeRole->company_id = $companyCode;
                        $compDirty = true;
                    }
                    if ($compositeRole->deskripsi !== $compDesc) {
                        $compositeRole->deskripsi = $compDesc;
                        $compDirty = true;
                    }
                    if (! $compositeRole->exists) {
                        $compositeRole->source = 'upload';
                        $compDirty = true;
                    }
                    if ($compDirty) {
                        $compositeRole->save();
                    }

                    // CASE 3: single role fields empty => composite-only update done above
                    if ($singleName === '' && $singleDesc === null) {
                        $processedRows++;
                        if (microtime(true) - $lastUpdate >= 1 || $processedRows === $totalRows) {
                            $send(['progress' => (int) round($processedRows / max(1, $totalRows) * 100)]);
                            $lastUpdate = microtime(true);
                        }
                        continue;
                    }

                    if ($singleName !== '') {
                        $singleRole = SingleRole::firstOrNew(['nama' => $singleName]);

                        if ($singleRole->deskripsi !== $singleDesc) {
                            $singleRole->deskripsi = $singleDesc;
                            if (! $singleRole->exists) {
                                $singleRole->source = 'upload';
                            }
                            $singleRole->save();
                        } elseif (! $singleRole->exists) {
                            $singleRole->source = 'upload';
                            $singleRole->save();
                        }

                        $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);
                    } else {
                        Log::info('Composite updated without single role attachment.', ['composite' => $compositeName]);
                        $warnings[] = "Row " . ($index + 1) . ": Composite '{$compositeName}' updated without single role.";
                    }

                    $processedRows++;
                    if (microtime(true) - $lastUpdate >= 1 || $processedRows === $totalRows) {
                        $send(['progress' => (int) round($processedRows / max(1, $totalRows) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                }

                $send([
                    'success'  => 'Data imported successfully!',
                    'warnings' => $warnings,
                ]);
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

    public function downloadTemplate()
    {
        return Excel::download(new CompositeSingleRoleTemplateExport(), 'composite_single_role_template.xlsx');
    }
}
