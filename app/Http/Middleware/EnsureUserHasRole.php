<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session has expired. Please log in again to continue.',
                ], 401);
            }

            return redirect()->route('login')->with('warning', 'Your session has expired. Please log in again to continue.');
        }

        $user = Auth::user();

        $normalizedRoles = array_map(
            fn (string $role) => $role === 'protected_admin' ? 'super_admin' : $role,
            $roles
        );

        if (! $user->hasRole(...$normalizedRoles)) {
            abort(403, 'You do not have permission to access this page. Please contact the system administrator if you need access to this feature.');
        }

        return $next($request);
    }
}
