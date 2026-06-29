<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Surat — {{ $reportTypeLabel }}</title>
    @include('pdf.layouts.official-report-styles')
    <style>
        @page {
            size: 210mm 330mm;
            margin: 12mm 10mm;
        }
    </style>
</head>

<body>
  <div class="report-document">
        @include('pdf.layouts.header', ['reportBranding' => $reportBranding])

        <h1 class="report-title">LAPORAN {{ strtoupper($reportTypeLabel) }}</h1>

        @php
            $filterParts = array_filter([
                $filters['search'] ? 'Pencarian: '.$filters['search'] : null,
                $filters['department_id'] ? 'Bidang: '.(\App\Models\Department::find($filters['department_id'])?->name ?? $filters['department_id']) : null,
                $filters['year'] ? 'Tahun: '.$filters['year'] : null,
                $filters['status'] ? 'Status: '.$filters['status'] : null,
                $filters['letter_type'] ? 'Jenis: '.$filters['letter_type'] : null,
            ]);
            $activeFilters = count($filterParts) ? implode(' · ', $filterParts) : 'Semua filter';
            $periodLabel = ($filters['period_start'] ?? 'Semua').' s/d '.($filters['period_end'] ?? 'Semua');
        @endphp

        @include('pdf.partials.official-report-meta', [
            'printedAt' => $printedAt,
            'printedBy' => $printedBy,
            'periodLabel' => $periodLabel,
            'activeFilters' => $activeFilters,
        ])

        <table class="report-table">
            <thead>
                <tr>
                    <th class="num">No</th>
                    @foreach ($exportColumns as $column)
                        <th>{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        @foreach ($exportColumns as $column)
                            <td>{{ $row[$column['key']] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($exportColumns) + 1 }}" style="text-align:center; padding:18px;">
                            Tidak ada data untuk dicetak.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @unless ($pdfMode ?? false)
            <div class="report-toolbar no-print">
                <a href="{{ route('reports.index') }}">Kembali</a>
                <button type="button" class="btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
            </div>
        @endunless
    </div>
</body>

</html>
