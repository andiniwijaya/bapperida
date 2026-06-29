import { createIcons, icons } from "lucide";
import { destroy, get, patch } from "../../api/api";
import {
    confirmAction,
    ERROR_MESSAGES,
    formatDateTime,
    LOADING_MESSAGES,
    runAction,
    showToast,
    reportRequestFailure,
    SUCCESS_MESSAGES,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const userId = document.getElementById("user_id")?.value;
const userDetail = document.getElementById("userDetail");
const editLink = document.getElementById("editLink");
const resetPasswordBtn = document.getElementById("resetPasswordBtn");
const resendPasswordSetupBtn = document.getElementById("resendPasswordSetupBtn");
const deleteUserBtn = document.getElementById("deleteUserBtn");

resetPasswordBtn?.addEventListener("click", resetPassword);
resendPasswordSetupBtn?.addEventListener("click", resendPasswordSetup);
deleteUserBtn?.addEventListener("click", deleteUser);

async function loadUser() {
    if (!userId || !userDetail) {
        return;
    }

    try {
        const response = await get(`/api/users/${userId}`);
        const user = response.data;

        userDetail.innerHTML = renderDetail(user);

        if (user.can?.update) {
            editLink.href = `/users/${user.id}/edit`;
            editLink.classList.remove("hidden");
            resetPasswordBtn.classList.remove("hidden");
        }

        if (user.can?.resend_password_setup) {
            resendPasswordSetupBtn.classList.remove("hidden");
        } else {
            resendPasswordSetupBtn.classList.add("hidden");
        }
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    }
}

function renderDetail(user) {
    return `
        <div class="app-crud-form-card app-crud-form-card--padded">
            <div class="grid gap-6 lg:grid-cols-2">
                <dl class="grid gap-4">
                    ${field("Nama", user.name)}
                    ${field("Nama Pengguna", user.username)}
                    ${field("Email", user.email)}
                    ${field("Peran", user.role)}
                    ${field("Bidang", user.department?.name ?? "-")}
                    ${field("Status Akun", user.status_label)}
                    ${field("Status Password", user.password_onboarding_status_label)}
                </dl>
                <dl class="grid gap-4">
                    ${field("Login Terakhir", formatDateTime(user.last_login_at))}
                    ${field("Email Terverifikasi", formatDateTime(user.email_verified_at))}
                    ${field("Dibuat", formatDateTime(user.created_at))}
                    ${field("Diperbarui", formatDateTime(user.updated_at))}
                </dl>
            </div>
        </div>
    `;
}

function field(label, value) {
    return `
        <div>
            <dt class="ds-detail-label">${label}</dt>
            <dd class="ds-detail-value">${value}</dd>
        </div>
    `;
}

async function resetPassword() {
    const confirmed = await confirmAction({
        title: "Atur ulang kata sandi?",
        message: "Email atur kata sandi akan dikirim ke pengguna. Kata sandi tidak akan dikirim melalui email.",
        confirmText: "Kirim Email",
        variant: "danger",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.resetPassword,
            action: () => patch(`/api/users/${userId}/reset-password`),
            successMessage: SUCCESS_MESSAGES.resetPassword,
            errorMessage: ERROR_MESSAGES.resetPassword,
            onSuccess: () => loadUser(),
        });
    } catch (error) {
        console.error(error);
    }
}

async function resendPasswordSetup() {
    const confirmed = await confirmAction({
        title: "Kirim ulang email atur kata sandi?",
        message: "Email atur kata sandi akan dikirim ulang ke pengguna.",
        confirmText: "Kirim Ulang",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.resendPasswordSetup,
            action: () => patch(`/api/users/${userId}/resend-password-setup`),
            successMessage: SUCCESS_MESSAGES.resendPasswordSetup,
            errorMessage: ERROR_MESSAGES.resendPasswordSetup,
        });
    } catch (error) {
        console.error(error);
    }
}

async function deleteUser() {
    const confirmed = await confirmAction({
        title: "Hapus pengguna?",
        message: "Apakah Anda yakin ingin menghapus pengguna ini?",
        confirmText: "Hapus",
        variant: "danger",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.delete,
            action: () => destroy(`/api/users/${userId}`),
            successMessage: SUCCESS_MESSAGES.delete,
            errorMessage: ERROR_MESSAGES.delete,
            onSuccess: () => {
                window.location.href = "/users";
            },
        });
    } catch (error) {
        console.error(error);
    }
}

loadUser();
