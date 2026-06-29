import { createIcons, icons } from "lucide";
import { get, patch } from "../../api/api";
import {
    buildListQueryParams,
    confirmAction,
    dsBadgeClass,
    extractPaginatedItems,
    ERROR_MESSAGES,
    formatDateTime,
    initTableControls,
    LOADING_MESSAGES,
    renderPagination,
    hideEmptyState,
    renderTableEmptyState,
    resolveEmptyStateContext,
    runAction,
    setLoadingState,
    showToast,
    reportRequestFailure,
    SUCCESS_MESSAGES,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

let currentPage = 1;

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadRequests();
    },
    perPage: 15,
});

async function loadRequests(page = 1) {
    setLoadingState(true);

    try {
        currentPage = page;
        const params = buildListQueryParams(currentPage, tableControls);
        const response = await get(`/api/registration-requests?${params.toString()}`);
        const { items, meta } = extractPaginatedItems(response);

        renderTable(items);
        renderPagination(meta, loadRequests);
        document.getElementById("totalCount").textContent = String(meta?.total ?? items.length);
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    } finally {
        setLoadingState(false);
    }
}

function renderTable(items) {
    const tbody = document.getElementById("dataTable");
    const emptyState = document.getElementById("emptyState");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = "";

    if (!items.length) {
        renderTableEmptyState(emptyState, "table");

        return;
    }

    hideEmptyState(emptyState);

    items.forEach((item, index) => {
        tbody.insertAdjacentHTML("beforeend", rowTemplate(item, index));
    });

    tbody.querySelectorAll("[data-approve-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            await approveRequest(Number(button.dataset.approveId));
        });
    });

    tbody.querySelectorAll("[data-reject-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            await rejectRequest(Number(button.dataset.rejectId));
        });
    });

    createIcons({ icons, selector: "i[data-lucide]" });
}

function rowTemplate(item, index) {
    const statusClass =
        item.status === "approved"
            ? dsBadgeClass("success")
            : item.status === "rejected"
              ? dsBadgeClass("danger")
              : dsBadgeClass("warning");

    const statusLabels = {
        pending: "Menunggu",
        approved: "Disetujui",
        rejected: "Ditolak",
    };

    return `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td>${item.user?.name ?? "-"}</td>
            <td>${item.user?.email ?? "-"}</td>
            <td>${item.user?.username ?? "-"}</td>
            <td class="text-center">
                <span class="${statusClass}">${statusLabels[item.status] ?? item.status}</span>
            </td>
            <td>${formatDateTime(item.created_at)}</td>
            <td class="text-center">
                <div class="flex items-center justify-center gap-2">
                    ${item.can?.approve ? `<button type="button" data-approve-id="${item.id}" class="rounded-md bg-ocean-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-ocean-900">Setujui</button>` : ""}
                    ${item.can?.reject ? `<button type="button" data-reject-id="${item.id}" class="rounded-md bg-maroon-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-maroon-700">Tolak</button>` : ""}
                </div>
            </td>
        </tr>
    `;
}

async function approveRequest(id) {
    const confirmed = await confirmAction({
        title: "Setujui registrasi?",
        message: "Apakah Anda yakin ingin menyetujui registrasi ini?",
        confirmText: "Setujui",
        variant: "primary",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.approve,
            action: () => patch(`/api/registration-requests/${id}/approve`),
            successMessage: SUCCESS_MESSAGES.approve,
            errorMessage: ERROR_MESSAGES.approve,
            onSuccess: () => loadRequests(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

async function rejectRequest(id) {
    const reason = await confirmAction({
        title: "Tolak registrasi?",
        message: "Registrasi akan ditolak dan tidak dapat masuk ke sistem.",
        confirmText: "Tolak",
        variant: "danger",
        requireInput: true,
        inputLabel: "Alasan penolakan",
        inputPlaceholder: "Masukkan alasan penolakan",
    });

    if (!reason || typeof reason !== "string") {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.reject,
            action: () =>
                patch(`/api/registration-requests/${id}/reject`, {
                    rejection_reason: reason,
                }),
            successMessage: SUCCESS_MESSAGES.reject,
            errorMessage: ERROR_MESSAGES.reject,
            onSuccess: () => loadRequests(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

loadRequests();
