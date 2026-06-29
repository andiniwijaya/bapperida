@props([
    'paginator' => null,
    'class' => '',
])

@if (is_object($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <nav class="flex items-center justify-between {{ $class }}" aria-label="Navigasi halaman">
        <div class="flex-1">
            @if ($paginator->onFirstPage())
                <span class="ds-pagination__link is-disabled cursor-not-allowed px-4 py-2">
                    ← Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="ds-pagination__link px-4 py-2">
                    ← Sebelumnya
                </a>
            @endif
        </div>

        <div class="flex items-center gap-1">
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="ds-pagination__page is-active">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="ds-pagination__page">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
        </div>

        <div class="flex-1 text-right">
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="ds-pagination__link px-4 py-2">
                    Berikutnya →
                </a>
            @else
                <span class="ds-pagination__link is-disabled cursor-not-allowed px-4 py-2">
                    Berikutnya →
                </span>
            @endif
        </div>
    </nav>
@else
    <nav class="flex items-center justify-between {{ $class }}" aria-label="Navigasi halaman">
        <span class="ds-caption">Navigasi halaman</span>
    </nav>
@endif
