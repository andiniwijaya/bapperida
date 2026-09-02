<?php

namespace Tests\Unit;

use App\Support\PaginationWindow;
use PHPUnit\Framework\TestCase;

class PaginationWindowTest extends TestCase
{
    public function test_it_returns_all_pages_when_the_range_is_short(): void
    {
        $this->assertSame([1, 2, 3, 4, 5], PaginationWindow::items(1, 5));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], PaginationWindow::items(4, 7));
    }

    public function test_it_keeps_the_first_pages_visible_on_page_one(): void
    {
        $this->assertSame(
            [1, 2, 3, 'ellipsis', 10],
            PaginationWindow::items(1, 10),
        );
    }

    public function test_it_windows_around_the_current_page(): void
    {
        $this->assertSame(
            [1, 'ellipsis', 3, 4, 5, 6, 7, 'ellipsis', 10],
            PaginationWindow::items(5, 10),
        );
    }

    public function test_it_keeps_the_last_pages_visible_on_the_final_page(): void
    {
        $this->assertSame(
            [1, 'ellipsis', 8, 9, 10],
            PaginationWindow::items(10, 10),
        );
    }

    public function test_it_returns_an_empty_list_when_there_are_no_pages(): void
    {
        $this->assertSame([], PaginationWindow::items(1, 0));
    }
}
