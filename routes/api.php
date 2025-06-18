<?php

use App\Http\Controllers\Admin\VehicleTypeRateController;
use App\Http\Controllers\Rider\VehicleController as RiderVehicleController;
use App\Models\VehicleTypeRate;
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



// for rider
Route::prefix('rider')->group(function () {
    Route::post('/signin', [RiderAuth::class, 'signin']);
    Route::post('/signup', [RiderAuth::class, 'signup']);
    Route::post('/resend-code', [RiderAuth::class, 'resendCode']);
    Route::post('/forgot-password', [RiderAuth::class, 'forgotPassword']);
    Route::post('/reset-password', [RiderAuth::class, 'resetPassword']);
    Route::put('/verify/{token}/{email}', [RiderAuth::class, 'verification']);
});


// for customer
Route::prefix('customer')->group(function () {
    Route::post('/signin', [CustomerAuth::class, 'signin']);
    Route::post('/signup', [CustomerAuth::class, 'signup']);
    Route::post('/resend-code', [CustomerAuth::class, 'resendCode']);
    Route::post('/forgot-password', [CustomerAuth::class, 'forgotPassword']);
    Route::post('/reset-password', [CustomerAuth::class, 'resetPassword']);
    Route::put('/verify/{token}/{email}', [CustomerAuth::class, 'verification']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('admin')->group(function () {

        Route::middleware('admin')->group(function () {

            Route::apiResource('riders',AdminRiderController::class)->only('index');
            Route::apiResource('vehicle-type-rates',VehicleTypeRateController::class)->only('index','store','update','destroy');


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

        // verification apis
        Route::post('/profile-picture', [RiderAuth::class, 'profilePicture']);
        Route::post('/driving-license', [RiderAuth::class, 'drivingLicense']);
        Route::post('/vehicle-insurance', [RiderAuth::class, 'vehicleInsurance']);
        Route::post('/registration-certificate', [RiderAuth::class, 'registrationCertificate']);
        Route::post('/background-check', [RiderAuth::class, 'backgroundCheck']);

        // vehicle apis
        Route::get('vehicle-type', [VehicleTypeRateController::class, 'list1']);
        Route::apiResource('/vehicle', RiderVehicleController::class)->only('index', 'store', 'update', 'destroy');
    });
    
    
    // for customer
    Route::prefix('customer')->group(function () {
        Route::get('/profile', [CustomerAuth::class, 'profile']);
        Route::post('/edit-profile', [CustomerAuth::class, 'editProfile']);
        Route::post('/update-lat-long', [CustomerAuth::class, 'updateLatLong']);
        Route::post('/change-password', [CustomerAuth::class, 'changePassword']);
        Route::post('/broadcasting/auth', [CustomerAuth::class, 'broadcast']);
        Route::get('/logout', [CustomerAuth::class, 'logout']);
        Route::get('vehicle-type', [VehicleTypeRateController::class, 'list2']);
    });
    
    // global 
    
});
