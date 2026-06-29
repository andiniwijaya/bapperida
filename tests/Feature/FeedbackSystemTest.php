<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FeedbackSystemTest extends TestCase
{
    use RefreshDatabase;
    public function test_feedback_root_component_renders_required_markup(): void
    {
        $html = Blade::render('<x-feedback.root />');

        $this->assertStringContainsString('id="app-feedback-root"', $html);
        $this->assertStringContainsString('data-feedback-loading', $html);
        $this->assertStringContainsString('data-feedback-progress-bar', $html);
        $this->assertStringContainsString('data-feedback-result', $html);
        $this->assertStringContainsString('data-feedback-confirm', $html);
        $this->assertStringContainsString('logo-bapperida.png', $html);
    }

    public function test_app_layout_includes_global_feedback_root(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="app-feedback-root"', false);
    }
}
