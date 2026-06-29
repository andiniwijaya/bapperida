<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAPORAN ARSIP SURAT KELUAR</title>
    @include('pdf.layouts.official-report-styles')
</head>

<body>
    @php
        $printedAt = $printedAt ?? now();
        $printedBy = $printedBy ?? (auth()->user()?->name ?? 'System');
        $filterParts = array_filter([
            request('search') ? 'Pencarian: '.request('search') : null,
            request('year') ? 'Tahun: '.request('year') : null,
            request('department_id') ? 'Bidang: '.(\App\Models\Department::find(request('department_id'))?->name ?? request('department_id')) : null,
            request('letter_type') ? 'Jenis: '.(config('letter.types')[request('letter_type')] ?? request('letter_type')) : null,
            request('status') ? 'Status: '.(config('status.outgoing_letter')[request('status')] ?? request('status')) : null,
        ]);
        $activeFilters = count($filterParts) ? implode(' · ', $filterParts) : 'Semua data';
        $periodLabel = request('year') ? 'Tahun '.request('year') : 'Semua periode';
    @endphp

    <div class="report-document">
        @include('pdf.layouts.header', ['reportBranding' => $reportBranding ?? null])

        <h1 class="report-title">LAPORAN ARSIP SURAT KELUAR</h1>

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
                    <th>Nomor Surat</th>
                    <th>Kode Indeks</th>
                    <th>Kode Surat</th>
                    <th>Nomor Urut</th>
                    <th>Tahun</th>
                    <th>Bidang</th>
                    <th>Tanggal Surat</th>
                    <th>Perihal</th>
                    <th>Isi Ringkas</th>
                    <th>Tujuan</th>
                    <th>Jenis Surat</th>
                    <th>Lampiran</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Nama File PDF</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outgoingLetters as $outgoingLetter)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $outgoingLetter->registration?->letter_number }}</td>
                        <td>{{ $outgoingLetter->registration?->index_code }}</td>
                        <td>{{ $outgoingLetter->registration?->letter_code }}</td>
                        <td>{{ $outgoingLetter->registration?->sequence_number }}</td>
                        <td>{{ $outgoingLetter->registration?->year }}</td>
                        <td>{{ $outgoingLetter->registration?->department?->name }}</td>
                        <td>{{ $outgoingLetter->registration?->letter_date?->format('d/m/Y') }}</td>
                        <td>{{ $outgoingLetter->registration?->subject }}</td>
                        <td>{{ $outgoingLetter->registration?->summary }}</td>
                        <td>{{ $outgoingLetter->registration?->recipient }}</td>
                        <td>{{ config('letter.types')[$outgoingLetter->letter_type] ?? $outgoingLetter->letter_type }}</td>
                        <td>{{ $outgoingLetter->attachment }}</td>
                        <td>{{ config('status.outgoing_letter')[$outgoingLetter->status] ?? $outgoingLetter->status }}</td>
                        <td>{{ $outgoingLetter->notes }}</td>
                        <td>{{ $outgoingLetter->file_path ? basename($outgoingLetter->file_path) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" style="text-align:center; padding:18px;">Tidak ada data arsip surat keluar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @unless ($pdfMode ?? false)
            <div class="report-toolbar no-print">
                <a href="{{ route('outgoing-letters.index') }}">Kembali</a>
                <button type="button" class="btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
            </div>
        @endunless
    </div>
</body>

</html>
