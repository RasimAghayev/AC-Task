<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1'])->prefix('v1')->group(function () {
    require __DIR__.'/api/check.php';
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/tasks.php';
});