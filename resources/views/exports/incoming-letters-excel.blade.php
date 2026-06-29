@php
    $columnCount = 16;
@endphp

<table>
    <tr>
        <td colspan="{{ $columnCount }}" style="vertical-align:top;">
            @include('pdf.layouts.header', ['reportBranding' => $reportBranding])
        </td>
    </tr>
    <tr>
        <td colspan="{{ $columnCount }}" style="text-align:center; font-weight:bold; font-size:14pt; font-family:'Times New Roman', Times, serif;">
            LAPORAN ARSIP SURAT MASUK
        </td>
    </tr>
    <tr>
        <td colspan="{{ $columnCount }}" style="font-family:'Times New Roman', Times, serif; font-size:10pt;">
            Periode: {{ $periodLabel }} |
            Tanggal Cetak: {{ $printedAt->format('d/m/Y') }} |
            Jam: {{ $printedAt->format('H:i') }} |
            Dicetak Oleh: {{ $printedBy }}
        </td>
    </tr>
    <tr>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">No</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Nomor Surat</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Tanggal Surat</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Tanggal Diterima</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Tanggal Disposisi</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Pengirim</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Bidang</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Bidang Disposisi</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Perihal</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Nama Agenda</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Isi Ringkas</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Jenis Surat</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Lampiran</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Status</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">Catatan</th>
        <th style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center;">File PDF</th>
    </tr>
    @forelse ($incomingLetters as $letter)
        <tr>
            <td style="border:1px solid #000; text-align:center;">{{ $loop->iteration }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->letter_number }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ optional($letter->sent_date)->format('d/m/Y') }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ optional($letter->received_date)->format('d/m/Y') }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ optional($letter->disposition_date)->format('d/m/Y') ?? '-' }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->sender }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->department?->name }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->dispositionDepartment?->name ?? '-' }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->subject }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->agenda_name ?? '-' }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->summary ?? '-' }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ config('letter.types')[$letter->letter_attribute] ?? $letter->letter_attribute }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->attachment ?? '-' }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ config('status.incoming_letter')[$letter->status] ?? $letter->status }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->notes ?? '-' }}</td>
            <td style="border:1px solid #000; vertical-align:top;">{{ $letter->file_path ? basename($letter->file_path) : '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="{{ $columnCount }}" style="border:1px solid #000; text-align:center; padding:12px;">
                Tidak ada data untuk diekspor.
            </td>
        </tr>
    @endforelse
</table>
