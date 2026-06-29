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
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("userForm");
const departmentSelect = document.getElementById("department_id");
const submitButton = form?.querySelector("button[type=submit]");

form?.addEventListener("submit", submitForm);

async function loadDepartments() {
    try {
        const response = await get("/api/departments?per_page=100&is_active=1");
        const items = response?.data?.data ?? [];

        if (!departmentSelect) {
            return;
        }

        populateSelect(departmentSelect, [
            { value: "", label: "Pilih bidang..." },
            ...items.map((item) => ({
                value: item.id,
                label: `${item.code} - ${item.name}`,
            })),
        ]);
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    }
}

async function submitForm(event) {
    event.preventDefault();

    if (!form) {
        return;
    }

    const data = {
        name: document.getElementById("name").value.trim(),
        username: document.getElementById("username").value.trim(),
        email: document.getElementById("email").value.trim(),
        role: document.getElementById("role").value,
        department_id: Number(document.getElementById("department_id").value),
    };

    const actorRole = document.getElementById("actorRole")?.value;
    if (actorRole === "admin") {
        data.role = "staff";
    }

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Menambahkan...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.create,
            action: () => post("/api/users", data),
            successMessage: SUCCESS_MESSAGES.create,
            errorMessage: ERROR_MESSAGES.create,
            onSuccess: () => {
                window.location.href = "/users";
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

loadDepartments();
