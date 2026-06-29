import { createIcons, icons } from "lucide";
import { get, put } from "../../api/api";
import {
    ERROR_MESSAGES,
    LOADING_MESSAGES,
    clearFieldErrors,
    handleValidationError,
    runAction,
    setButtonLoading,
    showToast,
    reportRequestFailure,
    SUCCESS_MESSAGES,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const departmentId = document.getElementById("department_id")?.value;
const form = document.getElementById("departmentForm");
const submitButton = form?.querySelector("button[type=submit]");

form?.addEventListener("submit", submitForm);

async function loadDepartment() {
    try {
        const response = await get(`/api/departments/${departmentId}`);
        const department = response.data;

        document.getElementById("code").value = department.code;
        document.getElementById("name").value = department.name;
        document.getElementById("is_active").checked = Boolean(department.is_active);
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    }
}

async function submitForm(event) {
    event.preventDefault();

    const data = {
        code: document.getElementById("code").value.trim(),
        name: document.getElementById("name").value.trim(),
        is_active: document.getElementById("is_active").checked,
    };

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Memperbarui...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.update,
            action: () => put(`/api/departments/${departmentId}`, data),
            successMessage: SUCCESS_MESSAGES.update,
            errorMessage: ERROR_MESSAGES.update,
            onSuccess: () => {
                window.location.href = "/departments";
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

loadDepartment();
