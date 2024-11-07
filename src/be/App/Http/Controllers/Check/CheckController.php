<?php

namespace App\Http\Controllers\Check;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\
{
    JsonResponse,
    Response
};
use App\Helpers\TransactionHelper;
use Illuminate\Support\Facades\
{
    Artisan,
    DB,
    Http
};

class CheckController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getDB(): JsonResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            DB::connection()->getPdo();
            return ['status' => 'Application is up and running, database connection is ok!'];
        });
    }

    /**
     * @return JsonResponse
     */
    public function getHealth(): JsonResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            return ['status' => 'Application is up and running, health is ok!'];
        });
    }

    /**
     * @return JsonResponse
     */
    public function getStatic(): JsonResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            return ['status' => true];
        });
    }

    /**
     * @return mixed
     */
    public function getIP(): mixed
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            return Http::get('https://ipapi.co/json/')->json();
        });
    }

    /**
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        Artisan::call('clear-compiled');
        Artisan::call('cache:clear');
        Artisan::call('route:cache');
        Artisan::call('view:clear');

        return TransactionHelper::handleWithTransaction(function () use ($request) {
            return ['status' => "Cache is cleared ".date(now())]
        });
    }
}