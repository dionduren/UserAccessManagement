<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompanyKompartemenImport;

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
                        ['name' => $row['kompartemen'], 'company_id' => $company->id],
                        ['company_id' => $company->id]
                    );

                    $departemen = \App\Models\Departemen::updateOrCreate(
                        ['name' => $row['departemen'], 'company_id' => $company->id, 'kompartemen_id' => $kompartemen->id],
                        ['company_id' => $company->id, 'kompartemen_id' => $kompartemen->id]
                    );
                } elseif (!empty($row['departemen']) && empty($row['kompartemen'])) {
                    $departemen = \App\Models\Departemen::updateOrCreate(
                        ['name' => $row['departemen'], 'company_id' => $company->id],
                        ['company_id' => $company->id, 'kompartemen_id' => null]
                    );
                } elseif (!empty($row['kompartemen']) && empty($row['departemen'])) {
                    $kompartemen = \App\Models\Kompartemen::updateOrCreate(
                        ['name' => $row['kompartemen'], 'company_id' => $company->id],
                        ['company_id' => $company->id]
                    );
                }

                // Create or update JobRole
                $jobRole = \App\Models\JobRole::updateOrCreate(
                    ['nama_jabatan' => $row['job_function'], 'company_id' => $company->id],
                    [
                        'company_id' => $company->id,
                        'kompartemen_id' => $kompartemen->id ?? null,
                        'departemen_id' => $departemen->id ?? null,
                        'deskripsi' => $row['job_description'] ?? null,
                    ]
                );

                // Create or update CompositeRole
                if (!empty($row['composite_role'])) {
                    $compositeRole = \App\Models\CompositeRole::updateOrCreate(
                        ['nama' => $row['composite_role'], 'company_id' => $company->id],
                        ['company_id' => $company->id]
                    );

                    // Associate CompositeRole with JobRole
                    $compositeRole->jobRole()->associate($jobRole);
                    $compositeRole->save();
                }
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
