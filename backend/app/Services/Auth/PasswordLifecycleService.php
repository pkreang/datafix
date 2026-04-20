<?php

namespace App\Services\Auth;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserPasswordHistory;

class PasswordLifecycleService
{
    /**
     * Local accounts only: directory users are excluded from in-app password rules.
     */
    public static function requiresPasswordChange(User $user): bool
    {
        if (! PasswordCapabilityService::canChangePasswordInApp($user)) {
            return false;
        }

        if ($user->password_must_change) {
            return true;
        }

        $days = Setting::getInt('password_expires_days', 0);
        if ($days <= 0) {
            return false;
        }

        $changedAt = $user->password_changed_at ?? $user->created_at;
        if ($changedAt === null) {
            return true;
        }

        return $changedAt->copy()->addDays($days)->isPast();
    }

    /**
     * After the user successfully changes their own password (profile / API).
     */
    public static function applySelfServicePasswordChange(User $user, string $plainNewPassword): void
    {
        self::rememberOldPasswordHash($user);

        $user->forceFill([
            'password' => $plainNewPassword,
            'password_changed_at' => now(),
            'password_must_change' => false,
        ])->save();
    }

    /**
     * When an administrator assigns a new password (API or future web flows).
     */
    public static function applyAdminPasswordAssignment(User $user, string $plainNewPassword): void
    {
        self::rememberOldPasswordHash($user);

        $user->forceFill([
            'password' => $plainNewPassword,
            'password_changed_at' => now(),
            'password_must_change' => Setting::getBool('password_force_change_first_login'),
        ])->save();
    }

    public static function rememberOldPasswordHash(User $user): void
    {
        $reuseCount = Setting::getInt('password_prevent_reuse', 0);
        if ($reuseCount <= 0) {
            return;
        }

        $oldHash = $user->getRawOriginal('password');
        if ($oldHash === null || $oldHash === '') {
            return;
        }

        UserPasswordHistory::query()->create([
            'user_id' => $user->id,
            'password_hash' => $oldHash,
        ]);

        self::pruneHistory($user->id, $reuseCount);
    }

    public static function pruneHistory(int $userId, int $keepCount): void
    {
        if ($keepCount <= 0) {
            return;
        }

        $idsToDrop = UserPasswordHistory::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->skip($keepCount)
            ->pluck('id');

        if ($idsToDrop->isNotEmpty()) {
            UserPasswordHistory::query()->whereIn('id', $idsToDrop)->delete();
        }
    }
}
