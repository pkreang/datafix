<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use App\Services\NavigationService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NavigationService::class);
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

        View::composer('layouts.app', function ($view) {
            if (session('api_token')) {
                $perms        = session('user_permissions', []);
                $isSuperAdmin = session('user.is_super_admin', false);
                $menus        = app(NavigationService::class)->getMenus($perms, $isSuperAdmin);
                $view->with('navigationMenus', $menus);
            } else {
                $view->with('navigationMenus', collect());
            }
        });
    }
}
