import { createIcons, icons } from "lucide";
import { get, post } from "../../api/api";
import {
    ERROR_MESSAGES,
    LOADING_MESSAGES,
    clearFieldErrors,
    handleValidationError,
    populateSelect,
    runAction,
    setButtonLoading,
    showToast,
    reportRequestFailure,
    SUCCESS_MESSAGES,
    unwrapApiPayload,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("incomingLetterForm");
const departmentSelect = document.getElementById("department_id");
const dispositionDepartmentSelect = document.getElementById(
    "disposition_department_id",
);
const letterAttributeSelect = document.getElementById("letter_attribute");
const statusSelect = document.getElementById("status");
const submitButton = form?.querySelector("button[type=submit]");

form?.addEventListener("submit", submitForm);

async function loadForm() {
    try {
        const response = await get("/api/incoming-letters/filters");
        const payload = unwrapApiPayload(response);

        populateSelect(departmentSelect, [
            { value: "", label: "Pilih bidang..." },
            ...(payload.departments ?? []).map((item) => ({
                value: item.id,
                label: `${item.code} - ${item.name}`,
            })),
        ]);
        populateSelect(dispositionDepartmentSelect, [
            { value: "", label: "Pilih bidang disposisi..." },
            ...(payload.departments ?? []).map((item) => ({
                value: item.id,
                label: `${item.code} - ${item.name}`,
            })),
        ]);
        populateSelect(letterAttributeSelect, [
            { value: "", label: "Pilih jenis surat..." },
            ...(payload.letter_attributes ?? []),
        ]);
        populateSelect(statusSelect, [
            { value: "", label: "Pilih status..." },
            ...(payload.statuses ?? []),
        ]);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat form arsip surat masuk.");
    }
}

async function submitForm(event) {
    event.preventDefault();

    if (!form) {
        return;
    }

    const formData = new FormData(form);

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Menyimpan...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.save,
            action: () => post("/api/incoming-letters", formData),
            successMessage: "Arsip surat masuk berhasil dibuat.",
            errorMessage: "Gagal menyimpan arsip surat masuk.",
            onSuccess: () => {
                window.location.href = "/incoming-letters";
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
