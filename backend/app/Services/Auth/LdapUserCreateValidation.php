<?php

namespace App\Services\Auth;

use Illuminate\Validation\ValidationException;

final class LdapUserCreateValidation
{
    /**
     * @throws ValidationException
     */
    public static function assertEmailAllowedForLocalUserCreate(string $email): void
    {
        if (! LdapUserDirectoryLookup::userCreateValidationRequired()) {
            return;
        }

        if (! LdapUserDirectoryLookup::isReadyForLookup()) {
            throw ValidationException::withMessages([
                'email' => [__('users.ldap_user_create_validation_unavailable')],
            ]);
        }

        $result = LdapUserDirectoryLookup::searchByEmail($email);

        if ($result['type'] === LdapUserDirectoryLookup::TYPE_ERROR) {
            throw ValidationException::withMessages([
                'email' => [__('users.ldap_lookup_failed')],
            ]);
        }

        if ($result['type'] === LdapUserDirectoryLookup::TYPE_NOT_FOUND) {
            throw ValidationException::withMessages([
                'email' => [__('users.email_not_in_ldap')],
            ]);
        }
    }
}
