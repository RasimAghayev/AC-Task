<?php

namespace App\Helpers;

use App\Http\Responses\ApiBaseResponse;
use App\Http\Responses\ErrorApiResponse;
use App\Http\Responses\SuccessApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class TransactionHelper
{
    /**
     * Run a piece of work and answer with the response of the project.
     *
     * A piece of work that answers with a response of its own keeps it, so
     * the status of the answer is the one of that response. Before, every
     * answer was wrapped in a successful one, so a request that was refused
     * with the status 401 was answered with the status 200 and a body of an
     * error.
     */
    public static function handleWithTransaction(callable $callback): ApiBaseResponse
    {
        try {
            $result = $callback();

            if ($result instanceof ApiBaseResponse) {
                return $result;
            }

            return SuccessApiResponse::make($result);
        } catch (AuthorizationException $e) {
            return ErrorApiResponse::make('Authorization failed: '.$e->getMessage(), 403);
        } catch (ModelNotFoundException|NotFoundHttpException $e) {
            return ErrorApiResponse::make('Resource not found: '.$e->getMessage(), 404);
        } catch (Throwable $e) {
            return ErrorApiResponse::make('An error occurred: '.$e->getMessage(), 500);
        }
    }
}
