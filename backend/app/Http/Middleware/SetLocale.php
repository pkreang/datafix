<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'th');
        if (! in_array($locale, ['th', 'en'])) {
            $locale = 'th';
        }
        app()->setLocale($locale);

        // Persist UI language for queued notifications (workers have no session).
        $userId = session('user.id');
        if ($userId && in_array($locale, ['th', 'en'], true)) {
            User::query()
                ->whereKey($userId)
                ->where(function ($q) {
                    $q->whereNull('locale')->orWhere('locale', '');
                })
                ->update(['locale' => $locale]);
        }

        return $next($request);
    }
}
