<?php

namespace App\Providers;

use App\Models\Setting;
use App\Policies\RolePolicy;
use App\Services\Auth\PasswordCapabilityService;
use App\Services\NavigationService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
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
            return config('app.url').'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });

        Gate::before(function ($user, $ability) {
            if ($user?->is_super_admin ?? false) {
                return true;
            }
        });

        View::composer('layouts.app', function ($view) {
            if (session('api_token')) {
                $perms = session('user_permissions', []);
                $isSuperAdmin = session('user.is_super_admin', false);
                $menus = app(NavigationService::class)->getMenus($perms, $isSuperAdmin);
                $view->with('navigationMenus', $menus);
            } else {
                $view->with('navigationMenus', collect());
            }

            $layoutUser = session('user', []);
            $canChangePassword = true;
            if (array_key_exists('can_change_password', $layoutUser)) {
                $canChangePassword = (bool) $layoutUser['can_change_password'];
            } elseif (session('api_token')) {
                $tokenUser = PersonalAccessToken::findToken(session('api_token'))?->tokenable;
                $canChangePassword = PasswordCapabilityService::canChangePasswordInApp($tokenUser);
            }
            $view->with('layoutCanChangePassword', $canChangePassword);
            $view->with('authPasswordHelpUrl', trim((string) Setting::get('auth_password_help_url', '')));
        });
    }
}
