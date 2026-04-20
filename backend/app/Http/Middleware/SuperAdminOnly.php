<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Instance setup / sensitive settings — only users with users.is_super_admin = true.
 * Not the same as Spatie roles "admin" / "super-admin".
 */
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user()?->is_super_admin) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.super_admin_only'),
                ], 403);
            }

            abort(403, 'Access denied. Super-admin only.');
        }

        return $next($request);
    }
}
