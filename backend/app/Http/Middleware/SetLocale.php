<?php

namespace App\Http\Middleware;

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

        return $next($request);
    }
}
