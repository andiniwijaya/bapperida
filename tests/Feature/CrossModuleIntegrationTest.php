<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesLetterNumberRegistrations;
use Tests\Concerns\CreatesOutgoingLetters;
use Tests\TestCase;

/**
 * Verifies cross-module business rules between foundation modules.
 *
 * Covers: User ↔ Department, LNR ↔ Department, Outgoing ↔ LNR, Dashboard aggregation.
 */
class CrossModuleIntegrationTest extends TestCase
{
    use CreatesLetterNumberRegistrations;
    use CreatesOutgoingLetters;
    use RefreshDatabase;

    /**
     * Active users must reference an active department for letter workflows.
     */
    public function test_letter_number_registration_requires_active_department(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $inactiveDepartment = Department::factory()->create(['is_active' => false]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/letter-number-registrations', [
                'index_code' => 'IDX-001',
                'letter_code' => 'TEST',
                'sequence_number' => 1,
                'year' => 2026,
                'subject' => 'Surat Uji',
                'summary' => 'Ringkasan',
                'recipient' => 'Direktur',
                'letter_date' => '2026-07-01',
                'letter_type' => 'regular',
                'attachment' => 'Tidak ada',
                'notes' => 'Catatan',
                'department_id' => $inactiveDepartment->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department_id']);
    }

    /**
     * Department with letter registrations cannot be soft-deleted.
     */
    public function test_department_with_registrations_cannot_be_deleted(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);

        $this->createLetterNumberRegistration($staff, $department);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/departments/{$department->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department']);
    }

    /**
     * Outgoing letter creation locks registration; second outgoing for same registration is rejected.
     */
    public function test_outgoing_letter_blocks_duplicate_registration_usage(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);
        $registration = $this->createLetterNumberRegistration($user, $department);

        $this->createOutgoingLetter($user, $registration);

        $payload = [
            'letter_number_registration_id' => $registration->id,
            'letter_type' => 'regular',
            'attachment' => '2 Berkas',
            'notes' => 'Duplikat',
            'file' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/outgoing-letters', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['letter_number_registration_id']);
    }

    /**
     * Registration linked to outgoing letter cannot be deleted or renumbered.
     */
    public function test_registration_with_outgoing_letter_is_immutable_for_numbering(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);
        $registration = $this->createLetterNumberRegistration($user, $department);

        $this->createOutgoingLetter($user, $registration);

        $this->actingAs($superadmin, 'sanctum')
            ->deleteJson("/api/letter-number-registrations/{$registration->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['registration']);

        $payload = [
            'index_code' => 'IDX-001',
            'letter_code' => 'TEST',
            'sequence_number' => 99,
            'year' => 2026,
            'subject' => 'Surat Uji',
            'summary' => 'Ringkasan',
            'recipient' => 'Direktur',
            'letter_date' => '2026-07-01',
            'letter_type' => 'regular',
            'attachment' => 'Tidak ada',
            'notes' => 'Catatan',
            'department_id' => $department->id,
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/letter-number-registrations/{$registration->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['letter_number']);
    }

    /**
     * Soft-deleted outgoing letter can be restored without breaking registration uniqueness.
     */
    public function test_outgoing_letter_restore_preserves_registration_link(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);
        $registration = $this->createLetterNumberRegistration($user, $department);
        $outgoing = $this->createOutgoingLetter($user, $registration);

        $outgoing->deleted_by = $user->id;
        $outgoing->save();
        $outgoing->delete();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/outgoing-letters/{$outgoing->id}/restore")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('outgoing_letters', [
            'id' => $outgoing->id,
            'letter_number_registration_id' => $registration->id,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    }

    /**
     * Dashboard summary reflects registrations and outgoing letters across modules.
     */
    public function test_dashboard_aggregates_cross_module_counts(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);

        $registration = $this->createLetterNumberRegistration($user, $department, [
            'sequence_number' => 10,
            'letter_code' => 'DASH',
            'letter_number' => 'DASH/010/DPT/2026',
            'letter_date' => now()->toDateString(),
        ]);

        $this->createOutgoingLetter($user, $registration);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.summary.registration', 1)
            ->assertJsonPath('data.summary.outgoing', 1);
    }

    /**
     * Staff cannot update outgoing letter created by another staff (cross-user authorization).
     */
    public function test_staff_cannot_update_other_staff_outgoing_letter(): void
    {
        $staffA = User::factory()->create(['role' => 'staff']);
        $staffB = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);
        $registration = $this->createLetterNumberRegistration($staffB, $department);
        $outgoing = $this->createOutgoingLetter($staffB, $registration);

        $this->actingAs($staffA, 'sanctum')
            ->putJson("/api/outgoing-letters/{$outgoing->id}", [
                'letter_type' => 'public',
                'attachment' => '1 Berkas',
                'notes' => 'IDOR test',
                'status' => 'inactive',
            ])
            ->assertForbidden();
    }
}
