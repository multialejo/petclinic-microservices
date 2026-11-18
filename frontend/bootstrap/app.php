<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\GatewayUnavailableException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Página de error del monolito (templates/error.html) para cualquier excepción no controlada
        // en las pantallas: 404, 500 (/oups) y 503 (gateway o microservicio caído). Las validaciones,
        // redirecciones y CSRF siguen el flujo normal de Laravel.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()
                || $e instanceof ValidationException
                || $e instanceof HttpResponseException
                || $e instanceof TokenMismatchException) {
                return null;
            }

            $status = match (true) {
                $e instanceof GatewayUnavailableException => 503,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            return response()->view('error', [
                'status' => $status,
                // Como el monolito, el mensaje técnico solo se muestra cuando aporta (503); en 500 queda vacío.
                'message' => $status === 503 ? $e->getMessage() : '',
            ], $status);
        });
    })->create();
