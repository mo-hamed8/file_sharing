<?php

use App\Exceptions\RoomException;
use App\Http\Middleware\AuthenticateRoomToken;
use App\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'room.auth' => AuthenticateRoomToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $wantsJson = fn (Request $request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($wantsJson);

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            Log::warning('Rate limit exceeded', [
                'event' => 'rate_limit_exceeded',
                'path' => $request->path(),
            ]);

            return null;
        });

        $exceptions->render(function (RoomException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error($e->getMessage(), $e->status());
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        });

        $exceptions->render(function (ValidationException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return ApiResponse::error('The provided data is invalid.', 422, $e->errors());
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            $message = $e->getMessage() ?: match ($e->getStatusCode()) {
                403 => 'This action is unauthorized.',
                404 => 'Resource not found.',
                429 => 'Too many requests.',
                default => 'An error occurred.',
            };

            return ApiResponse::error($message, $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return ApiResponse::error(
                config('app.debug') ? $e->getMessage() : 'Server error.',
                500,
            );
        });
    })->create();
