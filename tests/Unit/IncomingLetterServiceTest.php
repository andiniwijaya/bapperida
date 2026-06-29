<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\User;
use App\Services\IncomingLetter\IncomingLetterFileStorage;
use App\Services\IncomingLetter\StoreIncomingLetterService;
use App\Services\IncomingLetter\UpdateIncomingLetterService;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\TestCase;

/**
 * Unit tests for incoming letter store and update services.
 */
class IncomingLetterServiceTest extends TestCase
{
    use CreatesIncomingLetters;
    use RefreshDatabase;

    public function test_store_incoming_letter_persists_database_and_stores_pdf(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        Auth::loginUsingId($user->id);

        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $activityLog = $this->createMock(RecordActivityLogService::class);
        $notifications = $this->createMock(NotificationService::class);

        $service = new StoreIncomingLetterService(new IncomingLetterFileStorage(), $activityLog, $notifications);

        $file = UploadedFile::fake()->create('surat-masuk.pdf', 100, 'application/pdf');

        $incomingLetter = $service->handle([
            'letter_number' => 'TEST/001/DPT/2026',
            'sent_date' => '2026-07-01',
            'received_date' => '2026-07-02',
            'disposition_date' => '2026-07-03',
            'sender' => 'Pengirim Test',
            'department_id' => $department->id,
            'disposition_department_id' => null,
            'subject' => 'Perihal',
            'agenda_name' => 'Agenda',
            'summary' => 'Ringkasan',
            'letter_attribute' => 'regular',
            'attachment' => '2 Berkas',
            'status' => 'active',
            'notes' => 'Catatan internal',
        ], $file);

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $incomingLetter->id,
            'letter_number' => 'TEST/001/DPT/2026',
            'created_by' => $user->id,
        ]);

        Storage::disk('local')->assertExists($incomingLetter->file_path);
    }

    public function test_update_incoming_letter_updates_file_and_records_updated_by(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'admin']);
        Auth::loginUsingId($user->id);

        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $path = 'public/incoming-letters/original-file.pdf';
        Storage::disk('local')->put($path, 'existing pdf content');

        $incomingLetter = $this->createIncomingLetter($user, $department, [
            'file_path' => $path,
        ]);

        $activityLog = $this->createMock(RecordActivityLogService::class);
        $notifications = $this->createMock(NotificationService::class);

        $service = new UpdateIncomingLetterService(new IncomingLetterFileStorage(), $activityLog, $notifications);

        $newFile = UploadedFile::fake()->create('updated-file.pdf', 120, 'application/pdf');

        $updatedIncomingLetter = $service->handle($incomingLetter, [
            'letter_number' => 'UPDATED/001/DPT/2026',
            'sent_date' => '2026-07-01',
            'received_date' => '2026-07-02',
            'disposition_date' => '2026-07-03',
            'sender' => 'Pengirim Test',
            'department_id' => $department->id,
            'disposition_department_id' => null,
            'subject' => 'Perihal',
            'agenda_name' => 'Agenda',
            'summary' => 'Ringkasan',
            'letter_attribute' => 'regular',
            'attachment' => '2 Berkas',
            'status' => 'inactive',
            'notes' => 'Perubahan catatan',
        ], $newFile);

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $incomingLetter->id,
            'letter_number' => 'UPDATED/001/DPT/2026',
            'updated_by' => $user->id,
            'status' => 'inactive',
        ]);

        $this->assertFalse(Storage::disk('local')->exists($path));
        $this->assertTrue(Storage::disk('local')->exists($updatedIncomingLetter->file_path));
    }
}
