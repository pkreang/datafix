<?php

namespace App\Http\Middleware;

use App\Services\Auth\PasswordCapabilityService;
use App\Services\Auth\PasswordLifecycleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user || ! PasswordCapabilityService::canChangePasswordInApp($user)) {
            return $next($request);
        }

        if (! PasswordLifecycleService::requiresPasswordChange($user)) {
            return $next($request);
        }

        if ($request->routeIs('logout', 'profile.password', 'profile.password.update', 'lang.switch')) {
            return $next($request);
        }

        return redirect()
            ->route('profile.password')
            ->with('warning', __('auth.password_change_required'));
    }
}
