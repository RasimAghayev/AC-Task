<?php

use App\Http\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

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