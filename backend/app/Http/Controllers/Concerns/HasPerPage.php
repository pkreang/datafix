<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

trait HasPerPage
{
    /**
     * Resolve items-per-page from query → cookie → default, with 30-day cookie persistence.
     *
     * Allowed values: 10, 25, 50. Anything else falls through to $default.
     */
    protected function resolvePerPage(Request $request, string $cookieName, int $default = 10): int
    {
        $allowed = [10, 25, 50];

        $queryValue = (int) $request->query('per_page', 0);
        if (in_array($queryValue, $allowed, true)) {
            Cookie::queue($cookieName, (string) $queryValue, 60 * 24 * 30);

            return $queryValue;
        }

        $cookieValue = (int) $request->cookie($cookieName, 0);
        if (in_array($cookieValue, $allowed, true)) {
            return $cookieValue;
        }

        return $default;
    }
}
