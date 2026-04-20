<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_reachable_when_local_auth_enabled(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee(__('auth.forgot_password_page_title'), false);
    }

    public function test_forgot_password_sends_notification_for_local_user(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);

        $response = $this->post(route('password.email'), [
            'email' => 'admin@example.com',
        ]);

        $response->assertSessionHas('status', __('passwords.sent'));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_updates_password_with_valid_token(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', __('passwords.reset'));

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword1!', $user->getRawOriginal('password')));
    }
}
