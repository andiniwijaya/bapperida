import { createIcons, icons } from "lucide";
import { get } from "../../api/api";
import { formatDate, showToast } from "./helper";
import { reportRequestFailure } from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const outgoingLetterId = document.getElementById("outgoing_letter_id")?.value;
const outgoingLetterDetail = document.getElementById("outgoingLetterDetail");
const downloadPdfLink = document.getElementById("downloadPdf");

document.addEventListener("DOMContentLoaded", loadOutgoingLetter);

async function loadOutgoingLetter() {
    if (!outgoingLetterId || !outgoingLetterDetail) {
        showToast("danger", "ID arsip tidak ditemukan.");
        return;
    }

    try {
        const response = await get(`/api/outgoing-letters/${outgoingLetterId}`);
        outgoingLetterDetail.innerHTML = renderDetail(response.data);
        downloadPdfLink.href = `/api/outgoing-letters/${outgoingLetterId}/download`;
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat detail arsip surat keluar.");
    }
}

function renderDetail(letter) {
    return `
        <div class="grid gap-6 lg:grid-cols-2">
            ${renderPanel(
                "Informasi Registrasi",
                `<dl class="grid gap-4">
                    <div>
                        <dt class="ds-detail-label">Nomor Surat</dt>
                        <dd class="ds-detail-value">${letter.registration.letter_number}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Kode Indeks</dt>
                        <dd class="ds-detail-value">${letter.registration.index_code}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Kode Surat</dt>
                        <dd class="ds-detail-value">${letter.registration.letter_code}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Bidang</dt>
                        <dd class="ds-detail-value">${letter.registration.department.code} - ${letter.registration.department.name}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Tanggal Surat</dt>
                        <dd class="ds-detail-value">${formatDate(letter.registration.letter_date)}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Jenis Surat</dt>
                        <dd class="ds-detail-value">${letter.letter_type_label}</dd>
                    </div>
                </dl>`,
            )}
            ${renderPanel(
                "Detail Arsip",
                `<dl class="grid gap-4">
                    <div>
                        <dt class="ds-detail-label">Lampiran</dt>
                        <dd class="ds-detail-value">${letter.attachment || "-"}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Catatan</dt>
                        <dd class="ds-detail-value">${letter.notes || "-"}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Status</dt>
                        <dd class="ds-detail-value">${letter.status_label}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Nama File PDF</dt>
                        <dd class="ds-detail-value">${letter.file_name || "-"}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Dibuat Oleh</dt>
                        <dd class="ds-detail-value">${letter.created_by.name || "-"}</dd>
                    </div>
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
