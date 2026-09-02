@props([
    'paginator' => null,
    'class' => '',
])

@if (is_object($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    @php
        $pages = \App\Support\PaginationWindow::items(
            $paginator->currentPage(),
            $paginator->lastPage(),
        );
    @endphp

    <nav {{ $attributes->class(['ds-pagination', $class]) }} aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="ds-pagination__page is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">&lt;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="ds-pagination__page" aria-label="Halaman sebelumnya">&lt;</a>
        @endif

        @foreach ($pages as $page)
            @if ($page === 'ellipsis')
                <span class="ds-pagination__ellipsis" aria-hidden="true">&hellip;</span>
            @elseif ($page == $paginator->currentPage())
                <span class="ds-pagination__page is-active" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="ds-pagination__page">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="ds-pagination__page" aria-label="Halaman berikutnya">&gt;</a>
        @else
            <span class="ds-pagination__page is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">&gt;</span>
        @endif
    </nav>
@else
    <nav {{ $attributes->class(['ds-pagination', $class]) }} aria-label="Navigasi halaman">
        <span class="ds-caption">Navigasi halaman</span>
    </nav>
@endif
