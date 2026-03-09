<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);

        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            return config('app.url') . '/reset-password?token=' . $token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        });

        Gate::before(function ($user, $ability) {
            if ($user?->is_super_admin ?? false) {
                return true;
            }
        });
    }
}
