@php
    use App\Support\RegistrationCardPrint;

    $isTemplate = RegistrationCardPrint::isTemplateLayout($layout);
    $processor = $registration->creator?->name ?? '-';
    $sequence = str_pad((string) $registration->sequence_number, 3, '0', STR_PAD_LEFT);
    $letterDate = $registration->letter_date?->format('d/m/Y') ?? '-';
    $cardWidth = RegistrationCardPrint::CARD_WIDTH_MM.'mm';
    $cardHeight = RegistrationCardPrint::CARD_HEIGHT_MM.'mm';
    $sidebarWidth = '20mm';
    $contentWidth = $isTemplate ? '143mm' : $cardWidth;
@endphp

<div class="card-page" style="page-break-after: always;">
    <table cellpadding="0" cellspacing="0" class="card-table" style="width:{{ $cardWidth }};height:{{ $cardHeight }};border-collapse:collapse;table-layout:fixed;{{ $isTemplate ? 'border:1px solid #000;' : '' }}">
        <tr>
            @if ($isTemplate)
                <td class="card-sidebar" style="width:{{ $sidebarWidth }};background:#c62828;border:1px solid #000;vertical-align:middle;text-align:center;">
                    <div style="font-family:'Times New Roman', Times, serif;font-size:10pt;font-weight:bold;color:#ffffff;letter-spacing:0.08em;text-transform:uppercase;transform:rotate(-90deg);white-space:nowrap;">
                        KARTU SURAT KELUAR
                    </div>
                </td>
            @endif

            <td style="width:{{ $contentWidth }};background:{{ $backgroundColor }};border:{{ $isTemplate ? '1px solid #000' : 'none' }};border-left:{{ $isTemplate ? 'none' : 'none' }};padding:0;vertical-align:top;">
                <table cellpadding="0" cellspacing="0" style="width:100%;height:100%;border-collapse:collapse;table-layout:fixed;font-family:'Times New Roman', Times, serif;font-size:9.5pt;color:#000;">
                    <tr style="height:14mm;">
                        <td style="width:33.33%;border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Indeks :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $registration->index_code }}</div>
                        </td>
                        <td style="width:33.33%;border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Kode :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $registration->letter_code }}</div>
                        </td>
                        <td style="width:33.34%;border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">No. Urut :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $sequence }}</div>
                        </td>
                    </tr>

                    <tr style="height:14mm;">
                        <td colspan="3" style="border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Perihal :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};line-height:1.25;">{{ $registration->subject }}</div>
                        </td>
                    </tr>

                    <tr style="height:22mm;">
                        <td colspan="3" style="border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Isi Ringkas :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};line-height:1.3;">{{ $registration->summary }}</div>
                        </td>
                    </tr>

                    <tr style="height:14mm;">
                        <td colspan="3" style="border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Kepada :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $registration->recipient }}</div>
                        </td>
                    </tr>

                    <tr style="height:14mm;">
                        <td style="width:33.33%;border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Pengolah :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $processor }}</div>
                        </td>
                        <td style="width:33.33%;border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Tanggal Surat :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $letterDate }}</div>
                        </td>
                        <td style="width:33.34%;border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Lampiran :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};">{{ $registration->attachment ?: '-' }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" style="border:{{ $isTemplate ? '1px solid #000' : 'none' }};padding:1.5mm 2mm;vertical-align:top;">
                            @if ($isTemplate)
                                <span style="font-weight:normal;">Catatan :</span>
                            @endif
                            <div style="margin-top:{{ $isTemplate ? '1mm' : '2mm' }};line-height:1.25;">{{ $registration->notes ?: '-' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
