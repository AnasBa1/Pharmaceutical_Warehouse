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

    Route::prefix('medications')->controller(MedicationController::class)->group(function (){
        Route::get('/', 'listValidMedications');
        Route::get('expired', 'listExpiredMedications')->middleware('restrictRole:manager');
        Route::post('search', 'search');
        Route::get('{id}', 'showMedication');
        Route::post('/', 'createMedication');
    });

    Route::prefix('orders')->controller(BuyOrderController::class)->group(function (){
        Route::get('/', 'listOrders');
        /**
         * this two routes instead of the route above which is replace them
         */
        /*
        Route::get('all', 'listAllOrders')->middleware('restrictRole:manager');
        Route::get('user', 'listUserOrders')->middleware('restrictRole:pharmacist');
        */
        Route::get('{id}', 'showOrder');
        Route::patch('{id}', 'changeOrderStatus')->middleware('restrictRole:manager');
        Route::post('/', 'createOrder');
    });

    Route::prefix('classifications')->controller(ClassificationController::class)->group(function () {
        Route::get('/', 'listAllClassifications');
        Route::get('{id}', 'listMedicationsClassification');
    });
});

//Route::get('medications', [MedicationController::class, 'listValidMedications']);
//
//Route::get('medications/expired', [MedicationController::class, 'listExpiredMedications']);
//
//Route::get('medications/{id}', [MedicationController::class, 'showMedication']);
//
//Route::post('medications', [MedicationController::class, 'createMedication']);
//
//Route::post('search', [MedicationController::class, 'search']);

//Route::get('orders', [BuyOrderController::class, 'listAllOrders']);
//
//Route::get('orders/{id}', [BuyOrderController::class, 'showOrder']);
//
//Route::put('orders/{id}', [BuyOrderController::class, 'changeOrderStatus']);

//Route::get('classifications', [ClassificationController::class, 'listAllClassifications']);
//
//Route::get('classifications/{id}', [ClassificationController::class, 'listMedicationsClassification']);
