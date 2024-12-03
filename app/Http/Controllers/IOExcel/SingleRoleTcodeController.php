<?php

namespace App\Http\Controllers\IOExcel;

use App\Models\Company;
use App\Models\SingleRole;

use Illuminate\Http\Request;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

use App\Imports\TcodeSingleRoleImport;
use App\Models\Tcode;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SingleRoleTcodeController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.tcode_single_role');
    }

    // Preview the data from the uploaded Excel file
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data into a collection
            $data = Excel::toCollection(new TcodeSingleRoleImport, $filePath)->first();

            // Validate and parse each row
            $errors = [];
            $parsedData = [];
            foreach ($data as $index => $row) {
                // Normalize and check for empty fields
                $tcode = trim($row['tcode'] ?? '');
                $singleRole = trim($row['single_role'] ?? '');

                // Skip the row if 'tcode' or 'single_role' is empty
                if ($tcode == null || $singleRole == null) {
                    Log::info('Skipping row due to missing required fields', [
                        'row_index' => $index + 1,
                        'company_code' => $row['company'],
                        'kompartemen' => $row['kompartemen'] ?? 'null',
                        'departemen' => $row['departemen'] ?? 'null',
                        'tcode' => $row['tcode'] ?? 'null',
                        'single_role' => $row['single_role'] ?? 'null'
                    ]);
                    continue; // Skip to the next row
                }

                // Custom validation for each row (adjust rules as needed)
                $validator = Validator::make($row->toArray(), [
                    'company' => 'required|string',
                    'single_role' => 'required',
                    'single_role_desc' => 'nullable',
                    'tcode' => 'required',
                    'tcode_desc' => 'nullable',
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
                    // Find the company name based on the company code
                    $company = Company::where('company_code', $row['company'])->first();
                    $companyName = $company ? $company->name : 'N/A';

                    // Store validated data along with derived company name for preview
                    $parsedData[] = [
                        'company_code' => $row['company'],
                        'company_name' => $companyName,
                        'single_role' => $row['single_role'],
                        'single_role_desc' => $row['single_role_desc'] ?? 'None',
                        'tcode' => $row['tcode'],
                        'tcode_desc' => $row['tcode_desc'] ?? 'None',
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
                'company_code' => $row['company_code'] ?? null,
                'company_name' => $row['company_name'] ?? null,
                'single_role' => $row['single_role'] ?? null,
                'single_role_desc' => $row['single_role_desc'] ?? null,
                'tcode' => $row['tcode'] ?? null,
                'tcode_desc' => $row['tcode_desc'] ?? null,
                'sap_module' => $row['sap_module'] ?? null
            ];
        });

        // Return the data to DataTables
        return DataTables::of($formattedData)->make(true);
    }


    // Confirm and process the import
    public function confirmImport(Request $request)
    {
        set_time_limit(0);

        $data = session('parsedData');

        if (!$data) {
            return response()->json(['error' => 'No data available for import. Please upload a file first.'], 400);
        }

        try {
            // Convert the collection to an array if necessary
            $dataArray = $data instanceof Collection ? $data->toArray() : $data;
            $totalRows = count($dataArray);
            $processedRows = 0;

            // Set headers for streaming response
            $response = new StreamedResponse(function () use ($dataArray, $totalRows, &$processedRows) {
                $lastUpdate = microtime(true); // Track last time progress was sent

                echo json_encode(['progress' => 0]) . "\n";
                ob_flush();
                flush();

                foreach ($dataArray as $index => $row) {
                    // Skip invalid rows
                    if ($row['single_role'] == null || $row['tcode'] == null) {
                        Log::warning('Skipping invalid row.', ['row' => $row]);
                        continue;
                    }

                    // Step 1: Find the Company by company_code
                    $company = Company::where('company_code', $row['company_code'])->first();

                    if (!$company) {
                        Log::warning('Company not found for row', ['row' => $row]);
                        continue;
                    }

                    // Step 2: Create or Update SingleRole
                    $singleRole = SingleRole::updateOrCreate(
                        ['nama' => $row['single_role'], 'company_id' => $company->id],
                        ['deskripsi' => $row['single_role_desc']]
                    );

                    // Step 3: Create or Update TCode
                    $tCode = Tcode::updateOrCreate(
                        ['code' => $row['tcode']],
                        [
                            'deskripsi' => $row['tcode_desc'],
                            'sap_module' => $row['sap_module'],
                        ]
                    );

                    // Step 4: Link SingleRole to tCode
                    $singleRole->tcodes()->save($tCode);

                    // Update progress
                    $processedRows++;

                    // Check if 3 seconds have passed since the last update
                    if (microtime(true) - $lastUpdate >= 2 || $processedRows === $totalRows) {
                        $progress = round(($processedRows / $totalRows) * 100);
                        echo json_encode(['progress' => $progress]) . "\n";
                        ob_flush();
                        flush();

                        $lastUpdate = microtime(true); // Reset the timer
                    }
                }

                echo json_encode(['success' => 'Data imported successfully!']) . "\n";
                ob_flush();
                flush();
            });

            // Clear session data after processing
            session()->forget('parsedData');

            // return response()->json(['success' => 'Data imported successfully!']);
            // Set streaming headers explicitly
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('Cache-Control', 'no-cache');
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
}
