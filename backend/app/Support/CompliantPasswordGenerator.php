<?php

namespace App\Support;

use App\Models\Setting;
use App\Rules\PasswordPolicy;
use Illuminate\Support\Facades\Validator;

/**
 * Random password satisfying current {@see PasswordPolicy} / settings.
 */
final class CompliantPasswordGenerator
{
    public static function generate(): string
    {
        $min = max(Setting::getInt('password_min_length', 8), 1);
        $max = max(Setting::getInt('password_max_length', 255), $min);
        $length = min(max($min, 12), $max);

        for ($attempt = 0; $attempt < 50; $attempt++) {
            $password = self::buildCandidate($length);
            $validator = Validator::make(
                ['password' => $password],
                ['password' => [new PasswordPolicy]]
            );
            if (! $validator->fails()) {
                return $password;
            }
        }

        throw new \RuntimeException('Could not generate a password matching the current policy.');
    }

    private static function buildCandidate(int $length): string
    {
        $chars = [];

        if (Setting::getBool('password_require_uppercase')) {
            $chars[] = chr(random_int(65, 90));
        }
        if (Setting::getBool('password_require_lowercase')) {
            $chars[] = chr(random_int(97, 122));
        }
        if (Setting::getBool('password_require_number')) {
            $chars[] = (string) random_int(0, 9);
        }
        if (Setting::getBool('password_require_special')) {
            $specials = '!@#$%&*-_=+';
            $chars[] = $specials[random_int(0, strlen($specials) - 1)];
        }

        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if (Setting::getBool('password_require_special')) {
            $pool .= '!@#$%&*-_=+';
        }

        while (count($chars) < $length) {
            $chars[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($chars);

        return implode('', $chars);
    }
}
