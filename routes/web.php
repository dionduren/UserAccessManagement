<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\JSONController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TcodeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\SingleRoleController;
use App\Http\Controllers\KompartemenController;
use App\Http\Controllers\TcodeImportController;
use App\Http\Controllers\AccessMatrixController;

use App\Http\Controllers\CompositeRoleController;


// use App\Http\Controllers\IOExcel\ExcelImportController;
use App\Http\Controllers\MasterData\CostCenterController;
use App\Http\Controllers\MasterData\UserNIKController;
use App\Http\Controllers\MasterData\UserGenericController;
use App\Http\Controllers\MasterData\CostPrevUserController;

use App\Http\Controllers\IOExcel\CompanyKompartemenController;
use App\Http\Controllers\IOExcel\CompositeRoleSingleRoleController;
use App\Http\Controllers\IOExcel\SingleRoleTcodeController;
use App\Http\Controllers\IOExcel\TcodeSingleRoleController;

use App\Http\Controllers\Relationship\JobCompositeController;

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

Route::get('/job-roles/{id}', [JobRoleController::class, 'show'])->where('id', '[0-9]+');
Route::resource('job-roles', JobRoleController::class);

Route::get('/get-kompartemen', [KompartemenController::class, 'getKompartemenByCompany']);
Route::get('/get-departemen', [DepartemenController::class, 'getDepartemenByKompartemen']);
Route::get('/get-departemen-by-company', [DepartemenController::class, 'getDepartemenByCompany']);
Route::get('/get-job-roles', [JobRoleController::class, 'getJobRoles'])->name('job-roles.getData');

// In routes/web.php
// Route::get('/master-data-json', function () {
//     if (!auth()->check()) {
//         abort(403, 'Unauthorized access');
//     }

//     $path = storage_path('app/master_data.json');
//     if (file_exists($path)) {
//         return response()->file($path, [
//             'Content-Type' => 'application/json',
//         ]);
//     }
//     abort(404);
// })->middleware('auth'); // You can also apply any middleware if needed

// ======= MASTER DATA ROLES ======= 

Route::get('/composite-roles/data', [CompositeRoleController::class, 'getCompositeRoles'])->name('composite-roles.data');
Route::resource('composite-roles', CompositeRoleController::class);

Route::get('/single-roles/data', [SingleRoleController::class, 'getSingleRoles'])->name('single-roles.data');
Route::resource('single-roles', SingleRoleController::class);

// Route::get('/tcodes/download-template', [TcodeImportController::class, 'downloadTemplate'])->name('tcodes.download-template');
Route::get('/tcodes/data', [TcodeController::class, 'getTcodes'])->name('tcodes.data');
Route::resource('tcodes', TcodeController::class);

// ======= MASTER DATA RELATIONSHIP ======= 

Route::get('/relationship/job-composite/data', [JobCompositeController::class, 'getCompositeRoles'])->name('job-composite.data');
Route::get('/relationship/job-composite/empty-composite', [JobCompositeController::class, 'getEmptyCompositeRole'])->name('job-composite.empty-composite');
Route::get('/relationship/job-composite/company-composite', [JobCompositeController::class, 'getCompositeFilterCompany'])->name('job-composite.company-composite');
Route::resource('/relationship/job-composite', JobCompositeController::class);

// === import export excel
// Route::get('/import', [ExcelImportController::class, 'showUploadForm'])->name('excel.upload');
// Route::post('/import', [ExcelImportController::class, 'import'])->name('excel.import');
// Route::post('/import/confirm', [ExcelImportController::class, 'confirmImport'])->name('excel.confirm');

// ======= IMPORT DATA RELATIONSHIP ======= 

Route::get('/company-kompartemen/upload', [CompanyKompartemenController::class, 'uploadForm'])->name('company_kompartemen.upload');
Route::post('/company-kompartemen/preview', [CompanyKompartemenController::class, 'preview'])->name('company_kompartemen.preview');
Route::get('/company-kompartemen/preview-data', [CompanyKompartemenController::class, 'getPreviewData'])->name('company_kompartemen.preview_data');
Route::post('/company-kompartemen/confirm', [CompanyKompartemenController::class, 'confirmImport'])->name('company_kompartemen.confirm');

Route::get('/composite-role-single-role/upload', [CompositeRoleSingleRoleController::class, 'uploadForm'])->name('composite_single.upload');
Route::post('/composite-role-single-role/preview', [CompositeRoleSingleRoleController::class, 'preview'])->name('composite_single.preview');
Route::get('/composite-role-single-role/preview-data', [CompositeRoleSingleRoleController::class, 'getPreviewData'])->name('composite_single.preview_data');
Route::post('/composite-role-single-role/confirm', [CompositeRoleSingleRoleController::class, 'confirmImport'])->name('composite_single.confirm');

Route::get('/tcode-single-role/upload', [SingleRoleTcodeController::class, 'uploadForm'])->name('tcode_single_role.upload');
Route::post('/tcode-single-role/preview', [SingleRoleTcodeController::class, 'preview'])->name('tcode_single_role.preview');
Route::get('/tcode-single-role/preview-data', [SingleRoleTcodeController::class, 'getPreviewData'])->name('tcode_single_role.preview_data');
Route::post('/tcode-single-role/confirm', [SingleRoleTcodeController::class, 'confirmImport'])->name('tcode_single_role.confirm');

// ------------------ NEW MASTER DATA ------------------

Route::resource('user-nik', UserNIKController::class);

Route::get('cost-center/user-generic/dashboard', [UserGenericController::class, 'index_dashboard'])->name('dashboard.user-generic');
Route::resource('cost-center/user-generic', UserGenericController::class)->name('index', 'user-generic.index');
Route::get('cost-center/prev-user', [CostCenterController::class, 'index_prev_user'])->name('prev-user.index');
Route::resource('cost-center', CostCenterController::class);

// ------------------ ACCESS MATRIX ------------------

Route::get('/access-matrix', [AccessMatrixController::class, 'index'])->name('access-matrix');
Route::post('/access-matrix/assign-role', [AccessMatrixController::class, 'assignRole'])->name('access-matrix.assign-role');
Route::post('/access-matrix/assign-permission', [AccessMatrixController::class, 'assignPermission'])->name('access-matrix.assign-permission');

Route::middleware(['role:Admin'])->group(function () {

    Route::post('/admin/fetch-employee', [EmployeeController::class, 'fetchEmployeeData'])->name('admin.fetch-employee');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage-users');
    // In routes/web.php
    Route::get('/admin/regenerate-json', [JSONController::class, 'regenerateJson'])->name('json.regenerate');
});

Route::middleware(['permission:manage users'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');

    // Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    // Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage-users');
});
