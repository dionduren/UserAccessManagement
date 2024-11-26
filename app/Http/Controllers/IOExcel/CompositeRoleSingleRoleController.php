<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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

            return view('imports.preview.composite_role_single_role', ['data' => $data]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Confirm and process the import
    public function confirmImport()
    {
        $data = session('parsedData');

        if (!$data) {
            return redirect()->route('composite_single.upload')->withErrors(['error' => 'No data available for import.']);
        }

        foreach ($data as $row) {
            // Process each row in the collection
            (new CompositeRoleSingleRoleImport)->model($row->toArray());
        }

        // Clear session after import
        session()->forget('parsedData');

        return redirect()->route('composite_single.upload')->with('success', 'Data imported successfully!');
    }
}
