<table>
    <tr>
        <td colspan="{{ count($exportColumns) + 1 }}" style="vertical-align:top;">
            @include('pdf.layouts.header', ['reportBranding' => $reportBranding])
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($exportColumns) + 1 }}" style="text-align:center; font-weight:bold; font-size:14pt; font-family:'Times New Roman', Times, serif;">
            LAPORAN {{ strtoupper($reportTypeLabel) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($exportColumns) + 1 }}" style="font-family:'Times New Roman', Times, serif; font-size:10pt;">
            Jenis Laporan: {{ $reportTypeLabel }} |
            Periode: {{ $filters['period_start'] ?? 'Semua' }} s/d {{ $filters['period_end'] ?? 'Semua' }} |
            Tahun: {{ $filters['year'] ?? 'Semua' }} |
            Dicetak: {{ $printedAt->format('d/m/Y H:i') }} oleh {{ $printedBy }}
        </td>
    </tr>
    <tr>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">No</th>
        @foreach ($exportColumns as $column)
            <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">
                {{ $column['label'] }}
            </th>
        @endforeach
    </tr>
    @forelse ($rows as $row)
        <tr>
            <td style="border:1px solid #000; text-align:center;">{{ $loop->iteration }}</td>
            @foreach ($exportColumns as $column)
                <td style="border:1px solid #000; vertical-align:top;">
                    {{ $row[$column['key']] ?? '-' }}
                </td>
            @endforeach
        </tr>
    @empty
        <tr>
            <td colspan="{{ count($exportColumns) + 1 }}" style="border:1px solid #000; text-align:center; padding:12px;">
                Tidak ada data untuk diekspor.
            </td>
        </tr>
    @endforelse
</table>
