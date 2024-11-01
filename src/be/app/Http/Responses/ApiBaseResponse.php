<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\
{
    JsonResponse,
    Response,
};

abstract class ApiBaseResponse extends Response implements Responsable
{
    /**
     * @var array|string|object
     */
    protected array|string|object $dataOrMessage;

    /**
     * @var int|null
     */
    protected ?int $code;

    /**
     * @param array|string|object $dataOrMessage
     * @param int|null $code
     */
    private function __construct(array|string|object $dataOrMessage = [], int $code = null)
    {
        parent::__construct();

        $this->dataOrMessage = $dataOrMessage;
        $this->code = $code;
    }

    /**
     * @param array|string|object $dataOrMessage
     * @param int|null $code
     * @return static
     */
    public static function make(array|string|object $dataOrMessage = [], int $code = null): static
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
            'result' => $this->dataOrMessage->original??$this->dataOrMessage,
            'error' => null,
        ];

        if ($this->code === 422) {
            $responseArray['error'] = $this->dataOrMessage;
            $responseArray['result'] = [];
        }

        $response = new JsonResponse($responseArray);

        $response->setStatusCode($this->code ?? $this->defaultResponseCode());
        $response->headers->add(
            $this->headers->all()
        );

        return $response;
    }

    /**
     * @return int
     */
    protected function defaultResponseCode(): int
    {
        return 500;
    }
}