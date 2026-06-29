@php
    $logoPath = $reportBranding['logo_path'] ?? public_path('assets/images/logo-kab-bandung.png');
    $address = $reportBranding['address'] ?? 'Jl. RAYA SOREANG Km. 17 SOREANG 40912 KABUPATEN BANDUNG PROVINSI JAWA BARAT';
    $phone = $reportBranding['phone'] ?? '(022) 5891159';
    $email = $reportBranding['email'] ?? 'bappeda@bandungkab.go.id';
    $website = $reportBranding['website'] ?? 'bappeda.bandungkab.go.id';
@endphp

<table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
    <tr>
        <td style="width:95px; vertical-align:middle; padding-right:12px;">
            <img src="{{ $logoPath }}" alt="Logo Kabupaten Bandung"
                style="width:90px; height:auto; display:block;" />
        </td>
        <td style="vertical-align:middle; text-align:center; font-family:'Times New Roman', Times, serif; color:#000;">
            <div style="font-size:13pt; font-weight:bold; line-height:1.2;">
                PEMERINTAH KABUPATEN BANDUNG
            </div>
            <div style="font-size:20pt; font-weight:bold; letter-spacing:0.28em; margin:6px 0 4px; line-height:1.1;">
                BAPPERIDA
            </div>
            <div style="font-size:11pt; font-weight:bold; line-height:1.25;">
                BADAN PERENCANAAN PEMBANGUNAN, RISET DAN INOVASI DAERAH
            </div>
            @if ($address)
                <div style="font-size:9pt; margin-top:6px; line-height:1.35;">
                    {{ $address }}
                </div>
            @endif
            <div style="font-size:9pt; line-height:1.35;">
                @if ($phone)
                    TELP. {{ $phone }}
                @endif
                @if ($email)
                    &nbsp;&nbsp;E-mail : {{ $email }}
                @endif
                @if ($website)
                    &nbsp;&nbsp;Website {{ $website }}
                @endif
            </div>
        </td>
    </tr>
</table>

<div style="border-bottom:3px solid #000; margin-bottom:14px;"></div>
