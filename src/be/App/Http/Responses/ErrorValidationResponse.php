<?php

namespace App\Http\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ErrorValidationResponse",
 *     type="object",
 *     description="The answer of a request that did not pass the validation.",
 *
 * @OA\Property(property="timestamp", type="string", example="2026-01-01T12:00:00+00:00"),
 * @OA\Property(property="path", type="string", example="api/v1/tasks"),
 * @OA\Property(property="method", type="string", example="POST"),
 * @OA\Property(property="error", type="string", example="The title field is required."),
 * @OA\Property(property="result", type="array", @OA\Items(type="object"))
 * )
 */
class ErrorValidationResponse extends ApiErrorResponse
{
    protected function defaultResponseCode(): int
    {
        return 422;
    }

    protected function defaultErrorMessage(): string
    {
        return 'Validation error.';
    }
}
