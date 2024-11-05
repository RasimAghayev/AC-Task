<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SwaggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::group([
            'prefix' => 'api/documentation',
        ], function () {
            Route::get('/', [
                'as' => 'l5-swagger.api',
                'uses' => '\L5Swagger\Http\Controllers\SwaggerController@api'
            ]);

            Route::get('asset/{asset}', [
                'as' => 'l5-swagger.asset',
                'uses' => '\L5Swagger\Http\Controllers\SwaggerController@asset'
            ]);

            Route::get('docs', [
                'as' => 'l5-swagger.docs',
                'uses' => '\L5Swagger\Http\Controllers\SwaggerController@docs'
            ]);

            Route::get('oauth2-callback', [
                'as' => 'l5-swagger.oauth2',
                'uses' => '\L5Swagger\Http\Controllers\SwaggerController@oauth2Callback'
            ]);
        });
    }
}