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
    letter_attribute: "",
    status: "",
};

const selectedIds = new Set();
let currentPage = 1;

const searchInput = document.getElementById("search");
const yearSelect = document.getElementById("year");
const departmentSelect = document.getElementById("department");
const letterAttributeSelect = document.getElementById("letter_attribute");
const statusSelect = document.getElementById("status");
const resetButton = document.getElementById("reset-filter");
const printButton = document.getElementById("print-selected");
const exportPdfButton = document.getElementById("export-pdf");
const exportExcelButton = document.getElementById("export-excel");
const selectAllCheckbox = document.getElementById("select-all");

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadIncomingLetters();
    },
    perPage: 10,
});

function initializeEvents() {
    let debounce;

    searchInput?.addEventListener("input", () => {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            filters.search = searchInput.value.trim();
            currentPage = 1;
            loadIncomingLetters();
        }, 300);
    });

    yearSelect?.addEventListener("change", (event) => {
        filters.year = event.target.value;
        currentPage = 1;
        loadIncomingLetters();
    });

    departmentSelect?.addEventListener("change", (event) => {
        filters.department_id = event.target.value;
        currentPage = 1;
        loadIncomingLetters();
    });

    letterAttributeSelect?.addEventListener("change", (event) => {
        filters.letter_attribute = event.target.value;
        currentPage = 1;
        loadIncomingLetters();
    });

    statusSelect?.addEventListener("change", (event) => {
        filters.status = event.target.value;
        currentPage = 1;
        loadIncomingLetters();
    });

    resetButton?.addEventListener("click", () => {
        filters.search = "";
        filters.year = "";
        filters.department_id = "";
        filters.letter_attribute = "";
        filters.status = "";
        currentPage = 1;
        searchInput.value = "";
        yearSelect.value = "";
        departmentSelect.value = "";
        letterAttributeSelect.value = "";
        statusSelect.value = "";
        selectedIds.clear();
        selectAllCheckbox.checked = false;
        loadIncomingLetters();
    });

    printButton?.addEventListener("click", () => {
        openPrintPage();
    });

    exportPdfButton?.addEventListener("click", () => {
        openExportPdfPage();
    });

    exportExcelButton?.addEventListener("click", () => {
        openExportExcelPage();
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

async function loadIncomingLetters(page = 1) {
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
            `/api/incoming-letters?${params.toString()}`,
        );

        const { items, meta } = extractPaginatedItems(response);

        renderTable(items);
        renderPagination(meta, loadIncomingLetters);
        updateSummary(meta?.total ?? items.length);
        selectAllCheckbox.checked = false;
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat arsip surat masuk.");
    } finally {
        setLoadingState(false);
    }
}

function renderTable(incomingLetters) {
    const tbody = document.getElementById("incomingLetterTable");
    const emptyState = document.getElementById("emptyState");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = "";

    if (!incomingLetters.length) {
        renderTableEmptyState(
            emptyState,
            resolveEmptyStateContext({
                search: filters.search,
                filters: {
                    year: filters.year,
                    department_id: filters.department_id,
                    letter_attribute: filters.letter_attribute,
                    status: filters.status,
                },
            }),
        );

        return;
    }

    hideEmptyState(emptyState);

    incomingLetters.forEach((letter, index) => {
        tbody.insertAdjacentHTML("beforeend", rowTemplate(letter, index));
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
            await deleteIncomingLetter(Number(button.dataset.deleteId));
        });
    });

    createIcons({ icons, selector: "i[data-lucide]" });
}

function rowTemplate(letter, index) {
    const canEdit = letter.can?.update;
    const canDelete = letter.can?.delete;

    return `
        <tr class="border-b hover:bg-slate-50">
            <td class="px-4 py-3 text-center">
                <input data-row-checkbox data-id="${letter.id}" type="checkbox" class="rounded border-slate-300">
            </td>
            <td class="px-3 py-3 text-center text-sm text-slate-600">${index + 1}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.letter_number}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${formatDate(letter.sent_date)}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${formatDate(letter.received_date)}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${formatDate(letter.disposition_date)}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.sender}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.department?.name ?? "-"}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.disposition_department?.name ?? "-"}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.subject}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.agenda_name ?? "-"}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.summary ?? "-"}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.letter_attribute_label}</td>
            <td class="px-5 py-3 text-sm text-slate-700">${letter.attachment ?? "-"}</td>
            <td class="px-5 py-3 text-center">
                <span class="${letter.status === "active" ? dsBadgeClass("success") : dsBadgeClass("danger")}">
                    ${letter.status_label}
                </span>
            </td>
            <td class="px-5 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                    <a href="/incoming-letters/${letter.id}" class="rounded-md p-2 text-slate-500 hover:bg-slate-100"><i data-lucide="eye" class="h-4 w-4"></i></a>
                    ${canEdit ? `<a href="/incoming-letters/${letter.id}/edit" class="rounded-md p-2 text-slate-500 hover:bg-slate-100"><i data-lucide="square-pen" class="h-4 w-4"></i></a>` : ""}
                    ${canDelete ? `<button type="button" data-delete-id="${letter.id}" class="rounded-md p-2 text-slate-500 hover:bg-slate-100"><i data-lucide="trash-2" class="h-4 w-4"></i></button>` : ""}
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

async function deleteIncomingLetter(id) {
    const confirmed = await confirmAction({
        title: "Hapus arsip?",
        message: "Apakah Anda yakin ingin menghapus arsip surat masuk ini?",
        confirmText: "Hapus",
        variant: "danger",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.delete,
            action: () => destroy(`/api/incoming-letters/${id}`),
            successMessage: SUCCESS_MESSAGES.delete,
            errorMessage: ERROR_MESSAGES.delete,
            onSuccess: () => loadIncomingLetters(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

function buildReportQueryString() {
    const params = new URLSearchParams();

    if (selectedIds.size) {
        params.append("ids", Array.from(selectedIds).join(","));
    }

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== "") {
            params.append(key, value);
        }
    });

    return params.toString();
}

function openPrintPage() {
    showDocumentPreparing();
    window.open(
        `/incoming-letters/print?${buildReportQueryString()}`,
        "_blank",
    );
}

function openExportPdfPage() {
    showDocumentPreparing();
    window.location.href = `/incoming-letters/export-pdf?${buildReportQueryString()}`;
}

function openExportExcelPage() {
    showDocumentPreparing();
    window.location.href = `/api/incoming-letters/export-excel?${buildReportQueryString()}`;
}

async function loadFilters() {
    try {
        const response = await get("/api/incoming-letters/filters");
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
        populateSelect(letterAttributeSelect, [
            { value: "", label: "Semua" },
            ...(payload.letter_attributes ?? []),
        ]);
        populateSelect(statusSelect, [
            { value: "", label: "Semua" },
            ...(payload.statuses ?? []),
        ]);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat filter.");
    }
}

function initialize() {
    initializeEvents();
    loadFilters();
    loadIncomingLetters();
}

document.addEventListener("DOMContentLoaded", initialize);
