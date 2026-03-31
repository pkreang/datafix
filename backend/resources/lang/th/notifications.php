<?php

return [
    'notifications' => 'การแจ้งเตือน',
    'all_notifications_desc' => 'การแจ้งเตือนทั้งหมดของคุณ',
    'no_notifications' => 'ยังไม่มีการแจ้งเตือน',
    'mark_all_read' => 'อ่านทั้งหมด',
    'view_all' => 'ดูการแจ้งเตือนทั้งหมด',
    'just_now' => 'เมื่อสักครู่',
    'minutes_ago' => 'นาทีที่แล้ว',
    'hours_ago' => 'ชั่วโมงที่แล้ว',
    'days_ago' => 'วันที่แล้ว',

    // Notification content
    'greeting' => 'สวัสดี :name,',
    'view_document' => 'ดูเอกสาร',
    'approval_pending_title' => 'รอการอนุมัติ',
    'approval_pending_body' => ':document_type :reference รอการอนุมัติจากคุณ (ขั้นตอน: :step)',
    'workflow_approved_title' => 'เอกสารได้รับการอนุมัติ',
    'workflow_approved_body' => ':document_type :reference ของคุณได้รับการอนุมัติแล้ว',
    'workflow_rejected_title' => 'เอกสารถูกปฏิเสธ',
    'workflow_rejected_body' => ':document_type :reference ของคุณถูกปฏิเสธ',
    'rejection_comment' => 'ความเห็น: ":comment"',

    // Document types
    'document_types' => [
        'repair_request' => 'ใบแจ้งซ่อม',
        'pm_am_plan' => 'แผน PM/AM',
        'spare_parts_requisition' => 'ใบเบิกอะไหล่',
    ],

    // Settings
    'notification_settings' => 'ตั้งค่าการแจ้งเตือน',
    'notification_settings_desc' => 'กำหนดวิธีและเวลาในการส่งการแจ้งเตือน',
    'email_notifications' => 'การแจ้งเตือนทางอีเมล',
    'email_notifications_desc' => 'เปิดหรือปิดการแจ้งเตือนทางอีเมลทั้งระบบ',
    'event_approval_pending' => 'รอการอนุมัติ',
    'event_approval_pending_desc' => 'ส่งอีเมลเมื่อมีเอกสารรอการอนุมัติ',
    'event_workflow_approved' => 'เอกสารได้รับการอนุมัติ',
    'event_workflow_approved_desc' => 'ส่งอีเมลเมื่อเอกสารได้รับการอนุมัติครบทุกขั้นตอน',
    'event_workflow_rejected' => 'เอกสารถูกปฏิเสธ',
    'event_workflow_rejected_desc' => 'ส่งอีเมลเมื่อเอกสารถูกปฏิเสธ',
    'save_settings' => 'บันทึกการตั้งค่า',
    'settings_saved' => 'บันทึกการตั้งค่าการแจ้งเตือนเรียบร้อยแล้ว',

    // LINE Notify
    'line_notifications' => 'LINE Notify',
    'line_notifications_desc' => 'ส่งการแจ้งเตือนผ่าน LINE Notify ผู้ใช้ต้องตั้งค่า token ในโปรไฟล์',
    'line_token_hint' => 'ผู้ใช้สร้าง token ส่วนตัวได้ที่',
    'line_notify_token' => 'LINE Notify Token',
    'line_notify_token_placeholder' => 'วาง LINE Notify token ของคุณที่นี่',
    'line_notify_token_hint' => 'สร้าง token ส่วนตัวได้ที่',
    'event_approval_pending_line_desc' => 'ส่ง LINE เมื่อมีเอกสารรอการอนุมัติ',
    'event_workflow_approved_line_desc' => 'ส่ง LINE เมื่อเอกสารได้รับการอนุมัติครบทุกขั้นตอน',
    'event_workflow_rejected_line_desc' => 'ส่ง LINE เมื่อเอกสารถูกปฏิเสธ',
];
