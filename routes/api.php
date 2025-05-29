<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Rider\AuthController as RiderAuth;

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
    Route::post('login', [AdminAuth::class, 'login']);

    Route::middleware('admin')->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Admin Dashboard']);
        });
    });
});


Route::middleware(['auth:sanctum'])->group(function () {

    // for rider
    Route::prefix('rider')->group(function () {
        Route::post('/signin', [RiderAuth::class, 'signin']);
        Route::post('/signup', [RiderAuth::class, 'singup']);

    });


    // for customer
    Route::prefix('customer')->group(function () {
        Route::post('/signin', [RiderAuth::class, 'signin']);
        Route::post('/signup', [RiderAuth::class, 'singup']);

    });
});
