<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RegistrationCardPrint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\Concerns\CreatesLetterNumberRegistrations;
use Tests\TestCase;

/**
 * Print, PDF, and Excel presentation standardization checks.
 */
class PrintExportStandardizationTest extends TestCase
{
    use CreatesIncomingLetters;
    use CreatesLetterNumberRegistrations;
    use RefreshDatabase;

    public function test_registration_card_uses_required_landscape_dimensions(): void
    {
        $this->assertSame(163, RegistrationCardPrint::CARD_WIDTH_MM);
        $this->assertSame(103, RegistrationCardPrint::CARD_HEIGHT_MM);
        $this->assertSame(
            RegistrationCardPrint::BACKGROUND_YELLOW,
            RegistrationCardPrint::resolveBackground(null),
        );
    }

    public function test_registration_print_page_includes_card_dimensions_and_template_options(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = \App\Models\Department::factory()->create(['is_active' => true]);
        $this->createLetterNumberRegistration($user, $department);

        $response = $this->actingAs($user)
            ->get('/letter-number-registrations/print?layout=template&background=yellow');

        $response->assertOk();
        $response->assertSee('163mm', false);
        $response->assertSee('103mm', false);
        $response->assertSeeText('KARTU SURAT KELUAR');
        $response->assertSeeText('Indeks :');
    }

    public function test_registration_data_only_print_has_no_template_chrome(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = \App\Models\Department::factory()->create(['is_active' => true]);
        $this->createLetterNumberRegistration($user, $department);

        $response = $this->actingAs($user)
            ->get('/letter-number-registrations/print?layout=data');

        $response->assertOk();
        $response->assertDontSeeText('KARTU SURAT KELUAR');
        $response->assertDontSeeText('Indeks :');
    }

    public function test_incoming_letter_print_uses_official_report_header_and_title(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $department = \App\Models\Department::factory()->create(['is_active' => true]);
        $letter = $this->createIncomingLetter($user, $department);

        $response = $this->actingAs($user)
            ->get(route('incoming-letters.print', ['ids' => $letter->id]));

        $response->assertOk();
        $response->assertSeeText('PEMERINTAH KABUPATEN BANDUNG');
        $response->assertSeeText('LAPORAN ARSIP SURAT MASUK');
        $response->assertSeeText('Dicetak Oleh');
    }

    public function test_outgoing_letter_print_uses_official_report_header_and_title(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('outgoing-letters.print'));

        $response->assertOk();
        $response->assertSeeText('PEMERINTAH KABUPATEN BANDUNG');
        $response->assertSeeText('LAPORAN ARSIP SURAT KELUAR');
    }

    public function test_report_print_uses_shared_official_report_styles(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)
            ->get(route('reports.print'));

        $response->assertOk();
        $response->assertSeeText('PEMERINTAH KABUPATEN BANDUNG');
        $response->assertSee('report-table', false);
    }
}
