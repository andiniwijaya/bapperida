@php
    $printedAt = $printedAt ?? now();
    $printedBy = $printedBy ?? (auth()->user()?->name ?? 'System');
@endphp

<div class="report-meta">
    @if (!empty($periodLabel))
        <div class="report-meta-item">
            <strong>Periode</strong>
            <span>{{ $periodLabel }}</span>
        </div>
    @endif

    <div class="report-meta-item">
        <strong>Tanggal Cetak</strong>
        <span>{{ $printedAt->format('d/m/Y') }}</span>
    </div>

    <div class="report-meta-item">
        <strong>Jam Cetak</strong>
        <span>{{ $printedAt->format('H:i') }}</span>
    </div>

    <div class="report-meta-item">
        <strong>Dicetak Oleh</strong>
        <span>{{ $printedBy }}</span>
    </div>

    @if (!empty($activeFilters))
        <div class="report-meta-item" style="grid-column: 1 / -1;">
            <strong>Filter Aktif</strong>
            <span>{{ $activeFilters }}</span>
        </div>
    @endif
</div>
