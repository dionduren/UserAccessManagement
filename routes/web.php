<?php

use App\Http\Controllers\AccessMatrixController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DynamicUploadController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\JSONController;
use App\Http\Controllers\TcodeImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CheckpointController;

use App\Http\Controllers\Auth\EmailChangeRequestController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\IOExcel\CompanyKompartemenController;
use App\Http\Controllers\IOExcel\CompanyMasterDataController;
use App\Http\Controllers\IOExcel\CompositeRoleSingleRoleController;
use App\Http\Controllers\IOExcel\NIKJobRoleImportController;
use App\Http\Controllers\IOExcel\SingleRoleTcodeController;
use App\Http\Controllers\IOExcel\TcodeSingleRoleController;
use App\Http\Controllers\IOExcel\UserGenericImportController;
use App\Http\Controllers\IOExcel\UserGenericUnitKerjaController;
use App\Http\Controllers\IOExcel\UserNIKImportController;
use App\Http\Controllers\IOExcel\USSMJobRoleController;
use App\Http\Controllers\IOExcel\UserSystemImportController;

use App\Http\Controllers\IOExcel\WorkUnitImportController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\CompositeRoleController;
use App\Http\Controllers\MasterData\CostCenterController;
// use App\Http\Controllers\IOExcel\ExcelImportController;
use App\Http\Controllers\MasterData\CostPrevUserController;
use App\Http\Controllers\MasterData\DepartemenController;
use App\Http\Controllers\MasterData\JobRoleController;
use App\Http\Controllers\MasterData\KompartemenController;
use App\Http\Controllers\MasterData\PenomoranUAMController;
use App\Http\Controllers\MasterData\PenomoranUARController;
use App\Http\Controllers\MasterData\SingleRoleController;
use App\Http\Controllers\MasterData\TcodeController;
use App\Http\Controllers\MasterData\TerminatedEmployeeController;
use App\Http\Controllers\MasterData\UserDetailController;
use App\Http\Controllers\MasterData\UserGenericController;
use App\Http\Controllers\MasterData\UserSystemController;
use App\Http\Controllers\MasterData\UserNIKController;
use App\Http\Controllers\MasterData\UIDNIKUnitKerjaController;
use App\Http\Controllers\MasterData\UIDGenericUnitKerjaController;
use App\Http\Controllers\MasterData\MasterDataKaryawanLocalController;

use App\Http\Controllers\MasterData\Compare\UnitKerjaCompareController;
use App\Http\Controllers\MasterData\Compare\UAMCompareController;
use App\Http\Controllers\MasterData\Compare\UAMRelationshipCompareController;
use App\Http\Controllers\MasterData\Compare\USMMCompareController;

use App\Http\Controllers\Middle_DB\MasterDataKaryawanController;
use App\Http\Controllers\Middle_DB\UnitKerjaController;
use App\Http\Controllers\Middle_DB\MasterUSMMController;
use App\Http\Controllers\Middle_DB\UAMViewsController;
use App\Http\Controllers\Middle_DB\CompositeRoleCompareController;
use App\Http\Controllers\Middle_DB\DuplicateNameController;
use App\Http\Controllers\Middle_DB\GenericKaryawanMappingMidDBController;
use App\Http\Controllers\Middle_DB\UAMComponentController;
use App\Http\Controllers\Middle_DB\raw\UAMRelationshipRawController;
use App\Http\Controllers\Middle_DB\raw\GenericKaryawanMappingRawController;
use App\Http\Controllers\Middle_DB\import\ImportUserNIKUnitKerjaController;
use App\Http\Controllers\Middle_DB\import\ImportUnitKerjaController;
use App\Http\Controllers\Middle_DB\import\ImportUAMController;
use App\Http\Controllers\Middle_DB\import\ImportUserIDController;

use App\Http\Controllers\Relationship\CompositeSingleController;
use App\Http\Controllers\Relationship\JobCompositeController;
use App\Http\Controllers\Relationship\NIKJobController;
use App\Http\Controllers\Relationship\SingleTcodeController;
use App\Http\Controllers\Relationship\UserGenericJobRoleController;
use App\Http\Controllers\Relationship\LocalUAMRelationshipController;

use App\Http\Controllers\Report\JobRoleReportController;
use App\Http\Controllers\Report\UAMReportController;
use App\Http\Controllers\Report\UARReportController;
use App\Http\Controllers\Report\WorkUnitReportController;
use App\Http\Controllers\Report\BAPenarikanDataController;
use App\Http\Controllers\Report\AnomaliDataReportController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::middleware('guest')->group(function () {
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('password/reset', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('/', '/home'); // tanpa name()
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/home/empty/jobRolesComposite', [HomeController::class, 'getJobRolesCompositeEmpty'])->name('home.empty.jobRolesComposite');
    Route::get('/home/empty/compositeRolesJob', [HomeController::class, 'getCompositeRolesJobEmpty'])->name('home.empty.compositeRolesJob');
    Route::get('/home/empty/compositeRolesSingle', [HomeController::class, 'getCompositeRolesSingleEmpty'])->name('home.empty.compositeRolesSingle');
    Route::get('/home/empty/singleRolesComposite', [HomeController::class, 'getSingleRolesCompositeEmpty'])->name('home.empty.singleRolesComposite');
    Route::get('/home/empty/singleRolesTcode', [HomeController::class, 'getSingleRolesTcodeEmpty'])->name('home.empty.singleRolesTcode');
    Route::get('/home/empty/tcodesSingle', [HomeController::class, 'getTcodesSingleEmpty'])->name('home.empty.tcodesSingle');
    Route::get('/home/empty/nik-job', [HomeController::class, 'getNikJobEmpty'])->name('home.empty.nikJob');
    Route::get('/home/empty/generic-job', [HomeController::class, 'getGenericJobEmpty'])->name('home.empty.genericJob');


    // SQL SERVER CONNECTION
    // Route::get('/dblib/ping', function () {
    //     // TEST 1 - Ping to check profile
    //     // DB::connection('sqlsrv_ext')->getPdo(); // throws if fails
    //     // return DB::connection('sqlsrv_ext')->selectOne('SELECT SUSER_SNAME() AS login, DB_NAME() AS db');

    //     // TEST 2 - Get Top 20 data
    //     // $rows = DB::connection('sqlsrv_ext')
    //     //     ->table('dbo.BASIS_KARYAWAN')   // schema.view
    //     //     ->limit(20)
    //     //     ->get();

    //     // return $rows;

    //     // TEST 3 - create view
    //     // $schema = 'dbo';
    //     // $view   = 'UAR_Unit_Kerja';
    //     // $sql    = "SELECT DISTINCT
    //     //         company,
    //     //         dir_id as direktorat_id,
    //     //         dir_title as direktorat,
    //     //         komp_id as kompartemen_id,
    //     //         komp_title as kompartemen,
    //     //         dept_id as departement_id,
    //     //         dept_title as departemen,
    //     //         cc_code as cost_center
    //     //        FROM BASIS_KARYAWAN"; // removed ORDER BY

    //     // $conn = DB::connection('sqlsrv_ext');
    //     // $conn->statement("IF OBJECT_ID(N'{$schema}.{$view}', N'V') IS NOT NULL DROP VIEW [{$schema}].[{$view}]");
    //     // $conn->statement("CREATE VIEW [{$schema}].[{$view}] AS {$sql}");
    //     // return 'OK';

    //     // TEST 4 - check the table
    //     $rows = DB::connection('sqlsrv_freetds')
    //         ->table('dbo.BASIS_KARYAWAN')
    //         ->distinct()
    //         ->select([
    //             'company',
    //             DB::raw('dir_id  as direktorat_id'),
    //             DB::raw('dir_title as direktorat'),
    //             DB::raw('komp_id as kompartemen_id'),
    //             DB::raw('komp_title as kompartemen'),
    //             DB::raw('dept_id as departement_id'),
    //             DB::raw('dept_title as departemen'),
    //             DB::raw('cc_code as cost_center'),
    //         ])
    //         ->orderBy('company')
    //         ->orderBy('dir_id')
    //         ->orderBy('komp_id')
    //         ->orderBy('dept_id')
    //         ->get();

    //     // Simple JSON response:
    //     return response()->json($rows);
    // });

    // ======== PROGRESS CHECKPOINT ===========
    Route::get('/checkpoints', [CheckpointController::class, 'index'])->name('checkpoints.index');
    Route::post('/checkpoints/refresh', [CheckpointController::class, 'refresh'])->name('checkpoints.refresh');


    // ======== USER PROFILE ===========
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::post('/profile/email-request', [ProfileController::class, 'requestEmailChange'])->name('profile.requestEmailChange');

    // User submits an email change request
    Route::post('/account/email-change', [EmailChangeRequestController::class, 'store'])->name('account.email-change.store');


    // Route::get('/test-mail', function () {
    //     try {
    //         $smtp = config('mail.mailers.smtp');
    //         $from = config('mail.from');

    //         // Quick socket test
    //         $host = $smtp['host'] ?? 'sandbox.smtp.mailtrap.io';
    //         $port = $smtp['port'] ?? 2525;
    //         $errno = 0;
    //         $errstr = '';
    //         $fp = @fsockopen($host, $port, $errno, $errstr, 10);
    //         if (!$fp) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'TCP connect failed',
    //                 'host' => $host,
    //                 'port' => $port,
    //                 'error' => "$errno: $errstr",
    //                 'smtp' => $smtp,
    //                 'from' => $from,
    //             ], 500);
    //         }
    //         fclose($fp);

    //         // Send test mail
    //         $fromAddress = $from['address'] ?? env('MAIL_FROM_ADDRESS', 'no-reply@example.com');
    //         $fromName    = $from['name'] ?? env('MAIL_FROM_NAME', 'Mailer');

    //         Mail::mailer('smtp')->raw('Mailtrap SMTP test from UAM app.', function ($m) use ($fromAddress, $fromName) {
    //             $m->from($fromAddress, $fromName);
    //             $m->to('anyone@example.com')->subject('SMTP Test');
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Test email sent (check Mailtrap).',
    //             'smtp' => $smtp,
    //             'from' => $from,
    //         ]);
    //     } catch (\Throwable $e) {
    //         \Log::error('Failed to send test email: ' . $e->getMessage(), ['exception' => $e]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to send test email.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // });

    // ======= MASTER DATA COMPANY ======= 
    Route::resource('companies', CompanyController::class);
    // Route::resource('companies', CompanyController::class)->middleware('permission:view company|edit company|delete company');

    Route::resource('kompartemens', KompartemenController::class);
    Route::resource('departemens', DepartemenController::class);


    Route::get('/job-roles/data', [JobRoleController::class, 'getJobRoles'])->name('job-roles.data');

    Route::get('/job-roles/export', [JobRoleController::class, 'exportUserId'])->name('job-roles.export');
    Route::get('/job-roles/{job_role}/flagged', [JobRoleController::class, 'editFlagged'])->name('job-roles.edit-flagged');
    Route::post('/job-roles/{job_role}/flagged', [JobRoleController::class, 'updateFlagged'])->name('job-roles.update-flagged');
    Route::post('/job-roles/update-flagged-status', [JobRoleController::class, 'updateFlaggedStatus'])->name('job-roles.update-flagged-status');
    Route::get('/job-roles/generate-job-role-id', [JobRoleController::class, 'generateJobRoleId'])->name('job-roles.generate-job-role-id');
    Route::get('/job-roles/export-flagged', [JobRoleController::class, 'exportFlagged'])->name('job-roles.export-flagged');
    Route::delete('/job-roles/bulk-destroy', [JobRoleController::class, 'bulkDestroy'])->name('job-roles.bulk-delete')->middleware('can:Super User');
    Route::resource('job-roles', JobRoleController::class);

    Route::get('/get-kompartemen', [KompartemenController::class, 'getKompartemenByCompany']);
    Route::get('/get-departemen', [DepartemenController::class, 'getDepartemenByKompartemen']);
    Route::get('/get-departemen-by-company', [DepartemenController::class, 'getDepartemenByCompany']);
    Route::get('/get-job-roles', [JobRoleController::class, 'getJobRoles'])->name('job-roles.getData');

    // Upload Company Master Data

    Route::get('/upload/unit-kerja', [CompanyMasterDataController::class, 'showForm'])->name('unit-kerja.upload-form');
    Route::post('/upload/unit-kerja', [CompanyMasterDataController::class, 'upload'])->name('unit-kerja.upload');
    Route::get('/company-master-data/export', [CompanyMasterDataController::class, 'downloadMasterData'])->name('company_master_data.export');


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
    Route::get('/relationship/job-composite/export', [JobCompositeController::class, 'export'])->name('job-composite.export');
    Route::get('/relationship/job-composite/empty-composite', [JobCompositeController::class, 'getEmptyCompositeRole'])->name('job-composite.empty-composite');
    Route::get('/relationship/job-composite/company-composite', [JobCompositeController::class, 'getCompositeFilterCompany'])->name('job-composite.company-composite');
    Route::get('/relationship/job-composite/flagged/export', [JobCompositeController::class, 'exportFlagged'])->name('job-composite.export-flagged');
    Route::resource('/relationship/job-composite', JobCompositeController::class);

    // Route::get('/relationship/composite-single/data', [CompositeSingleController::class, 'getSingleRoles'])->name('composite-single.jsonIndex');
    // Route::get('/relationship/composite-single/empty-single', [CompositeSingleController::class, 'getEmptySingleRole'])->name('composite-single.empty-single');
    // Route::get('/relationship/composite-single/composite-single', [CompositeSingleController::class, 'getCompositeSingleRoles'])->name('composite-single.composite-single');
    Route::get('/relationship/composite-single/data', [CompositeSingleController::class, 'datatable'])->name('composite-single.datatable');
    // Route::get('/relationship/composite-single/data-set', [CompositeSingleController::class, 'jsonIndex'])->name('composite-single.jsonIndex');
    Route::get('/relationship/composite-single/data-filter-company', [CompositeSingleController::class, 'searchByCompany'])->name('composite-single.filter-company');
    Route::resource('/relationship/composite-single', CompositeSingleController::class);

    Route::prefix('/relationship/composite-ao')->name('composite_ao.')->group(function () {
        Route::get('/', [CompositeSingleController::class, 'index_ao'])->name('index');
        Route::get('/datatable', [CompositeSingleController::class, 'datatable_ao'])->name('datatable');
        Route::delete('/{id}', [CompositeSingleController::class, 'destroy_ao'])->name('destroy');
    });

    // Route::get('/relationship/single-tcode/data-set', [SingleTcodeController::class, 'jsonIndex'])->name('single-tcode.jsonIndex');
    Route::get('single-tcode/datatable', [SingleTcodeController::class, 'datatable'])->name('single-tcode.datatable');
    Route::get('/relationship/single-tcode/data-filter-company', [SingleTcodeController::class, 'searchByCompany'])->name('single-tcode.filter-company');
    Route::resource('/relationship/single-tcode', SingleTcodeController::class);

    Route::prefix('/relationship/generic-job-role')->name('user-generic-job-role.')->group(function () {
        Route::get('/', [UserGenericJobRoleController::class, 'index'])->name('index');
        Route::get('/create', [UserGenericJobRoleController::class, 'create'])->name('create');
        Route::post('/', [UserGenericJobRoleController::class, 'store'])->name('store');
        Route::get('/without-job-role', [UserGenericJobRoleController::class, 'indexWithoutJobRole'])->name('null-relationship');
        Route::get('/without-job-role/export', [UserGenericJobRoleController::class, 'exportWithoutJobRole'])->name('without.export');
        Route::get('/{id}', [UserGenericJobRoleController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserGenericJobRoleController::class, 'edit'])->name('edit');
        Route::post('/{id}/flagged', [UserGenericJobRoleController::class, 'updateFlagged']);
        Route::put('/{id}', [UserGenericJobRoleController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserGenericJobRoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('/relationship/uam')->name('relationship.uam.')->group(function () {
        Route::get('/', [LocalUAMRelationshipController::class, 'index'])->name('index');
        Route::get('/data', [LocalUAMRelationshipController::class, 'data'])->name('data');
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
    Route::get('/company-kompartemen/template', [CompanyKompartemenController::class, 'downloadTemplate'])->name('company_kompartemen.template');

    Route::get('/composite-role-single-role/upload', [CompositeRoleSingleRoleController::class, 'uploadForm'])->name('composite_single.upload');
    Route::post('/composite-role-single-role/preview', [CompositeRoleSingleRoleController::class, 'preview'])->name('composite_single.preview');
    Route::get('/composite-role-single-role/preview-data', [CompositeRoleSingleRoleController::class, 'getPreviewData'])->name('composite_single.preview_data');
    Route::post('/composite-role-single-role/confirm', [CompositeRoleSingleRoleController::class, 'confirmImport'])->name('composite_single.confirm');
    Route::get('/composite-role-single-role/template', [CompositeRoleSingleRoleController::class, 'downloadTemplate'])->name('composite_single.template');

    Route::get('/tcode-single-role/upload', [SingleRoleTcodeController::class, 'uploadForm'])->name('tcode_single_role.upload');
    Route::post('/tcode-single-role/preview', [SingleRoleTcodeController::class, 'preview'])->name('tcode_single_role.preview');
    Route::get('/tcode-single-role/preview-data', [SingleRoleTcodeController::class, 'getPreviewData'])->name('tcode_single_role.preview_data');
    Route::post('/tcode-single-role/confirm', [SingleRoleTcodeController::class, 'confirmImport'])->name('tcode_single_role.confirm');
    Route::get('/tcode-single-role/template', [SingleRoleTcodeController::class, 'downloadTemplate'])->name('tcode_single_role.template');


    // ------------------ NEW MASTER DATA ------------------
    Route::delete('/periode/{periode}', [PeriodeController::class, 'destroy'])->name('periode.destroy');
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
    Route::get('user-nik/middle-db', [MasterUSMMController::class, 'activeNIK'])->name('user-nik.middle_db');
    // Resource Route
    Route::resource('user-nik', UserNIKController::class);

    Route::prefix('user-generic-unit-kerja')->name('user-generic-unit-kerja.')->group(function () {
        Route::get('upload', [UserGenericUnitKerjaController::class, 'uploadForm'])->name('upload');
        Route::post('preview', [UserGenericUnitKerjaController::class, 'preview'])->name('preview');
        Route::get('preview-page', [UserGenericUnitKerjaController::class, 'previewPage'])->name('previewPage');
        Route::get('get-preview-data', [UserGenericUnitKerjaController::class, 'getPreviewData'])->name('getPreviewData');
        Route::post('confirm-import', [UserGenericUnitKerjaController::class, 'confirmImport'])->name('confirmImport');
        Route::get('download-template', [UserGenericUnitKerjaController::class, 'downloadTemplate'])->name('downloadTemplate');
    });

    Route::prefix('user-generic')->name('user-generic.')->group(function () {
        Route::get('upload', [UserGenericImportController::class, 'uploadForm'])->name('upload');
        Route::post('preview', [UserGenericImportController::class, 'preview'])->name('preview');
        Route::get('download-template', [UserGenericImportController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::get('preview', [UserGenericImportController::class, 'previewPage'])->name('previewPage');
        Route::get('preview-data', [UserGenericImportController::class, 'getPreviewData'])->name('getPreviewData');
        Route::post('import', [UserGenericImportController::class, 'confirmImport'])->name('confirmImport');
        Route::get('/middle-db',      [MasterUSMMController::class, 'activeGeneric'])->name('middle_db');
    });

    Route::get('terminated-employee/getData', [TerminatedEmployeeController::class, 'getData'])->name('terminated-employee.get-data');
    Route::resource('terminated-employee', TerminatedEmployeeController::class);

    Route::get('user-generic/dashboard', [UserGenericController::class, 'index_dashboard'])->name('dashboard.user-generic');
    Route::get('user-generic/compare', [UserGenericController::class, 'compare'])->name('user-generic.compare');
    Route::get('user-generic/get-periodic', [UserGenericController::class, 'getPeriodicGenericUser'])->name('user-generic.get-periodic');
    Route::get('user-generic/flagged-edit/{id}', [UserGenericController::class, 'editFlagged'])->name('user-generic.flagged-edit');
    Route::post('user-generic/flagged-edit/{id}', [UserGenericController::class, 'updateFlagged'])->name('user-generic.flagged-update');
    Route::resource('user-generic', UserGenericController::class)->name('index', 'user-generic.index');

    Route::get('user-system/flagged/{id}/edit', [UserSystemController::class, 'editFlagged'])->name('user-system.flagged-edit');
    Route::post('user-system/flagged/{id}',     [UserSystemController::class, 'updateFlag'])->name('user-system.flagged-update');
    Route::resource('user-system',               UserSystemController::class)->except(['show', 'create', 'store']);

    Route::get('user-system/import',            [UserSystemImportController::class, 'index'])->name('user_system.import.index');
    Route::get('user-system/import/template',   [UserSystemImportController::class, 'template'])->name('user_system.import.template');
    Route::get('user-system/import/preview',    [UserSystemImportController::class, 'previewGet'])->name('user_system.import.preview.get');
    Route::post('user-system/import/preview',   [UserSystemImportController::class, 'preview'])->name('user_system.import.preview');
    Route::post('user-system/import/confirm',   [UserSystemImportController::class, 'confirm'])->name('user_system.import.confirm');

    Route::get('cost-center/prev-user', [CostCenterController::class, 'index_prev_user'])->name('prev-user.index');
    Route::put('cost-center/prev-user/update', [CostCenterController::class, 'update_prev_user'])->name('prev-user.update');
    Route::get('cost-center/prev-user/{id}/edit', [CostCenterController::class, 'edit_prev_user'])->name('prev-user.edit');
    Route::put('cost-center/prev-user/full-update', [CostCenterController::class, 'full_update_prev_user'])->name('prev-user.full-update');
    Route::resource('cost-center', CostCenterController::class);

    Route::get('relationship/nik-job/users-by-periode', [NIKJobController::class, 'usersByPeriode'])->name('nik-job.users-by-periode');
    Route::get('relationship/nik-job/get-by-periode-id', [NIKJobController::class, 'getNIKJobRolesByPeriodeId'])->name('nik-job.get-by-periode');
    Route::get('relationship/nik-job/without-job-role', [NIKJobController::class, 'indexWithoutJobRole'])->name('nik-job.null-relationship');
    Route::get('relationship/nik-job/without-job-role/export', [NIKJobController::class, 'exportWithoutJobRole'])->name('nik-job.without.export');
    Route::resource('relationship/nik-job', NIKJobController::class);

    Route::prefix('karyawan-unit-kerja')->name('karyawan_unit_kerja.')->group(function () {
        Route::get('/', [MasterDataKaryawanLocalController::class, 'index'])->name('index');
        Route::get('/data', [MasterDataKaryawanLocalController::class, 'data'])->name('data');
        Route::get('/create', [MasterDataKaryawanLocalController::class, 'create'])->name('create');
        Route::post('/', [MasterDataKaryawanLocalController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [MasterDataKaryawanLocalController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MasterDataKaryawanLocalController::class, 'update'])->name('update');
        Route::delete('/{id}', [MasterDataKaryawanLocalController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('unit-kerja')->name('unit_kerja.')->group(function () {
        // User NIK
        Route::get('user-nik', [UIDNIKUnitKerjaController::class, 'index'])->name('user_nik.index');
        Route::get('user-nik/data', [UIDNIKUnitKerjaController::class, 'index'])->name('user_nik.data'); // returns JSON when requested via Accept: application/json
        Route::get('user-nik/without', [UIDNIKUnitKerjaController::class, 'withoutUnitKerja'])->name('user_nik.without');
        Route::get('user-nik/without/export', [UIDNIKUnitKerjaController::class, 'exportWithoutUnitKerja'])->name('user_nik.without.export');
        Route::get('user-nik/create', [UIDNIKUnitKerjaController::class, 'create'])->name('user_nik.create');
        Route::post('user-nik', [UIDNIKUnitKerjaController::class, 'store'])->name('user_nik.store');
        Route::get('user-nik/{userNIKUnitKerja}', [UIDNIKUnitKerjaController::class, 'show'])->name('user_nik.show');
        Route::get('user-nik/{userNIKUnitKerja}/edit', [UIDNIKUnitKerjaController::class, 'edit'])->name('user_nik.edit');
        Route::put('user-nik/{userNIKUnitKerja}', [UIDNIKUnitKerjaController::class, 'update'])->name('user_nik.update');
        Route::delete('user-nik/{userNIKUnitKerja}', [UIDNIKUnitKerjaController::class, 'destroy'])->name('user_nik.destroy');

        // User Generic
        // --- STATIC AJAX ENDPOINTS FIRST (avoid collision with {userGenericUnitKerja}) ---
        Route::get('user-generic/company-structure', [UIDGenericUnitKerjaController::class, 'companyStructure'])->name('user_generic.company_structure');
        Route::get('user-generic/search-users', [UIDGenericUnitKerjaController::class, 'searchUsers'])->name('user_generic.search_users');
        Route::get('user-generic/without', [UIDGenericUnitKerjaController::class, 'without'])->name('user_generic.without');
        Route::get('user-generic/without', [UIDGenericUnitKerjaController::class, 'without'])->name('user_generic.without');
        Route::get('user-generic/without/export', [UIDGenericUnitKerjaController::class, 'exportWithout'])->name('user_generic.without.export');

        // --- LIST/JSON INDEX ---
        Route::get('user-generic', [UIDGenericUnitKerjaController::class, 'index'])->name('user_generic.index');
        Route::get('user-generic/data', [UIDGenericUnitKerjaController::class, 'index'])->name('user_generic.data');

        // --- CREATE/STORE ---
        Route::get('user-generic/create', [UIDGenericUnitKerjaController::class, 'create'])->name('user_generic.create');
        Route::post('user-generic', [UIDGenericUnitKerjaController::class, 'store'])->name('user_generic.store');

        // --- DYNAMIC ROUTES (CONSTRAINED TO NUMBERS) ---
        Route::get('user-generic/{userGenericUnitKerja}', [UIDGenericUnitKerjaController::class, 'show'])->whereNumber('userGenericUnitKerja')->name('user_generic.show');
        Route::get('user-generic/{userGenericUnitKerja}/edit', [UIDGenericUnitKerjaController::class, 'edit'])->whereNumber('userGenericUnitKerja')->name('user_generic.edit');
        Route::put('user-generic/{userGenericUnitKerja}', [UIDGenericUnitKerjaController::class, 'update'])->whereNumber('userGenericUnitKerja')->name('user_generic.update');
        Route::delete('user-generic/{userGenericUnitKerja}', [UIDGenericUnitKerjaController::class, 'destroy'])->whereNumber('userGenericUnitKerja')->name('user_generic.destroy');
    });

    // ------------------ IMPORT DATA MIDDLE DB ------------------
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/user-nik-unit-kerja', [ImportUserNIKUnitKerjaController::class, 'index'])->name('nik_unit_kerja.index');
        Route::get('/user-nik-unit-kerja/data', [ImportUserNIKUnitKerjaController::class, 'data'])->name('nik_unit_kerja.data'); // expects ?periode_id=
        Route::post('/user-nik-unit-kerja/import', [ImportUserNIKUnitKerjaController::class, 'import'])->name('nik_unit_kerja.import');
        Route::post('/nik-unit-kerja/import-all', [ImportUserNIKUnitKerjaController::class, 'importAll'])->name('nik_unit_kerja.import_all');

        Route::get('/unit-kerja', [ImportUnitKerjaController::class, 'index'])->name('unit_kerja.index');
        Route::post('/unit-kerja/sync', [ImportUnitKerjaController::class, 'sync'])->name('unit_kerja.sync');
        Route::post('/unit-kerja/karyawan-sync', [ImportUnitKerjaController::class, 'karyawanSync'])->name('unit_kerja.karyawan.sync');

        Route::get('/uam', [ImportUAMController::class, 'index'])->name('uam.index');
        Route::post('/uam/sync', [ImportUAMController::class, 'sync_all'])->name('uam.sync');
        Route::post('/uam/composite-roles', [ImportUAMController::class, 'sync_composite_roles'])->name('uam.composite_roles');
        Route::post('/uam/composite_ao', [ImportUAMController::class, 'sync_ao'])->name('uam.composite_ao');
        Route::post('/uam/composite-role-single-roles', [ImportUAMController::class, 'sync_composite_role_single_roles'])->name('uam.composite_role_single_roles');
        Route::post('/uam/single-roles', [ImportUAMController::class, 'sync_single_roles'])->name('uam.single_roles');
        Route::post('/uam/single-role-tcodes', [ImportUAMController::class, 'sync_single_role_tcodes'])->name('uam.single_role_tcodes');
        Route::post('/uam/tcodes', [ImportUAMController::class, 'sync_tcodes'])->name('uam.tcodes');

        Route::get('/user-id', [ImportUserIDController::class, 'index'])->name('user_id.index');
        Route::post('/user-id/sync', [ImportUserIDController::class, 'sync'])->name('user_id.sync');
    });
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
        Route::get('/imports/ussm-job-role/template', [USSMJobRoleController::class, 'downloadTemplate'])->name('template');
    });

    // ------------------ SYNC DATA - MIDDLE DB ------------------

    Route::prefix('middle-db')->name('middle_db.')->group(function () {

        Route::prefix('master-data-karyawan')->name('master_data_karyawan.')->group(function () {
            Route::get('/',      [MasterDataKaryawanController::class, 'index'])->name('index');
            Route::get('/data',  [MasterDataKaryawanController::class, 'data'])->name('data');
            Route::post('/sync', [MasterDataKaryawanController::class, 'sync'])->name('sync');

            Route::get('/duplicates',        [DuplicateNameController::class, 'index'])->name('duplicates.index');
            Route::get('/duplicates/data',   [DuplicateNameController::class, 'data'])->name('duplicates.data');
            Route::post('/duplicates/store', [DuplicateNameController::class, 'store'])->name('duplicates.store');
        });

        Route::prefix('unit-kerja')->name('unit_kerja.')->group(function () {
            Route::get('/',      [UnitKerjaController::class, 'index'])->name('index');
            Route::get('/data',  [UnitKerjaController::class, 'data'])->name('data');
            Route::post('/sync', [UnitKerjaController::class, 'sync'])->name('sync');
        });

        Route::prefix('usmm')->name('usmm.')->group(function () {
            Route::get('/',              [MasterUSMMController::class, 'index'])->name('index');
            Route::get('/data',          [MasterUSMMController::class, 'data'])->name('data');

            Route::get('/active-generic',      [MasterUSMMController::class, 'activeGeneric'])->name('activeGeneric');
            Route::get('/active-generic/data', [MasterUSMMController::class, 'activeGenericData'])->name('activeGenericData');

            Route::get('/active-nik',      [MasterUSMMController::class, 'activeNIK'])->name('activeNIK');
            Route::get('/active-nik/data', [MasterUSMMController::class, 'activeNIKData'])->name('activeNIKData');

            Route::get('/inactive',      [MasterUSMMController::class, 'inactive'])->name('inactive');
            Route::get('/inactive/data', [MasterUSMMController::class, 'inactiveData'])->name('inactiveData');

            Route::get('/expired',       [MasterUSMMController::class, 'expired'])->name('expired');
            Route::get('/expired/data',  [MasterUSMMController::class, 'expiredData'])->name('expiredData');

            Route::get('/all',           [MasterUSMMController::class, 'all'])->name('all');
            Route::get('/all/data',      [MasterUSMMController::class, 'allData'])->name('allData');

            Route::post('/sync',         [MasterUSMMController::class, 'sync'])->name('sync');
        });

        // UAM Views (relationship + masters)
        Route::prefix('generic-karyawan-mapping')->name('generic_karyawan_mapping.')->group(function () {
            Route::get('/',              [GenericKaryawanMappingMidDBController::class, 'index'])->name('index');
            Route::get('/data',          [GenericKaryawanMappingMidDBController::class, 'data'])->name('data');
            Route::post('/sync',         [GenericKaryawanMappingMidDBController::class, 'sync'])->name('sync');
        });

        Route::prefix('view/uam')->name('view.uam.')->group(function () {

            // Relationship
            Route::get('/user-composite',        [UAMViewsController::class, 'userComposite'])->name('user_composite.index');
            Route::get('/user-composite/data',   [UAMViewsController::class, 'userCompositeData'])->name('user_composite.data');

            Route::get('/composite-single',      [UAMViewsController::class, 'compositeSingle'])->name('composite_single.index');
            Route::get('/composite-single/data', [UAMViewsController::class, 'compositeSingleData'])->name('composite_single.data');

            Route::get('/single-tcode',          [UAMViewsController::class, 'singleTcode'])->name('single_tcode.index');
            Route::get('/single-tcode/data',     [UAMViewsController::class, 'singleTcodeData'])->name('single_tcode.data');

            // Masters
            Route::get('/composite-master',      [UAMViewsController::class, 'compositeMaster'])->name('composite_master.index');
            Route::get('/composite-master/data', [UAMViewsController::class, 'compositeMasterData'])->name('composite_master.data');
            Route::get('/composite-ao',          [UAMViewsController::class, 'compositeSingleAO'])->name('composite_ao.index');
            Route::get('/composite-ao/data',     [UAMViewsController::class, 'compositeSingleAOData'])->name('composite_ao.data');

            Route::get('/single-master',         [UAMViewsController::class, 'singleMaster'])->name('single_master.index');
            Route::get('/single-master/data',    [UAMViewsController::class, 'singleMasterData'])->name('single_master.data');

            Route::get('/tcode-master',          [UAMViewsController::class, 'tcodeMaster'])->name('tcode_master.index');
            Route::get('/tcode-master/data',     [UAMViewsController::class, 'tcodeMasterData'])->name('tcode_master.data');
        });


        Route::prefix('uam')->name('uam.')->group(function () {
            // Composite
            Route::get('composite-role', [UAMComponentController::class, 'compositeRole'])->name('composite_role.index');
            Route::get('composite-role/data', [UAMComponentController::class, 'compositeData'])->name('composite_role.data');
            Route::post('composite-role/sync', [UAMComponentController::class, 'compositeSync'])->name('composite_role.sync');

            // Single
            Route::get('single-role', [UAMComponentController::class, 'singleRole'])->name('single_role.index');
            Route::get('single-role/data', [UAMComponentController::class, 'singleData'])->name('single_role.data');
            Route::post('single-role/sync', [UAMComponentController::class, 'singleSync'])->name('single_role.sync');

            // TCode
            Route::get('tcode', [UAMComponentController::class, 'tcode'])->name('tcode.index');
            Route::get('tcode/data', [UAMComponentController::class, 'tcodeData'])->name('tcode.data');
            Route::post('tcode/sync', [UAMComponentController::class, 'tcodeSync'])->name('tcode.sync');

            Route::get('composite-role/compare', [CompositeRoleCompareController::class, 'compare'])->name('composite_role.compare');
        });
    });

    Route::get('/mapping/middle-db/user-generic-uam', [GenericKaryawanMappingMidDBController::class, 'index'])->name('mapping.middle_db.user_generic_uam');

    Route::prefix('middle-db/raw')->name('middle_db.raw.')->group(function () {

        Route::prefix('uam-relationship')->name('uam_relationship.')->group(function () {
            Route::get('/', [UAMRelationshipRawController::class, 'index'])->name('index');
            Route::get('/data', [UAMRelationshipRawController::class, 'data'])->name('data');
            Route::post('/sync', [UAMRelationshipRawController::class, 'sync'])->name('sync');
        });

        Route::prefix('generic-karyawan-mapping')->name('generic_karyawan_mapping.')->group(function () {
            Route::get('/', [GenericKaryawanMappingRawController::class, 'index'])->name('index');
            Route::get('/data', [GenericKaryawanMappingRawController::class, 'data'])->name('data');
            Route::post('/sync', [GenericKaryawanMappingRawController::class, 'sync'])->name('sync');
        });
    });

    // ------------- DATA COMPARE ----------------

    // Route::get('/compare/unit-kerja', [UnitKerjaCompareController::class, 'index'])->name('compare.unit_kerja');

    Route::prefix('compare')->name('compare.')->group(function () {
        Route::get('/company', [UnitKerjaCompareController::class, 'company'])->name('unit_kerja');
        Route::get('/kompartemen', [UnitKerjaCompareController::class, 'kompartemen'])->name('unit_kerja');
        Route::get('/departemen', [UnitKerjaCompareController::class, 'departemen'])->name('unit_kerja');
        Route::get('/cost-center', [UnitKerjaCompareController::class, 'costCenter'])->name('unit_kerja');

        Route::prefix('/uam')->name('uam.')->group(function () {
            Route::get('/composite-role', [UAMCompareController::class, 'compositeRole'])->name('composite');
            Route::get('/composite-role-exist', [UAMCompareController::class, 'compositeRoleExist'])->name('composite.exist');
            Route::get('/single-role', [UAMCompareController::class, 'singleRole'])->name('single');
            Route::get('/single-role-exist', [UAMCompareController::class, 'singleRoleExist'])->name('single.exist');
            Route::get('/tcode', [UAMCompareController::class, 'tcode'])->name('tcode');
            Route::get('/tcode-exist', [UAMCompareController::class, 'tcodeExist'])->name('tcode.exist');
            Route::get('/compare/uam/export/{scope}/{side}', [UAMCompareController::class, 'export'])->name('compare.uam.export');

            // Relationship compares
            Route::prefix('/relationship')->name('relationship.')->group(function () {
                Route::get('/user-composite', [UAMRelationshipCompareController::class, 'userComposite'])->name('user_composite');
                Route::get('/user-composite-exist', [UAMRelationshipCompareController::class, 'userCompositeExist'])->name('user_composite.exist');
                Route::get('/composite-single', [UAMRelationshipCompareController::class, 'compositeSingle'])->name('composite_single');
                Route::get('/composite-single-exist', [UAMRelationshipCompareController::class, 'compositeSingleExist'])->name('composite_single.exist');
                Route::get('/single-tcode', [UAMRelationshipCompareController::class, 'singleTcode'])->name('single_tcode');
                Route::get('/single-tcode-exist', [UAMRelationshipCompareController::class, 'singleTcodeExist'])->name('single_tcode.exist');
            });
        });

        Route::prefix('/usmm')->name('usmm.')->group(function () {
            Route::get('/generic', [USMMCompareController::class, 'genericIndex'])->name('generic');
            Route::get('/generic/data', [USMMCompareController::class, 'genericCompareData'])->name('generic.data');

            Route::get('/nik', [USMMCompareController::class, 'nikIndex'])->name('nik');
            Route::get('/nik/data', [USMMCompareController::class, 'nikCompareData'])->name('nik.data');
        });
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

    Route::prefix('report/uam')->name('report.uam.')->group(function () {
        Route::get('/', [UAMReportController::class, 'index'])->name('index');
        Route::get('/get-kompartemen', [UAMReportController::class, 'getKompartemen'])->name('get-kompartemen');
        Route::get('/get-departemen', [UAMReportController::class, 'getDepartemen'])->name('get-departemen');
        Route::get('/job-roles', [UAMReportController::class, 'jobRolesData'])->name('job-roles');
        Route::get('/single-roles', [UAMReportController::class, 'getSingleRoles'])->name('single-roles');

        Route::get('/export-word', [UAMReportController::class, 'exportWord'])->name('export-word');
        Route::get('/export-user-job-composite-excel', [UAMReportController::class, 'exportUserJobCompositeExcel'])->name('export-user-job-composite-excel');
        Route::get('/export-single-excel', [UAMReportController::class, 'exportSingleExcel'])->name('export-single-excel');
        Route::get('/export-composite-excel', [UAMReportController::class, 'exportCompositeExcel'])->name('export-composite-excel');
        Route::get('/export-composite-no-ao', [UAMReportController::class, 'exportCompositeWithoutAO'])->name('export-composite-no-ao');
        // Route::get('/download', [UARReportController::class, 'download'])->name('download');
    });

    Route::prefix('report/ba-penarikan')->name('report.ba_penarikan.')->group(function () {
        Route::get('/', [BAPenarikanDataController::class, 'index'])->name('index');
        Route::get('/data', [BAPenarikanDataController::class, 'data'])->name('data');
        Route::get('/export-word', [BAPenarikanDataController::class, 'exportWord'])->name('export_word');
    });

    Route::prefix('report/anomali')->name('report.anomali.')->group(function () {
        Route::get('/job-role-multi-composite', [AnomaliDataReportController::class, 'jobRoleMultipleComposite'])->name('job-role-multi-composite');
        Route::get('/composite-multi-job-role', [AnomaliDataReportController::class, 'compositeMultipleJobRole'])->name('composite-multi-jobrole');
        Route::get('/job-role-same-name', [AnomaliDataReportController::class, 'jobRoleSameName'])->name('job-role-same-name');
    });

    // ------------------ PENOMORAN UAR & UAM ------------------
    Route::get('penomoran-uar/check-number', [PenomoranUARController::class, 'checkNumber'])->name('penomoran-uar.checkNumber');
    Route::resource('penomoran-uar', PenomoranUARController::class);

    Route::get('penomoran-uam/check-number', [PenomoranUAMController::class, 'checkNumber'])->name('penomoran-uam.checkNumber');
    Route::resource('penomoran-uam', PenomoranUAMController::class);


    // Admin review/approval (Super Admin only)
    Route::middleware(['role:Super Admin'])->prefix('admin')->name('admin.')->group(function () {


        // ------------------ EMAIL CHANGE APPROVAL ------------------

        // List page
        Route::get('/email-change-requests', [EmailChangeRequestController::class, 'index'])->name('email-change-requests.index');

        // DataTables JSON (no params) — keep BEFORE the show route
        Route::get('/email-change-requests/data', [EmailChangeRequestController::class, 'data'])->name('email-change-requests.data');

        // Detail + actions (numeric id only)
        Route::get('/email-change-requests/{emailChangeRequest}', [EmailChangeRequestController::class, 'show'])->whereNumber('emailChangeRequest')->name('email-change-requests.show');
        Route::post('/email-change-requests/{emailChangeRequest}/approve', [EmailChangeRequestController::class, 'approve'])->whereNumber('emailChangeRequest')->name('email-change-requests.approve');
        Route::post('/email-change-requests/{emailChangeRequest}/reject', [EmailChangeRequestController::class, 'reject'])->whereNumber('emailChangeRequest')->name('email-change-requests.reject');

        // ------------------ ACCESS MATRIX ------------------

        Route::get('/access-matrix', [AccessMatrixController::class, 'index'])->name('access-matrix.index');
        Route::post('/access-matrix/assign-role', [AccessMatrixController::class, 'assignRole'])->name('access-matrix.assign-role');
        Route::post('/access-matrix/assign-permission', [AccessMatrixController::class, 'assignPermission'])->name('access-matrix.assign-permission');

        Route::get('/access-matrix/roles', [AccessMatrixController::class, 'rolesIndex'])->name('access-matrix.roles.index');
        Route::get('/access-matrix/roles/data', [AccessMatrixController::class, 'rolesData'])->name('access-matrix.roles.data');

        Route::get('/access-matrix/permissions', [AccessMatrixController::class, 'permissionsIndex'])->name('access-matrix.permissions.index');
        Route::get('/access-matrix/permissions/data', [AccessMatrixController::class, 'permissionsData'])->name('access-matrix.permissions.data');
    });


    Route::middleware(['role:Super Admin'])->group(function () {

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
});
