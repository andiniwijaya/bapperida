<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TablePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagination_component_renders_angle_brackets_and_page_numbers(): void
    {
        $paginator = new LengthAwarePaginator(
            items: range(1, 10),
            total: 100,
            perPage: 10,
            currentPage: 1,
            options: ['path' => '/letter-number-registrations'],
        );

        $html = Blade::render('<x-pagination :paginator="$paginator" />', [
            'paginator' => $paginator,
        ]);

        $this->assertStringContainsString('ds-pagination', $html);
        $this->assertStringContainsString('aria-label="Navigasi halaman"', $html);
        $this->assertStringContainsString('aria-label="Halaman sebelumnya"', $html);
        $this->assertStringContainsString('aria-label="Halaman berikutnya"', $html);
        $this->assertStringContainsString('>&lt;</span>', $html);
        $this->assertStringContainsString('>&gt;</a>', $html);
        $this->assertStringContainsString('is-active', $html);
        $this->assertStringContainsString('>1</span>', $html);
        $this->assertStringContainsString('>2</a>', $html);
        $this->assertStringContainsString('>3</a>', $html);
    }

    public function test_letter_modules_include_a_centered_pagination_container(): void
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

            $this->assertStringContainsString('id="pagination"', $html);
            $this->assertStringContainsString('id="table-per-page"', $html);
            $this->assertStringContainsString('value="10" selected', $html);
            $this->assertStringContainsString('>10</option>', $html);
            $this->assertStringContainsString('>25</option>', $html);
            $this->assertStringContainsString('>50</option>', $html);
            $this->assertStringContainsString('>100</option>', $html);
        }
    }

    public function test_javascript_pagination_helper_renders_arrows_and_unhides_after_load(): void
    {
        $helper = file_get_contents(resource_path('js/modules/admin/helper.js'));
        $skeleton = file_get_contents(resource_path('js/modules/form/skeleton.js'));

        $this->assertIsString($helper);
        $this->assertIsString($skeleton);
        $this->assertStringContainsString('export const DEFAULT_TABLE_PER_PAGE = 10', $helper);
        $this->assertStringNotContainsString('perPage: 15', $helper);
        $this->assertStringNotContainsString('DEFAULT_TABLE_PER_PAGE = 15', $helper);
        $this->assertStringContainsString('export function paginationWindow', $helper);
        $this->assertStringContainsString('Halaman sebelumnya', $helper);
        $this->assertStringContainsString('Halaman berikutnya', $helper);
        $this->assertStringContainsString('>&lt;</button>', $helper);
        $this->assertStringContainsString('>&gt;</button>', $helper);
        $this->assertStringContainsString('class="ds-pagination"', $helper);
        $this->assertStringContainsString('pagination.classList.remove("hidden")', $helper);
        $this->assertStringContainsString('pagination.classList.remove("hidden")', $skeleton);
    }
}
