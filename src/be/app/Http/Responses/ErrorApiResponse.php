<?php

namespace App\Http\Responses;

class ErrorApiResponse extends ApiErrorResponse
{
    protected function defaultResponseCode(): int
    {
        return 500;
    }

    protected function defaultErrorMessage(): string
    {
        return 'Something went wrong';
    }
}