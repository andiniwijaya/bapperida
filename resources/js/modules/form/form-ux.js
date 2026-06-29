const VALIDATION_RULES = {
    email: {
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        message: "Format alamat email tidak valid.",
    },
    username: {
        pattern: /^[a-zA-Z0-9._-]{3,50}$/,
        message: "Nama pengguna hanya boleh huruf, angka, titik, strip, dan garis bawah (3–50 karakter).",
    },
};

/**
 * @param {HTMLFormElement | null | undefined} form
 */
export function clearFieldErrors(form) {
    if (!form) {
        return;
    }

    form.querySelectorAll(".is-error").forEach((element) => {
        element.classList.remove("is-error");
        element.removeAttribute("aria-invalid");
    });

    form.querySelectorAll(".ds-field-error[data-form-ux-error]").forEach((element) => {
        element.remove();
    });
}

/**
 * @param {HTMLElement | null | undefined} control
 * @param {string} message
 */
export function setFieldError(control, message) {
    if (!control) {
        return;
    }

    const form = control.closest("form");
    const controlId = control.id || control.name;
    control.classList.add("is-error");
    control.setAttribute("aria-invalid", "true");

    let errorElement = form?.querySelector(`#${CSS.escape(controlId)}-error[data-form-ux-error]`);

    if (!errorElement) {
        errorElement = document.createElement("p");
        errorElement.id = `${controlId}-error`;
        errorElement.className = "ds-field-error";
        errorElement.dataset.formUxError = "1";
        errorElement.setAttribute("role", "alert");
        errorElement.setAttribute("aria-live", "polite");

        const wrapper = control.closest(".w-full") ?? control.parentElement;
        wrapper?.appendChild(errorElement);
    }

    errorElement.textContent = message;
}

/**
 * @param {HTMLFormElement | null | undefined} form
 * @param {Record<string, string[] | string>} errors
 */
export function applyFieldErrors(form, errors) {
    if (!form || !errors) {
        return;
    }

    clearFieldErrors(form);

    Object.entries(errors).forEach(([field, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;
        const control =
            form.querySelector(`#${CSS.escape(field)}`) ??
            form.querySelector(`[name="${CSS.escape(field)}"]`) ??
            form.querySelector(`[name="${CSS.escape(field)}[]"]`);

        setFieldError(control, message);
    });
}

/**
 * @param {HTMLFormElement | null | undefined} form
 */
export function scrollToFirstFieldError(form) {
    if (!form) {
        return;
    }

    const firstErrorControl =
        form.querySelector(".is-error, .ds-field-error[data-form-ux-error]") ??
        form.querySelector("[aria-invalid='true']");

    if (!firstErrorControl) {
        return;
    }

    const target = firstErrorControl.classList?.contains("ds-field-error")
        ? firstErrorControl.previousElementSibling ?? firstErrorControl
        : firstErrorControl;

    target.scrollIntoView({ behavior: "smooth", block: "center" });

    if (target instanceof HTMLElement && typeof target.focus === "function") {
        target.focus({ preventScroll: true });
    }
}

/**
 * @param {HTMLFormElement | null | undefined} form
 * @param {{ status?: number, data?: { errors?: Record<string, string[] | string> } }} error
 */
export function handleValidationError(form, error) {
    if (error?.status !== 422 || !error?.data?.errors) {
        return false;
    }

    applyFieldErrors(form, error.data.errors);
    scrollToFirstFieldError(form);

    return true;
}

/**
 * @param {HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement} control
 */
function validateControlOnBlur(control) {
    const ruleName = control.dataset.validate;
    const value = control.value.trim();

    if (!value) {
        return;
    }

    if (ruleName && VALIDATION_RULES[ruleName]) {
        const rule = VALIDATION_RULES[ruleName];

        if (!rule.pattern.test(value)) {
            setFieldError(control, rule.message);

            return;
        }
    }

    if (control.type === "email" && !VALIDATION_RULES.email.pattern.test(value)) {
        setFieldError(control, VALIDATION_RULES.email.message);

        return;
    }

    control.classList.remove("is-error");
    control.setAttribute("aria-invalid", "false");

    const controlId = control.id || control.name;
    const form = control.closest("form");
    form?.querySelector(`#${CSS.escape(controlId)}-error[data-form-ux-error]`)?.remove();
}

/**
 * @param {HTMLFormElement} form
 */
function bindRealtimeValidation(form) {
    form.querySelectorAll("[data-validate], input[type='email']").forEach((control) => {
        control.addEventListener("blur", () => {
            validateControlOnBlur(control);
        });

        control.addEventListener("input", () => {
            if (control.classList.contains("is-error")) {
                validateControlOnBlur(control);
            }
        });
    });
}

/**
 * @param {HTMLFormElement} form
 */
function bindDoubleSubmitGuard(form) {
    form.addEventListener(
        "submit",
        (event) => {
            if (form.dataset.formUxSubmitting === "1") {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        },
        { capture: true },
    );
}

/**
 * @param {HTMLElement} root
 */
function focusFirstField(root) {
    const preferred =
        root.querySelector("[autofocus], [data-autofocus]") ??
        root.querySelector(
            "input.ds-input:not([readonly]):not([disabled]):not([type='hidden']), select.ds-select:not([disabled]), textarea.ds-textarea:not([disabled]), .ds-dropdown__trigger:not(.is-disabled)",
        );

    if (preferred instanceof HTMLElement) {
        window.setTimeout(() => preferred.focus(), 60);
    }
}

/**
 * @param {HTMLFormElement} form
 */
export function initFormUx(form) {
    if (!form || form.dataset.formUxInit === "1") {
        return;
    }

    form.dataset.formUxInit = "1";
    form.classList.add("app-form-ux");

    if (!form.hasAttribute("novalidate")) {
        form.setAttribute("novalidate", "");
    }

    bindDoubleSubmitGuard(form);
    bindRealtimeValidation(form);
    focusFirstField(form);
}

/**
 * @param {ParentNode} root
 */
export function initFormUxScope(root = document) {
    root.querySelectorAll("form[data-form-ux], form.app-form-ux").forEach((form) => {
        initFormUx(form);
    });
}

/**
 * @param {HTMLElement} modalRoot
 */
export function initModalFormUx(modalRoot) {
    if (!modalRoot) {
        return;
    }

    modalRoot.querySelectorAll("form[data-form-ux], form.app-form-ux").forEach((form) => {
        initFormUx(form);
        focusFirstField(form);
    });

    modalRoot.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            modalRoot.dispatchEvent(new CustomEvent("close-modal", { bubbles: true }));
        }
    });
}

/**
 * @param {HTMLButtonElement | null | undefined} button
 * @param {boolean} isLoading
 */
export function resetFormSubmittingState(form, isLoading) {
    if (!form) {
        return;
    }

    form.dataset.formUxSubmitting = isLoading ? "1" : "0";
}
