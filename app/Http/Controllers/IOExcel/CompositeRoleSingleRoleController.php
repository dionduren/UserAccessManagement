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
                    $companyName = $company ? $company->nama : 'N/A';

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

                foreach ($dataArray as $row) {
                    if (!isset($row['company_code'], $row['single_role'], $row['composite_role'])) {
                        Log::warning('Skipping invalid row.', ['row' => $row]);
                        continue;
                    }

                    $company = Company::where('company_code', $row['company_code'])->first();
                    if (!$company) {
                        Log::warning('Company not found for row', ['row' => $row]);
                        continue;
                    }

                    $compositeRole = CompositeRole::updateOrCreate(
                        ['nama' => $row['composite_role'], 'company_id' => $company->company_code]
                    );

                    $singleRole = SingleRole::updateOrCreate(
                        ['nama' => $row['single_role'], 'company_id' => $company->company_code],
                        ['deskripsi' => $row['single_role_desc']]
                    );

                    if (!$compositeRole->singleRoles()->where('single_role_id', $singleRole->id)->exists()) {
                        $compositeRole->singleRoles()->attach($singleRole->id);
                    }

                    $processedRows++;
                    if (microtime(true) - $lastUpdate >= 1 || $processedRows === $totalRows) {
                        $send(['progress' => (int) round($processedRows / max(1, $totalRows) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                }

                $send(['success' => 'Data imported successfully!']);
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
}
