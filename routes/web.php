<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
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



Route::prefix('admin')->group(function () {
    Route::get('login', [AdminAuth::class, 'login'])->name('admin.login');
    Route::post('signin', [AdminAuth::class, 'signin'])->name('admin.signin');

    Route::middleware('admin')->group(function () {
        Route::get('/logout', [AdminAuth::class, 'logout']);

            Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    });


});

