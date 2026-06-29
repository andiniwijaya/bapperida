<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_pages(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        $this->get(route('admin.departments.index'))->assertRedirect(route('login'));
        $this->get(route('admin.registration-requests.index'))->assertRedirect(route('login'));
        $this->get(route('admin.activity-logs.index'))->assertRedirect(route('login'));
        $this->get(route('admin.system-settings.index'))->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_superadmin_admin_pages(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.departments.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.registration-requests.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_access_superadmin_admin_pages(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superadmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText('Manajemen Pengguna');

        $this->actingAs($superadmin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSeeText('Tambah Pengguna');

        $this->actingAs($superadmin)
            ->get(route('admin.departments.index'))
            ->assertOk()
            ->assertSeeText('Manajemen Bidang');

        $this->actingAs($superadmin)
            ->get(route('admin.registration-requests.index'))
            ->assertOk()
            ->assertSeeText('Persetujuan Registrasi');
    }

    public function test_admin_can_access_staff_management_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText('Manajemen Staff');

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSeeText('Tambah Staff');
    }

    public function test_admin_can_access_activity_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSeeText('Log Aktivitas');
    }

    public function test_admin_cannot_access_system_settings_page(): void
    {
        $this->createSystemSettingRecord();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.system-settings.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_access_user_show_and_edit_pages(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $target = User::factory()->create(['role' => 'staff']);

        $this->actingAs($superadmin)
            ->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertSeeText('Detail Pengguna');

        $this->actingAs($superadmin)
            ->get(route('admin.users.edit', $target))
            ->assertOk()
            ->assertSeeText('Ubah Pengguna');
    }

    public function test_admin_can_access_staff_show_and_edit_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $staff))
            ->assertOk()
            ->assertSeeText('Detail Pengguna');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $staff))
            ->assertOk()
            ->assertSeeText('Ubah Pengguna');
    }

    public function test_admin_cannot_access_admin_user_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $otherAdmin))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $otherAdmin))
            ->assertForbidden();
    }

    public function test_superadmin_can_access_department_edit_page(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::create([
            'code' => 'IT',
            'name' => 'Bidang IT',
            'is_active' => true,
        ]);

        $this->actingAs($superadmin)
            ->get(route('admin.departments.edit', $department))
            ->assertOk()
            ->assertSeeText('Edit Bidang');
    }

    public function test_admin_can_access_activity_log_detail_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = ActivityLog::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.show', $log))
            ->assertOk()
            ->assertSeeText('Detail Log Aktivitas');
    }
}
