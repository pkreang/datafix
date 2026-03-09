<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\NavigationMenuController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.web')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    Route::get('/settings/password-policy', [SettingController::class, 'passwordPolicy'])->name('settings.password-policy');
    Route::post('/settings/password-policy', [SettingController::class, 'savePasswordPolicy'])->name('settings.password-policy.save');

    Route::middleware('super-admin')->group(function () {
        Route::get('/settings/navigation', [NavigationMenuController::class, 'index'])->name('settings.navigation.index');
        Route::get('/settings/navigation/create', [NavigationMenuController::class, 'create'])->name('settings.navigation.create');
        Route::post('/settings/navigation', [NavigationMenuController::class, 'store'])->name('settings.navigation.store');
        Route::get('/settings/navigation/{navigation}/edit', [NavigationMenuController::class, 'edit'])->name('settings.navigation.edit');
        Route::put('/settings/navigation/{navigation}', [NavigationMenuController::class, 'update'])->name('settings.navigation.update');
        Route::delete('/settings/navigation/{navigation}', [NavigationMenuController::class, 'destroy'])->name('settings.navigation.destroy');
        Route::patch('/settings/navigation/reorder', [NavigationMenuController::class, 'reorder'])->name('settings.navigation.reorder');
        Route::patch('/settings/navigation/{navigation}/toggle', [NavigationMenuController::class, 'toggle'])->name('settings.navigation.toggle');
    });
});
