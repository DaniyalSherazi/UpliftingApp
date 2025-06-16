<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\VehicleTypeRateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\RiderController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return 'Optimization cache cleared!';
});


Route::prefix('admin')->group(function () {
    Route::get('login', [AdminAuth::class, 'login'])->name('admin.login');
    Route::post('signin', [AdminAuth::class, 'signin'])->name('admin.signin');

    Route::middleware('admin')->group(function () {
        Route::get('/logout', [AdminAuth::class, 'logout'])->name('admin.logout');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

         Route::as('admin.')->group(function () {
            Route::resource('/riders', RiderController::class);
            Route::get('/riders/approved/{id}/{status}', [RiderController::class, 'approvedStatus'])->name('riders.approved');
            Route::post('/riders/update-status', [RiderController::class, 'updateStatus'])->name('riders.updateStatus');

            Route::resource('/customers', CustomerController::class);
            Route::post('/customers/update-status', [CustomerController::class, 'updateStatus'])->name('customers.updateStatus');
            Route::resource('/vehicle-type-rates', VehicleTypeRateController::class);

            Route::resource('/promo-codes', PromoCodeController::class);
            Route::get('/promocode/approved/{id}/{status}', [PromoCodeController::class, 'approvedStatus'])->name('promocode.approved');
            Route::post('/promocode/update-status', [PromoCodeController::class, 'updateStatus'])->name('promocode.updateStatus');
        });
    });


});

