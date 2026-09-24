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
            // Handle session expiration / CSRF token mismatch across web and AJAX
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your session has expired. Please refresh the page and log in again.',
                        'session_expired' => true,
                    ], 419);
                }

                return redirect()->route('login')->with('warning', 'Your session has expired. Please log in again to continue.');
            }

            if ($request->expectsJson() || $request->ajax()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null; // Keep standard Laravel validation response
                }

                \Illuminate\Support\Facades\Log::error('AJAX/API Request Exception: ' . $e->getMessage(), [
                    'exception' => $e,
                    'url' => $request->fullUrl(),
                    'input' => $request->except(['password', 'password_confirmation', 'current_password']),
                ]);

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'message' => 'Your session has expired. Please log in again to continue.',
                    ], 401);
                }

                if (
                    $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException ||
                    ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 403)
                ) {
                    return response()->json([
                        'message' => 'You do not have permission to perform this action. Please contact the system administrator if you need access.',
                    ], 403);
                }

                if (
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                    ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 404)
                ) {
                    return response()->json([
                        'message' => 'The requested record or resource could not be found.',
                    ], 404);
                }

                if ($e instanceof \Illuminate\Database\QueryException) {
                    $errorCode = (string) $e->getCode();
                    $errorMsg = strtolower($e->getMessage());

                    if ($errorCode === '23505' || str_contains($errorMsg, 'unique constraint') || str_contains($errorMsg, 'duplicate key')) {
                        return response()->json([
                            'message' => 'This record already exists. Please check the existing information before continuing.',
                        ], 422);
                    }

                    if ($errorCode === '23503' || str_contains($errorMsg, 'foreign key constraint') || str_contains($errorMsg, 'violates foreign key')) {
                        return response()->json([
                            'message' => 'This record cannot be modified or deleted because it is currently linked to other records in the system.',
                        ], 422);
                    }

                    return response()->json([
                        'message' => 'Unable to save the record due to a database constraint. Please check your entries and try again.',
                    ], 422);
                }

                $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : 500;

                return response()->json([
                    'message' => 'Something went wrong while processing your request. Please try again or contact the administrator if the issue continues.',
                ], $statusCode);
            }
        });
    })->create();
