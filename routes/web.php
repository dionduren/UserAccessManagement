<?php

use App\Models\TerminatedEmployee;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JSONController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TcodeImportController;


// use App\Http\Controllers\IOExcel\ExcelImportController;
use App\Http\Controllers\AccessMatrixController;
use App\Http\Controllers\DynamicUploadController;

use App\Http\Controllers\MasterData\TcodeController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\JobRoleController;
use App\Http\Controllers\MasterData\UserNIKController;
use App\Http\Controllers\Relationship\NIKJobController;
use App\Http\Controllers\Report\EmptyJobRoleController;
use App\Http\Controllers\IOExcel\UserNIKImportController;
use App\Http\Controllers\MasterData\CostCenterController;

use App\Http\Controllers\MasterData\DepartemenController;
use App\Http\Controllers\MasterData\SingleRoleController;
use App\Http\Controllers\Report\WorkUnitReportController;

use App\Http\Controllers\MasterData\KompartemenController;
use App\Http\Controllers\MasterData\UserGenericController;
use App\Http\Controllers\IOExcel\SingleRoleTcodeController;
use App\Http\Controllers\IOExcel\TcodeSingleRoleController;
use App\Http\Controllers\MasterData\CostPrevUserController;

use App\Http\Controllers\IOExcel\NIKJobRoleImportController;
use App\Http\Controllers\MasterData\CompositeRoleController;
use App\Http\Controllers\Relationship\SingleTcodeController;
use App\Http\Controllers\IOExcel\CompanyMasterDataController;
use App\Http\Controllers\Relationship\JobCompositeController;
use App\Http\Controllers\IOExcel\CompanyKompartemenController;
use App\Http\Controllers\Relationship\CompositeSingleController;
use App\Http\Controllers\MasterData\TerminatedEmployeeController;
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

Route::get('/job-roles/{id}', [JobRoleController::class, 'show'])->where('id', '[0-9]+');
Route::resource('job-roles', JobRoleController::class);

Route::get('/get-kompartemen', [KompartemenController::class, 'getKompartemenByCompany']);
Route::get('/get-departemen', [DepartemenController::class, 'getDepartemenByKompartemen']);
Route::get('/get-departemen-by-company', [DepartemenController::class, 'getDepartemenByCompany']);
Route::get('/get-job-roles', [JobRoleController::class, 'getJobRoles'])->name('job-roles.getData');

// Upload Company Master Data

Route::get('/upload/unit-kerja', [CompanyMasterDataController::class, 'showForm'])->name('unit-kerja.upload-form');
Route::post('/upload/unit-kerja', [CompanyMasterDataController::class, 'upload'])->name('unit-kerja.upload');


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

// Route::get('/relationship/composite-single/data', [CompositeSingleController::class, 'getSingleRoles'])->name('composite-single.jsonIndex');
// Route::get('/relationship/composite-single/empty-single', [CompositeSingleController::class, 'getEmptySingleRole'])->name('composite-single.empty-single');
// Route::get('/relationship/composite-single/composite-single', [CompositeSingleController::class, 'getCompositeSingleRoles'])->name('composite-single.composite-single');
Route::get('/relationship/composite-single/data-set', [CompositeSingleController::class, 'jsonIndex'])->name('composite-single.jsonIndex');
Route::get('/relationship/composite-single/data-filter-company', [CompositeSingleController::class, 'searchByCompany'])->name('composite-single.filter-company');
Route::resource('/relationship/composite-single', CompositeSingleController::class);

Route::get('/relationship/single-tcode/data-set', [SingleTcodeController::class, 'jsonIndex'])->name('single-tcode.jsonIndex');
Route::get('/relationship/single-tcode/data-filter-company', [SingleTcodeController::class, 'searchByCompany'])->name('single-tcode.filter-company');
Route::resource('/relationship/single-tcode', SingleTcodeController::class);

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
Route::resource('periode', PeriodeController::class);

// Route::get('user-nik/upload', [UserNIKController::class, 'upload'])->name('user-nik.upload.form');
// Route::post('user-nik/upload', [UserNIKImportController::class, 'store'])->name('user-nik.upload.store');
// Route::get('user-nik/upload/preview', [UserNIKImportController::class, 'preview'])->name('user-nik.upload.preview');
// Route::get('user-nik/upload/preview-data', [UserNIKImportController::class, 'getPreviewData'])->name('user-nik.upload.preview_data');
// Route::post('user-nik/update-inline-session', [UserNIKImportController::class, 'updateInlineSession'])->name('user-nik.upload.update-inline-session');
// Route::post('user-nik//upload/submit-single', [UserNIKImportController::class, 'submitSingle'])->name('user-nik.upload.submitSingle');
// Route::post('user-nik//upload/confirm', [UserNIKImportController::class, 'submitAll'])->name('user-nik.upload.confirm');

Route::prefix('user-nik/upload')->name('user-nik.upload.')->group(function () {
    Route::get('/', [UserNIKImportController::class, 'uploadForm'])->name('form');
    Route::post('/', [UserNIKImportController::class, 'store'])->name('store');
    Route::get('/preview', [UserNIKImportController::class, 'preview'])->name('preview');
    Route::get('/preview-data', [UserNIKImportController::class, 'getPreviewData'])->name('preview_data');
    Route::post('/update-inline-session', [UserNIKImportController::class, 'updateInlineSession'])->name('update-inline-session');
    Route::post('/submit-single', [UserNIKImportController::class, 'submitSingle'])->name('submitSingle');
    Route::post('/confirm', [UserNIKImportController::class, 'submitAll'])->name('confirm');
});
// Additional Routes
Route::get('user-nik/mixed', [UserNIKController::class, 'index_mixed'])->name('user-nik.index_mixed');
Route::get('user-nik/check-user-detail', [UserNIKController::class, 'checkUserDetail'])->name('user-nik.check-user-detail');
Route::get('user-nik/download-template', [UserNIKController::class, 'downloadTemplate'])->name('user-nik.download-template');
Route::get('user-nik/compare', [UserNIKController::class, 'compare'])->name('user-nik.compare');
Route::get('user-nik/get-periodic', [UserNIKController::class, 'getPeriodicUserNIK'])->name('user-nik.get-periodic');
// Resource Route
Route::resource('user-nik', UserNIKController::class);

Route::get('terminated-employee/getData', [TerminatedEmployeeController::class, 'getData'])->name('terminated-employee.get-data');
Route::resource('terminated-employee', TerminatedEmployeeController::class);

Route::get('cost-center/user-generic/dashboard', [UserGenericController::class, 'index_dashboard'])->name('dashboard.user-generic');
Route::get('cost-center/user-generic/compare', [UserGenericController::class, 'compare'])->name('user-generic.compare');
Route::get('cost-center/user-generic/get-periodic', [UserGenericController::class, 'getPeriodicGenericUser'])->name('user-generic.get-periodic');
Route::resource('cost-center/user-generic', UserGenericController::class)->name('index', 'user-generic.index');
Route::get('cost-center/prev-user', [CostCenterController::class, 'index_prev_user'])->name('prev-user.index');
Route::resource('cost-center', CostCenterController::class);

Route::get('relationship/nik-job/get-by-periode-id', [NIKJobController::class, 'getNIKJobRolesByPeriodeId'])->name('nik-job.get-by-periode');
Route::resource('relationship/nik-job', NIKJobController::class);

// ------------------ UPLOAD DATA ------------------

Route::prefix('nik-job/upload')->name('nik_job_role.upload.')->group(function () {
    Route::get('/', [NIKJobRoleImportController::class, 'uploadForm'])->name('form');
    Route::post('/', [NIKJobRoleImportController::class, 'store'])->name('store');
    Route::get('/download-template', [NIKJobRoleImportController::class, 'downloadTemplate'])->name('download-template');
    Route::get('/preview', [NIKJobRoleImportController::class, 'preview'])->name('preview');
    Route::get('/preview-data', [NIKJobRoleImportController::class, 'getPreviewData'])->name('preview_data');
    Route::post('/update-inline-session', [UserNIKImportController::class, 'updateInlineSession'])->name('update-inline-session');
    Route::post('/confirm', [NIKJobRoleImportController::class, 'confirmImport'])->name('confirm');
    Route::post('/submit-single', [NIKJobRoleImportController::class, 'submitSingle'])->name('submitSingle');
    Route::post('/submit-all', [NIKJobRoleImportController::class, 'submitAll'])->name('submitAll');
});

Route::prefix('dynamic-upload')->name('dynamic_upload.')->group(function () {
    Route::get('/{module}/upload', [DynamicUploadController::class, 'upload'])->name('upload');
    Route::post('/{module}/upload', [DynamicUploadController::class, 'handleUpload'])->name('handleUpload');
    Route::get('/{module}/preview', [DynamicUploadController::class, 'preview'])->name('preview');
    Route::get('/{module}/preview-data', [DynamicUploadController::class, 'getPreviewData'])->name('preview_data');
    Route::post('/{module}/update-inline', [DynamicUploadController::class, 'updateInlineSession'])->name('update_inline');
    Route::post('/{module}/submit-all', [DynamicUploadController::class, 'submitAll'])->name('submitAll');
});

// ------------------ REPORT ------------------

Route::prefix('report')->name('report.')->group(function () {
    Route::get('/unit', [WorkUnitReportController::class, 'index'])->name('unit');
    Route::get('/unit/grouped-data', [WorkUnitReportController::class, 'groupedJson'])->name('unit.groupedData');
    // Route::get('/unit/nested-data', [WorkUnitReportController::class, 'groupedJson'])->name('unit.nestedData');
    Route::get('/empty-job-role', [EmptyJobRoleController::class, 'index'])->name('empty-job-role.index');
});

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
