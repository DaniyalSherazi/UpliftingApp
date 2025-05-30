<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\RiderController as AdminRiderController;
use App\Http\Controllers\Rider\AuthController as RiderAuth;
use App\Http\Controllers\Customer\AuthController as CustomerAuth;

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

Route::prefix('admin')->group(function () {
    Route::post('signin', [AdminAuth::class, 'signin']);

    Route::middleware('admin')->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Admin Dashboard']);
        });
    });
});

// for rider
Route::prefix('rider')->group(function () {
    Route::post('/signin', [RiderAuth::class, 'signin']);
    Route::post('/signup', [RiderAuth::class, 'signup']);
    

});


// for customer
Route::prefix('customer')->group(function () {
    Route::post('/signin', [CustomerAuth::class, 'signin']);
    Route::post('/signup', [CustomerAuth::class, 'signup']);

});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('admin')->group(function () {

        Route::middleware('admin')->group(function () {

            Route::apiResource('riders',AdminRiderController::class)->only(methods: 'index');


            Route::get('/', function () {
                return response()->json(['message' => 'Admin Dashboard']);
            });
        });

        Route::get('/logout', [AdminAuth::class, 'logout']);
    });

    // for rider
    Route::prefix('rider')->group(function () {
        Route::post('/setup', [RiderAuth::class, 'setup']);
        Route::get('/profile', [RiderAuth::class, 'profile']);
        Route::post('/edit-profile', [RiderAuth::class, 'editProfile']);
        Route::post('/change-password', [RiderAuth::class, 'changePassword']);
        Route::get('/logout', [RiderAuth::class, 'logout']);
    });


    // for customer
    Route::prefix('customer')->group(function () {
        Route::get('/profile', [CustomerAuth::class, 'profile']);
        Route::post('/edit-profile', [CustomerAuth::class, 'editProfile']);
        Route::post('/change-password', [CustomerAuth::class, 'changePassword']);
        Route::get('/logout', [CustomerAuth::class, 'logout']);
    });
    
});
