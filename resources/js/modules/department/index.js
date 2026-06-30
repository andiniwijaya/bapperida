import { createIcons, icons } from "lucide";
import { destroy, get, patch } from "../../api/api";
import {
    buildListQueryParams,
    confirmAction,
    dsBadgeClass,
    extractPaginatedItems,
    ERROR_MESSAGES,
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
const filters = { search: "", is_active: "" };

const searchInput = document.getElementById("search");
const isActiveSelect = document.getElementById("is_active");
const resetButton = document.getElementById("reset-filter");

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadDepartments();
    },
    perPage: 15,
});

searchInput?.addEventListener("input", () => {
    clearTimeout(searchInput.dataset.timer);
    searchInput.dataset.timer = setTimeout(() => {
        filters.search = searchInput.value.trim();
        currentPage = 1;
        loadDepartments();
    }, 300);
});

isActiveSelect?.addEventListener("change", (event) => {
    filters.is_active = event.target.value;
    currentPage = 1;
    loadDepartments();
});

resetButton?.addEventListener("click", () => {
    filters.search = "";
    filters.is_active = "";
    searchInput.value = "";
    isActiveSelect.value = "";
    currentPage = 1;
    loadDepartments();
});

async function loadDepartments(page = 1) {
    setLoadingState(true);

    try {
        currentPage = page;
        const params = buildListQueryParams(currentPage, tableControls);

        if (filters.search) {
            params.append("search", filters.search);
        }

        if (filters.is_active !== "") {
            params.append("is_active", filters.is_active);
        }

        const response = await get(`/api/departments?${params.toString()}`);
        const { items, meta } = extractPaginatedItems(response);

        renderTable(items);
        renderPagination(meta, loadDepartments);
        document.getElementById("totalCount").textContent = String(meta?.total ?? items.length);
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    } finally {
        setLoadingState(false);
    }
}

function renderTable(departments) {
    const tbody = document.getElementById("dataTable");
    const emptyState = document.getElementById("emptyState");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = "";

    if (!departments.length) {
        renderTableEmptyState(
            emptyState,
            resolveEmptyStateContext({
                search: filters.search,
                filters: { is_active: filters.is_active },
            }),
        );

        return;
    }

    hideEmptyState(emptyState);

    departments.forEach((department, index) => {
        tbody.insertAdjacentHTML("beforeend", rowTemplate(department, index));
    });

    tbody.querySelectorAll("[data-delete-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            await deleteDepartment(Number(button.dataset.deleteId));
        });
    });

    tbody.querySelectorAll("[data-restore-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            await restoreDepartment(Number(button.dataset.restoreId));
        });
    });

    createIcons({ icons, selector: "i[data-lucide]" });
}

function rowTemplate(department, index) {
    const isDeleted = Boolean(department.deleted_at);
    const statusLabel = isDeleted ? "Dihapus" : department.is_active ? "Aktif" : "Nonaktif";
    const statusClass = isDeleted
        ? dsBadgeClass("danger")
        : department.is_active
          ? dsBadgeClass("success")
          : dsBadgeClass("neutral");

    return `
        <tr>
            <td class="px-3 py-3 text-center text-sm">${index + 1}</td>
            <td class="px-5 py-3 text-sm font-medium">${department.code}</td>
            <td class="px-5 py-3 text-sm">${department.name}</td>
            <td class="px-5 py-3 text-center">
                <span class="${statusClass}">${statusLabel}</span>
            </td>
            <td class="px-5 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                    ${!isDeleted && department.can?.update ? `<a href="/departments/${department.id}/edit" class="rounded-md p-2 text-charcoal-500 hover:bg-charcoal-100"><i data-lucide="square-pen" class="h-4 w-4"></i></a>` : ""}
                    ${!isDeleted && department.can?.delete ? `<button type="button" data-delete-id="${department.id}" class="rounded-md p-2 text-charcoal-500 hover:bg-charcoal-100"><i data-lucide="trash-2" class="h-4 w-4"></i></button>` : ""}
                    ${isDeleted && department.can?.restore ? `<button type="button" data-restore-id="${department.id}" class="rounded-md px-3 py-1 text-xs font-medium text-ocean-800 hover:bg-ocean-50">Pulihkan</button>` : ""}
                </div>
            </td>
        </tr>
    `;
}

async function deleteDepartment(id) {
    const confirmed = await confirmAction({
        title: "Hapus bidang?",
        message: "Apakah Anda yakin ingin menghapus bidang ini?",
        confirmText: "Hapus",
        variant: "danger",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.delete,
            action: () => destroy(`/api/departments/${id}`),
            successMessage: SUCCESS_MESSAGES.delete,
            errorMessage: ERROR_MESSAGES.delete,
            onSuccess: () => loadDepartments(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

async function restoreDepartment(id) {
    const confirmed = await confirmAction({
        title: "Pulihkan bidang?",
        message: "Bidang akan diaktifkan kembali.",
        confirmText: "Pulihkan",
        variant: "primary",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.restore,
            action: () => patch(`/api/departments/${id}/restore`),
            successMessage: SUCCESS_MESSAGES.restore,
            errorMessage: ERROR_MESSAGES.restore,
            onSuccess: () => loadDepartments(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

loadDepartments();
