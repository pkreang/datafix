<?php

namespace App\Http\Middleware;

use App\Services\Auth\PasswordCapabilityService;
use App\Services\Auth\PasswordLifecycleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChangeForSanctum
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! PasswordCapabilityService::canChangePasswordInApp($user)) {
            return $next($request);
        }

        if (! PasswordLifecycleService::requiresPasswordChange($user)) {
            return $next($request);
        }

        if ($request->is('api/v1/auth/logout', 'api/v1/auth/me', 'api/v1/auth/password')) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.password_change_required'),
            'password_change_required' => true,
        ], 403);
    }
}
