<?php

namespace Tests\Feature;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiValidationLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_validation_errors_use_accept_language_thai(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [],
            ['Accept-Language' => 'th']
        );

        $response->assertStatus(422);
        $emailError = $response->json('errors.email.0');
        $this->assertIsString($emailError);
        $this->assertStringContainsString('อีเมล', $emailError);
    }

    public function test_api_validation_errors_use_accept_language_english(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [],
            ['Accept-Language' => 'en']
        );

        $response->assertStatus(422);
        $emailError = $response->json('errors.email.0');
        $this->assertIsString($emailError);
        $this->assertStringContainsStringIgnoringCase('email', $emailError);
    }
}
