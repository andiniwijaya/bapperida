<style>
    @page {
        size: A4 landscape;
        margin: 10mm 8mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Times New Roman', Times, serif;
        margin: 0;
        color: #000;
        background: #fff;
        font-size: 9pt;
    }

    .report-document {
        width: 100%;
    }

    .report-title {
        margin: 0 0 8px;
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
    }

    .report-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px 16px;
        margin-bottom: 12px;
        font-size: 9pt;
    }

    .report-meta-item strong {
        display: block;
        font-weight: bold;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
        table-layout: fixed;
    }

    .report-table th,
    .report-table td {
        border: 1px solid #000;
        padding: 4px 3px;
        text-align: left;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .report-table th {
        background: #f3f4f6;
        font-weight: bold;
        text-align: center;
    }

    .report-table td.num {
        text-align: center;
        width: 28px;
    }

    .report-toolbar {
        margin-top: 24px;
        display: flex;
        gap: 12px;
    }

    .report-toolbar a,
    .report-toolbar button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 18px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f172a;
        text-decoration: none;
        font-family: system-ui, sans-serif;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .report-toolbar .btn-primary {
        background: #0f3550;
        border-color: #0f3550;
        color: #fff;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff;
        }
    }
</style>
