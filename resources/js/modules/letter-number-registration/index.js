import { createIcons, icons } from "lucide";
import { get, destroy } from "../../api/api";
import {
    buildListQueryParams,
    confirmAction,
    dsBadgeClass,
    extractPaginatedItems,
    ERROR_MESSAGES,
    initTableControls,
    LOADING_MESSAGES,
    populateSelect,
    renderPagination,
    runAction,
    hideEmptyState,
    renderTableEmptyState,
    resolveEmptyStateContext,
    showDocumentPreparing,
    showToast,
    reportRequestFailure,
    SUCCESS_MESSAGES,
    unwrapApiPayload,
} from "../admin/helper";
import { formatDate, setLoadingState } from "./helper";

createIcons({ icons, selector: "i[data-lucide]" });

const filters = {
    search: "",
    year: "",
    department_id: "",
    letter_type: "",
    status: "",
};

const selectedIds = new Set();
let currentPage = 1;

const searchInput = document.getElementById("search");
const yearSelect = document.getElementById("year");
const departmentSelect = document.getElementById("department");
const letterTypeSelect = document.getElementById("letter_type");
const statusSelect = document.getElementById("status");
const resetButton = document.getElementById("reset-filter");
const printLayoutSelect = document.getElementById("printLayout");
const printBackgroundSelect = document.getElementById("printBackground");
const printButton = document.getElementById("print-selected");
const exportButton = document.getElementById("export-pdf");
const selectAllCheckbox = document.getElementById("select-all");

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadRegistrations();
    },
    perPage: 10,
});

document.addEventListener("DOMContentLoaded", async () => {
    initializeEvents();

    await loadFilters();
    await loadRegistrations();
});

function initializeEvents() {
    let debounce;

    searchInput?.addEventListener("input", () => {
        clearTimeout(debounce);

        debounce = setTimeout(() => {
            filters.search = searchInput.value.trim();
            currentPage = 1;
            loadRegistrations();
        }, 300);
    });

    yearSelect?.addEventListener("change", (event) => {
        filters.year = event.target.value;
        currentPage = 1;
        loadRegistrations();
    });

    departmentSelect?.addEventListener("change", (event) => {
        filters.department_id = event.target.value;
        currentPage = 1;
        loadRegistrations();
    });

    letterTypeSelect?.addEventListener("change", (event) => {
        filters.letter_type = event.target.value;
        currentPage = 1;
        loadRegistrations();
    });

    statusSelect?.addEventListener("change", (event) => {
        filters.status = event.target.value;
        currentPage = 1;
        loadRegistrations();
    });

    resetButton?.addEventListener("click", () => {
        filters.search = "";
        filters.year = "";
        filters.department_id = "";
        filters.letter_type = "";
        filters.status = "";
        currentPage = 1;

        searchInput.value = "";
        yearSelect.value = "";
        departmentSelect.value = "";
        letterTypeSelect.value = "";
        statusSelect.value = "";

        selectedIds.clear();
        selectAllCheckbox.checked = false;

        loadRegistrations();
    });

    printButton?.addEventListener("click", () => {
        openPrintPage();
    });

    exportButton?.addEventListener("click", () => {
        openExportPage();
    });

    selectAllCheckbox?.addEventListener("change", (event) => {
        const checked = event.target.checked;
        document.querySelectorAll("[data-row-checkbox]").forEach((checkbox) => {
            checkbox.checked = checked;
            toggleSelection(Number(checkbox.dataset.id), checked);
        });

        updateSummary();
    });
}

async function loadFilters() {
    try {
        const response = await get("/api/letter-number-registrations/filters");
        const payload = unwrapApiPayload(response);

        populateSelect(yearSelect, [
            { value: "", label: "Semua" },
            ...(payload.years ?? []).map((year) => ({ value: year, label: year })),
        ]);
        populateSelect(departmentSelect, [
            { value: "", label: "Semua" },
            ...(payload.departments ?? []).map((department) => ({
                value: department.id,
                label: `${department.code} - ${department.name}`,
            })),
        ]);
        populateSelect(letterTypeSelect, [
            { value: "", label: "Semua" },
            ...(payload.letter_types ?? []),
        ]);
        populateSelect(statusSelect, [
            { value: "", label: "Semua" },
            ...(payload.statuses ?? []),
        ]);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat filter.");
    }
}

async function loadRegistrations(page = 1) {
    setLoadingState(true);

    try {
        currentPage = page;

        const params = buildListQueryParams(currentPage, tableControls);

        Object.entries(filters).forEach(([key, value]) => {
            if (value !== "") {
                params.append(key, value);
            }
        });

        const response = await get(
            `/api/letter-number-registrations?${params.toString()}`,
        );

        const { items, meta } = extractPaginatedItems(response);

        renderTable(items);
        renderPagination(meta, loadRegistrations);
        updateSummary(meta?.total ?? items.length);

        selectAllCheckbox.checked = false;
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat data registrasi.");
    } finally {
        setLoadingState(false);
    }
}

function renderTable(registrations) {
    const tbody = document.getElementById("registrationTable");
    const emptyState = document.getElementById("emptyState");

    tbody.innerHTML = "";

    if (!registrations.length) {
        renderTableEmptyState(
            emptyState,
            resolveEmptyStateContext({
                search: filters.search,
                filters: {
                    year: filters.year,
                    department_id: filters.department_id,
                    letter_type: filters.letter_type,
                    status: filters.status,
                },
            }),
        );

        return;
    }

    hideEmptyState(emptyState);

    registrations.forEach((registration, index) => {
        tbody.insertAdjacentHTML("beforeend", rowTemplate(registration, index));
    });

    document.querySelectorAll("[data-row-checkbox]").forEach((checkbox) => {
        checkbox.addEventListener("change", (event) => {
            toggleSelection(
                Number(event.target.dataset.id),
                event.target.checked,
            );
            updateSummary();
        });
    });

    document.querySelectorAll("[data-delete-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            const id = Number(button.dataset.deleteId);
            await deleteRegistration(id);
        });
    });

    createIcons({ icons, selector: "i[data-lucide]" });
}

function rowTemplate(registration, index) {
    const canEdit = registration.can?.update;
    const canDelete = registration.can?.delete;

    return `
        <tr class="border-b hover:bg-slate-50">
            <td class="px-4 py-3 text-center">
                <input data-row-checkbox data-id="${registration.id}" type="checkbox" class="rounded border-slate-300">
            </td>
            <td class="px-3 py-3 text-center text-sm text-slate-600">${index + 1}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${registration.letter_number}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${registration.index_code}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${registration.subject}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${registration.recipient}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${registration.department?.name ?? "-"}</td>
            <td class="px-5 py-3 text-center text-sm text-slate-700">${formatDate(registration.letter_date)}</td>
            <td class="px-5 py-3 text-center">
                <span class="${registration.status === "active" ? dsBadgeClass("success") : dsBadgeClass("danger")}">
                    ${registration.status_label ?? registration.status}
                </span>
            </td>
            <td class="px-5 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                    <a href="/letter-number-registrations/${registration.id}" class="rounded-md p-2 text-slate-500 hover:bg-slate-100">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                    </a>
                    ${canEdit ? `<a href="/letter-number-registrations/${registration.id}/edit" class="rounded-md p-2 text-slate-500 hover:bg-slate-100"><i data-lucide="square-pen" class="h-4 w-4"></i></a>` : ""}
                    ${canDelete ? `<button type="button" data-delete-id="${registration.id}" class="rounded-md p-2 text-slate-500 hover:bg-slate-100"><i data-lucide="trash-2" class="h-4 w-4"></i></button>` : ""}
                </div>
            </td>
        </tr>
    `;
}

function toggleSelection(id, checked) {
    if (checked) {
        selectedIds.add(id);
    } else {
        selectedIds.delete(id);
    }
}

function updateSummary(total = 0) {
    document.getElementById("totalCount").textContent = String(total);
    document.getElementById("selectedCount").textContent = String(
        selectedIds.size,
    );
}

async function deleteRegistration(id) {
    const confirmed = await confirmAction({
        title: "Hapus registrasi?",
        message: "Apakah Anda yakin ingin menghapus registrasi ini?",
        confirmText: "Hapus",
        variant: "danger",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.delete,
            action: () => destroy(`/api/letter-number-registrations/${id}`),
            successMessage: SUCCESS_MESSAGES.delete,
            errorMessage: ERROR_MESSAGES.delete,
            onSuccess: () => loadRegistrations(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

function appendPrintOptions(params) {
    if (printLayoutSelect?.value) {
        params.append("layout", printLayoutSelect.value);
    }

    if (printBackgroundSelect?.value) {
        params.append("background", printBackgroundSelect.value);
    }
}

function openPrintPage() {
    showDocumentPreparing();
    const selected = Array.from(selectedIds.values());
    const params = new URLSearchParams();

    if (selected.length) {
        params.append("ids", selected.join(","));
    } else {
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== "") {
                params.append(key, value);
            }
        });
    }

    appendPrintOptions(params);

    window.open(
        `/letter-number-registrations/print?${params.toString()}`,
        "_blank",
    );
}

function openExportPage() {
    showDocumentPreparing();
    const selected = Array.from(selectedIds.values());
    const params = new URLSearchParams();

    if (selected.length) {
        params.append("ids", selected.join(","));
    } else {
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== "") {
                params.append(key, value);
            }
        });
    }

    appendPrintOptions(params);

    window.open(
        `/letter-number-registrations/export-pdf?${params.toString()}`,
        "_blank",
    );
}
