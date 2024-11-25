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
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480', // Max size of 20MB
        ]);

        $filePath = $request->file('excel_file')->getRealPath();
        $import = new CompositeRoleSingleRoleImport();

        try {
            Excel::import($import, $filePath);

            if (!empty($import->errors)) {
                return redirect()->back()->with('validationErrors', $import->errors);
            }

            session(['parsedData' => $import->parsedData]);

            return view('imports.preview.composite_role_single_role', ['data' => $import->parsedData]);
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
            return redirect()->route('composite_single.upload')->with('error', 'No data available for import. Please upload a file first.');
        }

        try {
            foreach ($data as $row) {
                $singleRole = SingleRole::where('nama', $row['single_role'])->first();
                $compositeRole = CompositeRole::where('nama', $row['composite_role'])->first();

                if ($singleRole && $compositeRole) {
                    $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);
                }
            }

            session()->forget('parsedData');

            return redirect()->route('composite_single.upload')->with('success', 'Data imported successfully!');
        } catch (\Exception $e) {
            Log::error('Error during import', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('composite_single.upload')->with('error', 'Error during import: ' . $e->getMessage());
        }
    }
}
