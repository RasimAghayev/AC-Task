<?php

namespace App\Helpers;

use Throwable;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessApiResponse;
use App\Http\Responses\ErrorApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionHelper
{
    public static function handleWithTransaction(callable $callback)
    {
        try {
            return SuccessApiResponse::make($callback());
        } catch (AuthorizationException $e) {
            return ErrorApiResponse::make("Authorization failed: " . $e->getMessage(), 403);
        } catch (ModelNotFoundException|NotFoundHttpException $e) {
            return ErrorApiResponse::make("Resource not found: " . $e->getMessage(), 404);
        } catch (Throwable $e) {
            return ErrorApiResponse::make("An error occurred: " . $e->getMessage(), 500);
        }
    }
}
