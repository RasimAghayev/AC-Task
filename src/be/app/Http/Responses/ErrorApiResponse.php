<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Override;
use Throwable;

class ErrorApiResponse extends ApiErrorResponse
{
    /**
     * @param $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        $response = parent::toResponse($request);
        $data = (array) $response->getData();

        // Handle error message formatting
        $errorMessage = $this->formatErrorMessage($this->dataOrMessage);
        $data['error'] = $errorMessage;
        $data['result'] = [];

        $response->setData($data);
        return $response;
    }

    /**
     * Format error message based on input type
     *
     * @param mixed $error
     * @return string
     */
    protected function formatErrorMessage(mixed $error): string
    {
        if ($error instanceof Throwable) {
            return $error->getMessage();
        }

        if (is_array($error)) {
            return implode(', ', array_map(static function ($item) {
                return is_array($item) ? implode(', ', $item) : $item;
            }, $error));
        }

        if (is_object($error) && method_exists($error, '__toString')) {
            return (string) $error;
        }

        return (string) $error ?: $this->defaultErrorMessage();
    }

    /**
     * @return int
     */
    #[Override] protected function defaultResponseCode(): int
    {
        return 500;
    }

    /**
     * @return string
     */
    #[Override] protected function defaultErrorMessage(): string
    {
        return 'Something went wrong';
    }
}