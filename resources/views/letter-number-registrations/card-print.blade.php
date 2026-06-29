<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Surat Keluar</title>
    <style>
        @page {
            size: 163mm 103mm landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #e5e7eb;
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
            background: #fff;
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
            background: #fff;
            color: #111827;
            text-decoration: none;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-primary {
            background: #0f3550;
            border-color: #0f3550;
            color: #fff;
        }

        .cards-preview {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 16px 24px;
        }

        .card-page {
            width: 163mm;
            height: 103mm;
            margin: 0 auto 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .empty-state {
            text-align: center;
            padding: 48px 16px;
            color: #6b7280;
            font-size: 0.95rem;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .cards-preview {
                padding: 0;
                max-width: none;
            }

            .card-page {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .card-page:last-child {
                page-break-after: auto;
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
