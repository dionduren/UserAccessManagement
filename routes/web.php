<?php

use \App\Http\Controllers\IOExcel\USSMJobRoleController;
use App\Http\Controllers\AccessMatrixController;


use App\Http\Controllers\AdminController;
use App\Http\Controllers\DynamicUploadController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PeriodeController;

use App\Http\Controllers\IOExcel\CompanyKompartemenController;
use App\Http\Controllers\IOExcel\CompanyMasterDataController;
use App\Http\Controllers\IOExcel\CompositeRoleSingleRoleController;
use App\Http\Controllers\IOExcel\NIKJobRoleImportController;

// use App\Http\Controllers\IOExcel\ExcelImportController;
use App\Http\Controllers\IOExcel\SingleRoleTcodeController;
use App\Http\Controllers\IOExcel\TcodeSingleRoleController;

use App\Http\Controllers\IOExcel\UserGenericImportController;
use App\Http\Controllers\IOExcel\UserGenericUnitKerjaController;
use App\Http\Controllers\IOExcel\UserNIKImportController;
use App\Http\Controllers\JSONController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\CompositeRoleController;

use App\Http\Controllers\MasterData\CostCenterController;
use App\Http\Controllers\MasterData\CostPrevUserController;
use App\Http\Controllers\MasterData\DepartemenController;
use App\Http\Controllers\MasterData\JobRoleController;
use App\Http\Controllers\MasterData\KompartemenController;
use App\Http\Controllers\MasterData\SingleRoleController;
use App\Http\Controllers\MasterData\TcodeController;
use App\Http\Controllers\MasterData\TerminatedEmployeeController;
use App\Http\Controllers\MasterData\PenomoranUARController;
use App\Http\Controllers\MasterData\UserDetailController;
use App\Http\Controllers\MasterData\UserGenericController;
use App\Http\Controllers\MasterData\UserNIKController;
use App\Http\Controllers\Relationship\CompositeSingleController;
use App\Http\Controllers\Relationship\JobCompositeController;
use App\Http\Controllers\Relationship\NIKJobController;
use App\Http\Controllers\Relationship\SingleTcodeController;

use App\Http\Controllers\Relationship\UserGenericJobRoleController;
use App\Http\Controllers\Report\UARReportController;
use App\Http\Controllers\Report\JobRoleReportController;
use App\Http\Controllers\Report\WorkUnitReportController;
use App\Http\Controllers\TcodeImportController;
use App\Http\Controllers\UserController;

use App\Models\TerminatedEmployee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



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
Route::get('/job-roles/{job_role}/flagged', [JobRoleController::class, 'editFlagged'])->name('job-roles.edit-flagged');
Route::post('/job-roles/{job_role}/flagged', [JobRoleController::class, 'updateFlagged'])->name('job-roles.update-flagged');
Route::post('/job-roles/update-flagged-status', [JobRoleController::class, 'updateFlaggedStatus'])->name('job-roles.update-flagged-status');
Route::get('/job-roles/generate-job-role-id', [JobRoleController::class, 'generateJobRoleId'])->name('job-roles.generate-job-role-id');
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

Route::prefix('/relationship/generic-job-role')->name('user-generic-job-role.')->group(function () {
    Route::get('/', [UserGenericJobRoleController::class, 'index'])->name('index');
    Route::get('/create', [UserGenericJobRoleController::class, 'create'])->name('create');
    Route::post('/', [UserGenericJobRoleController::class, 'store'])->name('store');
    Route::get('/without-job-role', [UserGenericJobRoleController::class, 'indexWithoutJobRole'])->name('null-relationship');
    Route::get('/{id}', [UserGenericJobRoleController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [UserGenericJobRoleController::class, 'edit'])->name('edit');
    Route::post('/{id}/flagged', [UserGenericJobRoleController::class, 'updateFlagged']);
    Route::put('/{id}', [UserGenericJobRoleController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserGenericJobRoleController::class, 'destroy'])->name('destroy');
});

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

Route::get('user-detail/data', [UserDetailController::class, 'getData'])->name('user-detail.getData');
Route::get('user-detail/{userDetail}/flagged-edit', [UserDetailController::class, 'editFlagged'])->name('user-detail.flagged-edit');
Route::post('user-detail/{userDetail}/flagged-edit', [UserDetailController::class, 'updateFlagged'])->name('user-detail.flagged-update');
Route::resource('user-detail', UserDetailController::class);

Route::prefix('user-nik/upload')->name('user-nik.upload.')->group(function () {
    Route::get('/', [UserNIKImportController::class, 'uploadForm'])->name('form');
    Route::post('/', [UserNIKImportController::class, 'store'])->name('store');
    Route::get('/preview', [UserNIKImportController::class, 'preview'])->name('preview');
    Route::get('/preview-data', [UserNIKImportController::class, 'getPreviewData'])->name('preview_data');
    // Route::post('/update-inline-session', [UserNIKImportController::class, 'updateInlineSession'])->name('update-inline-session');
    // Route::post('/submit-single', [UserNIKImportController::class, 'submitSingle'])->name('submitSingle');
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

Route::prefix('user-generic-unit-kerja')->name('user-generic-unit-kerja.')->group(function () {
    Route::get('upload', [UserGenericUnitKerjaController::class, 'uploadForm'])->name('upload');
    Route::post('preview', [UserGenericUnitKerjaController::class, 'preview'])->name('preview');
    Route::get('preview-page', [UserGenericUnitKerjaController::class, 'previewPage'])->name('previewPage');
    Route::get('get-preview-data', [UserGenericUnitKerjaController::class, 'getPreviewData'])->name('getPreviewData');
    Route::post('confirm-import', [UserGenericUnitKerjaController::class, 'confirmImport'])->name('confirmImport');
});

Route::prefix('user-generic')->name('user-generic.')->group(function () {
    Route::get('upload', [UserGenericImportController::class, 'uploadForm'])->name('upload');
    Route::post('preview', [UserGenericImportController::class, 'preview'])->name('preview');
    // Route::get('download-template', [UserGenericImportController::class, 'downloadTemplate'])->name('downloadTemplate');
    Route::get('preview', [UserGenericImportController::class, 'previewPage'])->name('previewPage');
    Route::get('preview-data', [UserGenericImportController::class, 'getPreviewData'])->name('getPreviewData');
    Route::post('import', [UserGenericImportController::class, 'confirmImport'])->name('confirmImport');
});

Route::get('terminated-employee/getData', [TerminatedEmployeeController::class, 'getData'])->name('terminated-employee.get-data');
Route::resource('terminated-employee', TerminatedEmployeeController::class);

Route::get('user-generic/dashboard', [UserGenericController::class, 'index_dashboard'])->name('dashboard.user-generic');
Route::get('user-generic/compare', [UserGenericController::class, 'compare'])->name('user-generic.compare');
Route::get('user-generic/get-periodic', [UserGenericController::class, 'getPeriodicGenericUser'])->name('user-generic.get-periodic');
Route::get('user-generic/flagged-edit/{id}', [UserGenericController::class, 'editFlagged'])->name('user-generic.flagged-edit');
Route::post('user-generic/flagged-edit/{id}', [UserGenericController::class, 'updateFlagged'])->name('user-generic.flagged-update');
Route::resource('user-generic', UserGenericController::class)->name('index', 'user-generic.index');

Route::get('cost-center/prev-user', [CostCenterController::class, 'index_prev_user'])->name('prev-user.index');
Route::put('cost-center/prev-user/update', [CostCenterController::class, 'update_prev_user'])->name('prev-user.update');
Route::get('cost-center/prev-user/{id}/edit', [CostCenterController::class, 'edit_prev_user'])->name('prev-user.edit');
Route::put('cost-center/prev-user/full-update', [CostCenterController::class, 'full_update_prev_user'])->name('prev-user.full-update');
Route::resource('cost-center', CostCenterController::class);

Route::get('relationship/nik-job/get-by-periode-id', [NIKJobController::class, 'getNIKJobRolesByPeriodeId'])->name('nik-job.get-by-periode');
Route::get('relationship/nik-job/without-job-role', [NIKJobController::class, 'indexWithoutJobRole'])->name('nik-job.null-relationship');
Route::resource('relationship/nik-job', NIKJobController::class);

// ------------------ UPLOAD DATA ------------------

// Route::prefix('nik-job/upload')->name('nik_job_role.upload.')->group(function () {
//     Route::get('/', [NIKJobRoleImportController::class, 'uploadForm'])->name('form');
//     Route::post('/', [NIKJobRoleImportController::class, 'store'])->name('store');
//     Route::get('/download-template', [NIKJobRoleImportController::class, 'downloadTemplate'])->name('download-template');
//     Route::get('/preview', [NIKJobRoleImportController::class, 'preview'])->name('preview');
//     Route::get('/preview-data', [NIKJobRoleImportController::class, 'getPreviewData'])->name('preview_data');
//     Route::post('/update-inline-session', [UserNIKImportController::class, 'updateInlineSession'])->name('update-inline-session');
//     Route::post('/confirm', [NIKJobRoleImportController::class, 'confirmImport'])->name('confirm');
//     Route::post('/submit-single', [NIKJobRoleImportController::class, 'submitSingle'])->name('submitSingle');
//     Route::post('/submit-all', [NIKJobRoleImportController::class, 'submitAll'])->name('submitAll');
// });

Route::prefix('dynamic-upload')->name('dynamic_upload.')->group(function () {
    Route::get('/{module}/upload', [DynamicUploadController::class, 'upload'])->name('upload');
    Route::post('/{module}/upload', [DynamicUploadController::class, 'handleUpload'])->name('handleUpload');

    Route::get('/{module}/preview', [DynamicUploadController::class, 'preview'])->name('preview');
    Route::get('/{module}/preview-data', [DynamicUploadController::class, 'getPreviewData'])->name('preview_data');

    Route::post('/{module}/submit-all', [DynamicUploadController::class, 'submitAll'])->name('submitAll');
});

Route::prefix('ussm-job-role')->name('ussm-job-role.')->group(function () {
    Route::get('/upload', [USSMJobRoleController::class, 'uploadForm'])->name('upload');
    Route::post('/preview', [USSMJobRoleController::class, 'preview'])->name('preview');
    Route::get('/preview-page', [USSMJobRoleController::class, 'previewPage'])->name('previewPage');
    Route::get('/preview-data', [USSMJobRoleController::class, 'getPreviewData'])->name('previewData');
    Route::post('/confirm-import', [USSMJobRoleController::class, 'confirmImport'])->name('confirmImport');
});

// ------------------ REPORT ------------------

Route::prefix('report')->name('report.')->group(function () {
    Route::get('/unit', [WorkUnitReportController::class, 'index'])->name('unit');
    Route::get('/unit/grouped-data', [WorkUnitReportController::class, 'groupedJson'])->name('unit.groupedData');
    // Route::get('/unit/nested-data', [WorkUnitReportController::class, 'groupedJson'])->name('unit.nestedData');
    Route::get('/filled-job-role', [JobRoleReportController::class, 'index'])->name('filled-job-role.index');
    Route::get('/empty-job-role', [JobRoleReportController::class, 'index_empty'])->name('empty-job-role.index');
});

Route::prefix('report/uar')->name('report.uar.')->group(function () {
    Route::get('/', [UARReportController::class, 'index'])->name('index');
    Route::get('/get-kompartemen', [UARReportController::class, 'getKompartemen'])->name('get-kompartemen');
    Route::get('/get-departemen', [UARReportController::class, 'getDepartemen'])->name('get-departemen');
    Route::get('/job-roles', [UARReportController::class, 'jobRolesData'])->name('job-roles');
    Route::get('/export-word', [UARReportController::class, 'exportWord'])->name('export-word');
    // Route::get('/download', [UARReportController::class, 'download'])->name('download');
});

// ------------------ PENOMORAN UAR ------------------
Route::get('penomoran-uar/check-number', [PenomoranUARController::class, 'checkNumber'])->name('penomoran-uar.checkNumber');
Route::resource('penomoran-uar', PenomoranUARController::class);

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
