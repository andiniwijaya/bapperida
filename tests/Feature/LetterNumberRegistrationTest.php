<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOutgoingLetters;
use Tests\TestCase;

class LetterNumberRegistrationTest extends TestCase
{
    use CreatesOutgoingLetters;
    use RefreshDatabase;

    public function test_authenticated_user_can_preview_letter_number(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/letter-number-registrations/preview?letter_code=TEST&department_id={$department->id}&sequence_number=1&year=2026");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['letter_number', 'sequence_number']])
            ->assertJsonPath('data.letter_number', 'TEST/001/DPT/2026');
    }

    public function test_authenticated_user_can_store_letter_number_registration(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/letter-number-registrations', $this->payload($department));

        $response->assertCreated()
            ->assertJsonPath('data.letter_number', 'TEST/001/DPT/2026')
            ->assertJsonPath('data.sequence_number', 1)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('letter_number_registrations', [
            'letter_number' => 'TEST/001/DPT/2026',
            'sequence_number' => 1,
            'year' => 2026,
            'department_id' => $department->id,
            'status' => 'active',
        ]);
    }

    public function test_duplicate_sequence_number_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();

        $this->createRegistration($user, $department);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/letter-number-registrations', $this->payload($department))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sequence_number']);
    }

    public function test_same_sequence_number_is_allowed_for_different_years(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();

        $this->createRegistration($user, $department, 2026, 1);

        $payload = $this->payload($department);
        $payload['year'] = 2027;
        $payload['sequence_number'] = 1;

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/letter-number-registrations', $payload)
            ->assertCreated()
            ->assertJsonPath('data.sequence_number', 1)
            ->assertJsonPath('data.year', 2027);
    }

    public function test_preview_requires_sequence_number(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/letter-number-registrations/preview?letter_code=TEST&department_id={$department->id}&year=2026")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sequence_number']);
    }

    public function test_store_does_not_allow_status_mass_assignment(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/letter-number-registrations', array_merge($this->payload($department), [
                'status' => 'inactive',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_registration_with_outgoing_letter_cannot_be_deleted(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $admin = User::factory()->create(['role' => 'admin']);
        $department = $this->department();
        $registration = $this->createRegistration($staff, $department);

        $this->createOutgoingLetter($staff, $registration);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/letter-number-registrations/{$registration->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['registration']);
    }

    public function test_registration_with_outgoing_letter_cannot_change_numbering_fields(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();
        $registration = $this->createRegistration($user, $department);

        $this->createOutgoingLetter($user, $registration);

        $payload = $this->payload($department);
        $payload['sequence_number'] = 2;

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/letter-number-registrations/{$registration->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['letter_number']);
    }

    public function test_superadmin_can_restore_soft_deleted_registration(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();
        $registration = $this->createRegistration($user, $department);

        $registration->delete();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson("/api/letter-number-registrations/{$registration->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.letter_number', 'TEST/001/DPT/2026');

        $this->assertDatabaseHas('letter_number_registrations', [
            'id' => $registration->id,
            'deleted_at' => null,
        ]);
    }

    public function test_staff_cannot_update_other_staff_registration(): void
    {
        $owner = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);
        $department = $this->department();
        $registration = $this->createRegistration($owner, $department);

        $this->actingAs($otherStaff, 'sanctum')
            ->putJson("/api/letter-number-registrations/{$registration->id}", $this->payload($department))
            ->assertForbidden();
    }

    public function test_authenticated_user_can_view_print_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();
        $this->createRegistration($user, $department);

        $response = $this->actingAs($user)
            ->get('/letter-number-registrations/print?layout=template&background=yellow');

        $response->assertOk();
        $response->assertSeeText('Cetak Kartu Surat Keluar');
        $response->assertSee('card-frame-table', false);
        $response->assertSee('card-grid-table', false);
        $response->assertSee('card-layout-table', false);
        $response->assertSee('#c62828', false);
        $response->assertSeeText('Indeks :');
        $response->assertSeeText('IDX-001');
        $response->assertSeeText('Surat Uji');
    }

    public function test_authenticated_user_can_view_data_only_print_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();
        $this->createRegistration($user, $department);

        $response = $this->actingAs($user)
            ->get('/letter-number-registrations/print?layout=data');

        $response->assertOk();
        $response->assertSeeText('IDX-001');
        $response->assertDontSeeText('KARTU SURAT KELUAR');
        $response->assertDontSeeText('Indeks :');
    }

    public function test_authenticated_user_can_export_selected_registrations_to_pdf(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = $this->department();
        $registration = $this->createRegistration($user, $department);

        $response = $this->actingAs($user)
            ->get('/letter-number-registrations/export-pdf?ids='.$registration->id.'&layout=template&background=pink');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
        $this->assertSame(
            1,
            preg_match_all('/\/Type\s*\/Page(?!s)/', $response->getContent()),
            'Expected exactly one PDF page per exported registration card.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Department $department): array
    {
        return [
            'index_code' => 'IDX-001',
            'letter_code' => 'TEST',
            'sequence_number' => 1,
            'year' => 2026,
            'subject' => 'Surat Uji',
            'summary' => 'Ringkasan uji',
            'recipient' => 'Direktur',
            'letter_date' => '2026-07-01',
            'letter_type' => 'regular',
            'attachment' => 'Tidak ada',
            'notes' => 'Catatan uji',
            'department_id' => $department->id,
        ];
    }

    public function test_create_metadata_api_returns_dropdown_data_in_envelope(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->department();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/letter-number-registrations/create?year=2026');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'departments',
                    'letter_types',
                    'current_year',
                ],
            ]);
        $response->assertJsonMissingPath('data.available_sequences');
    }

    public function test_filters_api_returns_dropdown_data_in_envelope(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/letter-number-registrations/filters');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'years',
                    'departments',
                    'letter_types',
                    'statuses',
                ],
            ]);
    }

    public function test_create_form_includes_manual_sequence_number_field(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'status' => 'active']);

        $response = $this->actingAs($user)
            ->get(route('letter-number-registrations.create'));

        $response->assertOk();
        $response->assertSee('id="sequence_number"', false);
        $response->assertSee('type="number"', false);
        $response->assertSee('Nomor Urut', false);
    }

    public function test_create_form_includes_letter_date_field(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'status' => 'active']);

        $response = $this->actingAs($user)
            ->get(route('letter-number-registrations.create'));

        $response->assertOk();
        $response->assertSee('id="letter_date"', false);
        $response->assertSee('Tanggal Surat', false);
    }

    private function department(): Department
    {
        return Department::factory()->create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
        ]);
    }

    private function createRegistration(
        User $user,
        Department $department,
        int $year = 2026,
        int $sequenceNumber = 1,
    ): LetterNumberRegistration {
        $sequence = str_pad((string) $sequenceNumber, 3, '0', STR_PAD_LEFT);

        $registration = new LetterNumberRegistration([
            'index_code' => 'IDX-001',
            'letter_code' => 'TEST',
            'sequence_number' => $sequenceNumber,
            'year' => $year,
            'letter_number' => "TEST/{$sequence}/DPT/{$year}",
            'subject' => 'Surat Uji',
            'summary' => 'Ringkasan uji',
            'recipient' => 'Direktur',
            'letter_date' => '2026-07-01',
            'letter_type' => 'regular',
            'attachment' => 'Tidak ada',
            'notes' => 'Catatan uji',
            'department_id' => $department->id,
        ]);

        $registration->status = 'active';
        $registration->created_by = $user->id;
        $registration->save();

        return $registration;
    }
}
