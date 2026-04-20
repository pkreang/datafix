<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPinnedMenu;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarPinTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_pin_then_unpin_a_menu(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = $this->makeUser('pin-alice@example.test');

        $firstResponse = $this->actingAsWebSession($user)
            ->postJson(route('profile.pinned-menus.toggle'), ['menu_key' => '42']);

        $firstResponse->assertOk()->assertJson(['pinned' => true, 'menu_key' => '42']);
        $this->assertDatabaseHas('user_pinned_menus', ['user_id' => $user->id, 'menu_key' => '42']);

        $secondResponse = $this->actingAsWebSession($user)
            ->postJson(route('profile.pinned-menus.toggle'), ['menu_key' => '42']);

        $secondResponse->assertOk()->assertJson(['pinned' => false, 'menu_key' => '42']);
        $this->assertDatabaseMissing('user_pinned_menus', ['user_id' => $user->id, 'menu_key' => '42']);
    }

    public function test_toggle_requires_authenticated_session(): void
    {
        $response = $this->postJson(route('profile.pinned-menus.toggle'), ['menu_key' => '99']);

        $response->assertStatus(401);
        $this->assertDatabaseCount('user_pinned_menus', 0);
    }

    public function test_toggle_rejects_missing_menu_key(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = $this->makeUser('pin-bob@example.test');

        $response = $this->actingAsWebSession($user)
            ->postJson(route('profile.pinned-menus.toggle'), []);

        $response->assertStatus(422);
    }

    public function test_toggle_keys_are_scoped_per_user(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
        $alice = $this->makeUser('pin-scope-alice@example.test');
        $bob = $this->makeUser('pin-scope-bob@example.test');

        $this->actingAsWebSession($alice)
            ->postJson(route('profile.pinned-menus.toggle'), ['menu_key' => '7']);

        $this->assertSame(['7'], UserPinnedMenu::keysFor($alice->id));
        $this->assertSame([], UserPinnedMenu::keysFor($bob->id));
    }

    private function makeUser(string $email): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'auth_provider' => 'local',
        ]);
    }

    private function actingAsWebSession(User $user): self
    {
        $token = $user->createToken('phpunit-web')->plainTextToken;

        return $this->withSession([
            'api_token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => trim($user->first_name.' '.$user->last_name) ?: $user->email,
                'email' => $user->email,
                'is_super_admin' => (bool) $user->is_super_admin,
            ],
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);
    }
}
