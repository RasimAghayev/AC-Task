<?php

namespace App\Http\Responses;

class ErrorTokenExpired extends ApiErrorResponse
{
    protected function defaultResponseCode(): int
    {
        return 401;
    }

    protected function defaultErrorMessage(): string
    {
        return 'Token has expired.';
    }
}
