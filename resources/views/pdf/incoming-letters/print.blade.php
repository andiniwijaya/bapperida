<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAPORAN ARSIP SURAT MASUK</title>
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
            request('letter_attribute') ? 'Jenis: '.(config('letter.types')[request('letter_attribute')] ?? request('letter_attribute')) : null,
            request('status') ? 'Status: '.(config('status.incoming_letter')[request('status')] ?? request('status')) : null,
        ]);
        $activeFilters = count($filterParts) ? implode(' · ', $filterParts) : 'Semua data';
        $periodLabel = request('year') ? 'Tahun '.request('year') : 'Semua periode';
    @endphp

    <div class="report-document">
        @include('pdf.layouts.header', ['reportBranding' => $reportBranding ?? null])

        <h1 class="report-title">LAPORAN ARSIP SURAT MASUK</h1>

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
                    <th>Tanggal Surat</th>
                    <th>Tanggal Diterima</th>
                    <th>Tanggal Disposisi</th>
                    <th>Pengirim</th>
                    <th>Bidang</th>
                    <th>Bidang Disposisi</th>
                    <th>Perihal</th>
                    <th>Nama Agenda</th>
                    <th>Isi Ringkas</th>
                    <th>Jenis Surat</th>
                    <th>Lampiran</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>File PDF</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomingLetters as $incomingLetter)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $incomingLetter->letter_number }}</td>
                        <td>{{ $incomingLetter->sent_date?->format('d/m/Y') }}</td>
                        <td>{{ $incomingLetter->received_date?->format('d/m/Y') }}</td>
                        <td>{{ $incomingLetter->disposition_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $incomingLetter->sender }}</td>
                        <td>{{ $incomingLetter->department?->name }}</td>
                        <td>{{ $incomingLetter->dispositionDepartment?->name ?? '-' }}</td>
                        <td>{{ $incomingLetter->subject }}</td>
                        <td>{{ $incomingLetter->agenda_name ?? '-' }}</td>
                        <td>{{ $incomingLetter->summary ?? '-' }}</td>
                        <td>{{ config('letter.types')[$incomingLetter->letter_attribute] ?? $incomingLetter->letter_attribute }}</td>
                        <td>{{ $incomingLetter->attachment ?? '-' }}</td>
                        <td>{{ config('status.incoming_letter')[$incomingLetter->status] ?? $incomingLetter->status }}</td>
                        <td>{{ $incomingLetter->notes ?? '-' }}</td>
                        <td>{{ $incomingLetter->file_path ? basename($incomingLetter->file_path) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" style="text-align:center; padding:18px;">Tidak ada data yang dapat dicetak.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @unless ($pdfMode ?? false)
            <div class="report-toolbar no-print">
                <a href="{{ route('incoming-letters.index') }}">Kembali</a>
                <button type="button" class="btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
            </div>
        @endunless
    </div>
</body>

</html>
