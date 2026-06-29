import { feedbackManager } from "../modules/feedback/feedback-manager";

export const SESSION_EXPIRED_STORAGE_KEY = "app.sessionExpiredMessage";

/** @type {Record<number, string>} */
export const HTTP_ERROR_MESSAGES = {
    401: "Anda belum masuk ke dalam sistem.",
    403: "Anda tidak memiliki hak akses untuk membuka halaman ini.",
    404: "Data yang Anda cari tidak ditemukan.",
    419: "Sesi Anda telah berakhir. Silakan masuk kembali.",
    429: "Terlalu banyak permintaan. Silakan tunggu beberapa saat.",
    500: "Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.",
    503: "Sistem sedang dalam proses pemeliharaan. Silakan kembali beberapa saat lagi.",
};

/**
 * @param {{ status?: number, data?: { message?: string } }} error
 */
export function resolveApiErrorMessage(error) {
    const apiMessage = error?.data?.message;

    if (typeof apiMessage === "string" && apiMessage.trim() !== "") {
        return apiMessage;
    }

    return HTTP_ERROR_MESSAGES[error?.status ?? 0] ?? "Terjadi kesalahan. Silakan coba lagi.";
}

/**
 * Show standardized feedback for HTTP failures and redirect on session expiry.
 *
 * @param {{ status?: number, data?: { message?: string }, __feedbackShown?: boolean }} error
 */
export function dispatchHttpErrorFeedback(error) {
    if (!error || error.status === 422) {
        return false;
    }

    const status = error.status;

    if (status === 401 || status === 419) {
        sessionStorage.setItem(SESSION_EXPIRED_STORAGE_KEY, HTTP_ERROR_MESSAGES[419]);
        window.location.assign("/login");

        error.__feedbackShown = true;

        return true;
    }

    const feedbackType = status === 429 ? "warning" : "error";
    feedbackManager.showResult(feedbackType, resolveApiErrorMessage(error));
    error.__feedbackShown = true;

    return true;
}

async function request(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(token ? { "X-CSRF-TOKEN": token } : {}),
        ...(options.headers ?? {}),
    };

    let body = options.body;

    if (
        body &&
        !(body instanceof FormData) &&
        !(body instanceof URLSearchParams) &&
        typeof body === "object"
    ) {
        headers["Content-Type"] = "application/json";
        body = JSON.stringify(body);
    } else if (
        body &&
        !(body instanceof FormData) &&
        !(body instanceof URLSearchParams)
    ) {
        headers["Content-Type"] = "application/json";
    }

    const response = await fetch(url, {
        credentials: "same-origin",
        headers,
        ...options,
        body,
    });

    const text = await response.text();
    let data = null;

    try {
        data = text ? JSON.parse(text) : null;
    } catch {
        data = { message: null };
    }

    if (!response.ok) {
        const error = { status: response.status, data };
        dispatchHttpErrorFeedback(error);
        throw error;
    }

    return data;
}

export function get(url) {
    return request(url);
}

export function post(url, body) {
    return request(url, {
        method: "POST",
        body,
    });
}

export function put(url, body) {
    return request(url, {
        method: "PUT",
        body,
    });
}

export function patch(url, body = null) {
    return request(url, {
        method: "PATCH",
        body,
    });
}

export function destroy(url) {
    return request(url, {
        method: "DELETE",
    });
}
