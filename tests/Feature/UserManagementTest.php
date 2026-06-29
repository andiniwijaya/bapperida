<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_admin_user(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = $this->department();

        $response = $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Admin Baru',
                'username' => 'adminbaru',
                'email' => 'adminbaru@example.com',
                'role' => 'admin',
                'department_id' => $department->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'admin')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.must_change_password', true);

        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_superadmin_cannot_create_superadmin_via_api(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = $this->department();

        $response = $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Fake Super',
                'username' => 'fakesuper',
                'email' => 'fakesuper@example.com',
                'role' => 'superadmin',
                'department_id' => $department->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_superadmin_can_change_user_role_and_status(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff', 'department_id' => $this->department()->id]);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/role", ['role' => 'admin'])
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/status", ['status' => 'pending'])
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_superadmin_cannot_change_superadmin_role(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $otherSuperadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$otherSuperadmin->id}/role", ['role' => 'staff'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_superadmin_cannot_delete_self_or_superadmin_account(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $otherSuperadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/users/{$superadmin->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user']);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/users/{$otherSuperadmin->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user']);
    }

    public function test_superadmin_can_soft_delete_and_restore_staff(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/users/{$staff->id}")
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'deleted_by' => $superadmin->id,
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/restore")
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    }

    public function test_update_user_email_resets_email_verification(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create([
            'role' => 'staff',
            'username' => 'staffuser',
            'department_id' => $this->department()->id,
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->putJson("/api/users/{$staff->id}", [
                'name' => $staff->name,
                'username' => $staff->username,
                'email' => 'newemail@example.com',
                'department_id' => $staff->department_id,
            ])
            ->assertOk();

        $staff->refresh();
        $this->assertSame('newemail@example.com', $staff->email);
        $this->assertNull($staff->email_verified_at);
    }

    public function test_superadmin_can_reset_user_password(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $originalHash = $staff->password;

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/reset-password")
            ->assertOk()
            ->assertJsonPath('data.must_change_password', true);

        $staff->refresh();
        $this->assertTrue($staff->must_change_password);
        $this->assertNotSame($originalHash, $staff->password);
    }

    public function test_staff_cannot_access_user_management_endpoints(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $target = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/users')
            ->assertForbidden();

        $this->actingAs($staff, 'sanctum')
            ->patchJson("/api/users/{$target->id}/role", ['role' => 'admin'])
            ->assertForbidden();
    }

    public function test_update_profile_does_not_allow_role_mass_assignment(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'username' => 'staffuser',
        ]);

        $this->actingAs($staff, 'sanctum')
            ->patchJson('/api/auth/profile', [
                'name' => $staff->name,
                'username' => $staff->username,
                'email' => $staff->email,
                'role' => 'superadmin',
            ])
            ->assertOk();

        $this->assertSame('staff', $staff->fresh()->role);
    }

    public function test_admin_can_create_staff_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = $this->department();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Staff Baru',
                'username' => 'staffbaru',
                'email' => 'staffbaru@example.com',
                'role' => 'staff',
                'department_id' => $department->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.role', 'staff')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('users', [
            'email' => 'staffbaru@example.com',
            'role' => 'staff',
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_create_admin_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = $this->department();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Admin Baru',
                'username' => 'adminbaru2',
                'email' => 'adminbaru2@example.com',
                'role' => 'admin',
                'department_id' => $department->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_admin_cannot_create_superadmin_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = $this->department();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Fake Super',
                'username' => 'fakesuper2',
                'email' => 'fakesuper2@example.com',
                'role' => 'superadmin',
                'department_id' => $department->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_admin_can_manage_staff_but_not_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff', 'department_id' => $this->department()->id]);
        $otherAdmin = User::factory()->create(['role' => 'admin', 'department_id' => $this->department()->id]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment([
                'email' => $staff->email,
                'role' => 'staff',
            ])
            ->assertJsonMissing([
                'email' => $otherAdmin->email,
            ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/users/{$staff->id}")
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/users/{$otherAdmin->id}")
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/role", ['role' => 'admin'])
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$staff->id}/status", ['status' => 'pending'])
            ->assertForbidden();
    }

    private function department(): Department
    {
        return Department::factory()->create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
        ]);
    }
}
