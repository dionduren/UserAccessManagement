<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\SingleRole;
use App\Imports\CompositeRoleSingleRoleImport;

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
                    'company' => 'required|string',
                    'composite_role' => 'required|string',
                    'single_role' => 'nullable|string',
                    'single_role_desc' => 'nullable|string'
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
                    $company = Company::where('company_code', $row['company'])->first();
                    $companyName = $company ? $company->name : 'N/A';

                    // Store validated data along with derived company name for preview
                    $parsedData[] = [
                        'company_code' => $row['company'],
                        'company_name' => $companyName,
                        'composite_role' => $row['composite_role'],
                        'single_role' => $row['single_role'],
                        'single_role_desc' => $row['description'] ?? 'None'
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
                'single_role' => $row['single_role'] ?? null,
                'single_role_desc' => $row['single_role_desc'] ?? null
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
                    if (!isset($row['company_code'], $row['single_role'], $row['composite_role'])) {
                        Log::warning('Skipping invalid row.', ['row' => $row]);
                        continue;
                    }

                    // Step 1: Find the Company by company_code
                    $company = Company::where('company_code', $row['company_code'])->first();

                    if (!$company) {
                        Log::warning('Company not found for row', ['row' => $row]);
                        continue;
                    }

                    // Step 2: Create or Update CompositeRole
                    $compositeRole = CompositeRole::updateOrCreate(
                        ['nama' => $row['composite_role'], 'company_id' => $company->id]
                    );

                    // Step 3: Create or Update SingleRole
                    $singleRole = SingleRole::updateOrCreate(
                        ['nama' => $row['single_role'], 'company_id' => $company->id],
                        ['deskripsi' => $row['single_role_desc']]
                    );

                    // Step 4: Link SingleRole to CompositeRole
                    $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);

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
