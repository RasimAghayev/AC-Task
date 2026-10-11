<?php

namespace App\Http\Controllers\Check;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\
{
    Response
};
use App\Http\Responses\
{
    ErrorApiResponse,
    ErrorUnauthenticatedResponse,
    SuccessApiResponse
};
use App\Helpers\TransactionHelper;
use Illuminate\Support\Facades\
{
    Artisan,
    DB,
    Http
};
use OpenApi\Annotations as OA;

class CheckController extends Controller
{
    /**
     * Check database connection
     *
     * @OA\Get(
     *     path="/api/v1/check/db",
     *     operationId="checkDB",
     *     tags={"Check"},
     *     summary="Check database connection",
     *     description="Verify database connectivity",
     *     @OA\Response(
     *         response=200,
     *         description="Database connection OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Application is up and running, database connection is ok!")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Database connection failed", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return : SuccessApiResponse|ErrorApiResponse
     */
    public function getDB(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function (){
            DB::connection()->getPdo();
            return ["status" => "Application is up and running, database connection is ok!"];
        });
    }

    /**
     * Check application health
     *
     * @OA\Get(
     *     path="/api/v1/check/health",
     *     operationId="checkHealth",
     *     tags={"Check"},
     *     summary="Check application health",
     *     description="Verify application is healthy",
     *     @OA\Response(
     *         response=200,
     *         description="Application is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Application is up and running, health is ok!")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Health check failed", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function getHealth(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function (){
            return ["status" => "Application is up and running, health is ok!"];
        });
    }

    /**
     * Check static status
     *
     * @OA\Get(
     *     path="/api/v1/check/static",
     *     operationId="checkStatic",
     *     tags={"Check"},
     *     summary="Check static status",
     *     description="Returns static status true",
     *     @OA\Response(
     *         response=200,
     *         description="Static status OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function getStatic(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function (){
            return ["status" => true];
        });
    }

    /**
     * Get external IP information
     *
     * @OA\Get(
     *     path="/api/v1/check/ip",
     *     operationId="checkIP",
     *     tags={"Check"},
     *     summary="Get external IP information",
     *     description="Fetch IP information from external service",
     *     @OA\Response(
     *         response=200,
     *         description="IP information retrieved",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=500, description="Failed to fetch IP", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return mixed
     */
    public function getIP(): mixed
    {
        return TransactionHelper::handleWithTransaction(function (){
            return Http::get("https://ipapi.co/json/")->json();
        });
    }

    /**
     * Clear application cache
     *
     * @OA\Get(
     *     path="/api/v1/check/clear_cache",
     *     operationId="clearCache",
     *     tags={"Check"},
     *     summary="Clear application cache",
     *     description="Clear all Laravel caches (config, route, view, compiled)",
     *     @OA\Response(
     *         response=200,
     *         description="Cache cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Cache is cleared 2026-01-15 10:30:00")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Failed to clear cache", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function clearCache(): SuccessApiResponse|ErrorApiResponse
    {
        Artisan::call("optimize:clear");
        Artisan::call("config:cache");
        Artisan::call("config:clear");
        Artisan::call("clear-compiled");
        Artisan::call("cache:clear");
        Artisan::call("route:cache");
        Artisan::call("view:clear");

        return TransactionHelper::handleWithTransaction(function (){
            return ["status" => "Cache is cleared ".date(now())];
        });
    }
}
