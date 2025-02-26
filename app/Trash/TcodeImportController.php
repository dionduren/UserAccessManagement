<?php

namespace App\Http\Controllers;

use App\Models\Tcode;
use App\Models\SingleRole;
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
            'excel_file' => 'required|mimes:xlsx,csv'
        ]);

        // Load and process data from the uploaded file using TcodeImport
        $import = new TcodeImport();
        Excel::import($import, $request->file('excel_file'));

        $data = $import->getData();
        $warnings = [];
        $preparedData = [];

        foreach ($data as $row) {
            // Extract values from the mapped row
            $tcodeName = $row['code'];
            $tcodeDesc = $row['deskripsi'];
            $singleRoleName = $row['single_role_name'];
            $singleRoleDesc = $row['single_role_desc'];


            // Prepare data for the confirm step without creating or modifying any database entries
            $preparedData[] = [
                'code' => $tcodeName,
                'deskripsi' => $tcodeDesc,
                'single_role_name' => $singleRoleName,
                'single_role_desc' => $singleRoleDesc,
            ];

            // Optionally, add warnings for mismatched descriptions or other validation checks
            $existingSingleRole = SingleRole::where('nama', $singleRoleName)->first();
            // if ($existingSingleRole && $existingSingleRole->deskripsi !== $singleRoleDesc) {
            //     $warnings[] = "Mismatched description for Single Role: {$singleRoleName}.";
            // }

            $existingTcode = Tcode::where('code', $tcodeName)->first();
            // if ($existingTcode && $existingTcode->description !== $tcodeDesc) {
            //     $warnings[] = "Mismatched description for Tcode: {$tcodeName}.";
            // }
        }

        // Store data in session or pass it to the view for confirmation
        session(['import_data' => $preparedData]);

        return view('tcodes.preview', compact('preparedData', 'warnings'));
    }




    /**
     * Confirm and save data to the database.
     */
    public function confirm(Request $request)
    {
        $validatedData = $request->validate([
            'data' => 'required|string' // Validate as a string since we encoded it
        ]);

        // Decode and convert the data back into an array
        $decodedData = json_decode(base64_decode($validatedData['data']), true);

        if (!$decodedData || !is_array($decodedData)) {
            return redirect()->back()->withErrors(['data' => 'Invalid data received.']);
        }

        foreach ($decodedData as $item) {
            $companyId = $item['company_id'] ?? null;
            $singleRoleName = $item['single_role_name'] ?? null;
            $singleRoleDesc = $item['single_role_desc'] ?? null;
            $tcodeName = $item['code'] ?? null;
            $tcodeDesc = $item['deskripsi'] ?? null;

            // Fetch or create/update Single Role
            if ($singleRoleName) {
                $singleRole = SingleRole::firstOrCreate(
                    ['nama' => $singleRoleName],
                    ['deskripsi' => $singleRoleDesc, 'company_id' => $companyId]
                );

                // Optionally update description if it differs
                if ($singleRole->wasRecentlyCreated === false && $singleRole->deskripsi !== $singleRoleDesc) {
                    $singleRole->deskripsi = $singleRoleDesc;
                    $singleRole->company_id = $companyId; // Ensure company_id is consistent
                    $singleRole->save();
                }
            }

            // Fetch or create/update Tcode
            if ($tcodeName) {
                $tcode = Tcode::firstOrCreate(
                    ['code' => $tcodeName],
                    ['deskripsi' => $tcodeDesc, 'company_id' => $companyId]
                );

                // Optionally update description if it differs
                if ($tcode->wasRecentlyCreated === false && $tcode->deskripsi !== $tcodeDesc) {
                    $tcode->deskripsi = $tcodeDesc;
                    $tcode->company_id = $companyId; // Ensure company_id is consistent
                    $tcode->save();
                }
            }

            // Establish or update relationship between Single Role and Tcode
            if (isset($singleRole) && isset($tcode)) {
                $singleRole->tcodes()->syncWithoutDetaching([$tcode->id]);
            }
        }

        return redirect()->route('tcodes.index')->with('success', 'Data imported successfully!');
    }

    /**
     * Export the Tcode upload template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new TcodeTemplateExport, 'SingleRole_Tcode_Upload_Template.xlsx');
    }
}
