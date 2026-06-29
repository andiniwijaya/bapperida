<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ExceptionResponder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Blade;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

/**
 * Error page rendering and standardized exception response smoke tests.
 */
class ErrorPageTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_error_views_render_required_content(): void
    {
        $pages = [
            '401' => ['Anda belum masuk ke dalam sistem.', 'Masuk'],
            '403' => ['Anda tidak memiliki hak akses untuk membuka halaman ini.', 'Beranda'],
            '404' => ['Halaman yang Anda cari tidak ditemukan.', 'Beranda'],
            '419' => ['Sesi Anda telah berakhir.', 'Masuk'],
            '429' => ['Terlalu banyak permintaan.', 'Kembali'],
            '500' => ['Terjadi kesalahan pada sistem.', 'Beranda'],
            '503' => ['Sistem sedang dalam proses pemeliharaan.', 'Beranda'],
        ];

        foreach ($pages as $code => [$title, $button]) {
            $html = view("errors.{$code}")->render();

            $this->assertStringContainsString($code, $html);
            $this->assertStringContainsString($title, $html);
            $this->assertStringContainsString($button, $html);
            $this->assertStringContainsString('logo-kab-bandung.png', $html);
            $this->assertStringContainsString('logo-bapperida.png', $html);
            $this->assertStringContainsString('error-page__card', $html);
        }
    }

    public function test_not_found_route_returns_custom_404_page(): void
    {
        $this->get('/halaman-tidak-ada-audit-404')
            ->assertNotFound()
            ->assertSee('404', false)
            ->assertSee('Halaman yang Anda cari tidak ditemukan.', false);
    }

    public function test_guest_dashboard_redirects_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_activity_logs_page(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.activity-logs.index'))
            ->assertForbidden()
            ->assertSee('403', false)
            ->assertSee('Anda tidak memiliki hak akses untuk membuka halaman ini.', false);
    }

    public function test_unauthenticated_api_request_returns_json_401(): void
    {
        $this->getJson('/api/dashboard')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Anda belum masuk ke dalam sistem.',
            ]);
    }

    public function test_staff_api_activity_logs_returns_json_403(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->getJson('/api/activity-logs')
            ->assertForbidden()
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_validation_error_returns_422_with_field_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/incoming-letters', [])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_model_not_found_api_returns_404_not_500(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/incoming-letters/999999')
            ->assertNotFound();
    }

    public function test_token_mismatch_web_redirects_to_login(): void
    {
        $request = Request::create(route('login.store'), 'POST');

        $response = ExceptionResponder::respondTokenMismatch(new TokenMismatchException(), $request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertTrue($response->getSession()->has(ExceptionResponder::SESSION_EXPIRED_FLASH));
    }

    public function test_token_mismatch_api_returns_json_419(): void
    {
        $request = Request::create('/api/notifications/mark-all-read', 'PATCH');
        $request->headers->set('Accept', 'application/json');

        $response = ExceptionResponder::respondTokenMismatch(new TokenMismatchException(), $request);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => ExceptionResponder::SESSION_EXPIRED_MESSAGE,
        ], $response->getData(true));
    }

    public function test_production_api_errors_hide_exception_details(): void
    {
        config(['app.debug' => false]);

        $user = User::factory()->create();

        $this->mock(\App\Services\Dashboard\DashboardService::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->andThrow(new \RuntimeException('Sensitive SQL detail'));
        });

        $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.',
            ])
            ->assertJsonMissing(['Sensitive SQL detail']);
    }

    public function test_modal_component_still_renders_for_error_feedback_root(): void
    {
        $html = Blade::render('<x-errors.layout title="Test" code="500" />');

        $this->assertStringContainsString('app-feedback-root', $html);
    }
}
