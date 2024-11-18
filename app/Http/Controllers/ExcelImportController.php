<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompanyKompartemenImport;
use App\Imports\CompositeRoleSingleRoleImport;
use App\Imports\TcodeSingleRoleImport;

class ExcelImportController extends Controller
{
    /**
     * Show the form for uploading the Excel file.
     */
    public function showUploadForm()
    {
        return view('imports.upload');
    }

    /**
     * Handle the uploaded Excel file and process each sheet.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480', // Maximum file size of 20MB
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        // Parse the file for preview
        try {
            $parsedData = $this->parseExcelFile($filePath);

            // Store the parsed data in session for confirmation (or use another mechanism to pass data)
            session(['parsedData' => $parsedData]);

            // Redirect to the preview page with the parsed data
            return view('imports.preview', $parsedData);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during import preview: ' . $e->getMessage());
        }
    }

    public function confirmImport()
    {
        // Retrieve the parsed data from session
        $parsedData = session('parsedData');

        if (!$parsedData) {
            return redirect()->route('excel.upload')->with('error', 'No data available to import.');
        }

        // Process and save the data to the database
        try {
            foreach ($parsedData['companyKompartemenData'] as $row) {
                // Example: Create or update your data here
                // Replace with your own model logic
                \App\Models\Company::updateOrCreate(
                    ['company_code' => $row['company']], // Match criteria
                    ['name' => $row['company']] // Data to update or insert
                );
                // Repeat for other models and data
            }

            // Clear the session data after import
            session()->forget('parsedData');

            return redirect()->route('excel.upload')->with('success', 'Data imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('excel.upload')->with('error', 'Error during data import: ' . $e->getMessage());
        }
    }


    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            // Parse the data using the same method
            $parsedData = $this->parseExcelFile($filePath);

            // Display preview of the data
            return view('imports.preview', $parsedData);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during preview: ' . $e->getMessage());
        }
    }

    private function parseExcelFile($filePath)
    {
        $companyKompartemenData = Excel::toCollection(new CompanyKompartemenImport, $filePath)->first();
        $compositeRoleSingleRoleData = Excel::toCollection(new CompositeRoleSingleRoleImport, $filePath)->first();
        $tcodeSingleRoleData = Excel::toCollection(new TcodeSingleRoleImport, $filePath)->first();

        return compact('companyKompartemenData', 'compositeRoleSingleRoleData', 'tcodeSingleRoleData');
    }
}
