<?php

use App\Http\Controllers\Web\ActivityHistoryController;
use App\Http\Controllers\Web\ApprovalController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\DocumentFormController;
use App\Http\Controllers\Web\DocumentFormWorkflowPolicyController;
use App\Http\Controllers\Web\DocumentTypeController;
use App\Http\Controllers\Web\EquipmentController;
use App\Http\Controllers\Web\EquipmentLocationController;
use App\Http\Controllers\Web\EquipmentRegistryController;
use App\Http\Controllers\Web\MaintenanceController;
use App\Http\Controllers\Web\NavigationMenuController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\NotificationSettingController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\RepairRequestController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\SparePartsController;
use App\Http\Controllers\Web\LookupController;
use App\Http\Controllers\Web\RunningNumberController;
use App\Http\Controllers\Web\ThailandAddressSearchController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ReportDashboardController;
use App\Http\Controllers\Web\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['th', 'en'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('lang.switch');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/auth/entra/redirect', [AuthController::class, 'redirectToEntra'])->name('auth.entra.redirect');
Route::get('/auth/entra/callback', [AuthController::class, 'entraCallback'])->name('auth.entra.callback');
Route::post('/auth/ldap/login', [AuthController::class, 'loginLdap'])->name('auth.ldap.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.web')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/repair-requests', [RepairRequestController::class, 'index'])->name('repair-requests.index');
    Route::get('/repair-requests/my-jobs', [RepairRequestController::class, 'myJobs'])->name('repair-requests.my-jobs');
    Route::get('/repair-requests/assign', [RepairRequestController::class, 'assign'])->name('repair-requests.assign');
    Route::get('/repair-requests/evaluate', [RepairRequestController::class, 'evaluate'])->name('repair-requests.evaluate');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/create-plan', [MaintenanceController::class, 'createPlan'])->name('maintenance.create-plan');
    Route::get('/maintenance/auto-assign', [MaintenanceController::class, 'autoAssign'])->name('maintenance.auto-assign');
    Route::post('/maintenance/create-plan', [MaintenanceController::class, 'submitPlan'])
        ->name('maintenance.create-plan.submit');
    Route::get('/maintenance/{instance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/repair-history', [ReportController::class, 'repairHistory'])->name('reports.repair-history');
    Route::get('/reports/pm-am-history', [ReportController::class, 'pmAmHistory'])->name('reports.pm-am-history');
    Route::get('/reports/dashboards/{dashboard}', [ReportController::class, 'showDashboard'])->name('reports.dashboards.show');
    Route::get('/spare-parts/stock', [SparePartsController::class, 'stock'])->name('spare-parts.stock');
    Route::get('/spare-parts/withdrawal-history', [SparePartsController::class, 'withdrawalHistory'])->name('spare-parts.withdrawal-history');
    Route::get('/spare-parts/requisition', [SparePartsController::class, 'requisitionIndex'])->name('spare-parts.requisition.index');
    Route::get('/spare-parts/requisition/create', [SparePartsController::class, 'requisitionCreate'])->name('spare-parts.requisition.create');
    Route::post('/spare-parts/requisition', [SparePartsController::class, 'requisitionSubmit'])->name('spare-parts.requisition.submit');
    Route::get('/spare-parts/requisition/{instance}', [SparePartsController::class, 'requisitionShow'])->name('spare-parts.requisition.show');
    Route::post('/spare-parts/requisition/{instance}/issue', [SparePartsController::class, 'issueItems'])
        ->middleware('permission:spare_parts.manage')
        ->name('spare-parts.requisition.issue');
    Route::get('/equipment-registry', [EquipmentRegistryController::class, 'index'])->name('equipment-registry.index');
    Route::get('/equipment-registry/create', [EquipmentRegistryController::class, 'create'])->name('equipment-registry.create');
    Route::post('/equipment-registry', [EquipmentRegistryController::class, 'store'])->name('equipment-registry.store');
    Route::get('/equipment-registry/{equipment}/edit', [EquipmentRegistryController::class, 'edit'])->name('equipment-registry.edit');
    Route::put('/equipment-registry/{equipment}', [EquipmentRegistryController::class, 'update'])->name('equipment-registry.update');
    Route::delete('/equipment-registry/{equipment}', [EquipmentRegistryController::class, 'destroy'])->name('equipment-registry.destroy');
    Route::get('/equipment-locations', [EquipmentLocationController::class, 'browse'])->name('equipment-locations.index');
    Route::post('/repair-requests', [RepairRequestController::class, 'submit'])
        ->name('repair-requests.submit');
    Route::get('/repair-requests/{instance}', [RepairRequestController::class, 'show'])
        ->name('repair-requests.show');
    Route::get('/approvals/my', [ApprovalController::class, 'myApprovals'])
        ->middleware('permission:approval.approve')
        ->name('approvals.my');
    Route::post('/approvals/{instance}/act', [ApprovalController::class, 'act'])
        ->middleware('permission:approval.approve')
        ->name('approvals.act');
    Route::get('/addresses/thailand/subdistricts', [ThailandAddressSearchController::class, 'subdistricts'])
        ->name('addresses.thailand.subdistricts');
    Route::get('/lookup', [LookupController::class, 'index'])->name('lookup.index');
    Route::resource('companies', CompanyController::class);
    Route::post('companies/{company}/branches', [CompanyController::class, 'storeBranch'])->name('companies.branches.store');
    Route::put('companies/{company}/branches/{branch}', [CompanyController::class, 'updateBranch'])->name('companies.branches.update');
    Route::delete('companies/{company}/branches/{branch}', [CompanyController::class, 'destroyBranch'])->name('companies.branches.destroy');
    Route::get('/users/import', [UserController::class, 'importForm'])->name('users.import');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import.store');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'showPasswordForm'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/settings/password-policy', [SettingController::class, 'passwordPolicy'])->name('settings.password-policy');
    Route::post('/settings/password-policy', [SettingController::class, 'savePasswordPolicy'])->name('settings.password-policy.save');

    Route::middleware('super-admin')->group(function () {
        Route::get('/settings/branding', [SettingController::class, 'branding'])->name('settings.branding');
        Route::post('/settings/branding', [SettingController::class, 'saveBranding'])->name('settings.branding.save');
        Route::get('/settings/departments', [DepartmentController::class, 'index'])->name('settings.departments.index');
        Route::get('/settings/department-workflow-bindings', [DepartmentController::class, 'workflowBindingsMatrix'])->name('settings.department-workflow-bindings.index');
        Route::post('/settings/department-workflow-bindings', [DepartmentController::class, 'bulkBindWorkflows'])->name('settings.department-workflow-bindings.bulk');
        Route::get('/settings/departments/create', [DepartmentController::class, 'create'])->name('settings.departments.create');
        Route::post('/settings/departments', [DepartmentController::class, 'store'])->name('settings.departments.store');
        Route::get('/settings/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('settings.departments.edit');
        Route::put('/settings/departments/{department}', [DepartmentController::class, 'update'])->name('settings.departments.update');
        Route::delete('/settings/departments/{department}', [DepartmentController::class, 'destroy'])->name('settings.departments.destroy');
        Route::post('/settings/departments/{department}/bindings', [DepartmentController::class, 'bindWorkflow'])->name('settings.departments.bindings.store');
        Route::get('/settings/positions', [PositionController::class, 'index'])->name('settings.positions.index');
        Route::get('/settings/positions/create', [PositionController::class, 'create'])->name('settings.positions.create');
        Route::post('/settings/positions', [PositionController::class, 'store'])->name('settings.positions.store');
        Route::get('/settings/positions/{position}/edit', [PositionController::class, 'edit'])->name('settings.positions.edit');
        Route::put('/settings/positions/{position}', [PositionController::class, 'update'])->name('settings.positions.update');
        Route::delete('/settings/positions/{position}', [PositionController::class, 'destroy'])->name('settings.positions.destroy');
        Route::get('/settings/workflow', [WorkflowController::class, 'index'])->name('settings.workflow.index');
        Route::get('/settings/workflow/create', [WorkflowController::class, 'create'])->name('settings.workflow.create');
        Route::post('/settings/workflow', [WorkflowController::class, 'store'])->name('settings.workflow.store');
        Route::get('/settings/workflow/{workflow}/edit', [WorkflowController::class, 'edit'])->name('settings.workflow.edit');
        Route::put('/settings/workflow/{workflow}', [WorkflowController::class, 'update'])->name('settings.workflow.update');
        Route::delete('/settings/workflow/{workflow}', [WorkflowController::class, 'destroy'])->name('settings.workflow.destroy');
        Route::post('/settings/workflow/{workflow}/stages', [WorkflowController::class, 'addStage'])->name('settings.workflow.stages.store');
        Route::get('/settings/approval-routing', [SettingController::class, 'approvalRouting'])->name('settings.approval-routing');
        Route::post('/settings/approval-routing', [SettingController::class, 'saveApprovalRouting'])->name('settings.approval-routing.save');
        Route::get('/settings/authentication', [SettingController::class, 'authSettings'])->name('settings.auth');
        Route::post('/settings/authentication', [SettingController::class, 'saveAuthSettings'])->name('settings.auth.save');
        Route::get('/settings/document-types', [DocumentTypeController::class, 'index'])->name('settings.document-types.index');
        Route::get('/settings/document-types/create', [DocumentTypeController::class, 'create'])->name('settings.document-types.create');
        Route::post('/settings/document-types', [DocumentTypeController::class, 'store'])->name('settings.document-types.store');
        Route::get('/settings/document-types/{documentType}/edit', [DocumentTypeController::class, 'edit'])->name('settings.document-types.edit');
        Route::put('/settings/document-types/{documentType}', [DocumentTypeController::class, 'update'])->name('settings.document-types.update');
        Route::delete('/settings/document-types/{documentType}', [DocumentTypeController::class, 'destroy'])->name('settings.document-types.destroy');
        Route::get('/settings/document-forms', [DocumentFormController::class, 'index'])->name('settings.document-forms.index');
        Route::get('/settings/document-forms/create', [DocumentFormController::class, 'create'])->name('settings.document-forms.create');
        Route::post('/settings/document-forms', [DocumentFormController::class, 'store'])->name('settings.document-forms.store');
        Route::get('/settings/document-forms/{documentForm}/edit', [DocumentFormController::class, 'edit'])->name('settings.document-forms.edit');
        Route::put('/settings/document-forms/{documentForm}', [DocumentFormController::class, 'update'])->name('settings.document-forms.update');
        Route::delete('/settings/document-forms/{documentForm}', [DocumentFormController::class, 'destroy'])->name('settings.document-forms.destroy');
        Route::get('/settings/document-forms/{documentForm}/policy', [DocumentFormWorkflowPolicyController::class, 'edit'])->name('settings.document-forms.policy.edit');
        Route::put('/settings/document-forms/{documentForm}/policy', [DocumentFormWorkflowPolicyController::class, 'update'])->name('settings.document-forms.policy.update');
        Route::get('/settings/equipment', [EquipmentController::class, 'index'])->name('settings.equipment.index');
        Route::get('/settings/equipment/create', [EquipmentController::class, 'create'])->name('settings.equipment.create');
        Route::post('/settings/equipment', [EquipmentController::class, 'store'])->name('settings.equipment.store');
        Route::get('/settings/equipment/{equipmentCategory}/edit', [EquipmentController::class, 'edit'])->name('settings.equipment.edit');
        Route::put('/settings/equipment/{equipmentCategory}', [EquipmentController::class, 'update'])->name('settings.equipment.update');
        Route::delete('/settings/equipment/{equipmentCategory}', [EquipmentController::class, 'destroy'])->name('settings.equipment.destroy');
        Route::get('/settings/notifications', [NotificationSettingController::class, 'index'])->name('settings.notifications.index');
        Route::put('/settings/notifications', [NotificationSettingController::class, 'update'])->name('settings.notifications.update');
        Route::get('/settings/equipment-locations', [EquipmentLocationController::class, 'index'])->name('settings.equipment-locations.index');
        Route::get('/settings/equipment-locations/create', [EquipmentLocationController::class, 'create'])->name('settings.equipment-locations.create');
        Route::post('/settings/equipment-locations', [EquipmentLocationController::class, 'store'])->name('settings.equipment-locations.store');
        Route::get('/settings/equipment-locations/{equipmentLocation}/edit', [EquipmentLocationController::class, 'edit'])->name('settings.equipment-locations.edit');
        Route::put('/settings/equipment-locations/{equipmentLocation}', [EquipmentLocationController::class, 'update'])->name('settings.equipment-locations.update');
        Route::delete('/settings/equipment-locations/{equipmentLocation}', [EquipmentLocationController::class, 'destroy'])->name('settings.equipment-locations.destroy');
        Route::get('/settings/running-numbers', [RunningNumberController::class, 'index'])->name('settings.running-numbers.index');
        Route::get('/settings/running-numbers/create', [RunningNumberController::class, 'create'])->name('settings.running-numbers.create');
        Route::post('/settings/running-numbers', [RunningNumberController::class, 'store'])->name('settings.running-numbers.store');
        Route::get('/settings/running-numbers/{runningNumberConfig}/edit', [RunningNumberController::class, 'edit'])->name('settings.running-numbers.edit');
        Route::put('/settings/running-numbers/{runningNumberConfig}', [RunningNumberController::class, 'update'])->name('settings.running-numbers.update');
        Route::delete('/settings/running-numbers/{runningNumberConfig}', [RunningNumberController::class, 'destroy'])->name('settings.running-numbers.destroy');
        Route::post('/settings/running-numbers/{runningNumberConfig}/reset', [RunningNumberController::class, 'reset'])->name('settings.running-numbers.reset');
        Route::get('/settings/activity-history', [ActivityHistoryController::class, 'index'])->name('settings.activity-history.index');
        Route::get('/settings/navigation', [NavigationMenuController::class, 'index'])->name('settings.navigation.index');
        Route::get('/settings/navigation/create', [NavigationMenuController::class, 'create'])->name('settings.navigation.create');
        Route::post('/settings/navigation', [NavigationMenuController::class, 'store'])->name('settings.navigation.store');
        Route::get('/settings/navigation/{navigation}/edit', [NavigationMenuController::class, 'edit'])->name('settings.navigation.edit');
        Route::put('/settings/navigation/{navigation}', [NavigationMenuController::class, 'update'])->name('settings.navigation.update');
        Route::delete('/settings/navigation/{navigation}', [NavigationMenuController::class, 'destroy'])->name('settings.navigation.destroy');
        Route::patch('/settings/navigation/reorder', [NavigationMenuController::class, 'reorder'])->name('settings.navigation.reorder');
        Route::patch('/settings/navigation/{navigation}/toggle', [NavigationMenuController::class, 'toggle'])->name('settings.navigation.toggle');

        // Dashboard designer
        Route::resource('settings/dashboards', ReportDashboardController::class)
            ->names('settings.dashboards')
            ->except(['show']);
    });
});
