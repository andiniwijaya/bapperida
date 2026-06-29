<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\Concerns\CreatesLetterNumberRegistrations;
use Tests\Concerns\CreatesOutgoingLetters;
use Tests\TestCase;

class DashboardDataTest extends TestCase
{
    use CreatesIncomingLetters;
    use CreatesLetterNumberRegistrations;
    use CreatesOutgoingLetters;
    use RefreshDatabase;

    public function test_dashboard_api_returns_summary_and_recent_items(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user, 'sanctum');

        $department = Department::factory()->create([
            'code' => 'D01',
            'name' => 'Bidang A',
        ]);

        $year = now()->year;

        $registration = $this->createLetterNumberRegistration($user, $department, [
            'index_code' => 'IDX001',
            'letter_code' => 'L001',
            'sequence_number' => 1,
            'year' => $year,
            'letter_number' => 'REG-001',
            'subject' => 'Registrasi Surat',
            'summary' => 'Ringkasan registrasi',
            'recipient' => 'Tujuan A',
            'letter_date' => now()->toDateString(),
            'attachment' => null,
            'notes' => null,
        ]);

        $this->createLetterNumberRegistration($user, $department, [
            'index_code' => 'IDX002',
            'letter_code' => 'L002',
            'sequence_number' => 2,
            'year' => $year,
            'letter_number' => 'REG-002',
            'subject' => 'Registrasi Surat 2',
            'summary' => 'Ringkasan registrasi 2',
            'recipient' => 'Tujuan B',
            'letter_date' => now()->toDateString(),
            'attachment' => null,
            'notes' => null,
        ]);

        $this->createLetterNumberRegistration($user, $department, [
            'index_code' => 'IDX003',
            'letter_code' => 'L003',
            'sequence_number' => 3,
            'year' => $year,
            'letter_number' => 'REG-003',
            'subject' => 'Registrasi Surat 3',
            'summary' => 'Ringkasan registrasi 3',
            'recipient' => 'Tujuan C',
            'letter_date' => now()->toDateString(),
            'attachment' => null,
            'notes' => null,
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'IN-001',
            'sent_date' => now()->subDays(2)->toDateString(),
            'received_date' => now()->subDays(1)->toDateString(),
            'sender' => 'Pengirim A',
            'disposition_department_id' => null,
            'subject' => 'Surat Masuk 1',
            'agenda_name' => 'Agenda A',
            'summary' => 'Ringkasan masuk 1',
            'attachment' => null,
            'notes' => null,
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'IN-002',
            'sent_date' => now()->subDays(4)->toDateString(),
            'received_date' => now()->subDays(3)->toDateString(),
            'sender' => 'Pengirim B',
            'disposition_department_id' => null,
            'subject' => 'Surat Masuk 2',
            'agenda_name' => 'Agenda B',
            'summary' => 'Ringkasan masuk 2',
        ]);

        $this->createOutgoingLetter($user, $registration);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.role', 'admin');
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'role',
                'widgets',
                'summary' => ['registration', 'incoming', 'outgoing', 'total'],
                'monthly_trends' => ['labels', 'registration', 'incoming', 'outgoing'],
                'tables' => [
                    'recent_items',
                    'activity_logs',
                    'top_departments',
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'registration' => 3,
            'incoming' => 2,
            'outgoing' => 1,
            'total' => 6,
        ]);
    }

    public function test_dashboard_api_applies_filters_and_tracks_activity(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user, 'sanctum');

        $departmentA = Department::factory()->create([
            'code' => 'D01',
            'name' => 'Bidang A',
        ]);

        $departmentB = Department::factory()->create([
            'code' => 'D02',
            'name' => 'Bidang B',
        ]);

        $year = now()->year;

        $this->createLetterNumberRegistration($user, $departmentA, [
            'index_code' => 'IDX001',
            'letter_code' => 'L001',
            'sequence_number' => 1,
            'year' => $year,
            'letter_number' => 'REG-001',
            'subject' => 'Registrasi Surat A',
            'summary' => 'Ringkasan registrasi',
            'recipient' => 'Tujuan A',
            'letter_date' => now()->toDateString(),
            'attachment' => null,
            'notes' => null,
        ]);

        $this->createLetterNumberRegistration($user, $departmentB, [
            'index_code' => 'IDX002',
            'letter_code' => 'L002',
            'sequence_number' => 2,
            'year' => $year,
            'letter_number' => 'REG-002',
            'subject' => 'Registrasi Surat B',
            'summary' => 'Ringkasan registrasi',
            'recipient' => 'Tujuan B',
            'letter_date' => now()->toDateString(),
            'attachment' => null,
            'notes' => null,
        ]);

        $response = $this->getJson('/api/dashboard?department_id='.$departmentA->id);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'role',
                'widgets',
                'summary',
                'summary_growth',
                'monthly_trends',
                'tables',
                'last_updated_at',
            ],
        ]);

        $response->assertJsonFragment([
            'registration' => 1,
            'incoming' => 0,
            'outgoing' => 0,
            'total' => 1,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'dashboard_viewed',
            'module' => 'dashboard',
        ]);
    }

    public function test_staff_dashboard_only_shows_own_letter_counts(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create();

        $this->createIncomingLetter($staff, $department, [
            'letter_number' => 'IN-STAFF-001',
            'sent_date' => now()->toDateString(),
            'received_date' => now()->toDateString(),
            'sender' => 'Pengirim Staff',
            'subject' => 'Surat Staff',
        ]);

        $this->createIncomingLetter($otherStaff, $department, [
            'letter_number' => 'IN-OTHER-001',
            'sent_date' => now()->toDateString(),
            'received_date' => now()->toDateString(),
            'sender' => 'Pengirim Lain',
            'subject' => 'Surat Lain',
        ]);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.role', 'staff');
        $response->assertJsonPath('data.widgets.my_incoming', 1);
        $response->assertJsonPath('data.summary.incoming', 1);
        $response->assertJsonMissing(['total_users' => User::count()]);
    }

    public function test_superadmin_dashboard_includes_org_widgets(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        User::factory()->create(['role' => 'staff', 'status' => 'pending']);

        $response = $this->actingAs($superadmin, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.role', 'superadmin');
        $response->assertJsonStructure([
            'data' => [
                'widgets' => [
                    'total_users',
                    'total_departments',
                    'pending_approval_users',
                ],
            ],
        ]);
        $response->assertJsonPath('data.widgets.pending_approval_users', 1);
    }

    public function test_dashboard_api_accepts_granularity_filter(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard?granularity=day');

        $response->assertOk();
        $response->assertJsonPath('data.granularity', 'day');
        $response->assertJsonStructure([
            'data' => [
                'monthly_trends' => ['labels', 'registration', 'incoming', 'outgoing'],
            ],
        ]);
    }
}
