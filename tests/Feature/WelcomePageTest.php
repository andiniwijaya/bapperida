<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    public function test_welcome_page_renders_landing_content(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sistem Registrasi Penomoran dan Arsip Surat', false);
        $response->assertSee('Masuk', false);
        $response->assertSee('Daftar', false);
        $response->assertSee('logo-kab-bandung.png', false);
        $response->assertSee('logo-bapperida.png', false);
        $response->assertSee('Badan Perencanaan Pembangunan Riset dan Inovasi Daerah Kabupaten Bandung', false);
        $response->assertSee('landing-hero', false);
        $response->assertDontSee('Versi 1.0.0', false);
    }

    public function test_error_pages_render_with_design_system(): void
    {
        $notFound = $this->get('/non-existent-page-for-testing-404');

        $notFound->assertNotFound();
        $notFound->assertSee('404', false);
        $notFound->assertSee('Halaman yang Anda cari tidak ditemukan.', false);
        $notFound->assertSee('logo-kab-bandung.png', false);
        $notFound->assertSee('logo-bapperida.png', false);
    }
}
