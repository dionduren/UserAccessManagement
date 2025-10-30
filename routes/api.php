<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MasterDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('cascade')->group(function () {
    Route::get('/kompartemen', function (Request $req) {
        return \App\Models\Kompartemen::where('company_id', $req->company_id)
            ->get(['kompartemen_id as id', 'nama']);
    });

    Route::get('/departemen', function (Request $req) {
        return \App\Models\Departemen::where('kompartemen_id', $req->kompartemen_id)
            ->get(['departemen_id as id', 'nama']);
    });
});

// New dynamic master data endpoint
Route::get('/master-data', [MasterDataController::class, 'hierarchy']);
Route::get('/master-data/job-roles-by-periode', [MasterDataController::class, 'jobRolesByPeriode']);
