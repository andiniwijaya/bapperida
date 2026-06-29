import { get } from "../../api/api";
import { formatDate, showToast } from "./helper";
import { reportRequestFailure } from "../admin/helper";

const registrationId = document.getElementById("registration_id")?.value;
const registrationDetail = document.getElementById("registrationDetail");

document.addEventListener("DOMContentLoaded", loadRegistration);

async function loadRegistration() {
    if (!registrationId || !registrationDetail) {
        showToast("danger", "ID registrasi tidak ditemukan.");
        return;
    }

    try {
        const response = await get(
            `/api/letter-number-registrations/${registrationId}`,
        );
        registrationDetail.innerHTML = renderDetail(response.data);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat detail registrasi.");
    }
}

function renderDetail(data) {
    return `
        <div class="grid gap-6 lg:grid-cols-2">
            ${renderPanel(
                "Informasi Surat",
                `<dl class="grid gap-4">
                    <div>
                        <dt class="ds-detail-label">Nomor Surat</dt>
                        <dd class="ds-detail-value">${data.letter_number}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Kode Indeks</dt>
                        <dd class="ds-detail-value">${data.index_code}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Jenis Surat</dt>
                        <dd class="ds-detail-value">${data.letter_type_label}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Tanggal Surat</dt>
                        <dd class="ds-detail-value">${formatDate(data.letter_date)}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Bidang</dt>
                        <dd class="ds-detail-value">${data.department?.code} - ${data.department?.name}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Status</dt>
                        <dd class="ds-detail-value">${data.status_label ?? data.status}</dd>
                    </div>
                </dl>`,
            )}
            ${renderPanel(
                "Detail Pengirim",
                `<dl class="grid gap-4">
                    <div>
                        <dt class="ds-detail-label">Kepada</dt>
                        <dd class="ds-detail-value">${data.recipient}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Perihal</dt>
                        <dd class="ds-detail-value">${data.subject}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Isi Ringkas</dt>
                        <dd class="ds-detail-value">${data.summary || "-"}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Lampiran</dt>
                        <dd class="ds-detail-value">${data.attachment || "-"}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Catatan</dt>
                        <dd class="ds-detail-value">${data.notes || "-"}</dd>
                    </div>
                    <div>
                        <dt class="ds-detail-label">Dibuat Oleh</dt>
                        <dd class="ds-detail-value">${data.created_by?.name || "-"}</dd>
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
