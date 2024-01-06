<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyOrderController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\ReportController;
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
    Route::put('logout', [AuthController::class, 'logout']);

    Route::prefix('medications')->controller(MedicationController::class)->group(function (){
        Route::put('/', 'listValidMedications');
        Route::put('expired', 'listExpiredMedications')->middleware('restrictRole:manager');
        Route::post('search', 'search');
        Route::put('{id}', 'showMedication');
        Route::post('/', 'createMedication')->middleware('restrictRole:manager');
    });

    Route::prefix('orders')->controller(BuyOrderController::class)->group(function (){
        Route::put('/', 'listAllOrders')->middleware('checkOrdersManager');

        //this two routes instead of the route above which is merged them in one route based on user role
        Route::put('all', 'listAllOrders')->middleware('restrictRole:manager');
        Route::put('user', 'listUserOrders')->middleware('restrictRole:pharmacist');

        Route::put('{id}', 'showOrder')->middleware('checkOrderOwner');
        Route::post('/', 'createOrder')->middleware('restrictRole:pharmacist');

        Route::put('status/{id}', 'changeOrderStatus')->middleware('restrictRole:manager');
        Route::put('pay/{id}', 'changeOrderPayStatus')->middleware('restrictRole:manager');
    });

    Route::prefix('classifications')->controller(ClassificationController::class)->group(function () {
        Route::put('/', 'listAllClassifications');
        Route::put('{id}', 'listMedicationsClassification');
        Route::post('search', 'search');
    });

    Route::prefix('favorites')->controller(FavoriteController::class)->middleware('restrictRole:pharmacist')->group(function () {
        Route::put('/', 'favoritesList');
        Route::put('medications', 'listValidMedications');
        Route::put('classifications/{id}', 'listMedicationsClassification');
        Route::put('{id}', 'addToFavorites');
        Route::delete('{id}', 'removeFromFavorites');
        Route::post('search', 'search');
    });

    Route::prefix('report')->controller(ReportController::class)->middleware('restrictRole:manager')->group(function () {
        Route::post('orders', 'ordersReport');
        Route::post('sales', 'salesReport');
    });
});
