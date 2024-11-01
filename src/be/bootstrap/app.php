<?php

use App\Http\Responses\{
    ErrorTokenBlacklisted,
    ErrorTokenInvalid,
    ErrorTokenExpired,
    ErrorMethodNotAllowedResponse,
    ErrorNotFoundResponse,
    ErrorTooManyAttemptsResponse,
    ErrorUnauthenticatedResponse,
    ErrorValidationResponse,
    ErrorInternalServerErrorResponse
};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{
    Exceptions,
    Middleware
};
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\{
    ThrottleRequestsException,
    HttpResponseException
};
use phpOpenSourceSaver\JWTAuth\Exceptions\{
    TokenExpiredException,
    TokenInvalidException,
    TokenBlacklistedException
};
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(using: function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
//dd([$e,$request]);
            if ($request->is('api/*') || $request->wantsJson()) {
                if ($e instanceof HttpResponseException) {
                    return null;
                }
                $response = match (true) {
                    $e instanceof RouteNotFoundException => ErrorNotFoundResponse::make(),
                    $e instanceof AuthenticationException => ErrorUnauthenticatedResponse::make(),
                    $e instanceof BadMethodCallException => ErrorMethodNotAllowedResponse::make(),
                    $e instanceof ValidationException => ErrorValidationResponse::make($e->errors()),
                    $e instanceof ThrottleRequestsException => ErrorTooManyAttemptsResponse::make(),
                    $e instanceof TokenExpiredException => ErrorTokenExpired::make(),
                    $e instanceof TokenInvalidException => ErrorTokenInvalid::make(),
                    $e instanceof TokenBlacklistedException => ErrorTokenBlacklisted::make(),
                    default => null,
                };
                if (!$response) {
                    $errorMessage = config('app.debug') ? $e->getMessage() : '';
                    $response = ErrorInternalServerErrorResponse::make($errorMessage);
                }
                if ($response instanceof Response && $e instanceof HttpException) {
                    $headers = $e->getHeaders();
                    $response->headers->add($headers);
                }
                return $response;
            }
            return null;
        });

        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();