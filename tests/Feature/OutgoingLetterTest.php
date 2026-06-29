<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesLetterNumberRegistrations;
use Tests\Concerns\CreatesOutgoingLetters;
use Tests\TestCase;

class OutgoingLetterTest extends TestCase
{
    use CreatesLetterNumberRegistrations;
    use CreatesOutgoingLetters;
    use RefreshDatabase;

    public function test_guest_cannot_access_outgoing_letter_pages(): void
    {
        $response = $this->get(route('outgoing-letters.index'));

        $response->assertRedirect(route('login'));

        $response = $this->getJson('/api/outgoing-letters');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_outgoing_letters_index(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('outgoing-letters.index'));

        $response->assertOk();
        $response->assertSeeText('Arsip Surat Keluar');
    }

    public function test_authenticated_user_can_view_outgoing_letters_create_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('outgoing-letters.create'));

        $response->assertOk();
        $response->assertSeeText('Tambah Arsip Surat Keluar');
    }

    public function test_staff_can_store_outgoing_letter_with_pdf_upload(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($user, $department);

        $payload = $this->outgoingLetterPayload($registration);
        $payload['file'] = UploadedFile::fake()->create('surat-keluar.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/outgoing-letters', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.letter_type', 'regular')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('outgoing_letters', [
            'letter_number_registration_id' => $registration->id,
            'letter_type' => 'regular',
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }

    public function test_store_validation_returns_errors_for_invalid_payload(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $payload = [
            'letter_number_registration_id' => 9999,
            'letter_type' => 'invalid',
            'file' => UploadedFile::fake()->create('image.png', 100, 'image/png'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/outgoing-letters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'letter_number_registration_id',
            'letter_type',
            'file',
        ]);
    }

    public function test_cannot_create_outgoing_letter_for_registration_already_used(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($user, $department);

        $this->createOutgoingLetter($user, $registration);

        $payload = $this->outgoingLetterPayload($registration);
        $payload['file'] = UploadedFile::fake()->create('surat-keluar.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/outgoing-letters', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['letter_number_registration_id']);
    }

    public function test_staff_can_update_own_outgoing_letter(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($user, $department);
        $outgoingLetter = $this->createOutgoingLetter($user, $registration);

        $payload = [
            'letter_type' => 'public',
            'attachment' => '3 Berkas',
            'notes' => 'Catatan diperbarui',
            'status' => 'inactive',
            'file' => UploadedFile::fake()->create('updated.pdf', 110, 'application/pdf'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->put("/api/outgoing-letters/{$outgoingLetter->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.letter_type', 'public')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('outgoing_letters', [
            'id' => $outgoingLetter->id,
            'letter_type' => 'public',
            'status' => 'inactive',
            'updated_by' => $user->id,
        ]);
    }

    public function test_staff_cannot_update_other_user_outgoing_letter(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($otherStaff, $department);
        $outgoingLetter = $this->createOutgoingLetter($otherStaff, $registration);

        $payload = [
            'letter_type' => 'public',
            'attachment' => '3 Berkas',
            'notes' => 'Catatan diperbarui',
            'status' => 'inactive',
        ];

        $response = $this->actingAs($staff, 'sanctum')
            ->put("/api/outgoing-letters/{$outgoingLetter->id}", $payload);

        $response->assertStatus(403);
    }

    public function test_superadmin_can_delete_any_outgoing_letter_and_sets_deleted_by(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($staff, $department);
        $outgoingLetter = $this->createOutgoingLetter($staff, $registration);

        $response = $this->actingAs($superadmin, 'sanctum')
            ->delete("/api/outgoing-letters/{$outgoingLetter->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('outgoing_letters', [
            'id' => $outgoingLetter->id,
        ]);

        $this->assertDatabaseHas('outgoing_letters', [
            'id' => $outgoingLetter->id,
            'deleted_by' => $superadmin->id,
        ]);
    }

    public function test_staff_can_restore_own_deleted_outgoing_letter(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($staff, $department);
        $outgoingLetter = $this->createOutgoingLetter($staff, $registration);

        $outgoingLetter->deleted_by = $staff->id;
        $outgoingLetter->save();
        $outgoingLetter->delete();

        $response = $this->actingAs($staff, 'sanctum')
            ->post("/api/outgoing-letters/{$outgoingLetter->id}/restore");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('outgoing_letters', [
            'id' => $outgoingLetter->id,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    }

    public function test_file_download_and_missing_file_error(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($user, $department);

        $path = 'public/outgoing-letters/test-file.pdf';
        $pdfContent = "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Count 0 /Kids [] >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        Storage::put($path, $pdfContent);

        $outgoingLetter = $this->createOutgoingLetter($user, $registration, [
            'file_path' => $path,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/outgoing-letters/{$outgoingLetter->id}/download");

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/pdf');

        $outgoingLetter->file_path = 'public/outgoing-letters/missing.pdf';
        $outgoingLetter->save();

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/outgoing-letters/{$outgoingLetter->id}/download");

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_access_outgoing_letters_print_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('outgoing-letters.print'));

        $response->assertOk();
        $response->assertSeeText('LAPORAN ARSIP SURAT KELUAR');
    }

    public function test_authenticated_user_can_export_selected_outgoing_letters_to_pdf(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($user, $department);
        $outgoingLetter = $this->createOutgoingLetter($user, $registration);

        $response = $this->actingAs($user)
            ->get(route('outgoing-letters.export-pdf', ['ids' => $outgoingLetter->id]));

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
    }

    public function test_authenticated_user_can_export_selected_outgoing_letters_to_excel(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $registration = $this->createLetterNumberRegistration($user, $department);
        $outgoingLetter = $this->createOutgoingLetter($user, $registration);

        $response = $this->actingAs($user)
            ->getJson('/api/outgoing-letters/export-excel?ids=' . $outgoingLetter->id);

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition');
    }

    public function test_create_metadata_only_lists_available_active_registrations(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $availableRegistration = $this->createLetterNumberRegistration($user, $department, [
            'sequence_number' => 1,
            'letter_code' => 'AVAIL',
            'letter_number' => 'AVAIL/001/DPT/2026',
        ]);

        $usedRegistration = $this->createLetterNumberRegistration($user, $department, [
            'sequence_number' => 2,
            'letter_code' => 'USED',
            'letter_number' => 'USED/002/DPT/2026',
        ]);

        $this->createOutgoingLetter($user, $usedRegistration);

        $inactiveRegistration = $this->createLetterNumberRegistration($user, $department, [
            'sequence_number' => 3,
            'letter_code' => 'INACT',
            'letter_number' => 'INACT/003/DPT/2026',
        ]);
        $inactiveRegistration->status = 'inactive';
        $inactiveRegistration->save();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/outgoing-letters/create');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $registrationIds = collect($response->json('data.registrations'))->pluck('id')->all();

        $this->assertContains($availableRegistration->id, $registrationIds);
        $this->assertNotContains($usedRegistration->id, $registrationIds);
        $this->assertNotContains($inactiveRegistration->id, $registrationIds);
    }

    /**
     * @return array<string, mixed>
     */
    private function outgoingLetterPayload(LetterNumberRegistration $registration): array
    {
        return [
            'letter_number_registration_id' => $registration->id,
            'letter_type' => 'regular',
            'attachment' => '2 Berkas',
            'notes' => 'Catatan surat',
        ];
    }
}
