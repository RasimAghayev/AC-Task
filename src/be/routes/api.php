<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Check\CheckController;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\AuthController;

Route::middleware(['throttle:60,1'])->prefix('v1')->group(function () {
    Route::group(['prefix' => 'check'], function () {
        Route::get('db', [CheckController::class, 'getDB']);
        Route::get('health', [CheckController::class, 'getHealth']);
        Route::get('static', [CheckController::class, 'getStatic']);
        Route::get('ip', [CheckController::class, 'getIP']);
        Route::get('clear_cache', [CheckController::class, 'clearCache']);
    });
    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::middleware(['auth:api'])->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::put('me', [AuthController::class, 'updateMe'])->name('updateMe');
            Route::get('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        });
    });
});
