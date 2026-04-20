<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(session('api_token'))) {
            // AJAX/JSON requests (e.g. notification badge polling) must not capture
            // `intended` — otherwise the post-login redirect lands the user on a JSON
            // endpoint instead of the page they actually want.
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            session(['intended' => $request->fullUrl()]);

            return redirect()->route('login');
        }

        // Set user every request so @can(), $request->user(), and Spatie stay in sync with session
        $userId = session('user')['id'] ?? null;
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                Auth::setUser($user);
                $this->touchLastActive($user);
            }
        }

        return $next($request);
    }

    private function touchLastActive(User $user): void
    {
        if ($user->last_active_at && $user->last_active_at->greaterThan(now()->subMinute())) {
            return;
        }

        DB::table('users')->where('id', $user->id)->update(['last_active_at' => now()]);
    }
}
