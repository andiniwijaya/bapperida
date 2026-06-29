<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\TestCase;

class IncomingLetterTest extends TestCase
{
    use CreatesIncomingLetters;
    use RefreshDatabase;

    public function test_guest_cannot_access_incoming_letter_pages(): void
    {
        $response = $this->get(route('incoming-letters.index'));

        $response->assertRedirect(route('login'));

        $response = $this->getJson('/api/incoming-letters');

        $response->assertStatus(401);
    }

    public function test_staff_can_view_incoming_letter_pages(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get(route('incoming-letters.index'));
        $response->assertOk();
        $response->assertSeeText('Arsip Surat Masuk');

        $response = $this->actingAs($user)->get(route('incoming-letters.create'));
        $response->assertOk();
        $response->assertSeeText('Tambah Arsip Surat Masuk');
    }

    public function test_staff_can_store_incoming_letter_with_pdf_upload(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $payload = $this->incomingLetterPayload($department);
        $payload['file'] = UploadedFile::fake()->create('surat-masuk.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/incoming-letters', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.letter_number', $payload['letter_number'])
            ->assertJsonPath('data.status', $payload['status']);

        $this->assertDatabaseHas('incoming_letters', [
            'letter_number' => $payload['letter_number'],
            'status' => $payload['status'],
            'created_by' => $user->id,
        ]);
    }

    public function test_store_validation_returns_errors_for_invalid_payload(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $payload = [
            'letter_number' => '',
            'sent_date' => 'not-a-date',
            'received_date' => '',
            'sender' => '',
            'department_id' => 9999,
            'subject' => '',
            'letter_attribute' => 'invalid',
            'status' => 'unknown',
            'file' => UploadedFile::fake()->create('image.png', 100, 'image/png'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/incoming-letters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'letter_number',
            'sent_date',
            'received_date',
            'sender',
            'department_id',
            'subject',
            'letter_attribute',
            'status',
            'file',
        ]);
    }

    public function test_staff_can_update_own_incoming_letter(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $incomingLetter = $this->createIncomingLetter($user, $department);

        $payload = $this->incomingLetterPayload($department, 'UPDATED/001', 'inactive');
        $payload['file'] = UploadedFile::fake()->create('updated.pdf', 110, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->put("/api/incoming-letters/{$incomingLetter->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.letter_number', 'UPDATED/001')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $incomingLetter->id,
            'letter_number' => 'UPDATED/001',
            'status' => 'inactive',
            'updated_by' => $user->id,
        ]);
    }

    public function test_admin_can_update_staff_created_incoming_letter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $incomingLetter = $this->createIncomingLetter($staff, $department);

        $payload = $this->incomingLetterPayload($department, 'ADMIN-UPDATED/001');

        $response = $this->actingAs($admin, 'sanctum')
            ->put("/api/incoming-letters/{$incomingLetter->id}", $payload);

        $response->assertOk();
        $this->assertDatabaseHas('incoming_letters', [
            'id' => $incomingLetter->id,
            'letter_number' => 'ADMIN-UPDATED/001',
        ]);
    }

    public function test_admin_cannot_update_admin_created_incoming_letter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $incomingLetter = $this->createIncomingLetter($otherAdmin, $department);

        $payload = $this->incomingLetterPayload($department, 'OTHER-ADMIN-UPDATE');

        $response = $this->actingAs($admin, 'sanctum')
            ->put("/api/incoming-letters/{$incomingLetter->id}", $payload);

        $response->assertStatus(403);
    }

    public function test_staff_cannot_delete_other_user_incoming_letter(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $incomingLetter = $this->createIncomingLetter($otherStaff, $department);

        $response = $this->actingAs($staff, 'sanctum')
            ->delete("/api/incoming-letters/{$incomingLetter->id}");

        $response->assertStatus(403);
    }

    public function test_superadmin_can_delete_any_incoming_letter_and_sets_deleted_by(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $incomingLetter = $this->createIncomingLetter($staff, $department);

        $response = $this->actingAs($superadmin, 'sanctum')
            ->delete("/api/incoming-letters/{$incomingLetter->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('incoming_letters', [
            'id' => $incomingLetter->id,
        ]);

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $incomingLetter->id,
            'deleted_by' => $superadmin->id,
        ]);
    }

    public function test_search_filters_and_pagination_return_expected_results(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $departmentA = Department::create([
            'code' => 'DPTA',
            'name' => 'Departemen A',
            'is_active' => true,
        ]);
        $departmentB = Department::create([
            'code' => 'DPTB',
            'name' => 'Departemen B',
            'is_active' => true,
        ]);

        $this->createIncomingLetter($user, $departmentA, [
            'letter_number' => 'FILTER/001',
            'sent_date' => '2026-07-01',
            'received_date' => '2026-07-02',
            'disposition_date' => '2026-07-03',
            'letter_attribute' => 'public',
            'status' => 'active',
        ]);
        $this->createIncomingLetter($user, $departmentB, [
            'letter_number' => 'OTHER/002',
            'sent_date' => '2025-07-01',
            'received_date' => '2025-07-02',
            'disposition_date' => '2025-07-03',
            'letter_attribute' => 'top_secret',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/incoming-letters?search=FILTER&year=2026&department_id='.$departmentA->id.'&letter_attribute=public&status=active&per_page=1');

        $response->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.data.0.letter_number', 'FILTER/001');
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

        $path = 'public/incoming-letters/test-file.pdf';
        $pdfContent = "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Count 0 /Kids [] >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        Storage::put($path, $pdfContent);

        $incomingLetter = $this->createIncomingLetter($user, $department, [
            'file_path' => $path,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/incoming-letters/{$incomingLetter->id}/download");

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/pdf');

        $incomingLetter->update(['file_path' => 'public/incoming-letters/missing.pdf']);

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/incoming-letters/{$incomingLetter->id}/download");

        $response->assertStatus(404);
    }

    public function test_print_route_and_export_routes_return_correct_content(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $incomingLetter = $this->createIncomingLetter($user, $department);

        $response = $this->actingAs($user)
            ->get(route('incoming-letters.print', ['ids' => $incomingLetter->id]));

        $response->assertOk();
        $response->assertSeeText('LAPORAN ARSIP SURAT MASUK');

        $response = $this->actingAs($user)
            ->get(route('incoming-letters.export-pdf', ['ids' => $incomingLetter->id]));

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/incoming-letters/export-excel?ids=' . $incomingLetter->id);

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_staff_can_restore_own_deleted_incoming_letter(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);
        $incomingLetter = $this->createIncomingLetter($staff, $department, [
            'letter_number' => 'RESTORE/001',
        ]);

        $incomingLetter->deleted_by = $staff->id;
        $incomingLetter->save();
        $incomingLetter->delete();

        $this->actingAs($staff, 'sanctum')
            ->postJson("/api/incoming-letters/{$incomingLetter->id}/restore")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $incomingLetter->id,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    }

    public function test_cannot_store_incoming_letter_with_inactive_department(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $inactiveDepartment = Department::create([
            'code' => 'INACT',
            'name' => 'Departemen Nonaktif',
        ]);
        $inactiveDepartment->is_active = false;
        $inactiveDepartment->save();

        $payload = $this->incomingLetterPayload($inactiveDepartment);
        $payload['disposition_department_id'] = null;
        $payload['file'] = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/incoming-letters', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department_id']);
    }

    public function test_duplicate_letter_number_is_rejected_on_store(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'DUPLICATE/001',
        ]);

        $payload = $this->incomingLetterPayload($department, 'DUPLICATE/001');
        $payload['file'] = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/incoming-letters', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['letter_number']);
    }

    private function incomingLetterPayload(Department $department, string $letterNumber = 'TEST/001/DPT/2026', string $status = 'active'): array
    {
        return [
            'letter_number' => $letterNumber,
            'sent_date' => '2026-07-01',
            'received_date' => '2026-07-02',
            'disposition_date' => '2026-07-03',
            'sender' => 'Pengirim Test',
            'department_id' => $department->id,
            'disposition_department_id' => $department->id,
            'subject' => 'Perihal Surat',
            'agenda_name' => 'Agenda Surat',
            'summary' => 'Ringkasan surat masuk.',
            'letter_attribute' => 'regular',
            'attachment' => '2 Berkas',
            'status' => $status,
            'notes' => 'Catatan internal',
        ];
    }
}
