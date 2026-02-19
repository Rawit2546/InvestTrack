<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\BusinessController;
use App\Http\Controllers\API\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Dashboard Summary
Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

// Export
Route::get('/export', [BusinessController::class, 'export']);

// Business CRUD
Route::apiResource('businesses', BusinessController::class);

// Transaction CRUD
Route::apiResource('transactions', TransactionController::class);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
