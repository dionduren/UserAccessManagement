<?php

namespace App\Http\Controllers\IOExcel;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Imports\CompositeRoleSingleRoleImport;

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

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Load the data into a collection
            $data = Excel::toCollection(new CompositeRoleSingleRoleImport, $filePath)->first();

            session(['parsedData' => $data]);

            // dd($data, session('parsedData'));

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
                'composite_role' => $row['composite_role'] ?? null,
                'single_role' => $row['single_role'] ?? null,
                'description' => $row['description'] ?? null,
                'company' => $row['company'] ?? null,
            ];
        });

        // Return the data to DataTables
        return DataTables::of($formattedData)->make(true);
    }


    // Confirm and process the import
    // public function confirmImport()
    // {
    //     $data = session('parsedData');

    //     if (!$data) {
    //         return redirect()->route('composite_single.upload')->withErrors(['error' => 'No data available for import.']);
    //     }

    //     foreach ($data as $row) {
    //         // Process each row in the collection
    //         (new CompositeRoleSingleRoleImport)->model($row->toArray());
    //     }

    //     // Clear session after import
    //     session()->forget('parsedData');

    //     return redirect()->route('composite_single.upload')->with('success', 'Data imported successfully!');
    // }
    public function confirmImport(Request $request)
    {
        $data = session('parsedData');

        if (!$data) {
            return response()->json(['error' => 'No data available for import. Please upload a file first.'], 400);
        }

        try {
            // Convert the collection to an array if necessary
            $dataArray = $data instanceof \Illuminate\Support\Collection ? $data->toArray() : $data;

            foreach ($dataArray as $index => $row) {
                // Validate required keys
                // if (!isset($row['company'], $row['composite_role'], $row['single_role'], $row['description'])) {
                //     Log::warning('Skipping row due to missing keys', ['row' => $row]);
                //     continue;
                // }
                if (!isset($row['company']) || empty($row['company'])) {
                    Log::warning('Skipping row due to missing company key', ['row' => $row]);
                    continue;
                }

                // Step 1: Find the Company by company_code
                $company = \App\Models\Company::where('company_code', $row['company'])->first();

                if (!$company) {
                    Log::warning('Company not found for row', ['row' => $row]);
                    continue;
                }

                // Step 2: Create or Update CompositeRole
                $compositeRole = \App\Models\CompositeRole::updateOrCreate(
                    ['nama' => $row['composite_role'], 'company_id' => $company->id]
                );

                // Step 3: Create or Update SingleRole
                // Skip row if single_role (nama) is null
                if (empty($row['single_role'])) {
                    Log::warning('Skipping row due to missing single_role', ['row' => $row]);
                    continue;
                }
                
                $singleRole = \App\Models\SingleRole::updateOrCreate(
                    ['nama' => $row['single_role'], 'company_id' => $company->id],
                    ['deskripsi' => $row['description']]
                );

                // Step 4: Link SingleRole to CompositeRole
                $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);
            }

            // Clear session data after processing
            session()->forget('parsedData');

            return response()->json(['success' => 'Data imported successfully!']);
        } catch (\Exception $e) {
            Log::error('Error during import', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Error during import: ' . $e->getMessage()], 500);
        }
    }
}
