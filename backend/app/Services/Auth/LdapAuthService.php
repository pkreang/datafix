<?php

namespace App\Services\Auth;

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

        $result = LdapUserDirectoryLookup::searchByEmail($email);
        if ($result['type'] !== LdapUserDirectoryLookup::TYPE_FOUND) {
            return null;
        }

        $userDn = $result['dn'];
        $entry = $result['entry'];

        if (! LdapUserDirectoryLookup::verifyUserPassword($userDn, $password)) {
            return null;
        }

        $normalizedEmail = strtolower(trim($email));
        $mail = isset($entry['mail'][0]) ? strtolower((string) $entry['mail'][0]) : $normalizedEmail;
        $given = isset($entry['givenname'][0]) ? (string) $entry['givenname'][0] : null;
        $sn = isset($entry['sn'][0]) ? (string) $entry['sn'][0] : null;

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
