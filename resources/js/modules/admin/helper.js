import { feedbackManager } from "../feedback/feedback-manager";
import { ERROR_MESSAGES, LOADING_MESSAGES, SUCCESS_MESSAGES } from "../feedback/presets";
import { resolveApiErrorMessage } from "../../api/api";
import {
    applyFieldErrors,
    clearFieldErrors,
    handleValidationError,
    resetFormSubmittingState,
    scrollToFirstFieldError,
} from "../form/form-ux";

import {
    populateSelect,
    setSelectLoading,
    setSelectValue,
    syncDropdownSelect,
} from "../form/ds-dropdown";
import {
    buildEmptyStateHtml,
    buildNotificationEmptyStateHtml,
    hideEmptyState,
    initEmptyStateActions,
    refreshEmptyStateIcons,
    renderTableEmptyState,
    resolveEmptyStateContext,
} from "../form/empty-state";
import {
    buildCardSkeletonHtml,
    buildChartSkeletonHtml,
    buildNotificationListSkeletonHtml,
    buildTableSkeletonHtml,
    setLoadingState as setTableLoadingState,
} from "../form/skeleton";

export {
    applyFieldErrors,
    clearFieldErrors,
    handleValidationError,
    populateSelect,
    resetFormSubmittingState,
    scrollToFirstFieldError,
    setSelectLoading,
    setSelectValue,
    syncDropdownSelect,
    buildEmptyStateHtml,
    buildNotificationEmptyStateHtml,
    hideEmptyState,
    initEmptyStateActions,
    refreshEmptyStateIcons,
    renderTableEmptyState,
    resolveEmptyStateContext,
    buildCardSkeletonHtml,
    buildChartSkeletonHtml,
    buildNotificationListSkeletonHtml,
    buildTableSkeletonHtml,
};

const VARIANT_MAP = {
    success: "success",
    danger: "error",
    error: "error",
    warning: "warning",
    info: "info",
};

/**
 * @param {'success' | 'danger' | 'error' | 'warning' | 'info'} variant
 */
export function showToast(variant, message) {
    const type = VARIANT_MAP[variant] ?? "info";
    feedbackManager.showResult(type, message);
}

/**
 * Report AJAX failures without duplicating centralized HTTP feedback popups.
 *
 * @param {{ status?: number, data?: { message?: string }, __feedbackShown?: boolean } | unknown} error
 * @param {string} fallbackMessage
 */
export function reportRequestFailure(error, fallbackMessage) {
    if (error?.__feedbackShown || error?.status === 422) {
        return;
    }

    console.error(error);
    showToast("danger", resolveApiErrorMessage(error) || fallbackMessage);
}

/**
 * @param {{ title?: string, message?: string, confirmText?: string, cancelText?: string, variant?: 'danger' | 'primary', requireInput?: boolean, inputLabel?: string, inputPlaceholder?: string }} options
 */
export function confirmAction(options = {}) {
    return feedbackManager.confirm(options);
}

/**
 * @param {{ loadingMessage?: string, action: () => Promise<unknown>, successMessage?: string, successTitle?: string, errorMessage?: string, errorTitle?: string, onSuccess?: (result: unknown) => void | Promise<void> }} options
 */
export function runAction(options) {
    return feedbackManager.run(options);
}

export function showPageLoading(message = LOADING_MESSAGES.page) {
    feedbackManager.showPageLoading(message);
}

export function showLoading(message = LOADING_MESSAGES.wait) {
    feedbackManager.showLoading(message);
}

export function hideFeedback() {
    feedbackManager.hide();
}

/**
 * Show document preparation popup without page-navigation pending state.
 */
export function showDocumentPreparing() {
    feedbackManager.showLoading(LOADING_MESSAGES.prepareDocument);
}

/**
 * @param {boolean} isLoading
 * @param {string | null} tableBodyId
 * @param {{ rows?: number, columns?: number }} options
 */
export function setLoadingState(isLoading, tableBodyId = null, options = {}) {
    setTableLoadingState(isLoading, tableBodyId, options);
}

/**
 * @param {HTMLButtonElement | null} button
 * @param {boolean} isLoading
 * @param {string} loadingText
 */
export function setButtonLoading(button, isLoading, loadingText = "Memproses...") {
    if (!button) {
        return;
    }

    if (!button.dataset.originalHtml) {
        button.dataset.originalHtml = button.innerHTML;
    }

    button.disabled = isLoading;
    button.setAttribute("aria-busy", isLoading ? "true" : "false");
    button.setAttribute("aria-disabled", isLoading ? "true" : "false");
    button.classList.toggle("opacity-60", isLoading);
    button.classList.toggle("cursor-not-allowed", isLoading);

    if (isLoading) {
        button.innerHTML = `
            <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <span>${loadingText}</span>
        `;
    } else {
        button.innerHTML = button.dataset.originalHtml;
    }

    resetFormSubmittingState(button.closest("form"), isLoading);
}

export function formatDate(value) {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
}

export function formatDateTime(value) {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString("id-ID", {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
}

export function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/** Design system badge class map for dynamic table rows */
export const DS_BADGE = {
    success: "ds-badge ds-badge--success",
    danger: "ds-badge ds-badge--danger",
    warning: "ds-badge ds-badge--warning",
    info: "ds-badge ds-badge--info",
    neutral: "ds-badge ds-badge--neutral",
    gold: "ds-badge ds-badge--gold",
};

/**
 * @param {'success' | 'danger' | 'warning' | 'info' | 'neutral' | 'gold'} variant
 */
export function dsBadgeClass(variant) {
    return DS_BADGE[variant] ?? DS_BADGE.neutral;
}

/**
 * @param {number} currentPage
 * @param {number} lastPage
 * @param {number} [siblings]
 * @returns {Array<number|'ellipsis'>}
 */
export function paginationWindow(currentPage, lastPage, siblings = 2) {
    if (lastPage < 1) {
        return [];
    }

    const current = Math.min(Math.max(currentPage, 1), lastPage);

    if (lastPage <= 7) {
        return Array.from({ length: lastPage }, (_, index) => index + 1);
    }

    const start = Math.max(1, current - siblings);
    const end = Math.min(lastPage, current + siblings);
    const pages = [];

    if (start > 1) {
        pages.push(1);

        if (start > 2) {
            pages.push("ellipsis");
        }
    }

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    if (end < lastPage) {
        if (end < lastPage - 1) {
            pages.push("ellipsis");
        }

        pages.push(lastPage);
    }

    return pages;
}

/**
 * @param {Record<string, unknown>|null|undefined} meta
 * @param {unknown[]} [items]
 * @returns {{ current_page: number, last_page: number, per_page: number, total: unknown }|null}
 */
export function normalizePaginationMeta(meta, items = []) {
    if (!meta || typeof meta !== "object") {
        return null;
    }

    const currentPage = Number(meta.current_page ?? meta.currentPage) || 1;
    const perPage = Number(meta.per_page ?? meta.perPage) || 0;
    const total = Number(meta.total);
    let lastPage = Number(meta.last_page ?? meta.lastPage) || 0;

    if (lastPage < 1 && Number.isFinite(total) && perPage > 0) {
        lastPage = Math.max(1, Math.ceil(total / perPage));
    }

    return {
        ...meta,
        current_page: currentPage,
        last_page: lastPage || 1,
        per_page: perPage,
        total: Number.isFinite(total) ? total : meta.total,
        items_count: items.length,
    };
}

export function renderPagination(meta, onPageChange) {
    const pagination = document.getElementById("pagination");

    if (!pagination) {
        return;
    }

    const normalized = normalizePaginationMeta(meta);
    const currentPage = normalized?.current_page || 1;
    const lastPage = normalized?.last_page || 1;

    if (!normalized || lastPage <= 1) {
        pagination.innerHTML = "";
        pagination.classList.add("hidden");

        return;
    }

    pagination.classList.remove("hidden");

    const pageItems = paginationWindow(currentPage, lastPage)
        .map((page) => {
            if (page === "ellipsis") {
                return `<span class="ds-pagination__ellipsis" aria-hidden="true">&hellip;</span>`;
            }

            const isActive = page === currentPage;

            return `
                <button
                    type="button"
                    data-page="${page}"
                    class="ds-pagination__page ${isActive ? "is-active" : ""}"
                    ${isActive ? 'aria-current="page"' : ""}
                >${page}</button>
            `;
        })
        .join("");

    const prevDisabled = currentPage <= 1;
    const nextDisabled = currentPage >= lastPage;

    pagination.innerHTML = `
        <nav class="ds-pagination" aria-label="Navigasi halaman">
            <button
                type="button"
                data-page="${currentPage - 1}"
                class="ds-pagination__page ${prevDisabled ? "is-disabled" : ""}"
                ${prevDisabled ? "disabled" : ""}
                aria-label="Halaman sebelumnya"
            >&lt;</button>
            ${pageItems}
            <button
                type="button"
                data-page="${currentPage + 1}"
                class="ds-pagination__page ${nextDisabled ? "is-disabled" : ""}"
                ${nextDisabled ? "disabled" : ""}
                aria-label="Halaman berikutnya"
            >&gt;</button>
        </nav>
    `;

    pagination.querySelectorAll("button[data-page]").forEach((button) => {
        button.addEventListener("click", () => {
            if (button.disabled) {
                return;
            }

            onPageChange(Number(button.dataset.page));
        });
    });
}

export function extractPaginatedItems(response) {
    const payload = response?.data;

    if (Array.isArray(payload)) {
        return {
            items: payload,
            meta: normalizePaginationMeta(response?.meta, payload),
        };
    }

    if (payload && Array.isArray(payload.data)) {
        return {
            items: payload.data,
            meta: normalizePaginationMeta(payload.meta ?? response?.meta, payload.data),
        };
    }

    if (payload?.data && Array.isArray(payload.data.data)) {
        return {
            items: payload.data.data,
            meta: normalizePaginationMeta(
                payload.meta ?? payload.data.meta ?? response?.meta,
                payload.data.data,
            ),
        };
    }

    return {
        items: [],
        meta: null,
    };
}

/**
 * Unwrap standard API success envelopes `{ success, message, data }`.
 *
 * @param {Record<string, unknown>|null|undefined} response
 * @returns {Record<string, unknown>|Array<unknown>|null|undefined}
 */
export function unwrapApiPayload(response) {
    if (response?.success === true && response?.data !== undefined) {
        return response.data;
    }

    return response;
}

export const DEFAULT_TABLE_PER_PAGE = 10;
export const DEFAULT_TABLE_ORDER = "latest";

/**
 * @param {{ onChange?: () => void, perPage?: number, order?: string }} options
 */
export function initTableControls(options = {}) {
    const perPageSelect = document.getElementById("table-per-page");
    const orderSelect = document.getElementById("table-order");
    const defaultPerPage = options.perPage ?? DEFAULT_TABLE_PER_PAGE;
    const defaultOrder = options.order ?? DEFAULT_TABLE_ORDER;

    if (perPageSelect) {
        perPageSelect.value = String(defaultPerPage);
        perPageSelect.addEventListener("change", () => {
            options.onChange?.();
        });
    }

    if (orderSelect) {
        orderSelect.value = defaultOrder;
        orderSelect.addEventListener("change", () => {
            options.onChange?.();
        });
    }

    return {
        getPerPage: () => Number(perPageSelect?.value || defaultPerPage),
        getOrder: () => orderSelect?.value || defaultOrder,
    };
}

/**
 * @param {number} page
 * @param {{ getPerPage?: () => number, getOrder?: () => string }} controls
 */
export function buildListQueryParams(page, controls = {}) {
    const params = new URLSearchParams({
        page: String(page),
        per_page: String(controls.getPerPage?.() ?? DEFAULT_TABLE_PER_PAGE),
        order: controls.getOrder?.() ?? DEFAULT_TABLE_ORDER,
    });

    return params;
}

export { ERROR_MESSAGES, LOADING_MESSAGES, SUCCESS_MESSAGES };
