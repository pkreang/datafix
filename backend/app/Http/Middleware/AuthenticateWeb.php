<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(session('api_token'))) {
            session(['intended' => $request->fullUrl()]);

            return redirect()->route('login');
        }

        // Set user every request so @can(), $request->user(), and Spatie stay in sync with session
        $userId = session('user')['id'] ?? null;
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}
