<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

/**
 * End-to-end workflow smoke tests across guest, staff, admin, and super admin journeys.
 */
class EndToEndWorkflowTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_guest_can_navigate_landing_to_login_and_register(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Masuk', false)
            ->assertSee('Daftar', false);

        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('password.request'))->assertOk();
    }

    public function test_guest_auth_pages_redirect_authenticated_users_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_logout_returns_guest_to_landing_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_staff_workflow_pages_are_reachable_for_active_staff(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('letter-number-registrations.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('outgoing-letters.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('incoming-letters.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('reports.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_pending_staff_cannot_access_protected_workflow_after_login_attempt(): void
    {
        $pending = User::factory()->pending()->create();

        $this->post(route('login.store'), [
            'login' => $pending->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_admin_workflow_allows_staff_management_but_blocks_superadmin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.departments.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.registration-requests.index'))
            ->assertForbidden();

        $this->createSystemSettingRecord();

        $this->actingAs($admin)
            ->get(route('admin.system-settings.index'))
            ->assertForbidden();
    }

    public function test_superadmin_workflow_covers_full_administration_surface(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $this->createSystemSettingRecord();

        $this->actingAs($superadmin)
            ->get(route('admin.registration-requests.index'))
            ->assertOk();

        $this->actingAs($superadmin)
            ->get(route('admin.system-settings.index'))
            ->assertOk();

        $this->actingAs($superadmin)
            ->get(route('admin.departments.create'))
            ->assertOk();
    }

    public function test_admin_api_cannot_create_admin_or_superadmin_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create(['is_active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New Admin',
                'username' => 'newadmin',
                'email' => 'newadmin@example.com',
                'role' => 'admin',
                'department_id' => $department->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New Super',
                'username' => 'newsuper',
                'email' => 'newsuper@example.com',
                'role' => 'superadmin',
                'department_id' => $department->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_notification_payloads_use_web_routes_not_api_endpoints(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'status' => 'pending']);
        $superadmin = User::factory()->superadmin()->create();
        $admin = User::factory()->admin()->create();

        $service = app(NotificationService::class);

        $service->registrationSubmitted($staff);

        $superadminNotification = $superadmin->notifications()->first();
        $this->assertNotNull($superadminNotification);
        $this->assertStringContainsString('/registration-requests', $superadminNotification->data['url']);
        $this->assertStringNotContainsString('/api/', $superadminNotification->data['url']);

        $service->departmentCreated($admin, 'Sekretariat', 'SKT');

        $adminNotification = $admin->notifications()->latest()->first();
        $this->assertNotNull($adminNotification);
        $this->assertStringContainsString('/departments', $adminNotification->data['url']);
        $this->assertStringNotContainsString('/api/', $adminNotification->data['url']);
    }

    public function test_pending_user_email_verified_notification_links_to_registration_success(): void
    {
        $pending = User::factory()->pending()->create();

        app(NotificationService::class)->emailVerified($pending);

        $notification = $pending->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame(route('register.success'), $notification->data['url']);
    }

    public function test_password_reset_success_page_links_back_to_login(): void
    {
        $this->get(route('password.reset.success'))
            ->assertOk()
            ->assertSee(route('login'), false);
    }

    public function test_register_success_page_is_reachable_for_guests(): void
    {
        $this->get(route('register.success'))
            ->assertOk();
    }
}
