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
        Route::post('/', 'createMedication')->middleware('restrictRole:manager');
    });

    Route::prefix('orders')->controller(BuyOrderController::class)->group(function (){
        Route::get('/', 'listAllOrders')->middleware('checkOrdersManager');

        //this two routes instead of the route above which is merged them in one route based on user role
        Route::get('all', 'listAllOrders')->middleware('restrictRole:manager');
        Route::get('user', 'listUserOrders')->middleware('restrictRole:pharmacist');

        Route::get('{id}', 'showOrder')->middleware('checkOrderOwner');
        Route::post('/', 'createOrder')->middleware('restrictRole:pharmacist');

        Route::get('status/{id}', 'changeOrderStatus')->middleware('restrictRole:manager');
        Route::get('pay/{id}', 'changeOrderPayStatus')->middleware('restrictRole:manager');
    });

    Route::prefix('classifications')->controller(ClassificationController::class)->group(function () {
        Route::get('/', 'listAllClassifications');
        Route::get('{id}', 'listMedicationsClassification');
    });
});
