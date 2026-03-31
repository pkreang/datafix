<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Emergency recovery when bootstrap admin cannot sign in locally (wrong hash, SSO-linked row, etc.).
 */
class ResetBootstrapAdminCommand extends Command
{
    protected $signature = 'user:reset-bootstrap-admin
                            {--email=admin@example.com : User email}
                            {--password=password : New plain password}';

    protected $description = 'Set local password and clear Entra/LDAP identity fields for the bootstrap admin user';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));
        $plain = (string) $this->option('password');

        $user = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if (! $user) {
            $this->error("No user found for email: {$email}");

            return self::FAILURE;
        }

        $user->forceFill([
            'password' => $plain,
            'auth_provider' => null,
            'external_id' => null,
            'ldap_dn' => null,
            'is_active' => true,
        ])->save();

        $this->info("OK: {$email} — local password updated, directory fields cleared.");

        return self::SUCCESS;
    }
}
