<?php

namespace App\Services\Auth;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LoginHistoryRecorder
{
    public function recordSuccess(User $user, Request $request, string $provider): void
    {
        LoginHistory::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'auth_provider' => $provider,
            'ip_address' => $this->clientIp($request),
            'user_agent' => $this->truncate($request->userAgent()),
            'result' => 'success',
            'created_at' => now(),
        ]);
    }

    public function recordFailure(?string $email, ?User $user, Request $request, string $provider, string $reason): void
    {
        LoginHistory::create([
            'user_id' => $user?->id,
            'email' => $email ?: $user?->email,
            'auth_provider' => $provider,
            'ip_address' => $this->clientIp($request),
            'user_agent' => $this->truncate($request->userAgent()),
            'result' => 'failed',
            'failure_reason' => $reason,
            'created_at' => now(),
        ]);
    }

    /**
     * Parse a user-agent string into a short, human-readable label.
     * Intentionally simple — no external dependency — covers the ~90% common case.
     */
    public static function summarizeUserAgent(?string $ua): string
    {
        if (! $ua) {
            return '—';
        }

        $browser = 'Browser';
        if (preg_match('/Edg\/([\d.]+)/', $ua, $m)) {
            $browser = 'Edge '.explode('.', $m[1])[0];
        } elseif (preg_match('/OPR\/([\d.]+)/', $ua, $m)) {
            $browser = 'Opera '.explode('.', $m[1])[0];
        } elseif (preg_match('/Chrome\/([\d.]+)/', $ua, $m)) {
            $browser = 'Chrome '.explode('.', $m[1])[0];
        } elseif (preg_match('/Firefox\/([\d.]+)/', $ua, $m)) {
            $browser = 'Firefox '.explode('.', $m[1])[0];
        } elseif (preg_match('/Version\/([\d.]+).*Safari/', $ua, $m)) {
            $browser = 'Safari '.explode('.', $m[1])[0];
        }

        // Check mobile identifiers first — they may co-occur with "Mac OS X" in iOS UAs.
        $os = 'Unknown';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'Windows NT 10')) {
            $os = 'Windows 10/11';
        } elseif (str_contains($ua, 'Mac OS X')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        }

        return $browser.' · '.$os;
    }

    private function clientIp(Request $request): ?string
    {
        // Trust X-Forwarded-For when running behind a known proxy; Laravel handles that
        // via trustedProxies middleware if configured. We fall back to ip() which picks
        // the first forwarded address when trusted.
        return $request->ip();
    }

    private function truncate(?string $s): ?string
    {
        return $s ? mb_substr($s, 0, 512) : null;
    }
}
