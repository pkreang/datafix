<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ResetTestingUserLayerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_removes_users_except_keep_list(): void
    {
        $this->seed(RolePermissionSeeder::class);

        User::query()->create([
            'first_name' => 'Temp',
            'last_name' => 'One',
            'email' => 'temp-one@test.local',
            'password' => bcrypt('password'),
            'password_changed_at' => now(),
            'password_must_change' => false,
            'is_active' => true,
            'is_super_admin' => false,
        ]);
        User::query()->create([
            'first_name' => 'Temp',
            'last_name' => 'Two',
            'email' => 'temp-two@test.local',
            'password' => bcrypt('password'),
            'password_changed_at' => now(),
            'password_must_change' => false,
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $this->assertSame(3, User::query()->count());

        Artisan::call('testing:reset-user-layer', [
            '--keep' => ['admin@example.com'],
            '--force' => true,
        ]);

        $this->assertSame(1, User::query()->count());
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'temp-one@test.local']);
        $this->assertDatabaseMissing('users', ['email' => 'temp-two@test.local']);
    }
}
