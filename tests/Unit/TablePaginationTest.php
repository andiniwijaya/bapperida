<?php

namespace Tests\Unit;

use App\Support\TablePagination;
use PHPUnit\Framework\TestCase;

class TablePaginationTest extends TestCase
{
    public function test_it_defaults_to_ten_records_per_page(): void
    {
        $this->assertSame(10, TablePagination::DEFAULT_PER_PAGE);
        $this->assertSame([10, 25, 50, 100], TablePagination::PER_PAGE_OPTIONS);
        $this->assertSame(10, TablePagination::resolve(null));
        $this->assertSame(10, TablePagination::resolve(15));
    }

    public function test_it_accepts_the_toolbar_page_sizes(): void
    {
        $this->assertSame(10, TablePagination::resolve(10));
        $this->assertSame(25, TablePagination::resolve(25));
        $this->assertSame(50, TablePagination::resolve(50));
        $this->assertSame(100, TablePagination::resolve(100));
    }
}
