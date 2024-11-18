<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompanyKompartemenImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


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

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data using the Excel facade
            $data = Excel::toCollection(new CompanyKompartemenImport, $filePath)->first();

            // Validate each row of the data
            $errors = [];
            $validatedData = [];
            foreach ($data as $index => $row) {
                $validator = Validator::make($row->toArray(), [
                    'company' => 'required|string',
                    'kompartemen' => 'nullable|string',
                    'departemen' => 'nullable|string',
                    'job_function' => 'required|string',
                    'composite_role' => 'required|string',
                ]);

                if ($validator->fails()) {
                    $errorDetails = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->all(),
                    ];
                    $errors[$index + 1] = $validator->errors()->all();

                    // Log the validation errors with details
                    Log::error('Validation failed for Company-Kompartemen data', $errorDetails);
                } else {
                    // Collect validated data for further use (confirmation)
                    $validatedData[] = $row->toArray();
                }
            }

            if (!empty($errors)) {
                // Redirect back with validation errors if any
                return redirect()->back()->with('validationErrors', $errors);
            }

            // Store the validated data in session for confirmation
            session(['parsedData' => $validatedData]);

            // dd(session()->all());

            // Pass data to the view without pagination for DataTables client-side processing
            return view('imports.preview.company_kompartemen', compact('validatedData'));
        } catch (\Exception $e) {
            // Log the exception with detailed information
            Log::error('Error during import preview', [
                'file' => $request->file('excel_file')->getClientOriginalName(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error during preview: ' . $e->getMessage());
        }
    }


    public function confirmImport()
    {
        $data = session('parsedData');


        // dd(session()->all());

        if (!$data) {
            // Add debug line
            Log::debug('Session data not found or empty in confirmImport.');
            return redirect()->route('company_kompartemen.upload')->with('error', 'No data available for import. Please upload a file first.');
        }

        // Optionally display the data for debugging (temporary)
        Log::debug('Parsed Data:', ['data' => $data]);

        try {
            foreach ($data as $row) {
                // Retrieve the Company record (assuming it's guaranteed to exist)
                $company = \App\Models\Company::where('company_code', $row['company'])->first();

                if (!$company) {
                    return redirect()->back()->with('error', 'Company not found for the provided code: ' . $row['company']);
                }

                // Update or create a record for Kompartemen model
                $kompartemen = null;
                if (!empty($row['kompartemen'])) {
                    $kompartemen = \App\Models\Kompartemen::updateOrCreate(
                        ['name' => $row['kompartemen']],
                        ['company_id' => $company->id]
                    );
                }

                // Update or create a record for Departemen model
                $departemen = null;
                if (!empty($row['departemen'])) {
                    $departemen = \App\Models\Departemen::updateOrCreate(
                        ['name' => $row['departemen']],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id ?? null,
                        ]
                    );
                }

                // Update or create a record for JobRole model
                $jobRole = null;
                if (!empty($row['job_function'])) {
                    $jobRole = \App\Models\JobRole::updateOrCreate(
                        ['nama_jabatan' => $row['job_function']],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id ?? null,
                            'departemen_id' => $departemen->id ?? null,
                            'deskripsi' => $row['job_description'] ?? null,
                            'created_by' => $row['created_by'] ?? null,
                        ]
                    );
                }

                // Update or create a record for CompositeRole model
                $compositeRole = null;
                if (!empty($row['composite_role'])) {
                    $compositeRole = \App\Models\CompositeRole::updateOrCreate(
                        ['nama' => $row['composite_role']],
                        [
                            'company_id' => $company->id,
                            'jabatan_id' => $jobRole->id ?? null,
                            'deskripsi' => $row['composite_description'] ?? null,
                            'created_by' => $row['created_by'] ?? null,
                        ]
                    );
                }

                // Update the pivot table relationship if both JobRole and CompositeRole exist
                if ($jobRole && $compositeRole) {
                    $compositeRole->jobRole()->associate($jobRole); // Associate directly if applicable
                    $compositeRole->save();

                    // If there's a many-to-many relation with a different pivot table, handle it here
                    // Example for `pt_composite_role_single_role` if applicable:
                    if (!empty($row['single_role_id'])) {
                        DB::table('pt_composite_role_single_role')->updateOrInsert(
                            [
                                'composite_role_id' => $compositeRole->id,
                                'single_role_id' => $row['single_role_id']
                            ],
                            [
                                'created_by' => $row['created_by'] ?? null,
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

            session()->forget('parsedData');

            return redirect()->route('company_kompartemen.upload')->with('success', 'Data imported and relationships updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error during data import', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('company_kompartemen.upload')->with('error', 'Error during data import: ' . $e->getMessage());
        }
    }
}
