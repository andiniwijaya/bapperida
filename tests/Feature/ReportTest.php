<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\Concerns\CreatesLetterNumberRegistrations;
use Tests\Concerns\CreatesOutgoingLetters;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use CreatesIncomingLetters;
    use CreatesLetterNumberRegistrations;
    use CreatesOutgoingLetters;
    use RefreshDatabase;

    public function test_guest_cannot_access_report_pages(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));

        $response = $this->getJson('/api/reports');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_report_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertSeeText('Laporan Surat');
    }

    public function test_authenticated_user_can_view_report_print_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('reports.print'));

        $response->assertOk();
        $response->assertSeeText('LAPORAN SEMUA');
        $response->assertSeeText('Dicetak Oleh');
    }

    public function test_authenticated_user_can_fetch_report_filters(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/filters');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'report_types',
                'years',
                'departments',
            ],
        ]);
    }

    public function test_authenticated_user_can_get_report_api_results(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'TEST/001/DPT/2026',
            'sent_date' => '2026-06-25',
            'received_date' => '2026-06-25',
            'sender' => 'Pengirim Test',
            'subject' => 'Surat Uji',
            'agenda_name' => 'Agenda Uji',
            'summary' => 'Ringkasan uji',
            'attachment' => 'Lampiran',
            'notes' => 'Catatan kosong',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports?report_type=incoming&per_page=10');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.meta.total', 1);
        $response->assertJsonCount(1, 'data.data');
    }

    public function test_authenticated_user_can_get_report_statistics(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::factory()->create([
            'code' => 'D01',
            'name' => 'Bidang Statistik',
        ]);

        $registration = $this->createLetterNumberRegistration($user, $department, [
            'index_code' => 'IDX001',
            'letter_code' => 'L001',
            'sequence_number' => 1,
            'year' => now()->year,
            'letter_number' => 'REG-STAT-001',
            'subject' => 'Registrasi Statistik',
            'summary' => 'Ringkasan',
            'recipient' => 'Tujuan',
            'letter_date' => now()->toDateString(),
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'IN-STAT-001',
            'sent_date' => now()->toDateString(),
            'received_date' => now()->toDateString(),
            'sender' => 'Pengirim',
            'subject' => 'Surat Masuk Statistik',
        ]);

        $this->createOutgoingLetter($user, $registration);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/statistics?granularity=month');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'totals' => [
                    'registration',
                    'incoming',
                    'outgoing',
                    'letters_total',
                    'users',
                    'departments',
                ],
                'by_period',
                'by_department',
                'by_user',
                'by_status',
                'by_letter_type',
                'last_updated_at',
            ],
        ]);

        $response->assertJsonFragment([
            'registration' => 1,
            'incoming' => 1,
            'outgoing' => 1,
            'letters_total' => 3,
        ]);
    }

    public function test_report_statistics_rejects_invalid_department_filter(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/statistics?department_id=99999');

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_export_report_excel(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'TEST/001/DPT/2026',
            'sent_date' => '2026-06-25',
            'received_date' => '2026-06-25',
            'sender' => 'Pengirim Test',
            'subject' => 'Surat Uji',
            'agenda_name' => 'Agenda Uji',
            'summary' => 'Ringkasan uji',
            'attachment' => 'Lampiran',
            'notes' => 'Catatan kosong',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->get('/api/reports/export-excel?report_type=incoming');

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition');
    }

    public function test_authenticated_user_can_export_report_pdf(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = Department::create([
            'code' => 'DPT',
            'name' => 'Departemen Test',
            'is_active' => true,
        ]);

        $this->createIncomingLetter($user, $department, [
            'letter_number' => 'TEST/001/DPT/2026',
            'sent_date' => '2026-06-25',
            'received_date' => '2026-06-25',
            'sender' => 'Pengirim Test',
            'subject' => 'Surat Uji',
            'agenda_name' => 'Agenda Uji',
            'summary' => 'Ringkasan uji',
            'attachment' => 'Lampiran',
            'notes' => 'Catatan kosong',
        ]);

        $response = $this->actingAs($user)
            ->get('/reports/export-pdf?report_type=incoming');

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
    }
}
