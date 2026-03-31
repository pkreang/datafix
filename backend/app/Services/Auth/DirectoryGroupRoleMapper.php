<?php

namespace App\Services\Auth;

use App\Models\Setting;
use Spatie\Permission\Models\Role;

class DirectoryGroupRoleMapper
{
    /**
     * Match directory hints (LDAP memberOf DNs, Entra group id/displayName, etc.)
     * against configured rules. Comparison is case-insensitive substring on both sides.
     *
     * @param  list<string>  $hints
     * @return list<string> Existing Spatie role names (guard web), unique, rule order preserved
     */
    public static function resolveRolesFromHints(array $hints): array
    {
        $cleanHints = [];
        foreach ($hints as $h) {
            if (! is_string($h)) {
                continue;
            }
            $t = trim($h);
            if ($t !== '') {
                $cleanHints[] = $t;
            }
        }
        if ($cleanHints === []) {
            return [];
        }

        $raw = (string) Setting::get('auth_directory_group_role_map', '[]');
        $rules = json_decode($raw, true);
        if (! is_array($rules)) {
            return [];
        }

        $matched = [];
        foreach ($rules as $rule) {
            if (! is_array($rule)) {
                continue;
            }
            $pattern = isset($rule['pattern']) ? trim((string) $rule['pattern']) : '';
            $role = isset($rule['role']) ? trim((string) $rule['role']) : '';
            if ($pattern === '' || $role === '') {
                continue;
            }
            $patternLc = self::lower($pattern);
            foreach ($cleanHints as $hint) {
                if (str_contains(self::lower($hint), $patternLc)) {
                    $matched[] = $role;
                    break;
                }
            }
        }

        $matched = array_values(array_unique($matched));
        if ($matched === []) {
            return [];
        }

        $existing = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $matched)
            ->pluck('name')
            ->all();

        // Preserve configured order for roles that exist
        $existingSet = array_flip($existing);

        return array_values(array_filter($matched, static fn (string $n) => isset($existingSet[$n])));
    }

    private static function lower(string $s): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($s, 'UTF-8');
        }

        return strtolower($s);
    }
}
