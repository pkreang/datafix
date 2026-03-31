<?php

return [
    'notifications' => 'Notifications',
    'all_notifications_desc' => 'All your notifications in one place.',
    'no_notifications' => 'No notifications yet.',
    'mark_all_read' => 'Mark all as read',
    'view_all' => 'View all notifications',
    'just_now' => 'just now',
    'minutes_ago' => 'min ago',
    'hours_ago' => 'hr ago',
    'days_ago' => 'days ago',

    // Notification content
    'greeting' => 'Hello :name,',
    'view_document' => 'View Document',
    'approval_pending_title' => 'Approval Pending',
    'approval_pending_body' => ':document_type :reference requires your approval (Step: :step)',
    'workflow_approved_title' => 'Document Approved',
    'workflow_approved_body' => 'Your :document_type :reference has been approved.',
    'workflow_rejected_title' => 'Document Rejected',
    'workflow_rejected_body' => 'Your :document_type :reference has been rejected.',
    'rejection_comment' => 'Comment: ":comment"',

    // Document types
    'document_types' => [
        'repair_request' => 'Repair Request',
        'pm_am_plan' => 'PM/AM Plan',
        'spare_parts_requisition' => 'Spare Parts Requisition',
    ],

    // Settings
    'notification_settings' => 'Notification Settings',
    'notification_settings_desc' => 'Configure how and when notifications are sent.',
    'email_notifications' => 'Email Notifications',
    'email_notifications_desc' => 'Enable or disable all email notifications system-wide.',
    'event_approval_pending' => 'Approval Pending',
    'event_approval_pending_desc' => 'Send email when a document requires approval.',
    'event_workflow_approved' => 'Document Approved',
    'event_workflow_approved_desc' => 'Send email when a document is fully approved.',
    'event_workflow_rejected' => 'Document Rejected',
    'event_workflow_rejected_desc' => 'Send email when a document is rejected.',
    'save_settings' => 'Save Settings',
    'settings_saved' => 'Notification settings saved successfully.',

    // LINE Notify
    'line_notifications' => 'LINE Notify',
    'line_notifications_desc' => 'Send notifications via LINE Notify. Users must configure their token in profile.',
    'line_token_hint' => 'Users generate personal tokens at',
    'line_notify_token' => 'LINE Notify Token',
    'line_notify_token_placeholder' => 'Paste your LINE Notify token here',
    'line_notify_token_hint' => 'Generate a personal token at',
    'event_approval_pending_line_desc' => 'Send LINE message when a document requires approval.',
    'event_workflow_approved_line_desc' => 'Send LINE message when a document is fully approved.',
    'event_workflow_rejected_line_desc' => 'Send LINE message when a document is rejected.',
];
