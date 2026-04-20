<?php

namespace App\Services\Mail;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * When mail.use_db_settings is enabled, overlays config/mail.php from the settings table.
 */
class ApplyDatabaseMailConfig
{
    public static function apply(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (QueryException) {
            return;
        }

        if (! Setting::getBool('mail.use_db_settings')) {
            return;
        }

        $mailer = (string) Setting::get('mail.mailer', 'smtp');
        if (! in_array($mailer, ['smtp', 'log', 'sendmail', 'array'], true)) {
            $mailer = 'smtp';
        }

        Config::set('mail.default', $mailer);

        if ($mailer === 'smtp') {
            $host = trim((string) Setting::get('mail.smtp_host', config('mail.mailers.smtp.host')));
            $port = Setting::getInt('mail.smtp_port', (int) config('mail.mailers.smtp.port', 587));
            $username = trim((string) Setting::get('mail.smtp_username', ''));
            $encryption = strtolower(trim((string) Setting::get('mail.smtp_encryption', '')));

            $password = '';
            $enc = Setting::get('mail.smtp_password_enc', '');
            if (is_string($enc) && $enc !== '') {
                try {
                    $password = decrypt($enc);
                } catch (\Throwable) {
                    $password = '';
                }
            }

            $scheme = config('mail.mailers.smtp.scheme');
            if ($encryption === 'ssl' || $port === 465) {
                $scheme = 'smtps';
            } elseif ($encryption === 'tls') {
                $scheme = 'smtp';
            }

            $smtp = array_merge(config('mail.mailers.smtp'), [
                'url' => null,
                'host' => $host !== '' ? $host : config('mail.mailers.smtp.host'),
                'port' => $port,
                'username' => $username !== '' ? $username : null,
                'password' => $password !== '' ? $password : null,
                'scheme' => $scheme,
            ]);

            Config::set('mail.mailers.smtp', $smtp);
        }

        $fromAddress = trim((string) Setting::get('mail.from_address', ''));
        $fromName = trim((string) Setting::get('mail.from_name', ''));

        Config::set('mail.from', [
            'address' => $fromAddress !== '' ? $fromAddress : config('mail.from.address'),
            'name' => $fromName !== '' ? $fromName : config('mail.from.name'),
        ]);
    }
}
