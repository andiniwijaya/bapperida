<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class EmptyStateVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_state_css_hides_icon_when_table_has_data(): void
    {
        $css = file_get_contents(resource_path('css/design-system.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.ds-empty-state.hidden', $css);
        $this->assertStringContainsString('.ds-empty-state[hidden]', $css);
        $this->assertMatchesRegularExpression(
            '/\.ds-empty-state\.hidden,\s*\.ds-empty-state\[hidden\]\s*\{\s*display:\s*none\s*!important;/s',
            $css,
        );
    }

    public function test_empty_state_javascript_syncs_hidden_attribute_with_table_rows(): void
    {
        $script = file_get_contents(resource_path('js/modules/form/empty-state.js'));
        $skeleton = file_get_contents(resource_path('js/modules/form/skeleton.js'));

        $this->assertIsString($script);
        $this->assertIsString($skeleton);
        $this->assertStringContainsString('function setEmptyStateVisibility(root, visible)', $script);
        $this->assertStringContainsString('root.setAttribute("hidden", "")', $script);
        $this->assertStringContainsString('root.removeAttribute("hidden")', $script);
        $this->assertStringContainsString('hideEmptyState(emptyState)', $skeleton);
    }

    public function test_table_index_pages_start_with_empty_state_hidden(): void
    {
        $user = User::factory()->superadmin()->create();

        $pages = [
            route('letter-number-registrations.index'),
            route('incoming-letters.index'),
            route('outgoing-letters.index'),
            route('reports.index'),
            route('admin.users.index'),
            route('admin.departments.index'),
            route('admin.activity-logs.index'),
            route('admin.registration-requests.index'),
        ];

        foreach ($pages as $url) {
            $html = $this->actingAs($user)->get($url)->assertOk()->getContent();

            $this->assertSame(
                1,
                preg_match('/<div\b[^>]*\bid="emptyState"[^>]*>/s', $html, $matches),
                "Empty state on {$url} is missing.",
            );

            $tag = $matches[0];

            $this->assertMatchesRegularExpression(
                '/\bclass="[^"]*\bhidden\b/',
                $tag,
                "Empty state on {$url} must include the hidden class until JavaScript shows it.",
            );
            $this->assertMatchesRegularExpression(
                '/(?:^|\s)hidden(="[^"]*")?(?=[\s>])/',
                $tag,
                "Empty state on {$url} must include the hidden attribute until the table is empty.",
            );
        }
    }

    public function test_visible_empty_state_does_not_force_the_hidden_attribute(): void
    {
        $html = Blade::render('<x-empty-state title="Belum ada data." icon="inbox" />');

        $this->assertStringContainsString('ds-empty-state', $html);
        $this->assertDoesNotMatchRegularExpression('/\bclass="[^"]*\bhidden\b[^"]*"/', $html);
    }
}
