<?php

return [
    'general_info' => 'ข้อมูลทั่วไป',
    'validation_email_unique' => 'อีเมลนี้มีในระบบแล้ว กรุณาใช้อีเมลอื่น',
    'validation_role_required' => 'กรุณาเลือกบทบาท',
    'validation_permissions_required' => 'กรุณาเลือกสิทธิ์อย่างน้อย 1 รายการ',
    'email_readonly_hint' => 'ไม่สามารถแก้ไขอีเมลได้ (ใช้สำหรับเข้าสู่ระบบ)',
    'no_permissions_configured' => 'ยังไม่ได้ตั้งค่าสิทธิ์ กรุณารัน: php artisan db:seed',
    'remark' => 'หมายเหตุ',
    'placeholder_first_name' => 'เช่น สมชาย',
    'placeholder_last_name' => 'เช่น ใจดี',
    'placeholder_email' => 'เช่น user@company.com',
    'placeholder_department' => 'เช่น ฝ่ายไอที',
    'placeholder_position' => 'เช่น นักพัฒนาระบบ',
    'placeholder_remark' => 'หมายเหตุเพิ่มเติม (ถ้ามี)...',

    // Permission actions (for matrix headers)
    'action_create' => 'สร้าง',
    'action_read' => 'ดู',
    'action_update' => 'แก้ไข',
    'action_delete' => 'ลบ',
    'action_export' => 'ส่งออก',

    // Permission modules
    'module_dashboard' => 'แดชบอร์ด',
    'module_product' => 'สินค้า',
    'module_sales' => 'ขาย',
    'module_purchase' => 'ซื้อ',
    'module_expense' => 'ค่าใช้จ่าย',
    'module_report' => 'รายงาน',
    'module_loan' => 'สินเชื่อ',
    'module_company_profile' => 'ข้อมูลบริษัท',
    'module_user_access' => 'ผู้ใช้และสิทธิ์',
    'module_integrations' => 'เชื่อมต่อระบบ',
    'module_role_access' => 'บทบาทและสิทธิ์',
    'module_permission_access' => 'สิทธิ์การเข้าถึง',

    // Flash messages
    'user_created' => 'สร้างผู้ใช้เรียบร้อยแล้ว',
    'user_enabled' => 'เปิดใช้งานผู้ใช้เรียบร้อยแล้ว',
    'user_disabled' => 'ปิดใช้งานผู้ใช้เรียบร้อยแล้ว',
    'user_deleted' => 'ลบผู้ใช้เรียบร้อยแล้ว',
    'cannot_delete_super_admin' => 'ไม่สามารถลบผู้ใช้ที่เป็น Super Admin ได้',

    // Import
    'import_title' => 'นำเข้าผู้ใช้',
    'import_subtitle' => 'อัปโหลดไฟล์ CSV เพื่อนำเข้าผู้ใช้จำนวนมาก',
    'import_upload_label' => 'ไฟล์ CSV',
    'import_upload_hint' => 'รองรับ: .csv, .txt (สูงสุด 2 MB)',
    'import_template_title' => 'คอลัมน์ที่รองรับ',
    'import_template_hint' => 'ไฟล์ CSV ต้องมี header row คอลัมน์ที่รองรับ:',
    'import_file_required' => 'กรุณาเลือกไฟล์ CSV',
    'import_file_mimes' => 'ไฟล์ต้องเป็น CSV (.csv หรือ .txt)',
    'import_result' => 'สร้างผู้ใช้ :created คน, ข้าม :skipped คน',
    'import_errors' => 'เกิดข้อผิดพลาด :count รายการ',
    'import_skip_duplicate' => 'ข้าม :email — มีอยู่ในระบบแล้ว',
];
