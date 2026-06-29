<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_sees_superadmin_dashboard_view(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSeeText('Beranda Super Admin');
        $response->assertSeeText('Filter Grafik');
        $response->assertSeeText('Kelompokkan');
        $response->assertSee('dashboardResetButton', false);
        $response->assertSee('data-app-tooltip="Atur Ulang Filter"', false);
    }

    public function test_admin_sees_admin_dashboard_view(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSeeText('Beranda Admin');
    }

    public function test_staff_sees_staff_dashboard_view(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSeeText('Beranda Staff');
    }
}
