<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemNotification;
use App\Notifications\Data\SystemNotificationPayload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Feature tests for user notification API and communication layer security.
 */
class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_notifications(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $user->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'Test Title',
            message: 'Test message body',
            module: 'auth',
            action: 'test_action',
            url: '/dashboard',
            metadata: ['key' => 'value'],
        )));

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.meta.total', 1);
        $response->assertJsonPath('data.data.0.title', 'Test Title');
        $response->assertJsonPath('data.data.0.module', 'auth');
        $response->assertJsonPath('data.data.0.action', 'test_action');
    }

    public function test_user_cannot_access_other_users_notification(): void
    {
        $owner = User::factory()->create(['role' => 'staff']);
        $other = User::factory()->create(['role' => 'staff']);

        $owner->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'Private',
            message: 'Private message',
            module: 'user',
            action: 'user_created',
        )));

        $notificationId = $owner->notifications()->first()->id;

        $this->actingAs($other, 'sanctum')
            ->patchJson("/api/notifications/{$notificationId}/read")
            ->assertNotFound();
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $user->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'Unread',
            message: 'Unread message',
            module: 'report',
            action: 'export_pdf',
        )));

        $notificationId = $user->notifications()->first()->id;

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $user->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'One',
            message: 'One',
            module: 'auth',
            action: 'test',
        )));
        $user->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'Two',
            message: 'Two',
            module: 'auth',
            action: 'test',
        )));

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/notifications/mark-all-read')
            ->assertOk()
            ->assertJsonPath('data.marked_count', 2);

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_unread_count_endpoint_returns_correct_count(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $user->notify(new SystemNotification(new SystemNotificationPayload(
            title: 'Unread',
            message: 'Unread',
            module: 'auth',
            action: 'test',
        )));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    }

    public function test_registration_creates_database_notification(): void
    {
        Notification::fake();

        $department = \App\Models\Department::factory()->create(['is_active' => true]);

        $this->postJson('/api/auth/register', [
            'name' => 'Staff Baru',
            'username' => 'staffbaru',
            'email' => 'staffbaru@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'department_id' => $department->id,
        ])->assertCreated();

        Notification::assertSentTo(
            User::query()->where('email', 'staffbaru@example.test')->first(),
            SystemNotification::class,
        );
    }
}
