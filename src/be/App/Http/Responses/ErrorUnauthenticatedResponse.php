<?php

namespace App\Http\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ErrorUnauthenticatedResponse",
 *     type="object",
 *     description="The answer of a request without a token.",
 *
 * @OA\Property(property="timestamp", type="string", example="2026-01-01T12:00:00+00:00"),
 * @OA\Property(property="path", type="string", example="api/v1/tasks/1"),
 * @OA\Property(property="method", type="string", example="GET"),
 * @OA\Property(property="error", type="string", example="Not found."),
 * @OA\Property(property="result", type="array", @OA\Items(type="object"))
 * )
 */
class ErrorUnauthenticatedResponse extends ApiErrorResponse
{
    protected function defaultResponseCode(): int
    {
        return 401;
    }

    protected function defaultErrorMessage(): string
    {
        return 'Authorization error.';
    }
}
