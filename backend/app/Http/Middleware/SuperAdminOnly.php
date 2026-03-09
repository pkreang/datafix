<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('user.is_super_admin')) {
            abort(403, 'Access denied. Super-admin only.');
        }

        return $next($request);
    }
}
