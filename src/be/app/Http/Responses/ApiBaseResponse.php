<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use JsonException;
use Illuminate\Http\{JsonResponse, Resources\Json\JsonResource, Response};

abstract class ApiBaseResponse extends Response implements Responsable
{
    /**
     * @var mixed
     */
    protected mixed $dataOrMessage;

    /**
     * @var int|null
     */
    protected ?int $code;

    /**
     * @param mixed $dataOrMessage
     * @param int|null $code
     */
    private function __construct(mixed $dataOrMessage = [], ?int $code = null)
    {
        parent::__construct();

        $this->dataOrMessage = $dataOrMessage;
        $this->code = $code;
    }

    /**
     * @param mixed $dataOrMessage
     * @param int|null $code
     * @return static
     */
    public static function make(mixed $dataOrMessage = [], ?int $code = null): static
    {
        return new static($dataOrMessage, $code);
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        $responseArray = [
            'timestamp' => now()->toIso8601String(),
            'path' => $request->path(),
            'method' => $request->method(),
            'error' => null,
            'result' => $this->formatResult($this->dataOrMessage),
        ];

        if ($this->code === 422) {
            $responseArray['error'] = $this->dataOrMessage;
            $responseArray['result'] = [];
        }

        $response = new JsonResponse($responseArray);
        $response->setStatusCode($this->code ?? $this->defaultResponseCode());

        $headers = $this->headers->all();
        if (!empty($headers)) {
            $response->headers->add($headers);
        }

        return $response;
    }

    /**
     * Format the result data
     *
     * @param mixed $result
     * @return mixed
     * @throws JsonException
     */
    protected function formatResult(mixed $result): mixed
    {
        if ($result instanceof JsonResponse) {
            return json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_object($result) && method_exists($result, 'toArray')) {
            if ($result instanceof JsonResource) {
                return $result->resolve(request());
            }
            return $result->toArray(request());
        }

        if (is_object($result) && method_exists($result, 'getData')) {
            return $result->getData(true);
        }

        if (is_array($result) && isset($result['original'])) {
            return $result['original'];
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function defaultResponseCode(): int
    {
        return 500;
    }

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->code ?? $this->defaultResponseCode();
    }
}