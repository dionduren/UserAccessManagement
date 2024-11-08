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
use App\Http\Controllers\AccessMatrixController;
use App\Http\Controllers\CompositeRoleController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('companies', CompanyController::class);
// Route::resource('companies', CompanyController::class)->middleware('permission:view company|edit company|delete company');

Route::resource('kompartemens', KompartemenController::class);
Route::resource('departemens', DepartemenController::class);

Route::get('/job-roles/filtered-data', [JobRoleController::class, 'getFilteredData'])->name('job-roles.filtered-data');
Route::get('/job-roles/{id}', [JobRoleController::class, 'show'])->where('id', '[0-9]+');
Route::resource('job-roles', JobRoleController::class);

Route::get('/composite-roles/ajax', [CompositeRoleController::class, 'getCompositeRolesAjax'])->name('composite-roles.ajax');
Route::get('/composite-roles/filtered-data', [CompositeRoleController::class, 'getFilteredData'])->name('composite-roles.filtered-data');
Route::resource('composite-roles', CompositeRoleController::class);

Route::get('/single-roles/create', [SingleRoleController::class, 'create'])->name('single-roles.create');
Route::post('/single-roles', [SingleRoleController::class, 'store'])->name('single-roles.store');
Route::get('/single-roles/filtered-data', [SingleRoleController::class, 'getFilteredData'])->name('single-roles.filtered-data');
Route::resource('single-roles', SingleRoleController::class);

Route::resource('tcodes', TcodeController::class);

// Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
// Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage-users');

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
