import { createIcons, icons } from "lucide";
import { get } from "../../api/api";
import {
    buildListQueryParams,
    extractPaginatedItems,
    formatDateTime,
    initTableControls,
    renderPagination,
    hideEmptyState,
    renderTableEmptyState,
    resolveEmptyStateContext,
    setLoadingState,
    showDocumentPreparing,
    showToast,
    reportRequestFailure,
} from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

let currentPage = 1;
const filters = {
    search: "",
    module: "",
    action: "",
    period_start: "",
    period_end: "",
};

const searchInput = document.getElementById("search");
const moduleInput = document.getElementById("module");
const actionInput = document.getElementById("action");
const periodStartInput = document.getElementById("period_start");
const periodEndInput = document.getElementById("period_end");
const resetButton = document.getElementById("reset-filter");
const exportButton = document.getElementById("export-excel");

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadActivityLogs();
    },
    perPage: 15,
});

function bindFilter(input, key) {
    input?.addEventListener("change", () => {
        filters[key] = input.value.trim();
        currentPage = 1;
        loadActivityLogs();
    });
}

searchInput?.addEventListener("input", () => {
    clearTimeout(searchInput.dataset.timer);
    searchInput.dataset.timer = setTimeout(() => {
        filters.search = searchInput.value.trim();
        currentPage = 1;
        loadActivityLogs();
    }, 300);
});

bindFilter(moduleInput, "module");
bindFilter(actionInput, "action");
bindFilter(periodStartInput, "period_start");
bindFilter(periodEndInput, "period_end");

resetButton?.addEventListener("click", () => {
    filters.search = "";
    filters.module = "";
    filters.action = "";
    filters.period_start = "";
    filters.period_end = "";
    searchInput.value = "";
    moduleInput.value = "";
    actionInput.value = "";
    periodStartInput.value = "";
    periodEndInput.value = "";
    currentPage = 1;
    loadActivityLogs();
});

exportButton?.addEventListener("click", () => {
    showDocumentPreparing();
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            params.append(key, value);
        }
    });

    window.location.href = `/api/activity-logs/export-excel?${params.toString()}`;
});

async function loadActivityLogs(page = 1) {
    setLoadingState(true);

    try {
        currentPage = page;
        const params = buildListQueryParams(currentPage, tableControls);

        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                params.append(key, value);
            }
        });

        const response = await get(`/api/activity-logs?${params.toString()}`);
        const { items, meta } = extractPaginatedItems(response);

        renderTable(items);
        renderPagination(meta, loadActivityLogs);
        document.getElementById("totalCount").textContent = String(meta?.total ?? items.length);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat log aktivitas.");
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
        renderTableEmptyState(
            emptyState,
            resolveEmptyStateContext({
                search: filters.search,
                filters: {
                    module: filters.module,
                    action: filters.action,
                    period_start: filters.period_start,
                    period_end: filters.period_end,
                },
            }),
        );

        return;
    }

    hideEmptyState(emptyState);

    items.forEach((item, index) => {
        tbody.insertAdjacentHTML("beforeend", rowTemplate(item, index));
    });

    createIcons({ icons, selector: "i[data-lucide]" });
}

function rowTemplate(item, index) {
    return `
        <tr class="hover:bg-ocean-50/50 dark:hover:bg-navy-900/40">
            <td class="px-3 py-3 text-center text-sm">${index + 1}</td>
            <td class="px-5 py-3 text-sm">${formatDateTime(item.logged_at ?? item.created_at)}</td>
            <td class="px-5 py-3 text-sm">${item.user?.name ?? "-"}</td>
            <td class="px-5 py-3 text-sm">${item.module}</td>
            <td class="px-5 py-3 text-sm">${item.action}</td>
            <td class="px-5 py-3 text-sm">${item.description ?? "-"}</td>
            <td class="px-5 py-3 text-center">
                <a href="/activity-logs/${item.id}" class="rounded-md p-2 text-charcoal-500 hover:bg-charcoal-100">
                    <i data-lucide="eye" class="h-4 w-4"></i>
                </a>
            </td>
        </tr>
    `;
}

loadActivityLogs();
