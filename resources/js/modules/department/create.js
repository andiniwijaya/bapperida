import { createIcons, icons } from "lucide";
import { post } from "../../api/api";
import {
    ERROR_MESSAGES,
    LOADING_MESSAGES,
    clearFieldErrors,
    handleValidationError,
    runAction,
    setButtonLoading,
    showToast,
    SUCCESS_MESSAGES,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("departmentForm");
const submitButton = form?.querySelector("button[type=submit]");

form?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const data = {
        code: document.getElementById("code").value.trim(),
        name: document.getElementById("name").value.trim(),
    };

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Menambahkan...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.create,
            action: () => post("/api/departments", data),
            successMessage: SUCCESS_MESSAGES.create,
            errorMessage: ERROR_MESSAGES.create,
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
});
