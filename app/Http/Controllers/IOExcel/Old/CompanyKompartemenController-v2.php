<?php

namespace App\Http\Controllers\IOExcel;

use App\Models\Company;

use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;
use App\Models\CompositeRole;

use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

use Yajra\DataTables\Facades\DataTables;

use App\Imports\CompanyKompartemenImport;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $filePath = $request->file('excel_file');

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
                    'kompartemen_id' => 'nullable',
                    'kompartemen' => 'nullable|string',
                    'departemen_id' => 'nullable',
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
                    $companyName = $company ? $company->nama : 'N/A';

                    $kompartemen_id = $row['kompartemen_id'] ?? 'None';
                    $kompartemen = $row['kompartemen'] ?? 'None';
                    $departemen_id = $row['departemen_id'] ?? 'None';
                    $departemen = $row['departemen'] ?? 'None';

                    // Store validated data along with derived company name for preview
                    $parsedData[] = [
                        'company_code' => $row['company'],
                        'company_name' => $companyName,
                        'kompartemen_id' => $kompartemen_id,
                        'kompartemen' => $kompartemen,
                        'departemen_id' => $departemen_id,
                        'departemen' => $departemen,
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
                $lastUpdate = microtime(true); // Track last time progress was sent
                $totalRows = count($data);
                $processed = 0;

                echo json_encode(['progress' => 0]) . "\n";

                // Start output buffering
                if (!ob_get_level()) {
                    ob_start();
                }

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

                    // Create or update Kompartemen and Departemen based on data
                    // If Kompartemen & Departemen not null
                    if (!empty($row['kompartemen']) && !empty($row['departemen'])) {
                        $existing = Kompartemen::find($row['kompartemen_id']);
                        if ($existing) {
                            $existing->update([
                                'nama' => $row['kompartemen'],
                                'company_id' => $company->company_code,
                                'updated_by' => Auth::user()->name,
                            ]);
                            $kompartemen = $existing;
                        } else {
                            $kompartemen = Kompartemen::create([
                                'kompartemen_id' => $row['kompartemen_id'],
                                'nama' => $row['kompartemen'],
                                'company_id' => $company->company_code,
                                'created_by' => Auth::user()->name,
                                'updated_by' => Auth::user()->name,
                            ]);
                        }

                        $existing = Departemen::find($row['departemen_id']);
                        if ($existing) {
                            $existing->update([
                                'nama' => $row['departemen'],
                                'company_id' => $company->company_code,
                                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                                'updated_by' => Auth::user()->name,
                            ]);
                            $departemen = $existing;
                        } else {
                            $departemen = Departemen::create([
                                'departemen_id' => $row['departemen_id'],
                                'nama' => $row['departemen'],
                                'company_id' => $company->company_code,
                                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                                'created_by' => Auth::user()->name,
                                'updated_by' => Auth::user()->name,
                            ]);
                        }
                    }
                    // If Kompartemen Row is Null 
                    elseif (!empty($row['departemen']) && empty($row['kompartemen'])) {
                        $existing = Departemen::find($row['departemen_id']);
                        if ($existing) {
                            $existing->update([
                                'nama' => $row['departemen'],
                                'company_id' => $company->company_code,
                                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                                'updated_by' => Auth::user()->name,
                            ]);
                            $departemen = $existing;
                        } else {
                            $departemen = Departemen::create([
                                'departemen_id' => $row['departemen_id'],
                                'nama' => $row['departemen'],
                                'company_id' => $company->company_code,
                                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                                'created_by' => Auth::user()->name,
                                'updated_by' => Auth::user()->name,
                            ]);
                        }
                    }
                    // If Kompartemen Exists but Departemen Row is Null 
                    elseif (!empty($row['kompartemen']) && empty($row['departemen'])) {
                        $existing = Kompartemen::find($row['kompartemen_id']);
                        if ($existing) {
                            $existing->update([
                                'nama' => $row['kompartemen'],
                                'company_id' => $company->company_code,
                                'updated_by' => Auth::user()->name,
                            ]);
                            $kompartemen = $existing;
                        } else {
                            $kompartemen = Kompartemen::create([
                                'kompartemen_id' => $row['kompartemen_id'],
                                'nama' => $row['kompartemen'],
                                'company_id' => $company->company_code,
                                'created_by' => Auth::user()->name,
                                'updated_by' => Auth::user()->name,
                            ]);
                        }
                    }

                    // Create or update JobRole
                    $jobRole = JobRole::updateOrCreate(
                        [
                            'nama' => $row['job_function'],
                            'company_id' => $company->company_code,
                            'kompartemen_id' => $kompartemen ? $kompartemen->kompartemen_id : null,
                            'departemen_id' => $departemen ? $departemen->departemen_id : null
                        ],
                        [
                            'company_id' => $company->company_code,
                            'kompartemen_id' => $kompartemen ? $kompartemen->kompartemen_id : null,
                            'departemen_id' => $departemen ? $departemen->departemen_id : null,
                            'created_by' => JobRole::where('nama', $row['job_function'])->where('company_id', $company->company_code)->exists() ? null : Auth::user()->name,
                            'updated_by' => JobRole::where('nama', $row['job_function'])->where('company_id', $company->company_code)->exists() ? Auth::user()->name : null
                        ]
                    );

                    // Create or update CompositeRole
                    if (!$row['composite_role'] == null) {
                        $compositeRole = CompositeRole::updateOrCreate(
                            [
                                'nama' => $row['composite_role'],
                                'company_id' => $company->company_code,
                                'kompartemen_id' => $kompartemen ? $kompartemen->kompartemen_id : null,
                                'departemen_id' => $departemen ? $departemen->departemen_id : null,
                                'job_function' => $row['job_function']
                            ],
                            [
                                'company_id' => $company->company_code,
                                'job_function' => $row['job_function'],
                                'created_by' => JobRole::where('nama', $row['job_function'])->where('company_id', $company->company_code)->exists() ? null : Auth::user()->name,
                                'updated_by' => JobRole::where('nama', $row['job_function'])->where('company_id', $company->company_code)->exists() ? Auth::user()->name : null
                            ]
                        );

                        $jobRole->compositeRole()->save($compositeRole);
                        // $compositeRole->jobRole()->associate($jobRole)->save();
                    }

                    $processed++;

                    // Check if 3 seconds have passed since the last update
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $totalRows) {
                        $progress = round(($processed / $totalRows) * 100);
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
