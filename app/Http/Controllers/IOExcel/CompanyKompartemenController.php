<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\CompositeRole;

use App\Imports\CompanyKompartemenImport;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CompanyKompartemenController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.company_kompartemen');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480', // Max size of 20MB
        ]);

        // dd($request->file('excel_file')->getPathName());
        // dd($request->file('excel_file')->getRealPath());

        $filePath = $request->file('excel_file');
        // $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data using the Excel facade
            $data = Excel::toCollection(new CompanyKompartemenImport, $filePath)->first();

            // Validate and parse each row
            $errors = [];
            $parsedData = [];

            foreach ($data as $index => $row) {
                // Custom validation for each row (adjust rules as needed)
                $validator = Validator::make($row->toArray(), [
                    'company' => 'required|string',
                    'kompartemen' => 'nullable|string',
                    'departemen' => 'nullable|string',
                    'job_function' => 'required|string',
                    'composite_role' => 'required|string'
                ]);

                if ($validator->fails()) {
                    $errorDetails = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->all(),
                    ];
                    $errors[$index + 1] = $validator->errors()->all();

                    // Log the validation errors with details
                    Log::error('Validation failed for JobRole-Composite data', $errorDetails);
                } else {
                    // Find the company name based on the company code
                    $company = Company::where('company_code', $row['company'])->first();
                    $companyName = $company ? $company->name : 'N/A';

                    $kompartemen = $row['kompartemen'] ?? 'None';
                    $departemen = $row['departemen'] ?? 'None';

                    // Debug if kompartemen or departemen is 'None'
                    // if (
                    //     $kompartemen === 'None' || $departemen === 'None'
                    // ) {
                    //     dd([
                    //         'row_index' => $index + 1,
                    //         'row_data' => $row->toArray(),
                    //         'kompartemen' => $kompartemen,
                    //         'departemen' => $departemen,
                    //     ]);
                    // }


                    // Store validated data along with derived company name for preview
                    $parsedData[] = [
                        'company_code' => $row['company'],
                        'company_name' => $companyName,
                        'kompartemen' => $row['kompartemen'],
                        'departemen' => $row['departemen'],
                        'job_function' => $row['job_function'],
                        'composite_role' => $row['composite_role'],
                    ];
                }
            }

            if (!($errors) == null) {
                return redirect()->back()->with('validationErrors', $errors);
            }

            session(['parsedData' => $parsedData]);

            return view('imports.preview.company_kompartemen');
        } catch (\Exception $e) {
            Log::error('Error during Upload', [
                'file' => $request->file('excel_file')->getClientOriginalName(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error during Upload: ' . $e->getMessage());
        }
    }

    public function getPreviewData()
    {
        $data = session('parsedData');

        if (!$data) {
            return response()->json(['error' => 'No data available for preview.'], 400);
        }

        return DataTables::of(collect($data))->make(true);
    }

    public function confirmImport()
    {
        $data = session('parsedData');

        if (!$data) {
            Log::debug('Session data not found or empty in confirmImport.');
            return redirect()->route('company_kompartemen.upload')->with('error', 'No data available for import. Please upload a file first.');
        }

        try {
            $response = new StreamedResponse(function () use ($data) {
                $totalRows = count($data);
                $processed = 0;

                echo json_encode(['progress' => 0]) . "\n";
                ob_flush();
                flush();

                foreach ($data as $row) {
                    $company = Company::where('company_code', $row['company_code'])->first();

                    if (!$company) {
                        Log::error('Company not found', ['row' => $row]);
                        continue;
                    }

                    $kompartemen = null;
                    $departemen = null;

                    // Log the row data before processing to confirm it is being checked
                    // if ($row['kompartemen'] == null || $row['departemen'] == null) {
                    //     Log::debug('Processing row data:', $row);
                    // }

                    // Create or update Kompartemen and Departemen based on data
                    if (!$row['kompartemen'] == null && !$row['departemen'] == null) {
                        $kompartemen = Kompartemen::updateOrCreate(
                            ['name' => $row['kompartemen'], 'company_id' => $company->id],
                            ['company_id' => $company->id]
                        );

                        $departemen = Departemen::updateOrCreate(
                            ['name' => $row['departemen'], 'company_id' => $company->id, 'kompartemen_id' => $kompartemen->id],
                            ['company_id' => $company->id, 'kompartemen_id' => $kompartemen->id]
                        );
                    } elseif (!$row['departemen'] == null && $row['kompartemen'] == null) {
                        $departemen = Departemen::updateOrCreate(
                            ['name' => $row['departemen'], 'company_id' => $company->id],
                            ['company_id' => $company->id, 'kompartemen_id' => null]
                        );
                    } elseif (!$row['kompartemen'] == null && $row['departemen'] == null) {
                        $kompartemen = Kompartemen::updateOrCreate(
                            ['name' => $row['kompartemen'], 'company_id' => $company->id],
                            ['company_id' => $company->id]
                        );
                    }

                    // Create or update JobRole
                    $jobRole = JobRole::updateOrCreate(
                        ['nama_jabatan' => $row['job_function'], 'company_id' => $company->id],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id ?? null,
                            'departemen_id' => $departemen->id ?? null
                        ]
                    );

                    // Create or update CompositeRole
                    if (!$row['composite_role'] == null) {
                        $compositeRole = CompositeRole::updateOrCreate(
                            ['nama' => $row['composite_role'], 'company_id' => $company->id],
                            ['company_id' => $company->id]
                        );

                        $jobRole->compositeRole()->save($compositeRole);
                        // $compositeRole->jobRole()->associate($jobRole)->save();
                    }


                    $processed++;
                    echo json_encode(['progress' => round(($processed / $totalRows) * 100)]) . "\n";
                    ob_flush();
                    flush();
                }

                echo json_encode(['success' => 'Data imported successfully!']) . "\n";
                ob_flush();
                flush();
            });

            session()->forget('parsedData');
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
                'message' => 'An unexpected error occurred. Please try again or contact support.',
                'details' => [
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }
}
