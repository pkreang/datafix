<?php

namespace App\Services\Auth;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * Read-only LDAP search (service bind) — shared by LDAP login and optional user-create validation.
 */
final class LdapUserDirectoryLookup
{
    public const TYPE_FOUND = 'found';

    public const TYPE_NOT_FOUND = 'not_found';

    public const TYPE_ERROR = 'error';

    public static function userCreateValidationRequired(): bool
    {
        return Setting::get('ldap_user_create_validation', 'disabled') === 'required';
    }

    /**
     * LDAP sign-in enabled, extension loaded, host/base/bind/secret configured.
     */
    public static function isReadyForLookup(): bool
    {
        if (! extension_loaded('ldap')) {
            return false;
        }

        if (! AuthModeService::isLdapEnabled()) {
            return false;
        }

        return AuthModeService::ldapConfigured();
    }

    /**
     * @return array{type: self::TYPE_*, dn?: string, entry?: array}
     */
    public static function searchByEmail(string $email): array
    {
        if (! extension_loaded('ldap')) {
            Log::warning('LDAP lookup requested but PHP ldap extension is not loaded.');

            return ['type' => self::TYPE_ERROR];
        }

        $host = (string) Setting::get('ldap_host', '');
        $port = (int) Setting::get('ldap_port', 389);
        $baseDn = (string) Setting::get('ldap_base_dn', '');
        $bindDn = (string) Setting::get('ldap_bind_dn', '');
        $bindPassword = (string) config('services.ldap.bind_password', '');
        $useTls = Setting::getBool('ldap_use_tls', false);
        $filterTemplate = (string) Setting::get('ldap_user_filter', '(mail=%s)');

        if ($host === '' || $baseDn === '' || $bindDn === '' || $bindPassword === '') {
            return ['type' => self::TYPE_ERROR];
        }

        $email = strtolower(trim($email));
        if ($email === '') {
            return ['type' => self::TYPE_NOT_FOUND];
        }

        $escaped = ldap_escape($email, '', LDAP_ESCAPE_FILTER);
        $filter = str_contains($filterTemplate, '%s')
            ? sprintf($filterTemplate, $escaped)
            : '(&(objectClass=*)(mail='.$escaped.'))';

        $conn = @ldap_connect($host, $port);
        if ($conn === false) {
            Log::warning('LDAP lookup: connect failed.');

            return ['type' => self::TYPE_ERROR];
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        if ($useTls) {
            if (! @ldap_start_tls($conn)) {
                @ldap_close($conn);
                Log::warning('LDAP lookup: STARTTLS failed.');

                return ['type' => self::TYPE_ERROR];
            }
        }

        if (! @ldap_bind($conn, $bindDn, $bindPassword)) {
            @ldap_close($conn);
            Log::warning('LDAP lookup: service bind failed.');

            return ['type' => self::TYPE_ERROR];
        }

        $search = @ldap_search($conn, $baseDn, $filter, ['dn', 'mail', 'givenName', 'sn', 'cn', 'memberOf'], 0, 1, 10);
        if ($search === false) {
            @ldap_close($conn);
            Log::warning('LDAP lookup: search failed.');

            return ['type' => self::TYPE_ERROR];
        }

        $entries = @ldap_get_entries($conn, $search);
        @ldap_close($conn);

        if ($entries === false || ($entries['count'] ?? 0) < 1) {
            return ['type' => self::TYPE_NOT_FOUND];
        }

        $entry = $entries[0];
        $userDn = $entry['dn'] ?? null;
        if (! is_string($userDn) || $userDn === '') {
            return ['type' => self::TYPE_ERROR];
        }

        return [
            'type' => self::TYPE_FOUND,
            'dn' => $userDn,
            'entry' => $entry,
        ];
    }

    public static function verifyUserPassword(string $userDn, string $password): bool
    {
        $host = (string) Setting::get('ldap_host', '');
        $port = (int) Setting::get('ldap_port', 389);
        $useTls = Setting::getBool('ldap_use_tls', false);

        if ($host === '' || $userDn === '') {
            return false;
        }

        $conn = @ldap_connect($host, $port);
        if ($conn === false) {
            return false;
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        if ($useTls) {
            if (! @ldap_start_tls($conn)) {
                @ldap_close($conn);

                return false;
            }
        }

        $ok = @ldap_bind($conn, $userDn, $password);
        @ldap_close($conn);

        return (bool) $ok;
    }
}
