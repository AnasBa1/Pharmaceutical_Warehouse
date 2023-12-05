<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyOrderController;
use App\Http\Controllers\ClassificationController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::get('medications', [MedicationController::class, 'listValidMedications']);

Route::get('medications/{id}', [MedicationController::class, 'showMedication']);

Route::post('medications', [MedicationController::class, 'createMedication']);

Route::post('search', [MedicationController::class, 'search']);

Route::get('orders', [BuyOrderController::class, 'listAllOrders']);

Route::get('orders/{id}', [BuyOrderController::class, 'showOrder']);

Route::put('orders/{id}', [BuyOrderController::class, 'changeOrderStatus']);
