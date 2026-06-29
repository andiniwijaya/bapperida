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

const registrationForm = document.getElementById("registrationForm");
const letterCodeInput = document.getElementById("letter_code");
const departmentSelect = document.getElementById("department_id");
const sequenceSelect = document.getElementById("sequence_number");
const yearInput = document.getElementById("year");
const previewInput = document.getElementById("letter_number_preview");
const submitButton = registrationForm?.querySelector("button[type=submit]");

let availableLetterTypes = [];

document.addEventListener("DOMContentLoaded", loadForm);
registrationForm?.addEventListener("submit", submitForm);
letterCodeInput?.addEventListener("change", previewLetterNumber);
departmentSelect?.addEventListener("change", previewLetterNumber);
sequenceSelect?.addEventListener("change", previewLetterNumber);
yearInput?.addEventListener("change", async () => {
    await loadSequenceNumbers();
    await previewLetterNumber();
});

async function loadForm() {
    try {
        const response = await get(
            `/api/letter-number-registrations/create?year=${yearInput?.value || new Date().getFullYear()}`,
        );
        const payload = unwrapApiPayload(response);

        populateDepartments(payload.departments ?? []);
        populateLetterTypes(payload.letter_types ?? []);
        await loadSequenceNumbers(payload.available_sequences);

        if (payload.current_year) {
            yearInput.value = payload.current_year;
        }

        if (payload.default_letter_type && document.getElementById("letter_type")) {
            document.getElementById("letter_type").value = payload.default_letter_type;
        }

        if (payload.letter_prefix && letterCodeInput && !letterCodeInput.value) {
            letterCodeInput.value = payload.letter_prefix;
        }

        await previewLetterNumber();
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat formulir registrasi.");
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

    availableLetterTypes = types;

    populateSelect(letterTypeSelect, [
        { value: "", label: "Pilih jenis surat..." },
        ...types.map((type) => ({
            value: type.value,
            label: type.label,
        })),
    ]);
}

async function loadSequenceNumbers(numbers = null) {
    if (!sequenceSelect) {
        return;
    }

    const year = yearInput?.value || new Date().getFullYear();

    if (!numbers) {
        const response = await get(
            `/api/letter-number-registrations/available-sequences?year=${year}`,
        );
        numbers = unwrapApiPayload(response);
    }

    populateSelect(
        sequenceSelect,
        numbers.map((number) => ({
            value: number,
            label: number.toString().padStart(3, "0"),
        })),
    );
}

async function previewLetterNumber() {
    const letterCode = letterCodeInput?.value.trim();
    const departmentId = departmentSelect?.value;
    const sequenceNumber = sequenceSelect?.value;
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

    if (!registrationForm) {
        return;
    }

    const data = {
        index_code: document.getElementById("index_code").value.trim(),
        letter_code: document.getElementById("letter_code").value.trim(),
        sequence_number: Number(
            document.getElementById("sequence_number").value,
        ),
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
    setButtonLoading(submitButton, true, "Menyimpan...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.save,
            action: () => post("/api/letter-number-registrations", data),
            successMessage: "Registrasi surat berhasil disimpan.",
            errorMessage: "Terjadi kesalahan saat menyimpan data.",
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
