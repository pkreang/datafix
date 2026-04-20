<?php

namespace App\Rules;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserPasswordHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class PasswordNotReused implements ValidationRule
{
    public function __construct(
        private readonly User $user
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $current = $this->user->getRawOriginal('password');
        if ($current && Hash::check($value, $current)) {
            $fail(__('validation.password_same_as_current'));

            return;
        }

        $n = Setting::getInt('password_prevent_reuse', 0);
        if ($n <= 0) {
            return;
        }

        $hashes = UserPasswordHistory::query()
            ->where('user_id', $this->user->id)
            ->orderByDesc('id')
            ->limit($n)
            ->pluck('password_hash');

        foreach ($hashes as $hash) {
            if (Hash::check($value, $hash)) {
                $fail(__('validation.password_reused'));

                return;
            }
        }
    }
}
