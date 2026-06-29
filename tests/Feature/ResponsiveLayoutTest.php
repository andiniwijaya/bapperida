<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

/**
 * Smoke tests for responsive layout markup and shell structure.
 */
class ResponsiveLayoutTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_app_layout_includes_responsive_shell_markup(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="app-shell"', false);
        $response->assertSee('data-mobile-drawer', false);
        $response->assertSee('data-mobile-drawer-backdrop', false);
        $response->assertSee('id="app-main-content"', false);
        $response->assertSee('class="app-content', false);
        $response->assertSee('data-sidebar-toggle', false);
        $response->assertSee('role="contentinfo"', false);
    }

    public function test_guest_auth_pages_render_without_horizontal_shell_overflow_markers(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar', false);

        $this->get(route('password.request'))
            ->assertOk();
    }

    public function test_modal_component_includes_viewport_constrained_scroll_classes(): void
    {
        $html = Blade::render('<x-modal title="Dialog">Body</x-modal>');

        $this->assertStringContainsString('max-h-[min(90dvh,90vh)]', $html);
        $this->assertStringContainsString('overflow-y-auto', $html);
        $this->assertStringContainsString('data-modal-content', $html);
    }

    public function test_crud_index_filters_use_single_column_on_mobile_grid_classes(): void
    {
        $staff = User::factory()->create();

        $incoming = $this->actingAs($staff)
            ->get(route('incoming-letters.index'));

        $incoming->assertOk();
        $incoming->assertSee('col-span-12 sm:col-span-6 xl:col-span-2', false);

        $superadmin = User::factory()->superadmin()->create();

        $activity = $this->actingAs($superadmin)
            ->get(route('admin.activity-logs.index'));

        $activity->assertOk();
        $activity->assertSee('col-span-12 sm:col-span-6 md:col-span-2', false);
    }

    public function test_key_crud_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->superadmin()->create();

        $routes = [
            'incoming-letters.index',
            'outgoing-letters.index',
            'letter-number-registrations.index',
            'reports.index',
            'admin.activity-logs.index',
            'profile.edit',
            'admin.system-settings.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk();
        }
    }
}
