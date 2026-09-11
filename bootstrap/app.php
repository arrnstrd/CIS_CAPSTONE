<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/super-admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/school-admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/teacher.php'));

            Route::middleware('web')
                ->group(base_path('routes/scanner-operator.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null; // Keep standard Laravel validation response
                }

                \Illuminate\Support\Facades\Log::error('AJAX/API Request Exception: ' . $e->getMessage(), [
                    'exception' => $e,
                    'url' => $request->fullUrl(),
                    'input' => $request->except(['password', 'password_confirmation', 'current_password']),
                ]);

                $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : 500;

                return response()->json([
                    'message' => 'An error occurred while processing your request. Please check your entries and try again.',
                ], $statusCode);
            }
        });
    })->create();
