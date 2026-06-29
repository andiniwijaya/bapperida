<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use CreatesIncomingLetters;
    use RefreshDatabase;

    public function test_superadmin_can_create_department(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/departments', [
                'code' => 'newdept',
                'name' => 'Bidang Baru',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'NEWDEPT')
            ->assertJsonPath('data.name', 'Bidang Baru')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('departments', [
            'code' => 'NEWDEPT',
            'name' => 'Bidang Baru',
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_list_departments_with_search_and_pagination(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Department::factory()->create(['code' => 'AAA', 'name' => 'Alpha Bidang']);
        Department::factory()->create(['code' => 'BBB', 'name' => 'Beta Bidang']);

        $response = $this->actingAs($superadmin, 'sanctum')
            ->getJson('/api/departments?search=Alpha&per_page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.code', 'AAA');
    }

    public function test_superadmin_can_update_department_and_toggle_status(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::factory()->create([
            'code' => 'OLD',
            'name' => 'Nama Lama',
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->putJson("/api/departments/{$department->id}", [
                'code' => 'newcode',
                'name' => 'Nama Baru',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'NEWCODE')
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.is_active', false);
    }

    public function test_department_code_and_name_must_be_unique_among_active_records(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        Department::factory()->create(['code' => 'DUP', 'name' => 'Duplikat']);

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/departments', [
                'code' => 'dup',
                'name' => 'Duplikat',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_superadmin_cannot_delete_department_in_use_by_user(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::factory()->create();
        User::factory()->create([
            'role' => 'staff',
            'department_id' => $department->id,
        ]);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/departments/{$department->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department']);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'deleted_at' => null,
        ]);
    }

    public function test_superadmin_can_delete_unused_department(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::factory()->create();

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/departments/{$department->id}")
            ->assertOk();

        $this->assertSoftDeleted('departments', ['id' => $department->id]);
        $this->assertFalse(Department::withTrashed()->find($department->id)->is_active);
    }

    public function test_superadmin_can_restore_soft_deleted_department(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::factory()->create([
            'code' => 'REST',
            'name' => 'Restore Test',
        ]);

        $department->delete();

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson("/api/departments/{$department->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.code', 'REST');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'deleted_at' => null,
        ]);
    }

    public function test_superadmin_cannot_delete_department_used_by_incoming_letter(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::factory()->create();
        $creator = User::factory()->create(['role' => 'admin']);

        $this->createIncomingLetter($creator, $department);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/departments/{$department->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department']);
    }

    public function test_superadmin_cannot_delete_department_used_by_letter_number_registration(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $department = Department::factory()->create();
        $creator = User::factory()->create(['role' => 'staff']);

        $registration = new LetterNumberRegistration([
            'index_code' => 'IDX-001',
            'letter_code' => 'B/001',
            'sequence_number' => 1,
            'year' => 2026,
            'letter_number' => 'B/001/001/DPT/2026',
            'subject' => 'Perihal Surat',
            'recipient' => 'Tujuan Surat',
            'letter_date' => '2026-06-01',
            'letter_type' => 'regular',
            'department_id' => $department->id,
        ]);

        $registration->status = 'active';
        $registration->created_by = $creator->id;
        $registration->save();

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/departments/{$department->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department']);
    }

    public function test_admin_and_staff_cannot_manage_departments_via_api(): void
    {
        $department = Department::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/departments')
            ->assertOk();

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/departments')
            ->assertForbidden();

        $this->actingAs($staff, 'sanctum')
            ->postJson('/api/departments', [
                'code' => 'STF',
                'name' => 'Staff Attempt',
            ])
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/departments', [
                'code' => 'ADM',
                'name' => 'Admin Attempt',
            ])
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/departments/{$department->id}")
            ->assertForbidden();
    }
}
