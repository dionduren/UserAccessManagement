<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Company;
use App\Models\SingleRole;
use App\Models\CompositeRole;
use App\Imports\CompositeRoleSingleRoleImport;

class CompositeRoleSingleRoleController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.composite_role_single_role');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480', // 20MB max file size
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data using the Excel facade (assuming the use of maatwebsite/excel package)
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
                // Redirect back with validation errors if any
                return redirect()->back()->with('validationErrors', $errors);
            }

            // Store parsed data in session for preview and confirmation
            session(['parsedCompositeSingleRoles' => $parsedData]);

            // Redirect to preview page
            return view('imports.preview.composite_role_single_role', compact('parsedData'));
        } catch (\Exception $e) {
            // Log the exception with detailed information
            Log::error('Composite & Single Role - Error during import preview', [
                'file' => $request->file('excel_file')->getClientOriginalName(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error during preview: ' . $e->getMessage());
        }
    }

    public function confirmImport()
    {
        $data = session('parsedCompositeSingleRoles');

        if (!$data) {
            // Add debug line
            Log::debug('Composite & Single Role - Session data not found or empty in confirmImport.');
            return redirect()->route('composite_single.upload')->with('error', 'No data available to import.');
        }

        $user = Auth::user();

        try {
            foreach ($data as $row) {
                $company = Company::where('company_code', $row['company_code'])->first();

                // Create or update CompositeRole
                $compositeRole = CompositeRole::updateOrCreate(
                    ['company_id' => $company->id, 'nama' => $row['composite_role']],
                    ['created_by' => $user->name, 'updated_by' => $user->name]
                );

                // Create or update SingleRole
                $singleRole = SingleRole::updateOrCreate(
                    ['company_id' => $company->id, 'nama' => $row['single_role']],
                    ['deskripsi' => $row['single_role_desc'] ?? null, 'created_by' => $user->name, 'updated_by' => $user->name]
                );

                // Update the pivot table relationship if both CompositeRole and SingleRole exist
                if ($compositeRole && $singleRole) {
                    // Update the relationship in the pivot table
                    DB::table('pt_composite_role_single_role')->updateOrInsert(
                        [
                            'composite_role_id' => $compositeRole->id,
                            'single_role_id' => $singleRole->id
                        ],
                        [
                            'created_by' => $user->name,
                            'updated_by' => $user->name,
                            'updated_at' => now()
                        ]
                    );
                }
            }

            // Clear session data after successful import
            session()->forget('parsedCompositeSingleRoles');

            return redirect()->route('composite_single.upload')->with('success', 'Data imported successfully!');
        } catch (\Exception $e) {
            // Log the exception with detailed information
            Log::error('Composite & Single Role - Error during import preview', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('composite_single.upload')->with('error', 'Error during data import: ' . $e->getMessage());
        }
    }
}
