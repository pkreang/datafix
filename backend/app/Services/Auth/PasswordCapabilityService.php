<?php

namespace App\Services\Auth;

use App\Models\User;

class PasswordCapabilityService
{
    /**
     * Whether the user may change the app-stored password (local account).
     * Directory users (LDAP / Entra) authenticate against the IdP; changing only
     * the local hash would not update AD and is therefore disabled in the UI.
     */
    public static function canChangePasswordFromAuthProvider(?string $authProvider): bool
    {
        if ($authProvider === null || $authProvider === '') {
            return true;
        }

        return $authProvider === 'local';
    }

    public static function canChangePasswordInApp(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::canChangePasswordFromAuthProvider($user->auth_provider);
    }

    /**
     * Whether the user may change the email stored in the app (admin user edit / profile).
     * Same rule as password: directory identities (LDAP / Entra) are managed by the IdP.
     */
    public static function canEditEmailInApp(?User $user): bool
    {
        return self::canChangePasswordInApp($user);
    }
}
