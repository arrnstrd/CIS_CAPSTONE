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

            // Handle cloud database / DNS connectivity drop
            $isDbConnectionError = false;
            $checkExceptions = [$e];
            if ($e->getPrevious()) {
                $checkExceptions[] = $e->getPrevious();
            }

            foreach ($checkExceptions as $candidate) {
                if ($candidate instanceof \Illuminate\Database\QueryException || $candidate instanceof \PDOException) {
                    $code = (string) $candidate->getCode();
                    $msg = strtolower($candidate->getMessage());

                    if (
                        $code === '08006' ||
                        $code === '7' ||
                        str_starts_with($code, '08') ||
                        str_contains($msg, 'could not translate host name') ||
                        str_contains($msg, 'temporary failure in name resolution') ||
                        str_contains($msg, 'connection refused') ||
                        str_contains($msg, 'connection to server at') ||
                        str_contains($msg, 'server closed the connection unexpectedly') ||
                        str_contains($msg, 'network is unreachable') ||
                        str_contains($msg, 'ssl connection has been closed unexpectedly')
                    ) {
                        $isDbConnectionError = true;
                        break;
                    }
                }
            }

            if ($isDbConnectionError) {
                \Illuminate\Support\Facades\Log::error('Cloud Database Connection Error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'url' => $request->fullUrl(),
                ]);

                $userTitle = 'Cloud Database Service Temporarily Unavailable';
                $userMessage = 'The application is temporarily unable to connect to the cloud data service due to a network resolution delay. No data was lost. Please wait a moment and refresh the page.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'title' => $userTitle,
                        'message' => $userMessage,
                    ], 503);
                }

                return response()->view('errors.503', [
                    'title' => $userTitle,
                    'message' => $userMessage,
                    'badge' => 'Service Notice',
                    'icon' => 'fa-cloud-slash',
                ], 503);
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
