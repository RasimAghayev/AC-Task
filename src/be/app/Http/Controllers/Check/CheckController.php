<?php

namespace App\Http\Controllers\Check;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\
{
    JsonResponse,
    Response
};
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
        try {
            DB::connection()->getPdo();
            return response()->json(
                data:['status' => 'Application is up and running, database connection is ok!',],
                status: Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json(
                data:['status' => 'Failed to connect to the database.',],
                status: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @return JsonResponse
     */
    public function getHealth(): JsonResponse
    {
        return response()->json(
            data:['status' => 'Application is up and running, health is ok!',],
            status: Response::HTTP_OK
        );
    }

    /**
     * @return JsonResponse
     */
    public function getStatic(): JsonResponse
    {
        return response()->json([
            'status' => true,
        ]);
    }

    /**
     * @return mixed
     */
    public function getIP(): mixed
    {
        return Http::get('https://ipapi.co/json/')->json();
    }

    public function clearCache(): JsonResponse
    {
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        Artisan::call('clear-compiled');
        Artisan::call('cache:clear');
        Artisan::call('route:cache');
        Artisan::call('view:clear');

        return response()->json([
            'status' => "Cache is cleared ".date(now()),
        ]);
    }
}