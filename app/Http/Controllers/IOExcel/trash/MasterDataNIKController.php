<?php

// namespace App\Http\Controllers\IOExcel;

// use App\Http\Controllers\Controller;
// use App\Imports\UserDetailImport;
// use App\Models\UserDetail;
// use Illuminate\Http\Request;
// use Maatwebsite\Excel\Facades\Excel;

// class MasterDataNIKController extends Controller
// {
//     public function uploadForm()
//     {
//         return view('upload.user_detail.upload');
//     }

//     public function upload(Request $request)
//     {
//         $file = $request->file('file');

//         $import = new UserDetailImport();
//         $import->import($file);

//         $userDetails = UserDetail::all();

//         return view('upload.user_detail.preview', compact('userDetails'));
//     }

//     public function confirmImport(Request $request)
//     {
//         $import = new UserDetailImport();
//         $import->confirmImport($request);

//         // buat menu user detail
//         return redirect()->route('user-nik.index');
//     }

//     public function previewData()
//     {
//         $userDetails = UserDetail::all();

//         return response()->json($userDetails);
//     }
// }
