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
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (in_array('super_admin', $roles, true) || in_array('protected_admin', $roles, true)) {
            if (!$user->isProtectedAdmin()) {
                abort(403, 'Unauthorized. Only the Protected Super Admin can access this resource.');
            }
            return $next($request);
        }

        if (!$user->hasRole(...$roles)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
