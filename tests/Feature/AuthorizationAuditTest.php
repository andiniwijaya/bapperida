<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

class AuthorizationAuditTest extends TestCase
{
    use CreatesIncomingLetters;
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_staff_cannot_access_superadmin_user_management_api(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/users')
            ->assertForbidden();

        $this->actingAs($staff, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New Admin',
                'username' => 'newadmin',
                'email' => 'newadmin@example.com',
                'role' => 'admin',
                'department_id' => $this->department()->id,
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_access_superadmin_department_management_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/departments')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/departments', [
                'code' => 'NEW',
                'name' => 'Bidang Baru',
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_approve_registration_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingUser = User::factory()->pending()->create();
        $registrationRequest = RegistrationRequest::create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/registration-requests')
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/registration-requests/{$registrationRequest->id}/approve")
            ->assertForbidden();
    }

    public function test_admin_cannot_view_system_settings_api(): void
    {
        $this->createSystemSettingRecord();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/system-settings')
            ->assertForbidden();
    }

    public function test_staff_cannot_approve_registration_requests(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $pendingUser = User::factory()->pending()->create();
        $registrationRequest = RegistrationRequest::create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/registration-requests')
            ->assertForbidden();

        $this->actingAs($staff, 'sanctum')
            ->patchJson("/api/registration-requests/{$registrationRequest->id}/approve")
            ->assertForbidden();
    }

    public function test_admin_cannot_update_system_settings(): void
    {
        $this->createSystemSettingRecord();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/system-settings', $this->systemSettingAttributes())
            ->assertForbidden();
    }

    public function test_superadmin_can_update_system_settings(): void
    {
        $this->createSystemSettingRecord();
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superadmin, 'sanctum')
            ->patchJson('/api/system-settings', $this->systemSettingAttributes())
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_staff_cannot_update_other_staff_letter_registration_edit_page(): void
    {
        $department = $this->department();
        $owner = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);

        $registration = $this->createLetterNumberRegistration($department, $owner);

        $this->actingAs($otherStaff)
            ->get(route('letter-number-registrations.edit', $registration))
            ->assertForbidden();
    }

    public function test_staff_cannot_delete_other_incoming_letter_via_api(): void
    {
        $department = $this->department();
        $owner = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);

        $incomingLetter = $this->createIncomingLetter($owner, $department, [
            'letter_number' => 'IN/001',
            'disposition_department_id' => null,
            'agenda_name' => null,
            'summary' => null,
            'attachment' => null,
            'notes' => null,
        ]);

        $this->actingAs($otherStaff, 'sanctum')
            ->deleteJson("/api/incoming-letters/{$incomingLetter->id}")
            ->assertForbidden();
    }

    public function test_pending_user_cannot_access_settings_profile(): void
    {
        $pendingUser = User::factory()->pending()->create();

        $this->actingAs($pendingUser)
            ->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    private function department(): Department
    {
        return Department::factory()->create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
        ]);
    }

    private function createLetterNumberRegistration(Department $department, User $creator): LetterNumberRegistration
    {
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

        return $registration;
    }
}
