import { get, put } from "../../api/api";
import {
    LOADING_MESSAGES,
    clearFieldErrors,
    handleValidationError,
    populateSelect,
    setSelectValue,
    runAction,
    setButtonLoading,
    showToast,
    reportRequestFailure,
    unwrapApiPayload,
} from "../admin/helper";

const registrationId = document.getElementById("registration_id")?.value;
const registrationForm = document.getElementById("registrationForm");
const submitButton = registrationForm?.querySelector("button[type=submit]");
const letterCodeInput = document.getElementById("letter_code");
const departmentSelect = document.getElementById("department_id");
const sequenceInput = document.getElementById("sequence_number");
const yearInput = document.getElementById("year");
const previewInput = document.getElementById("letter_number_preview");

document.addEventListener("DOMContentLoaded", init);
registrationForm?.addEventListener("submit", submitForm);
letterCodeInput?.addEventListener("change", previewLetterNumber);
departmentSelect?.addEventListener("change", previewLetterNumber);
sequenceInput?.addEventListener("input", previewLetterNumber);
yearInput?.addEventListener("change", previewLetterNumber);

async function init() {
    if (!registrationId) {
        showToast("danger", "ID registrasi tidak ditemukan.");
        return;
    }

    try {
        const [createResponse, registrationResponse] = await Promise.all([
            get(
                `/api/letter-number-registrations/create?year=${yearInput?.value || new Date().getFullYear()}`,
            ),
            get(`/api/letter-number-registrations/${registrationId}`),
        ]);
        const createPayload = unwrapApiPayload(createResponse);

        populateDepartments(createPayload.departments ?? []);
        populateLetterTypes(createPayload.letter_types ?? []);
        populateForm(registrationResponse.data);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat data registrasi.");
    }
}

function populateDepartments(departments) {
    if (!departmentSelect) {
        return;
    }

    populateSelect(departmentSelect, [
        { value: "", label: "Pilih bidang..." },
        ...departments.map((department) => ({
            value: department.id,
            label: `${department.code} - ${department.name}`,
        })),
    ]);
}

function populateLetterTypes(types) {
    const letterTypeSelect = document.getElementById("letter_type");

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

function populateForm(data) {
    document.getElementById("index_code").value = data.index_code;
    document.getElementById("letter_code").value = data.letter_code;
    sequenceInput.value = data.sequence_number;
    document.getElementById("year").value = data.year;
    previewInput.value = data.letter_number;
    document.getElementById("subject").value = data.subject;
    document.getElementById("summary").value = data.summary ?? "";
    document.getElementById("recipient").value = data.recipient;
    document.getElementById("letter_date").value = data.letter_date;
    setSelectValue(document.getElementById("letter_type"), data.letter_type);
    document.getElementById("attachment").value = data.attachment ?? "";
    document.getElementById("notes").value = data.notes ?? "";
    setSelectValue(departmentSelect, data.department.id);
}

async function previewLetterNumber() {
    const letterCode = letterCodeInput?.value.trim();
    const departmentId = departmentSelect?.value;
    const sequenceNumber = sequenceInput?.value;
    const year = yearInput?.value;

    if (!letterCode || !departmentId || !sequenceNumber || !year) {
        previewInput.value = "";
        return;
    }

    try {
        const response = await get(
            `/api/letter-number-registrations/preview?letter_code=${encodeURIComponent(letterCode)}&department_id=${departmentId}&sequence_number=${sequenceNumber}&year=${year}`,
        );

        previewInput.value = response.data.letter_number;
    } catch (error) {
        console.error(error);
        showToast("warning", "Periksa kembali kode surat dan bidang.");
    }
}

async function submitForm(event) {
    event.preventDefault();

    if (!registrationForm || !registrationId) {
        return;
    }

    const data = {
        index_code: document.getElementById("index_code").value.trim(),
        letter_code: document.getElementById("letter_code").value.trim(),
        sequence_number: Number(sequenceInput.value),
        year: Number(document.getElementById("year").value),
        subject: document.getElementById("subject").value.trim(),
        summary: document.getElementById("summary").value.trim(),
        recipient: document.getElementById("recipient").value.trim(),
        letter_date: document.getElementById("letter_date").value,
        letter_type: document.getElementById("letter_type").value,
        attachment: document.getElementById("attachment").value.trim(),
        notes: document.getElementById("notes").value.trim(),
        department_id: Number(document.getElementById("department_id").value),
    };

    clearFieldErrors(registrationForm);
    setButtonLoading(submitButton, true, "Memperbarui...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.update,
            action: () => put(`/api/letter-number-registrations/${registrationId}`, data),
            successMessage: "Registrasi berhasil diperbarui.",
            errorMessage: "Gagal memperbarui registrasi.",
            onSuccess: () => {
                window.location.href = "/letter-number-registrations";
            },
        });
    } catch (error) {
        console.error(error);

        if (handleValidationError(registrationForm, error)) {
            return;
        }
    } finally {
        setButtonLoading(submitButton, false);
    }
}
