<?php

namespace Tests\Feature;

use App\Models\DocumentForm;
use App\Models\DocumentFormField;
use App\Models\DocumentFormSubmission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_status_returns_draft_when_submission_is_draft(): void
    {
        $this->seedBase();
        [$form, $user] = $this->makeForm();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => ['title' => 'x'],
            'status' => 'draft',
        ]);

        $this->assertSame('draft', $submission->effective_status);
    }

    public function test_effective_status_falls_back_to_submitted_when_no_instance(): void
    {
        $this->seedBase();
        [$form, $user] = $this->makeForm();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => ['title' => 'x'],
            'status' => 'submitted',
        ]);

        $this->assertSame('submitted', $submission->effective_status);
    }

    public function test_preview_returns_first_searchable_field_value(): void
    {
        $this->seedBase();
        $form = DocumentForm::create([
            'form_key' => 'prev_form',
            'name' => 'Preview Form',
            'document_type' => 'generic',
            'is_active' => true,
        ]);
        DocumentFormField::create([
            'form_id' => $form->id, 'field_key' => 'code', 'label' => 'Code',
            'field_type' => 'text', 'sort_order' => 1, 'is_searchable' => false,
        ]);
        DocumentFormField::create([
            'form_id' => $form->id, 'field_key' => 'title', 'label' => 'Title',
            'field_type' => 'text', 'sort_order' => 2, 'is_searchable' => true,
        ]);
        $user = $this->makeUser();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => ['code' => 'C1', 'title' => 'ปั๊มน้ำเสียงดัง'],
            'status' => 'draft',
        ]);

        // title is searchable, so it wins even though code has lower sort_order
        $this->assertSame('ปั๊มน้ำเสียงดัง', $submission->fresh('form.fields')->preview);
    }

    public function test_action_plan_for_draft_shows_edit_primary_and_delete_menu(): void
    {
        $this->seedBase();
        [$form, $user] = $this->makeForm();
        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => [],
            'status' => 'draft',
        ]);

        $plan = $submission->actionPlan($this->viewerFor($user));

        $this->assertSame(__('common.edit'), $plan['primary']['label']);
        $menuLabels = array_column($plan['menu'], 'label');
        $this->assertContains(__('common.action_duplicate'), $menuLabels);
        $this->assertContains(__('common.action_delete_draft'), $menuLabels);
    }

    public function test_action_plan_for_non_owner_without_approval_permission_is_empty(): void
    {
        $this->seedBase();
        [$form, $owner] = $this->makeForm();
        $other = $this->makeUser();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $owner->id,
            'payload' => [],
            'status' => 'submitted',
        ]);

        $plan = $submission->actionPlan($this->viewerFor($other));
        $this->assertNull($plan['primary']);
        $this->assertEmpty($plan['menu']);
    }

    public function test_print_route_returns_200_for_owner(): void
    {
        $this->seedBase();
        [$form, $user] = $this->makeForm();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => ['title' => 'x'],
            'status' => 'submitted',
            'reference_no' => 'R-1',
        ]);

        $response = $this->actingAsWebSession($user)->get(route('forms.submission.print', $submission));
        $response->assertOk();
        $response->assertSee('R-1');
    }

    public function test_print_route_forbidden_for_other_user(): void
    {
        $this->seedBase();
        [$form, $owner] = $this->makeForm();
        $other = $this->makeUser();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $owner->id,
            'payload' => ['title' => 'x'],
            'status' => 'submitted',
            'reference_no' => 'R-2',
        ]);

        $response = $this->actingAsWebSession($other)->get(route('forms.submission.print', $submission));
        $response->assertForbidden();
    }

    public function test_print_route_404_for_draft(): void
    {
        $this->seedBase();
        [$form, $user] = $this->makeForm();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => [],
            'status' => 'draft',
        ]);

        $response = $this->actingAsWebSession($user)->get(route('forms.submission.print', $submission));
        $response->assertNotFound();
    }

    public function test_duplicate_creates_new_draft_with_nulled_metadata(): void
    {
        $this->seedBase();
        [$form, $user] = $this->makeForm();

        $original = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'payload' => ['title' => 'orig'],
            'status' => 'submitted',
            'reference_no' => 'R-100',
            'approval_instance_id' => null,
        ]);

        $response = $this->actingAsWebSession($user)
            ->post(route('forms.submission.duplicate', $original));

        $copy = DocumentFormSubmission::where('user_id', $user->id)
            ->where('id', '!=', $original->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($copy);
        $this->assertSame('draft', $copy->status);
        $this->assertNull($copy->reference_no);
        $this->assertNull($copy->approval_instance_id);
        $this->assertSame(['title' => 'orig'], $copy->payload);
        $response->assertRedirect(route('forms.draft.edit', $copy));
    }

    public function test_duplicate_forbidden_for_non_owner(): void
    {
        $this->seedBase();
        [$form, $owner] = $this->makeForm();
        $other = $this->makeUser();

        $submission = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $owner->id,
            'payload' => [],
            'status' => 'submitted',
        ]);

        $response = $this->actingAsWebSession($other)
            ->post(route('forms.submission.duplicate', $submission));
        $response->assertForbidden();
    }

    // ── Helpers ─────────────────────────────────────────────

    private function seedBase(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    private function makeForm(): array
    {
        $form = DocumentForm::create([
            'form_key' => 'af_form',
            'name' => 'Actions Form',
            'document_type' => 'generic',
            'is_active' => true,
        ]);
        DocumentFormField::create([
            'form_id' => $form->id, 'field_key' => 'title', 'label' => 'Title',
            'field_type' => 'text', 'sort_order' => 1, 'is_searchable' => true,
        ]);
        $user = $this->makeUser();

        return [$form->fresh('fields'), $user];
    }

    private function viewerFor(User $user): array
    {
        return [
            'id' => $user->id,
            'can_approve' => $user->getAllPermissions()->contains('name', 'approval.approve'),
            'is_super_admin' => (bool) $user->is_super_admin,
        ];
    }

    private function makeUser(): User
    {
        static $counter = 0;
        $counter++;

        return User::create([
            'first_name' => 'Test',
            'last_name' => "Actions{$counter}",
            'email' => "actions{$counter}@example.test",
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => false,
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
                'department_id' => $user->department_id,
                'can_change_password' => true,
                'roles' => $user->getRoleNames()->toArray(),
            ],
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);
    }
}
