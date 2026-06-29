import { createIcons, icons } from "lucide";
import { get } from "../../api/api";
import { formatDate, showToast } from "./helper";
import { reportRequestFailure } from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const incomingLetterId = document.getElementById("incoming_letter_id")?.value;
const incomingLetterDetail = document.getElementById("incomingLetterDetail");
const downloadPdfLink = document.getElementById("downloadPdf");

async function loadIncomingLetter() {
    if (!incomingLetterId || !incomingLetterDetail) {
        showToast("danger", "ID arsip tidak ditemukan.");
        return;
    }

    try {
        const response = await get(`/api/incoming-letters/${incomingLetterId}`);
        incomingLetterDetail.innerHTML = renderDetail(response.data);
        downloadPdfLink.href = `/api/incoming-letters/${incomingLetterId}/download`;
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat detail arsip surat masuk.");
    }
}

function renderDetail(letter) {
    return `
        <div class="grid gap-6 lg:grid-cols-2">
            ${renderPanel(
                "Informasi Utama",
                `<dl class="grid gap-4">
                    ${renderField("Nomor Surat", letter.letter_number)}
                    ${renderField("Tanggal Surat", formatDate(letter.sent_date))}
                    ${renderField("Tanggal Diterima", formatDate(letter.received_date))}
                    ${renderField("Tanggal Disposisi", formatDate(letter.disposition_date))}
                    ${renderField("Pengirim", letter.sender)}
                    ${renderField("Bidang", letter.department?.name || "-")}
                    ${renderField("Bidang Disposisi", letter.disposition_department?.name || "-")}
                    ${renderField("Perihal", letter.subject)}
                    ${renderField("Nama Agenda", letter.agenda_name || "-")}
                    ${renderField("Jenis Surat", letter.letter_attribute_label)}
                </dl>`,
            )}
            ${renderPanel(
                "Detail Arsip",
                `<dl class="grid gap-4">
                    ${renderField("Isi Ringkas", letter.summary || "-")}
                    ${renderField("Lampiran", letter.attachment || "-")}
                    ${renderField("Status", letter.status_label)}
                    ${renderField("Catatan", letter.notes || "-")}
                    ${renderField("Nama File PDF", letter.file_name || "-")}
                    ${renderField("Dibuat Oleh", letter.created_by?.name || "-")}
                    ${renderField("Dibuat Pada", letter.created_at || "-")}
                </dl>`,
            )}
        </div>
    `;
}

function renderPanel(title, content) {
    return `
        <div class="app-panel">
            <div class="app-panel__header">
                <h2 class="app-panel__title">${title}</h2>
            </div>
            <div class="app-panel__body">${content}</div>
        </div>
    `;
}

function renderField(label, value) {
    return `
        <div>
            <dt class="ds-detail-label">${label}</dt>
            <dd class="ds-detail-value">${value}</dd>
        </div>
    `;
}

loadIncomingLetter();
