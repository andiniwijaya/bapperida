<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

/**
 * Feature tests for admin-driven user creation and password onboarding flows.
 */
class UserPasswordOnboardingTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSystemSettingRecord();
    }

    public function test_superadmin_create_admin_sends_password_setup_email_without_password(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $department = $this->department();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Admin Baru',
                'username' => 'adminbaru',
                'email' => 'adminbaru@example.com',
                'role' => 'admin',
                'department_id' => $department->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonPath('data.password_onboarding_status', 'pending')
            ->assertJsonPath('data.password_onboarding_status_label', 'Belum Mengatur Kata Sandi');

        $user = User::query()->where('email', 'adminbaru@example.com')->firstOrFail();

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, UserCreatedNotification::class, function (UserCreatedNotification $notification) use ($user) {
            $mail = $notification->toMail($user);
            $html = $mail->render();

            return str_contains($html, 'ATUR KATA SANDI')
                && ! str_contains(strtolower($html), 'password sementara')
                && isset($mail->viewData['passwordSetupUrl'])
                && str_contains($mail->viewData['passwordSetupUrl'], 'reset-password');
        });
    }

    public function test_superadmin_can_create_staff_user(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $department = $this->department();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Staff Baru',
                'username' => 'staffbaru',
                'email' => 'staffbaru@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'staff');

        $user = User::query()->where('email', 'staffbaru@example.com')->firstOrFail();

        Notification::assertSentTo($user, UserCreatedNotification::class);
    }

    public function test_admin_can_create_staff_user(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $department = $this->department();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Staff Admin',
                'username' => 'staffadmin',
                'email' => 'staffadmin@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ])
            ->assertCreated();

        Notification::assertSentTo(
            User::query()->where('email', 'staffadmin@example.com')->firstOrFail(),
            UserCreatedNotification::class,
        );
    }

    public function test_admin_cannot_create_admin_or_superadmin(): void
    {
        $admin = User::factory()->admin()->create();
        $department = $this->department();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Admin Baru',
                'username' => 'adminbaru3',
                'email' => 'adminbaru3@example.com',
                'role' => 'admin',
                'department_id' => $department->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Fake Super',
                'username' => 'fakesuper3',
                'email' => 'fakesuper3@example.com',
                'role' => 'superadmin',
                'department_id' => $department->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_staff_cannot_create_user(): void
    {
        $staff = User::factory()->staff()->create();
        $department = $this->department();

        $this->actingAs($staff, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Blocked',
                'username' => 'blocked',
                'email' => 'blocked@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ])
            ->assertForbidden();
    }

    public function test_user_can_complete_password_setup_and_login(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $department = $this->department();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Onboard User',
                'username' => 'onboarduser',
                'email' => 'onboard@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ])
            ->assertCreated();

        $user = User::query()->where('email', 'onboard@example.com')->firstOrFail();
        $setupUrl = $this->capturePasswordSetupUrl($user);

        $token = $this->extractTokenFromSetupUrl($setupUrl);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('password.reset.success'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('newpassword', $user->password));

        $this->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'newpassword',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_password_reset_token_can_only_be_used_once(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $department = $this->department();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Token User',
                'username' => 'tokenuser',
                'email' => 'token@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ]);

        $user = User::query()->where('email', 'token@example.com')->firstOrFail();
        $token = $this->extractTokenFromSetupUrl($this->capturePasswordSetupUrl($user));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'firstpassword',
            'password_confirmation' => 'firstpassword',
        ])->assertSessionHasNoErrors();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'secondpassword',
            'password_confirmation' => 'secondpassword',
        ])->assertSessionHasErrors();
    }

    public function test_admin_reset_password_uses_same_email_flow(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $staff = User::factory()->staff()->create([
            'must_change_password' => false,
            'department_id' => $this->department()->id,
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/reset-password")
            ->assertOk()
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonPath('data.password_onboarding_status', 'pending');

        Notification::assertSentTo($staff, UserCreatedNotification::class, function (UserCreatedNotification $notification) use ($staff) {
            $mail = $notification->toMail($staff);

            return str_contains($mail->render(), 'ATUR KATA SANDI');
        });
    }

    public function test_resend_password_setup_email_succeeds_for_pending_users(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $staff = User::factory()->staff()->create([
            'must_change_password' => true,
            'department_id' => $this->department()->id,
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/resend-password-setup")
            ->assertOk()
            ->assertJsonPath('message', 'Email atur kata sandi berhasil dikirim ulang.');

        Notification::assertSentTo($staff, UserCreatedNotification::class);
    }

    public function test_resend_password_setup_email_fails_when_password_already_set(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $staff = User::factory()->staff()->create([
            'must_change_password' => false,
            'department_id' => $this->department()->id,
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/resend-password-setup")
            ->assertForbidden();
    }

    public function test_onboarding_status_changes_after_password_setup(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $department = $this->department();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Status User',
                'username' => 'statususer',
                'email' => 'status@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ]);

        $user = User::query()->where('email', 'status@example.com')->firstOrFail();
        $token = $this->extractTokenFromSetupUrl($this->capturePasswordSetupUrl($user));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'statuspassword',
            'password_confirmation' => 'statuspassword',
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.password_onboarding_status', 'completed')
            ->assertJsonPath('data.password_onboarding_status_label', 'Sudah Mengatur Kata Sandi');
    }

    public function test_api_response_never_includes_password(): void
    {
        Notification::fake();

        $superadmin = User::factory()->superadmin()->create();
        $department = $this->department();

        $response = $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'No Password',
                'username' => 'nopassword',
                'email' => 'nopassword@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ]);

        $json = json_encode($response->json());

        $this->assertStringNotContainsString('password sementara', strtolower($json));
        $this->assertArrayNotHasKey('temporary_password', $response->json('data') ?? []);
    }

    private function capturePasswordSetupUrl(User $user): string
    {
        $setupUrl = '';

        Notification::assertSentTo($user, UserCreatedNotification::class, function (UserCreatedNotification $notification) use ($user, &$setupUrl) {
            $mail = $notification->toMail($user);
            $setupUrl = $mail->viewData['passwordSetupUrl'];

            return true;
        });

        return $setupUrl;
    }

    private function extractTokenFromSetupUrl(string $setupUrl): string
    {
        $path = parse_url($setupUrl, PHP_URL_PATH) ?? '';
        $segments = explode('/', trim($path, '/'));

        return end($segments);
    }

    private function department(): Department
    {
        return Department::factory()->create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
        ]);
    }
}
