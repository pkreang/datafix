<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(session('api_token'))) {
            session(['intended' => $request->fullUrl()]);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
