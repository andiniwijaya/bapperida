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
    setSelectValue,
    showToast,
    reportRequestFailure,
    SUCCESS_MESSAGES,
    unwrapApiPayload,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("incomingLetterForm");
const incomingLetterId = document.getElementById("incoming_letter_id")?.value;
const departmentSelect = document.getElementById("department_id");
const dispositionDepartmentSelect = document.getElementById(
    "disposition_department_id",
);
const letterAttributeSelect = document.getElementById("letter_attribute");
const statusSelect = document.getElementById("status");
const submitButton = form?.querySelector("button[type=submit]");

form?.addEventListener("submit", submitForm);

async function loadForm() {
    if (!incomingLetterId) {
        showToast("danger", "ID arsip tidak ditemukan.");
        return;
    }

    try {
        const [filtersResponse, detail] = await Promise.all([
            get("/api/incoming-letters/filters"),
            get(`/api/incoming-letters/${incomingLetterId}`),
        ]);
        const payload = unwrapApiPayload(filtersResponse);

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
        fillDetail(detail.data);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat data arsip surat masuk.");
    }
}

function fillDetail(letter) {
    document.getElementById("letter_number").value = letter.letter_number;
    document.getElementById("sent_date").value = letter.sent_date;
    document.getElementById("received_date").value = letter.received_date;
    document.getElementById("disposition_date").value =
        letter.disposition_date || "";
    document.getElementById("sender").value = letter.sender;
    setSelectValue(departmentSelect, letter.department?.id || "");
    setSelectValue(
        dispositionDepartmentSelect,
        letter.disposition_department?.id || "",
    );
    document.getElementById("subject").value = letter.subject;
    document.getElementById("agenda_name").value = letter.agenda_name || "";
    document.getElementById("summary").value = letter.summary || "";
    setSelectValue(letterAttributeSelect, letter.letter_attribute);
    document.getElementById("attachment").value = letter.attachment || "";
    setSelectValue(statusSelect, letter.status);
    document.getElementById("notes").value = letter.notes || "";
}

async function submitForm(event) {
    event.preventDefault();

    if (!form || !incomingLetterId) {
        return;
    }

    const formData = new FormData(form);
    formData.append("_method", "PUT");

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Memperbarui...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.update,
            action: () => post(`/api/incoming-letters/${incomingLetterId}`, formData),
            successMessage: "Arsip surat masuk berhasil diperbarui.",
            errorMessage: "Gagal memperbarui arsip surat masuk.",
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
