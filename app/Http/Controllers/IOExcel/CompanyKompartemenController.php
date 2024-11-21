<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompanyKompartemenImport;

class CompanyKompartemenController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.company_kompartemen');
    }

    // public function preview(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls|max:20480', // Max size of 20MB
    //     ]);

    //     $filePath = $request->file('excel_file')->getRealPath();

    //     try {
    //         // Load the data using the Excel facade
    //         $data = Excel::toCollection(new CompanyKompartemenImport, $filePath)->first();

    //         // Validate each row of the data
    //         $errors = [];
    //         $validatedData = [];
    //         foreach ($data as $index => $row) {
    //             // Custom validation for each row (adjust rules as needed)
    //             $validator = Validator::make($row->toArray(), [
    //                 'company' => 'required|string',
    //                 'kompartemen' => 'nullable|string',
    //                 'departemen' => 'nullable|string',
    //                 'job_function' => 'required|string',
    //                 'composite_role' => 'required|string',
    //             ]);

    //             if ($validator->fails()) {
    //                 $errorDetails = [
    //                     'row' => $index + 1,
    //                     'errors' => $validator->errors()->all(),
    //                 ];
    //                 $errors[$index + 1] = $validator->errors()->all();

    //                 // Log the validation errors with details
    //                 Log::error('Validation failed for JobRole-Composite data', $errorDetails);
    //             } else {
    //                 // Collect validated data for further use (confirmation)
    //                 $validatedData[] = $row->toArray();
    //             }
    //         }

    //         if (!empty($errors)) {
    //             // Redirect back with validation errors if any
    //             return redirect()->back()->with('validationErrors', $errors);
    //         }

    //         // Store the validated data in session for confirmation
    //         session(['parsedData' => $validatedData]);

    //         // dd(session()->all());

    //         // Pass data to the view without pagination for DataTables client-side processing
    //         return view('imports.preview.company_kompartemen', compact('validatedData'));
    //     } catch (\Exception $e) {
    //         // Log the exception with detailed information
    //         Log::error('JobRole & Composite Role - Error during import preview', [
    //             'file' => $request->file('excel_file')->getClientOriginalName(),
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return redirect()->back()->with('error', 'Error during preview: ' . $e->getMessage());
    //     }
    // }

    // public function confirmImport()
    // {
    //     $data = session('parsedData');

    //     if (!$data) {
    //         Log::debug('JobRole & Composite Role - Session data not found or empty in confirmImport.');
    //         return redirect()->route('company_kompartemen.upload')->with('error', 'No data available for import. Please upload a file first.');
    //     }

    //     try {
    //         foreach ($data as $row) {
    //             // Retrieve the Company record (assuming it's guaranteed to exist)
    //             $company = \App\Models\Company::where('company_code', $row['company'])->first();

    //             if (!$company) {
    //                 Log::error('Company not found', ['row' => $row]);
    //                 return redirect()->back()->with('error', 'Company not found for the provided code: ' . $row['company']);
    //             }

    //             // Update or create Kompartemen with company scoping
    //             $kompartemen = null;
    //             if (!empty($row['kompartemen'])) {
    //                 $kompartemen = \App\Models\Kompartemen::updateOrCreate(
    //                     [
    //                         'name' => $row['kompartemen'],
    //                         'company_id' => $company->id, // Include company_id to prevent overwriting
    //                     ],
    //                     [
    //                         'company_id' => $company->id,
    //                     ]
    //                 );
    //             }

    //             // Update or create Departemen with company and Kompartemen scoping
    //             $departemen = null;
    //             if (!empty($row['departemen'])) {
    //                 $departemen = \App\Models\Departemen::updateOrCreate(
    //                     [
    //                         'name' => $row['departemen'],
    //                         'company_id' => $company->id, // Include company_id
    //                         'kompartemen_id' => $kompartemen->id ?? null, // Include Kompartemen relationship
    //                     ],
    //                     [
    //                         'company_id' => $company->id,
    //                         'kompartemen_id' => $kompartemen->id ?? null,
    //                     ]
    //                 );
    //             }

    //             // Update or create JobRole with proper relationships
    //             $jobRole = null;
    //             if (!empty($row['job_function'])) {
    //                 $jobRole = \App\Models\JobRole::updateOrCreate(
    //                     [
    //                         'nama_jabatan' => $row['job_function'],
    //                         'company_id' => $company->id, // Include company_id
    //                     ],
    //                     [
    //                         'company_id' => $company->id,
    //                         'kompartemen_id' => $kompartemen->id ?? null,
    //                         'departemen_id' => $departemen->id ?? null,
    //                         'deskripsi' => $row['job_description'] ?? null,
    //                         'created_by' => $row['created_by'] ?? null,
    //                     ]
    //                 );
    //             }

    //             // Update or create CompositeRole
    //             if (!empty($row['composite_role'])) {
    //                 \App\Models\CompositeRole::updateOrCreate(
    //                     [
    //                         'nama' => $row['composite_role'],
    //                         'company_id' => $company->id, // Include company_id
    //                     ],
    //                     [
    //                         'jabatan_id' => $jobRole->id ?? null,
    //                         'deskripsi' => $row['composite_description'] ?? null,
    //                         'created_by' => $row['created_by'] ?? null,
    //                     ]
    //                 );
    //             }
    //         }

    //         session()->forget('parsedData');

    //         return redirect()->route('company_kompartemen.upload')->with('success', 'Data imported and relationships updated successfully!');
    //     } catch (\Exception $e) {
    //         Log::error('Error during data import', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return redirect()->route('company_kompartemen.upload')->with('error', 'Error during data import: ' . $e->getMessage());
    //     }
    // }
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480', // Max size of 20MB
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data using the Excel facade
            $data = Excel::toCollection(new CompanyKompartemenImport, $filePath)->first();

            $errors = [];
            $validatedData = [];

            foreach ($data as $index => $row) {
                $row = $row->toArray();

                // Retrieve the Company record
                $company = \App\Models\Company::where('company_code', $row['company'])->first();

                if (!$company) {
                    $errors[] = [
                        'row' => $index + 1,
                        'errors' => ['Company not found for the provided code: ' . $row['company']]
                    ];
                    continue;
                }

                $kompartemen = null;
                $departemen = null;

                // Handle Kompartemen and Departemen based on provided data
                if (!empty($row['kompartemen']) && !empty($row['departemen'])) {
                    $kompartemen = \App\Models\Kompartemen::updateOrCreate(
                        [
                            'name' => $row['kompartemen'],
                            'company_id' => $company->id,
                        ],
                        [
                            'company_id' => $company->id,
                        ]
                    );

                    $departemen = \App\Models\Departemen::updateOrCreate(
                        [
                            'name' => $row['departemen'],
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id,
                        ],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id,
                        ]
                    );
                } elseif (!empty($row['departemen']) && empty($row['kompartemen'])) {
                    $departemen = \App\Models\Departemen::updateOrCreate(
                        [
                            'name' => $row['departemen'],
                            'company_id' => $company->id,
                        ],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => null,
                        ]
                    );
                } elseif (!empty($row['kompartemen']) && empty($row['departemen'])) {
                    $kompartemen = \App\Models\Kompartemen::updateOrCreate(
                        [
                            'name' => $row['kompartemen'],
                            'company_id' => $company->id,
                        ],
                        [
                            'company_id' => $company->id,
                        ]
                    );
                } else {
                    $errors[] = [
                        'row' => $index + 1,
                        'errors' => ['Each job role must have at least a Kompartemen or Departemen.']
                    ];
                    continue;
                }

                // Collect validated data
                $validatedData[] = $row;
            }

            if (!empty($errors)) {
                return redirect()->back()->with('validationErrors', $errors);
            }

            session(['parsedData' => $validatedData]);

            return view('imports.preview.company_kompartemen', compact('validatedData'));
        } catch (\Exception $e) {
            Log::error('Error during preview', [
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

        if (!$data) {
            Log::debug('Session data not found or empty in confirmImport.');
            return redirect()->route('company_kompartemen.upload')->with('error', 'No data available for import. Please upload a file first.');
        }

        try {
            foreach ($data as $row) {
                $company = \App\Models\Company::where('company_code', $row['company'])->first();

                if (!$company) {
                    Log::error('Company not found', ['row' => $row]);
                    continue;
                }

                $kompartemen = null;
                $departemen = null;

                // Create or update Kompartemen and Departemen based on data
                if (!empty($row['kompartemen']) && !empty($row['departemen'])) {
                    $kompartemen = \App\Models\Kompartemen::updateOrCreate(
                        [
                            'name' => $row['kompartemen'],
                            'company_id' => $company->id,
                        ],
                        [
                            'company_id' => $company->id,
                        ]
                    );

                    $departemen = \App\Models\Departemen::updateOrCreate(
                        [
                            'name' => $row['departemen'],
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id,
                        ],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => $kompartemen->id,
                        ]
                    );
                } elseif (!empty($row['departemen']) && empty($row['kompartemen'])) {
                    $departemen = \App\Models\Departemen::updateOrCreate(
                        [
                            'name' => $row['departemen'],
                            'company_id' => $company->id,
                        ],
                        [
                            'company_id' => $company->id,
                            'kompartemen_id' => null,
                        ]
                    );
                } elseif (!empty($row['kompartemen']) && empty($row['departemen'])) {
                    $kompartemen = \App\Models\Kompartemen::updateOrCreate(
                        [
                            'name' => $row['kompartemen'],
                            'company_id' => $company->id,
                        ],
                        [
                            'company_id' => $company->id,
                        ]
                    );
                }

                // Create or update JobRole
                \App\Models\JobRole::updateOrCreate(
                    ['nama_jabatan' => $row['job_function'], 'company_id' => $company->id],
                    [
                        'company_id' => $company->id,
                        'kompartemen_id' => $kompartemen->id ?? null,
                        'departemen_id' => $departemen->id ?? null,
                        'deskripsi' => $row['job_description'] ?? null,
                        'created_by' => $row['created_by'] ?? null,
                    ]
                );
            }

            session()->forget('parsedData');

            return redirect()->route('company_kompartemen.upload')->with('success', 'Data imported successfully!');
        } catch (\Exception $e) {
            Log::error('Error during data import', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('company_kompartemen.upload')->with('error', 'Error during import: ' . $e->getMessage());
        }
    }
}
