import { createIcons, icons } from "lucide";
import { get, post } from "../../api/api";
import {
    LOADING_MESSAGES,
    clearFieldErrors,
    handleValidationError,
    populateSelect,
    runAction,
    setButtonLoading,
    setSelectValue,
    showToast,
    reportRequestFailure,
    unwrapApiPayload,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("outgoingLetterForm");
const letterTypeSelect = document.getElementById("letter_type");
const statusSelect = document.getElementById("status");
const attachmentInput = document.getElementById("attachment");
const fileInput = document.getElementById("file");
const notesInput = document.getElementById("notes");
const submitButton = form?.querySelector("button[type=submit]");
const outgoingLetterId = document.getElementById("outgoing_letter_id")?.value;

const previewFields = {
    letter_number: document.getElementById("registration_letter_number"),
    index_code: document.getElementById("registration_index_code"),
    letter_code: document.getElementById("registration_letter_code"),
    department: document.getElementById("registration_department"),
    subject: document.getElementById("registration_subject"),
    recipient: document.getElementById("registration_recipient"),
};

form?.addEventListener("submit", submitForm);

async function loadForm() {
    if (!outgoingLetterId) {
        showToast("danger", "ID arsip tidak ditemukan.");
        return;
    }

    try {
        const [createResponse, detail] = await Promise.all([
            get("/api/outgoing-letters/create"),
            get(`/api/outgoing-letters/${outgoingLetterId}`),
        ]);
        const payload = unwrapApiPayload(createResponse);

        populateLetterTypeSelect(payload.letter_types ?? []);
        populateStatusSelect(payload.statuses ?? []);
        fillDetail(detail.data);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat data arsip surat keluar.");
    }
}

function populateLetterTypeSelect(types) {
    if (!letterTypeSelect) {
        return;
    }

    populateSelect(letterTypeSelect, [
        { value: "", label: "Pilih jenis surat..." },
        ...types.map((type) => ({
            value: type.value,
            label: type.label,
        })),
    ]);
}

function populateStatusSelect(statuses) {
    if (!statusSelect) {
        return;
    }

    populateSelect(statusSelect, [
        { value: "", label: "Pilih status..." },
        ...statuses.map((status) => ({
            value: status.value,
            label: status.label,
        })),
    ]);
}

function fillDetail(letter) {
    setSelectValue(letterTypeSelect, letter.letter_type);
    setSelectValue(statusSelect, letter.status);
    attachmentInput.value = letter.attachment ?? "";
    notesInput.value = letter.notes ?? "";

    previewFields.letter_number.textContent =
        letter.registration.letter_number || "-";
    previewFields.index_code.textContent =
        letter.registration.index_code || "-";
    previewFields.letter_code.textContent =
        letter.registration.letter_code || "-";
    previewFields.department.textContent = `${letter.registration.department.code} - ${letter.registration.department.name}`;
    previewFields.subject.textContent = letter.registration.subject || "-";
    previewFields.recipient.textContent = letter.registration.recipient || "-";
}

async function submitForm(event) {
    event.preventDefault();

    if (!form || !outgoingLetterId) {
        return;
    }

    const formData = new FormData();
    formData.append("letter_type", String(letterTypeSelect?.value || ""));
    formData.append("status", String(statusSelect?.value || ""));
    formData.append("attachment", attachmentInput?.value || "");
    formData.append("notes", notesInput?.value || "");
    formData.append("_method", "PUT");
    if (fileInput?.files?.[0]) {
        formData.append("file", fileInput.files[0]);
    }

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Memperbarui...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.update,
            action: () => post(`/api/outgoing-letters/${outgoingLetterId}`, formData),
            successMessage: "Arsip surat keluar berhasil diperbarui.",
            errorMessage: "Gagal memperbarui arsip surat keluar.",
            onSuccess: () => {
                window.location.href = "/outgoing-letters";
            },
        });
    } catch (error) {
        console.error(error);
        if (handleValidationError(form, error)) {
            return;
        }
    } finally {
        setButtonLoading(submitButton, false);
    }
}

loadForm();
