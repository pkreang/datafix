<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets app locale for JSON API from Accept-Language, X-Locale, or ?locale= (th|en), then config.
 */
class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $header = (string) $request->header('Accept-Language', '');
        if ($header !== '') {
            $first = trim(explode(',', $header)[0]);
            $tag = strtolower(trim(explode(';', $first)[0]));
            $lang = explode('-', $tag)[0];
            if (in_array($lang, ['th', 'en'], true)) {
                return $lang;
            }
        }

        $x = strtolower(trim((string) $request->header('X-Locale', '')));
        if (in_array($x, ['th', 'en'], true)) {
            return $x;
        }

        $q = strtolower(trim((string) $request->query('locale', '')));
        if (in_array($q, ['th', 'en'], true)) {
            return $q;
        }

        $app = (string) config('app.locale', 'th');

        return in_array($app, ['th', 'en'], true) ? $app : 'th';
    }
}
