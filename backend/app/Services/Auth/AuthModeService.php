<?php

namespace App\Services\Auth;

use App\Models\Setting;

class AuthModeService
{
    public static function isLocalEnabled(): bool
    {
        return Setting::getBool('auth_local_enabled', true);
    }

    public static function isEntraEnabled(): bool
    {
        return Setting::getBool('auth_entra_enabled', false);
    }

    public static function isLdapEnabled(): bool
    {
        return Setting::getBool('auth_ldap_enabled', false);
    }

    public static function isLocalSuperAdminOnly(): bool
    {
        return Setting::getBool('auth_local_super_admin_only', false);
    }

    public static function anyMethodEnabled(): bool
    {
        return self::isLocalEnabled() || self::isEntraEnabled() || self::isLdapEnabled();
    }

    public static function entraConfigured(): bool
    {
        $tenant = trim((string) Setting::get('entra_tenant_id', ''));
        $clientId = trim((string) Setting::get('entra_client_id', ''));
        $secret = trim((string) config('services.entra.client_secret', ''));

        return $tenant !== '' && $clientId !== '' && $secret !== '';
    }

    public static function ldapConfigured(): bool
    {
        $host = trim((string) Setting::get('ldap_host', ''));
        $base = trim((string) Setting::get('ldap_base_dn', ''));
        $bindDn = trim((string) Setting::get('ldap_bind_dn', ''));
        $bindPassword = trim((string) config('services.ldap.bind_password', ''));

        return $host !== '' && $base !== '' && $bindDn !== '' && $bindPassword !== '';
    }
}
