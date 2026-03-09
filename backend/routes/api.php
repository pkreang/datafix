<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/users', [UserController::class, 'index'])->middleware('permission:user_access.read');
        Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:user_access.read');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:user_access.create');
        Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:user_access.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:user_access.delete');

        Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:role_access.read');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('permission:role_access.read');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:role_access.create');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:role_access.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:role_access.delete');

        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permission_access.read');
    });
});
