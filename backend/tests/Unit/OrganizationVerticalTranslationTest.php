<?php

namespace Tests\Unit;

use Tests\TestCase;

class OrganizationVerticalTranslationTest extends TestCase
{
    public function test_company_th_label_matches_org_vertical(): void
    {
        app()->setLocale('th');

        if (config('organization.vertical') === 'school') {
            $this->assertSame('โรงเรียน', __('company.company'));
            $this->assertSame('โรงเรียน', __('common.companies'));
        } else {
            $this->assertSame('องค์กร', __('company.company'));
            $this->assertSame('องค์กร', __('common.companies'));
        }
    }

    public function test_school_override_files_exist_under_resources_lang(): void
    {
        $this->assertFileExists(resource_path('lang/verticals/school/th/company.php'));
        $this->assertFileExists(resource_path('lang/verticals/school/en/company.php'));
    }
}
