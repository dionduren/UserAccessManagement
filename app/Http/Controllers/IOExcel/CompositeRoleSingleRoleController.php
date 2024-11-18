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

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        try {
            $data = Excel::toCollection(new CompositeRoleSingleRoleImport, $filePath)->first();
            $paginatedData = collect($data)->paginate(10);

            return view('imports.preview.composite_role_single_role', compact('paginatedData'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during preview: ' . $e->getMessage());
        }
    }
}
