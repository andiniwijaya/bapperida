import { createIcons, icons } from "lucide";
import { get, post } from "../../api/api";
import {
    LOADING_MESSAGES,
    clearFieldErrors,
    handleValidationError,
    populateSelect,
    runAction,
    setButtonLoading,
    showToast,
    reportRequestFailure,
    unwrapApiPayload,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("outgoingLetterForm");
const registrationSelect = document.getElementById(
    "letter_number_registration_id",
);
const letterTypeSelect = document.getElementById("letter_type");
const attachmentInput = document.getElementById("attachment");
const fileInput = document.getElementById("file");
const notesInput = document.getElementById("notes");
const submitButton = form?.querySelector("button[type=submit]");

const previewFields = {
    letter_number: document.getElementById("registration_letter_number"),
    index_code: document.getElementById("registration_index_code"),
    letter_code: document.getElementById("registration_letter_code"),
    department: document.getElementById("registration_department"),
    subject: document.getElementById("registration_subject"),
    recipient: document.getElementById("registration_recipient"),
};

let registrations = [];

form?.addEventListener("submit", submitForm);
registrationSelect?.addEventListener("change", renderRegistrationPreview);

async function loadForm() {
    try {
        const response = await get("/api/outgoing-letters/create");
        const payload = unwrapApiPayload(response);

        registrations = payload.registrations ?? [];
        populateRegistrationSelect(registrations);
        populateLetterTypeSelect(payload.letter_types ?? []);
        renderRegistrationPreview();
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat form arsip surat keluar.");
    }
}

function populateRegistrationSelect(items) {
    if (!registrationSelect) {
        return;
    }

    populateSelect(registrationSelect, [
        { value: "", label: "Pilih registrasi penomoran..." },
        ...items.map((item) => ({
            value: item.id,
            label: `${item.letter_number} — ${item.department.code}`,
        })),
    ]);
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

function renderRegistrationPreview() {
    const selectedId = Number(registrationSelect?.value);
    const registration = registrations.find((item) => item.id === selectedId);

    if (!registration) {
        Object.values(previewFields).forEach((field) => {
            field.textContent = "-";
        });
        return;
    }

    previewFields.letter_number.textContent = registration.letter_number || "-";
    previewFields.index_code.textContent = registration.index_code || "-";
    previewFields.letter_code.textContent = registration.letter_code || "-";
    previewFields.department.textContent = `${registration.department.code} - ${registration.department.name}`;
    previewFields.subject.textContent = registration.subject || "-";
    previewFields.recipient.textContent = registration.recipient || "-";
}

async function submitForm(event) {
    event.preventDefault();

    if (!form) {
        return;
    }

    const formData = new FormData();
    formData.append(
        "letter_number_registration_id",
        String(registrationSelect?.value || ""),
    );
    formData.append("letter_type", String(letterTypeSelect?.value || ""));
    formData.append("attachment", attachmentInput?.value || "");
    formData.append("notes", notesInput?.value || "");

    if (fileInput?.files?.[0]) {
        formData.append("file", fileInput.files[0]);
    }

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Menyimpan...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.save,
            action: () => post("/api/outgoing-letters", formData),
            successMessage: "Arsip surat keluar berhasil dibuat.",
            errorMessage: "Gagal menyimpan arsip surat keluar.",
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
