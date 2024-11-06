<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use App\Imports\TcodeImport;
use Illuminate\Http\Request;
use App\Exports\TcodeTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class TcodeImportController extends Controller
{
    /**
     * Display the upload form.
     */
    public function showUploadForm()
    {
        return view('tcodes.upload');
    }

    /**
     * Handle the uploaded Excel file and display a preview of the data.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new TcodeImport();
        Excel::import($import, $request->file('excel_file'));

        // Get imported data with missing roles
        $data = $import->getData();

        // Collect warnings from the imported data
        $missingRolesSummary = [];
        foreach ($data as $tcode) {
            if (!empty($tcode['missing_roles'])) {
                foreach ($tcode['missing_roles'] as $missingRole) {
                    // Count occurrences of each missing role
                    if (!isset($missingRolesSummary[$missingRole])) {
                        $missingRolesSummary[$missingRole] = 0;
                    }
                    $missingRolesSummary[$missingRole]++;
                }
            }
        }

        return view('tcodes.preview', [
            'tcodes' => $data,
            'missingRolesSummary' => $missingRolesSummary, // Pass the summary to the view
        ]);
    }


    /**
     * Confirm and save data to the database.
     */
    public function confirm(Request $request)
    {
        $data = $request->input('data');

        foreach ($data as $row) {
            // Assuming you have a model for Tcode, save each row
            Tcode::create([
                'company_id' => $row['company_id'],
                'code' => $row['code'],
                'deskripsi' => $row['deskripsi'],
                'single_roles' => $row['single_roles'] // Adjust as necessary if single_roles needs separate handling
            ]);
        }

        return redirect()->route('tcodes.index')->with('status', 'Data uploaded successfully.');
    }

    /**
     * Export the Tcode upload template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new TcodeTemplateExport, 'Tcode_Upload_Template.xlsx');
    }
}
