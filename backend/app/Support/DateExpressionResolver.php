<?php

namespace App\Support;

class DateExpressionResolver
{
    /**
     * Resolve date constraint expression used in form field validation_rules.min_date/max_date.
     *
     * Supports keywords (today, yesterday, tomorrow) and YYYY-MM-DD literals.
     * Returns null when the value is empty or malformed — callers should treat that as "no constraint".
     */
    public static function resolve(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return null;
        }

        return match ($value) {
            'today' => now()->toDateString(),
            'yesterday' => now()->subDay()->toDateString(),
            'tomorrow' => now()->addDay()->toDateString(),
            default => preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null,
        };
    }
}
