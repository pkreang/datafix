<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Whether branch records can be managed (Companies UI + branch API mutations).
 * Independent of branch_scoping.* (list filtering).
 */
final class BranchesSetting
{
    public static function managementEnabled(): bool
    {
        return Setting::getBool('branches.enabled', true);
    }
}
