<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Notifications\Data\SystemNotificationPayload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cross-module integration smoke tests for full-system consistency.
 */
class FullSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_payload_includes_unread_notification_count(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $user->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'Badge Test',
            message: 'Unread for badge',
            module: 'dashboard',
            action: 'test',
        )));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.notifications_unread_count', 1);
    }

    public function test_activity_log_and_notification_are_separate_on_login(): void
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
            'module' => 'auth',
            'action' => 'login',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => $user->getMorphClass(),
        ]);
    }

    public function test_system_configuration_service_used_for_upload_validation(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/incoming-letters', [
                'letter_number' => 'INTEGRATION-001',
                'sent_date' => now()->toDateString(),
                'received_date' => now()->toDateString(),
                'sender' => 'Pengirim',
                'department_id' => $department->id,
                'subject' => 'Subjek',
                'letter_attribute' => 'regular',
                'status' => 'active',
                'file' => \Illuminate\Http\UploadedFile::fake()->create('doc.txt', 100, 'text/plain'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }
}
