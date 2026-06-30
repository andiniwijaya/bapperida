@php
    use App\Support\RegistrationCardPrint;

    $isTemplate = RegistrationCardPrint::isTemplateLayout($layout);
    $processor = $registration->creator?->name ?? '-';
    $sequence = str_pad((string) $registration->sequence_number, 3, '0', STR_PAD_LEFT);
    $letterDate = $registration->letter_date?->format('d/m/Y') ?? '-';
    $paperWidth = RegistrationCardPrint::CARD_WIDTH_MM.'mm';
    $paperHeight = RegistrationCardPrint::CARD_HEIGHT_MM.'mm';
    $margin = RegistrationCardPrint::CARD_MARGIN_MM.'mm';
    $innerWidth = RegistrationCardPrint::cardInnerWidthMm().'mm';
    $innerHeight = RegistrationCardPrint::cardInnerHeightMm().'mm';
    $sidebarWidth = RegistrationCardPrint::CARD_SIDEBAR_WIDTH_MM.'mm';
    $sidebarGap = RegistrationCardPrint::CARD_SIDEBAR_GAP_MM.'mm';
    $gridWidth = RegistrationCardPrint::cardGridWidthMm($isTemplate).'mm';
    $border = '1px solid #000000';
    $fontFamily = "'Times New Roman', Times, serif";
    $labelStyle = 'font-weight:normal;font-size:9pt;line-height:1.05;';
    $valueStyle = 'display:block;margin-top:0.6mm;font-size:9pt;line-height:1.15;overflow:hidden;';
    $cellPad = 'padding:0;vertical-align:top;overflow:hidden;';
    $innerPad = 'padding:1mm 1.5mm 0.6mm 1.5mm;';
    $contentBg = "background-color:{$backgroundColor};";
    $cellBorder = $isTemplate ? "border:{$border};" : 'border:none;';
    $colUnit = round(RegistrationCardPrint::cardGridWidthMm($isTemplate) / 12, 4).'mm';
    $rowHeights = [
        'index' => '10mm',
        'subject' => '9mm',
        'summary' => '22mm',
        'recipient' => '9mm',
        'meta' => '10mm',
        'notes' => '35mm',
    ];
@endphp

<div class="card-page" style="width:{{ $paperWidth }};height:{{ $paperHeight }};margin:0;background:#ffffff;overflow:hidden;line-height:0;font-size:0;"><table cellpadding="0" cellspacing="0" border="0" class="card-frame-table" style="width:{{ $paperWidth }};height:{{ $paperHeight }};border-collapse:collapse;table-layout:fixed;margin:0;padding:0;border:none;"><colgroup><col style="width:{{ $margin }};"><col style="width:{{ $innerWidth }};"><col style="width:{{ $margin }};"></colgroup><tr style="height:{{ $margin }};"><td colspan="3" style="padding:0;margin:0;font-size:0;line-height:0;">&nbsp;</td></tr><tr style="height:{{ $innerHeight }};"><td style="padding:0;margin:0;font-size:0;line-height:0;">&nbsp;</td><td style="width:{{ $innerWidth }};height:{{ $innerHeight }};padding:0;margin:0;vertical-align:top;">@if ($isTemplate)<table cellpadding="0" cellspacing="0" border="0" class="card-layout-table" style="width:{{ $innerWidth }};height:{{ $innerHeight }};border-collapse:collapse;table-layout:fixed;margin:0;padding:0;border:none;"><tr><td class="card-sidebar-cell" style="width:{{ $sidebarWidth }};height:{{ $innerHeight }};padding:0;margin:0;vertical-align:middle;text-align:center;background-color:#c62828;overflow:hidden;"><span class="card-sidebar-text">KARTU SURAT KELUAR</span></td><td style="width:{{ $sidebarGap }};padding:0;margin:0;font-size:0;line-height:0;">&nbsp;</td><td style="width:{{ $gridWidth }};height:{{ $innerHeight }};padding:0;margin:0;vertical-align:top;"><table cellpadding="0" cellspacing="0" border="0" class="card-grid-table" style="width:{{ $gridWidth }};height:{{ $innerHeight }};border-collapse:collapse;table-layout:fixed;margin:0;padding:0;border:none;">@else<table cellpadding="0" cellspacing="0" border="0" class="card-grid-table" style="width:{{ $innerWidth }};height:{{ $innerHeight }};border-collapse:collapse;table-layout:fixed;margin:0;padding:0;border:none;">@endif
    <colgroup>
        @for ($i = 0; $i < 12; $i++)
            <col style="width:{{ $colUnit }};">
        @endfor
    </colgroup>

    <tr style="height:{{ $rowHeights['index'] }};">
        <td colspan="3" style="height:{{ $rowHeights['index'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Indeks :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->index_code }}</span>
        </div></td>
        <td colspan="4" style="height:{{ $rowHeights['index'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Kode :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->letter_code }}</span>
        </div></td>
        <td colspan="5" style="height:{{ $rowHeights['index'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">No. Urut :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $sequence }}</span>
        </div></td>
    </tr>

    <tr style="height:{{ $rowHeights['subject'] }};">
        <td colspan="12" style="height:{{ $rowHeights['subject'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Perihal :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->subject }}</span>
        </div></td>
    </tr>

    <tr style="height:{{ $rowHeights['summary'] }};">
        <td colspan="12" style="height:{{ $rowHeights['summary'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Isi Ringkas :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->summary }}</span>
        </div></td>
    </tr>

    <tr style="height:{{ $rowHeights['recipient'] }};">
        <td colspan="12" style="height:{{ $rowHeights['recipient'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Kepada :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->recipient }}</span>
        </div></td>
    </tr>

    <tr style="height:{{ $rowHeights['meta'] }};">
        <td colspan="3" style="height:{{ $rowHeights['meta'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Pengolah :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $processor }}</span>
        </div></td>
        <td colspan="4" style="height:{{ $rowHeights['meta'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Tanggal Surat :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $letterDate }}</span>
        </div></td>
        <td colspan="5" style="height:{{ $rowHeights['meta'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Lampiran :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->attachment ?: '-' }}</span>
        </div></td>
    </tr>

    <tr style="height:{{ $rowHeights['notes'] }};">
        <td colspan="12" style="height:{{ $rowHeights['notes'] }};{{ $cellBorder }}{{ $cellPad }}{{ $contentBg }}font-family:{{ $fontFamily }};"><div style="{{ $innerPad }}">
            @if ($isTemplate)
                <span style="{{ $labelStyle }}">Catatan :</span>
            @endif
            <span style="{{ $valueStyle }}">{{ $registration->notes ?: '-' }}</span>
        </div></td>
    </tr>
</table>@if ($isTemplate)</td></tr></table>@endif</td><td style="padding:0;margin:0;font-size:0;line-height:0;">&nbsp;</td></tr><tr style="height:{{ $margin }};"><td colspan="3" style="padding:0;margin:0;font-size:0;line-height:0;">&nbsp;</td></tr></table></div>
