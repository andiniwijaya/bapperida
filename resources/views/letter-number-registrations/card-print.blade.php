<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Surat Keluar</title>
    <style>
        @page {
            size: 462.047pt 292.064pt;
            margin: 0;
        }

        @if ($pdfMode ?? false)
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
            line-height: 0;
            font-size: 0;
        }

        .cards-preview {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 0;
            font-size: 0;
        }

        .card-page {
            width: 163mm;
            height: 103mm;
            margin: 0 !important;
            padding: 0;
            box-shadow: none;
            page-break-inside: avoid;
            overflow: hidden;
            line-height: 0;
            font-size: 0;
        }

        .card-page + .card-page {
            page-break-before: always;
        }
        @endif

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            background: {{ ($pdfMode ?? false) ? '#ffffff' : '#e5e7eb' }};
        }

        .screen-toolbar {
            max-width: 960px;
            margin: 0 auto;
            padding: 16px;
        }

        .toolbar-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 16px;
        }

        .toolbar-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .toolbar-meta {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: #4b5563;
        }

        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #111827;
            text-decoration: none;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-primary {
            background: #0f3550;
            border-color: #0f3550;
            color: #ffffff;
        }

        .cards-preview {
            max-width: 960px;
            margin: 0 auto;
            padding: {{ ($pdfMode ?? false) ? '0' : '0 16px 24px' }};
        }

        .card-page {
            width: 163mm;
            height: 103mm;
            margin: {{ ($pdfMode ?? false) ? '0' : '0 auto 16px' }};
            padding: 0;
            box-shadow: {{ ($pdfMode ?? false) ? 'none' : '0 2px 8px rgba(0, 0, 0, 0.12)' }};
            overflow: hidden;
            page-break-inside: avoid;
            line-height: 0;
            background: #ffffff;
        }

        .card-page + .card-page {
            page-break-before: always;
        }

        .card-frame-table,
        .card-layout-table,
        .card-grid-table {
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            page-break-inside: avoid;
            margin: 0;
            padding: 0;
        }

        .card-grid-table {
            line-height: normal;
            font-size: 9pt;
        }

        .card-sidebar-cell {
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        .card-sidebar-text {
            display: inline-block;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.06em;
            white-space: nowrap;
            line-height: 1;
            -webkit-transform: rotate(-90deg);
            transform: rotate(-90deg);
            transform-origin: center center;
        }

        .empty-state {
            text-align: center;
            padding: 48px 16px;
            color: #6b7280;
            font-size: 0.95rem;
        }

        @media print {
            @page {
                size: 163mm 103mm;
                margin: 0;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                background: #ffffff;
                line-height: 0;
            }

            .no-print {
                display: none !important;
            }

            .cards-preview {
                padding: 0;
                margin: 0;
                max-width: none;
                line-height: 0;
            }

            .card-page {
                width: 163mm;
                height: 103mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
                page-break-inside: avoid;
                overflow: hidden;
                line-height: 0;
                font-size: 0;
            }

            .card-page + .card-page {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>
    @unless ($pdfMode ?? false)
        <div class="screen-toolbar no-print">
            <div class="toolbar-inner">
                <div>
                    <h1 class="toolbar-title">Cetak Kartu Surat Keluar</h1>
                    <p class="toolbar-meta">
                        Format:
                        {{ $layoutLabel }}
                        · Latar:
                        {{ $backgroundLabel }}
                        · {{ $registrations->count() }} kartu
                    </p>
                </div>
                <div class="toolbar-actions">
                    <a href="{{ route('letter-number-registrations.index') }}" class="btn">Kembali</a>
                    <button type="button" onclick="window.print()" class="btn btn-primary">Cetak / Simpan PDF</button>
                </div>
            </div>
        </div>
    @endunless

    <div class="cards-preview">
        @forelse ($registrations as $registration)
            @include('letter-number-registrations.partials.card', [
                'registration' => $registration,
                'layout' => $layout,
                'backgroundColor' => $backgroundColor,
            ])
        @empty
            <p class="empty-state">Tidak ada data registrasi yang dapat dicetak.</p>
        @endforelse
    </div>
</body>

</html>
