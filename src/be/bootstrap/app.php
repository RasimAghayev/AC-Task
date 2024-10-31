<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{
    Exceptions,Middleware
};
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(using: function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Route not found.',
                    'error' => [
                        'code' => 404,
                        'type' => 'NotFoundHttpException',
                        'details' => config('app.debug')
                            ? ($e->getMessage() ?: 'The requested endpoint does not exist')
                            : 'The requested endpoint does not exist'
                    ],
                    'timestamp' => now()->toIso8601String(),
                    'path' => $request->path(),
                    'method' => $request->method()
                ], 404);
            }
            return null;
        });
    })->create();
