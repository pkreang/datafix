<?php

namespace App\Services\Auth;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LdapAuthService
{
    public function attempt(string $email, string $password): ?User
    {
        if (! extension_loaded('ldap')) {
            Log::warning('LDAP auth requested but PHP ldap extension is not loaded.');

            return null;
        }

        $host = (string) Setting::get('ldap_host', '');
        $port = (int) Setting::get('ldap_port', 389);
        $baseDn = (string) Setting::get('ldap_base_dn', '');
        $bindDn = (string) Setting::get('ldap_bind_dn', '');
        $bindPassword = (string) config('services.ldap.bind_password', '');
        $useTls = Setting::getBool('ldap_use_tls', false);
        $filterTemplate = (string) Setting::get('ldap_user_filter', '(mail=%s)');

        if ($host === '' || $baseDn === '' || $bindDn === '' || $bindPassword === '') {
            return null;
        }

        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }

        $escaped = ldap_escape($email, '', LDAP_ESCAPE_FILTER);
        $filter = str_contains($filterTemplate, '%s')
            ? sprintf($filterTemplate, $escaped)
            : '(&(objectClass=*)(mail='.$escaped.'))';

        $conn = @ldap_connect($host, $port);
        if ($conn === false) {
            return null;
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        if ($useTls) {
            if (! @ldap_start_tls($conn)) {
                @ldap_close($conn);

                return null;
            }
        }

        if (! @ldap_bind($conn, $bindDn, $bindPassword)) {
            @ldap_close($conn);
            Log::warning('LDAP service bind failed.');

            return null;
        }

        $search = @ldap_search($conn, $baseDn, $filter, ['dn', 'mail', 'givenName', 'sn', 'cn', 'memberOf'], 0, 1, 10);
        if ($search === false) {
            @ldap_close($conn);

            return null;
        }

        $entries = @ldap_get_entries($conn, $search);
        if ($entries === false || ($entries['count'] ?? 0) < 1) {
            @ldap_close($conn);

            return null;
        }

        $entry = $entries[0];
        $userDn = $entry['dn'] ?? null;
        if (! is_string($userDn) || $userDn === '') {
            @ldap_close($conn);

            return null;
        }

        if (! @ldap_bind($conn, $userDn, $password)) {
            @ldap_close($conn);

            return null;
        }

        $mail = isset($entry['mail'][0]) ? strtolower((string) $entry['mail'][0]) : $email;
        $given = isset($entry['givenname'][0]) ? (string) $entry['givenname'][0] : null;
        $sn = isset($entry['sn'][0]) ? (string) $entry['sn'][0] : null;

        @ldap_close($conn);

        $groupHints = [];
        if (isset($entry['memberof']['count'])) {
            $n = (int) $entry['memberof']['count'];
            for ($i = 0; $i < $n; $i++) {
                if (isset($entry['memberof'][$i]) && is_string($entry['memberof'][$i])) {
                    $groupHints[] = $entry['memberof'][$i];
                }
            }
        }

        $externalId = md5(strtolower($userDn));
        $provisioner = app(DirectoryUserProvisioner::class);

        return $provisioner->findOrCreate('ldap', $externalId, $mail, $given, $sn, $userDn, $groupHints);
    }
}
