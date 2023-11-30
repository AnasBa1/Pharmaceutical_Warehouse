<?php

use App\Http\Controllers\MedicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('medications', [MedicationController::class, 'listAllMedications']);

Route::get('medications/{id}', [MedicationController::class, 'showMedication']);

Route::post('medications', [MedicationController::class, 'createMedication']);

Route::post('search', [MedicationController::class, 'search']);
