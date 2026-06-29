import { createIcons, icons } from "lucide";
import { get, patch, put } from "../../api/api";
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
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const userId = document.getElementById("user_id")?.value;
const form = document.getElementById("userForm");
const departmentSelect = document.getElementById("department_id");
const roleField = document.getElementById("roleField");
const statusField = document.getElementById("statusField");
const submitButton = form?.querySelector("button[type=submit]");

let canChangeRole = false;
let canChangeStatus = false;

form?.addEventListener("submit", submitForm);

async function loadForm() {
    try {
        const departmentsResponse = await get("/api/departments?per_page=100&is_active=1");
        const departments = departmentsResponse?.data?.data ?? [];

        if (departmentSelect) {
            populateSelect(departmentSelect, [
                { value: "", label: "Pilih bidang..." },
                ...departments.map((item) => ({
                    value: item.id,
                    label: `${item.code} - ${item.name}`,
                })),
            ]);
        }

        const userResponse = await get(`/api/users/${userId}`);
        const user = userResponse.data;

        document.getElementById("name").value = user.name;
        document.getElementById("username").value = user.username;
        document.getElementById("email").value = user.email;
        setSelectValue(departmentSelect, user.department?.id ?? "");

        canChangeRole = Boolean(user.can?.change_role);
        canChangeStatus = Boolean(user.can?.change_status);

        if (canChangeRole) {
            document.getElementById("role").value = user.role;
            roleField?.classList.remove("hidden");
        } else {
            roleField?.classList.add("hidden");
        }

        if (canChangeStatus) {
            document.getElementById("status").value = user.status;
            statusField?.classList.remove("hidden");
        } else {
            statusField?.classList.add("hidden");
        }
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    }
}

async function submitForm(event) {
    event.preventDefault();

    const data = {
        name: document.getElementById("name").value.trim(),
        username: document.getElementById("username").value.trim(),
        email: document.getElementById("email").value.trim(),
        department_id: Number(document.getElementById("department_id").value),
    };

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Memperbarui...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.update,
            action: async () => {
                await put(`/api/users/${userId}`, data);

                if (canChangeRole) {
                    await patch(`/api/users/${userId}/role`, {
                        role: document.getElementById("role").value,
                    });
                }

                if (canChangeStatus) {
                    await patch(`/api/users/${userId}/status`, {
                        status: document.getElementById("status").value,
                    });
                }
            },
            successMessage: SUCCESS_MESSAGES.update,
            errorMessage: ERROR_MESSAGES.update,
            onSuccess: () => {
                window.location.href = `/users/${userId}`;
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
