<?php

use Illuminate\Support\Facades\Route;
use L5Swagger\Http\Controllers\SwaggerController;

Route::get('/', function () {
    return view('welcome');
});
Route::group([
    'prefix' => config('l5-swagger.documentations.default.routes.api'),
    'middleware' => []
], function () {
    Route::get('/', [
        'as' => 'l5-swagger.default.api',
        'uses' => '\L5Swagger\Http\Controllers\SwaggerController@api'
    ]);

    Route::get('/asset/{asset}', [
        'as' => 'l5-swagger.default.asset',
        'uses' => '\L5Swagger\Http\Controllers\SwaggerController@asset'
    ]);

    Route::get('/docs', [
        'as' => 'l5-swagger.default.docs',
        'uses' => '\L5Swagger\Http\Controllers\SwaggerController@docs'
    ]);
});