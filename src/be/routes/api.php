<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Check\CheckController;
use App\Http\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

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
    Route::group([
        'middleware' => ['api', 'auth:api'],
        'prefix' => 'tasks'
    ], function () {
        // List and search
        Route::get('/', [TaskController::class, 'index'])
            ->name('tasks.index');

        // Create
        Route::post('/', [TaskController::class, 'store'])
            ->name('tasks.store');

        // Show, update, delete
        Route::get('{id}', [TaskController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('tasks.show');

        Route::match(['put', 'patch'], '{id}', [TaskController::class, 'update'])
            ->where('id', '[0-9]+')
            ->name('tasks.update');

        Route::delete('{id}', [TaskController::class, 'destroy'])
            ->where('id', '[0-9]+')
            ->name('tasks.destroy');

        // Statistics
        Route::get('reports', [TaskController::class, 'getTasksReport'])
            ->name('tasks.reports');
    });
});
