<?php

namespace Tests\Unit;

use App\Support\PermissionDisplay;
use Tests\TestCase;

class PermissionDisplayTest extends TestCase
{
    public function test_label_uses_permissions_display_names(): void
    {
        app()->setLocale('th');
        $label = PermissionDisplay::label('manage profile');
        $this->assertNotSame('manage profile', $label);
        $this->assertStringContainsString('จัดการ', $label);
    }

    public function test_module_company_matches_vertical(): void
    {
        app()->setLocale('th');
        $module = PermissionDisplay::module('company');
        if (config('organization.vertical') === 'school') {
            $this->assertSame('โรงเรียน', $module);
        } else {
            $this->assertSame('องค์กร', $module);
        }
    }

    public function test_action_manage_translated_th(): void
    {
        app()->setLocale('th');
        $this->assertSame('จัดการ', PermissionDisplay::action('manage'));
    }
}
