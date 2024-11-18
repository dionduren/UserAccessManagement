<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TcodeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\SingleRoleController;
use App\Http\Controllers\KompartemenController;
use App\Http\Controllers\TcodeImportController;
use App\Http\Controllers\AccessMatrixController;

use App\Http\Controllers\CompositeRoleController;
// use App\Http\Controllers\IOExcel\ExcelImportController;
use App\Http\Controllers\IOExcel\TcodeSingleRoleController;
use App\Http\Controllers\IOExcel\CompanyKompartemenController;
use App\Http\Controllers\IOExcel\CompositeRoleSingleRoleController;


// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ======= MASTER DATA COMPANY ======= 
Route::resource('companies', CompanyController::class);
// Route::resource('companies', CompanyController::class)->middleware('permission:view company|edit company|delete company');

Route::resource('kompartemens', KompartemenController::class);
Route::resource('departemens', DepartemenController::class);

Route::get('/job-roles/filtered-data', [JobRoleController::class, 'getFilteredData'])->name('job-roles.filtered-data');
Route::get('/job-roles/{id}', [JobRoleController::class, 'show'])->where('id', '[0-9]+');
Route::resource('job-roles', JobRoleController::class);

Route::get('/get-kompartemen', [KompartemenController::class, 'getKompartemenByCompany']);
Route::get('/get-departemen', [DepartemenController::class, 'getDepartemenByKompartemen']);
Route::get('/get-job-roles', [JobRoleController::class, 'getJobRoles']);

// ======= MASTER DATA ROLES ======= 

Route::get('/composite-roles/ajax', [CompositeRoleController::class, 'getCompositeRolesAjax'])->name('composite-roles.ajax');
Route::resource('composite-roles', CompositeRoleController::class);

// Route::post('/single-roles', [SingleRoleController::class, 'store'])->name('single-roles.store');
Route::resource('single-roles', SingleRoleController::class);

Route::get('/tcodes/upload', [TcodeImportController::class, 'showUploadForm'])->name('tcodes.upload');
Route::post('/tcodes/preview', [TcodeImportController::class, 'preview'])->name('tcodes.preview');
Route::get('/tcodes/preview-refresh', [TcodeImportController::class, 'previewTcodes'])->name('tcodes.preview-refresh');
Route::post('/tcodes/confirm', [TcodeImportController::class, 'confirm'])->name('tcodes.confirm');
Route::get('/tcodes/download-template', [TcodeImportController::class, 'downloadTemplate'])->name('tcodes.download-template');
Route::get('/tcodes/{id}', [TcodeController::class, 'show'])->name('tcodes.show');
Route::resource('tcodes', TcodeController::class);

// Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
// Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage-users');

// === import export excel
// Route::get('/import', [ExcelImportController::class, 'showUploadForm'])->name('excel.upload');
// Route::post('/import', [ExcelImportController::class, 'import'])->name('excel.import');
// Route::post('/import/confirm', [ExcelImportController::class, 'confirmImport'])->name('excel.confirm');

Route::get('/company-kompartemen/upload', [CompanyKompartemenController::class, 'uploadForm'])->name('company_kompartemen.upload');
Route::post('/company-kompartemen/preview', [CompanyKompartemenController::class, 'preview'])->name('company_kompartemen.preview');
Route::post('/company-kompartemen/confirm', [CompanyKompartemenController::class, 'confirmImport'])->name('company_kompartemen.confirm');

Route::get('/composite-role-single-role/upload', [CompositeRoleSingleRoleController::class, 'uploadForm'])->name('composite_role_single_role.upload');
Route::post('/composite-role-single-role/preview', [CompositeRoleSingleRoleController::class, 'preview'])->name('composite_role_single_role.preview');

Route::get('/tcode-single-role/upload', [TcodeSingleRoleController::class, 'uploadForm'])->name('tcode_single_role.upload');
Route::post('/tcode-single-role/preview', [TcodeSingleRoleController::class, 'preview'])->name('tcode_single_role.preview');


Route::get('/access-matrix', [AccessMatrixController::class, 'index'])->name('access-matrix');
Route::post('/access-matrix/assign-role', [AccessMatrixController::class, 'assignRole'])->name('access-matrix.assign-role');
Route::post('/access-matrix/assign-permission', [AccessMatrixController::class, 'assignPermission'])->name('access-matrix.assign-permission');

Route::middleware(['role:Admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage-users');
});

Route::middleware(['permission:manage users'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
});
