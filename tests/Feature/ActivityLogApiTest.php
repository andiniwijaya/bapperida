<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_activity_logs(): void
    {
        $response = $this->getJson('/api/activity-logs');

        $response->assertStatus(401);
    }

    public function test_staff_cannot_access_activity_logs(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'action' => 'test_action',
            'module' => 'test_module',
            'description' => 'Testing activity log access.',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/activity-logs');

        $response->assertStatus(403);
    }

    public function test_admin_can_list_and_view_activity_logs(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $activityLog = ActivityLog::factory()->create([
            'user_id' => $user->id,
            'user_role' => 'admin',
            'action' => 'test_action',
            'module' => 'test_module',
            'description' => 'Testing activity log listing.',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/activity-logs');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.meta.total', 1);
        $response->assertJsonPath('data.data.0.id', $activityLog->id);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/activity-logs/'.$activityLog->id);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $activityLog->id);
        $response->assertJsonPath('data.action', 'test_action');
        $response->assertJsonStructure([
            'data' => [
                'user_role',
                'entity_type',
                'entity_id',
                'department',
            ],
        ]);
    }

    public function test_admin_can_filter_activity_logs_by_module_and_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::factory()->create();

        ActivityLog::factory()->create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'department_id' => $department->id,
            'module' => 'incoming_letter',
            'action' => 'created',
            'description' => 'Log surat masuk',
            'logged_at' => now(),
        ]);

        ActivityLog::factory()->create([
            'user_id' => $admin->id,
            'module' => 'user',
            'action' => 'user_created',
            'description' => 'Log user',
            'logged_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/activity-logs?module=incoming_letter&period_start='.now()->toDateString());

        $response->assertOk();
        $response->assertJsonPath('data.meta.total', 1);
        $response->assertJsonPath('data.data.0.module', 'incoming_letter');
    }

    public function test_admin_can_export_activity_logs_excel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        ActivityLog::factory()->create([
            'user_id' => $admin->id,
            'module' => 'auth',
            'action' => 'login',
            'description' => 'Login test',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->get('/api/activity-logs/export-excel');

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
