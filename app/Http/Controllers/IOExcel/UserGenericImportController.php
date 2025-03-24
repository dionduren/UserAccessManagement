<?php

namespace App\Http\Controllers\IOExcel;

use App\Imports\UserGenericImport;
use App\Models\UserGeneric;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class UserGenericImportController extends Controller
{
    public function uploadForm()
    {
        return view('upload.user_generic.upload');
    }

    public function upload(Request $request)
    {
        $file = $request->file('file');

        $import = new UserGenericImport();
        $import->import($file);

        $userGenerics = UserGeneric::all();

        return view('upload.user_generic.preview', compact('userGenerics'));
    }

    public function confirmImport(Request $request)
    {
        $import = new UserGenericImport();
        $import->confirmImport($request);

        return redirect()->route('user-generic.index');
    }

    public function previewData()
    {
        $userGenerics = UserGeneric::all();

        return response()->json($userGenerics);
    }
}

