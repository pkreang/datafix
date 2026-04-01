<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DashboardWidgetDataController;
use App\Http\Controllers\Api\HomeDashboardKpiController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EquipmentCategoryController;
use App\Http\Controllers\Api\EquipmentLocationController;
use App\Http\Controllers\Api\EquipmentRegistryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Users
        Route::get('/users', [UserController::class, 'index'])->middleware('permission:user_access.read');
        Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:user_access.read');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:user_access.create');
        Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:user_access.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:user_access.delete');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:role_access.read');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('permission:role_access.read');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:role_access.create');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:role_access.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:role_access.delete');

        // Permissions
        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permission_access.read');

        // Companies + Branches
        Route::middleware('permission:manage companies')->group(function () {
            Route::get('/companies', [CompanyController::class, 'index']);
            Route::get('/companies/{company}', [CompanyController::class, 'show']);
            Route::post('/companies', [CompanyController::class, 'store']);
            Route::put('/companies/{company}', [CompanyController::class, 'update']);
            Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);

            Route::get('/companies/{company}/branches', [CompanyController::class, 'branchIndex']);
            Route::post('/companies/{company}/branches', [CompanyController::class, 'branchStore']);
            Route::put('/companies/{company}/branches/{branch}', [CompanyController::class, 'branchUpdate']);
            Route::delete('/companies/{company}/branches/{branch}', [CompanyController::class, 'branchDestroy']);
        });

        // Departments
        Route::middleware('permission:manage_settings')->group(function () {
            Route::get('/departments', [DepartmentController::class, 'index']);
            Route::get('/departments/{department}', [DepartmentController::class, 'show']);
            Route::post('/departments', [DepartmentController::class, 'store']);
            Route::put('/departments/{department}', [DepartmentController::class, 'update']);
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
        });

        // Equipment Categories
        Route::middleware('permission:manage equipment')->group(function () {
            Route::get('/equipment-categories', [EquipmentCategoryController::class, 'index']);
            Route::get('/equipment-categories/{equipmentCategory}', [EquipmentCategoryController::class, 'show']);
            Route::post('/equipment-categories', [EquipmentCategoryController::class, 'store']);
            Route::put('/equipment-categories/{equipmentCategory}', [EquipmentCategoryController::class, 'update']);
            Route::delete('/equipment-categories/{equipmentCategory}', [EquipmentCategoryController::class, 'destroy']);
        });

        // Equipment Locations
        Route::middleware('permission:manage equipment')->group(function () {
            Route::get('/equipment-locations', [EquipmentLocationController::class, 'index']);
            Route::get('/equipment-locations/{equipmentLocation}', [EquipmentLocationController::class, 'show']);
            Route::post('/equipment-locations', [EquipmentLocationController::class, 'store']);
            Route::put('/equipment-locations/{equipmentLocation}', [EquipmentLocationController::class, 'update']);
            Route::delete('/equipment-locations/{equipmentLocation}', [EquipmentLocationController::class, 'destroy']);
        });

        // Equipment Registry
        Route::get('/equipment', [EquipmentRegistryController::class, 'index'])->middleware('permission:view equipment');
        Route::get('/equipment/{equipment}', [EquipmentRegistryController::class, 'show'])->middleware('permission:view equipment');
        Route::post('/equipment', [EquipmentRegistryController::class, 'store'])->middleware('permission:manage equipment');
        Route::put('/equipment/{equipment}', [EquipmentRegistryController::class, 'update'])->middleware('permission:manage equipment');
        Route::delete('/equipment/{equipment}', [EquipmentRegistryController::class, 'destroy'])->middleware('permission:manage equipment');

        // Dashboard widget data
        Route::get('/dashboards/{dashboard}/widgets/{widget}/data',
            [DashboardWidgetDataController::class, 'show']
        );

        // Home dashboard KPI
        Route::get('/dashboard/kpi/{card}', [HomeDashboardKpiController::class, 'show']);
        Route::post('/dashboard/kpi-config', [HomeDashboardKpiController::class, 'saveConfig'])
            ->middleware('permission:manage_own_dashboard');
    });
});
