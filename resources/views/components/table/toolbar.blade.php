@props([
    'showPerPage' => true,
    'showOrder' => true,
])

<div class="app-table-toolbar">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-charcoal-600 dark:text-slate-400">
            Total data: <span id="totalCount">0</span>
        </p>

        <div class="flex flex-wrap items-center gap-3">
            @isset($extra)
                {{ $extra }}
            @endisset

            @if ($showPerPage)
                <label class="flex items-center gap-2 text-sm text-charcoal-600 dark:text-slate-400">
                    <span>Tampilkan</span>
                    <select id="table-per-page" class="app-table-toolbar__select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>
            @endif

            @if ($showOrder)
                <label class="flex items-center gap-2 text-sm text-charcoal-600 dark:text-slate-400">
                    <span>Urutkan</span>
                    <select id="table-order" class="app-table-toolbar__select">
                        <option value="latest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                    </select>
                </label>
            @endif
        </div>
    </div>
</div>
