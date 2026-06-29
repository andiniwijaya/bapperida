<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityLogRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_records_activity_log(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'module' => 'auth',
        ]);
    }

    public function test_incoming_letter_create_records_activity_log(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $payload = [
            'letter_number' => 'IN-AUDIT-001',
            'sent_date' => '2026-07-01',
            'received_date' => '2026-07-02',
            'sender' => 'Pengirim Audit',
            'department_id' => $department->id,
            'subject' => 'Subjek Audit',
            'letter_attribute' => 'regular',
            'status' => 'active',
            'file' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/incoming-letters', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'module' => 'incoming_letter',
            'action' => 'created',
        ]);
    }

    public function test_activity_log_policy_denies_update_and_delete(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = ActivityLog::factory()->create(['user_id' => $admin->id]);

        $this->assertFalse($admin->can('update', $log));
        $this->assertFalse($admin->can('delete', $log));
        $this->assertFalse($admin->can('forceDelete', $log));
    }
}
