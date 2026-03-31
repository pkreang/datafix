<?php

return [
    'general_info' => 'General Info',
    'validation_email_unique' => 'This email is already in use. Please use a different email.',
    'validation_role_required' => 'Please select a role.',
    'validation_permissions_required' => 'Please select at least one permission.',
    'email_readonly_hint' => 'Email cannot be changed (used for login).',
    'no_permissions_configured' => 'No permissions configured. Run: php artisan db:seed',
    'remark' => 'Remark',
    'placeholder_first_name' => 'e.g. Andy',
    'placeholder_last_name' => 'e.g. Smith',
    'placeholder_email' => 'e.g. user@company.com',
    'placeholder_department' => 'e.g. Engineering',
    'placeholder_position' => 'e.g. Senior Developer',
    'placeholder_remark' => 'Optional notes...',

    // Permission actions (for matrix headers)
    'action_create' => 'Create',
    'action_read' => 'Read',
    'action_update' => 'Update',
    'action_delete' => 'Delete',
    'action_export' => 'Export',

    // Permission modules
    'module_dashboard' => 'Dashboard',
    'module_product' => 'Product',
    'module_sales' => 'Sales',
    'module_purchase' => 'Purchase',
    'module_expense' => 'Expense',
    'module_report' => 'Report',
    'module_loan' => 'Loan',
    'module_company_profile' => 'Company profile',
    'module_user_access' => 'User & access',
    'module_integrations' => 'Integrations',
    'module_role_access' => 'Role & permission',
    'module_permission_access' => 'Permission',

    // Flash messages
    'user_created' => 'User created successfully.',
    'user_enabled' => 'User enabled successfully.',
    'user_disabled' => 'User disabled successfully.',
    'user_deleted' => 'User deleted successfully.',
    'cannot_delete_super_admin' => 'Cannot delete a super admin user.',

    // Import
    'import_title' => 'Import Users',
    'import_subtitle' => 'Upload a CSV file to import users in bulk.',
    'import_upload_label' => 'CSV file',
    'import_upload_hint' => 'Accepted formats: .csv, .txt (max 2 MB)',
    'import_template_title' => 'Template columns',
    'import_template_hint' => 'Your CSV must include a header row. Supported columns:',
    'import_file_required' => 'Please select a CSV file to upload.',
    'import_file_mimes' => 'File must be a CSV (.csv or .txt).',
    'import_result' => ':created user(s) created, :skipped skipped.',
    'import_errors' => ':count error(s) occurred.',
    'import_skip_duplicate' => 'Skipped :email — already exists.',
];
