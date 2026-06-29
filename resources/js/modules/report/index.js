import { createIcons, icons } from "lucide";
import { get } from "../../api/api";
import {
    buildListQueryParams,
    extractPaginatedItems,
    initTableControls,
    populateSelect,
    renderPagination,
    unwrapApiPayload,
    hideEmptyState,
    renderTableEmptyState,
    resolveEmptyStateContext,
    showDocumentPreparing,
    showToast,
    reportRequestFailure,
} from "../admin/helper";
import {
    formatDate,
    setLoadingState,
} from "../incoming-letter/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const filters = {
    report_type: "all",
    search: "",
    year: "",
    department_id: "",
    period_start: "",
    period_end: "",
};

let currentPage = 1;

const reportTypeSelect = document.getElementById("reportType");
const searchInput = document.getElementById("search");
const yearSelect = document.getElementById("year");
const departmentSelect = document.getElementById("department");
const periodStartInput = document.getElementById("periodStart");
const periodEndInput = document.getElementById("periodEnd");
const resetButton = document.getElementById("reset-filter");
const printButton = document.getElementById("print-page");
const exportPdfButton = document.getElementById("export-pdf");
const exportExcelButton = document.getElementById("export-excel");

const tableHead = document.getElementById("reportTableHead");
const tableBody = document.getElementById("reportTableBody");
const totalCount = document.getElementById("totalCount");
const loadingState = document.getElementById("loadingState");
const emptyState = document.getElementById("emptyState");

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadReport();
    },
    perPage: 10,
});

function initializeEvents() {
    let debounce;

    reportTypeSelect?.addEventListener("change", (event) => {
        filters.report_type = event.target.value;
        currentPage = 1;
        loadReport();
    });

    searchInput?.addEventListener("input", () => {
        clearTimeout(debounce);

        debounce = setTimeout(() => {
            filters.search = searchInput.value.trim();
            currentPage = 1;
            loadReport();
        }, 300);
    });

    yearSelect?.addEventListener("change", (event) => {
        filters.year = event.target.value;
        currentPage = 1;
        loadReport();
    });

    departmentSelect?.addEventListener("change", (event) => {
        filters.department_id = event.target.value;
        currentPage = 1;
        loadReport();
    });

    periodStartInput?.addEventListener("change", (event) => {
        filters.period_start = event.target.value;
        currentPage = 1;
        loadReport();
    });

    periodEndInput?.addEventListener("change", (event) => {
        filters.period_end = event.target.value;
        currentPage = 1;
        loadReport();
    });

    resetButton?.addEventListener("click", () => {
        filters.report_type = "all";
        filters.search = "";
        filters.year = "";
        filters.department_id = "";
        filters.period_start = "";
        filters.period_end = "";
        currentPage = 1;

        reportTypeSelect.value = "all";
        searchInput.value = "";
        yearSelect.value = "";
        departmentSelect.value = "";
        periodStartInput.value = "";
        periodEndInput.value = "";

        loadReport();
    });

    printButton?.addEventListener("click", () => {
        showDocumentPreparing();
        window.open(`/reports/print?${buildQueryString()}`, "_blank");
    });

    exportPdfButton?.addEventListener("click", () => {
        showDocumentPreparing();
        window.location.href = `/reports/export-pdf?${buildQueryString()}`;
    });

    exportExcelButton?.addEventListener("click", () => {
        showDocumentPreparing();
        window.location.href = `/api/reports/export-excel?${buildQueryString()}`;
    });
}

async function loadFilters() {
    try {
        const response = await get("/api/reports/filters");
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
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat filter laporan.");
    }
}

async function loadReport(page = 1) {
    setLoadingState(true, "reportTable");

    try {
        currentPage = page;

        const params = buildListQueryParams(currentPage, tableControls);

        Object.entries(filters).forEach(([key, value]) => {
            if (value !== "") {
                params.append(key, value);
            }
        });

        const response = await get(`/api/reports?${params.toString()}`);

        const { items, meta } = extractPaginatedItems(response);

        renderReport(items, filters.report_type || "all");
        renderPagination(meta, loadReport);
        totalCount.textContent = String(meta?.total ?? items.length);
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat laporan.");
    } finally {
        setLoadingState(false, "reportTable");
    }
}

function renderReport(rows, reportType) {
    const columns = getColumnsForReportType(reportType);
    tableHead.innerHTML = `
        <tr>
            ${columns.map((column) => `<th class="app-data-table__th">${column}</th>`).join("")}
        </tr>
    `;

    tableBody.innerHTML = "";

    if (!rows.length) {
        renderTableEmptyState(
            emptyState,
            resolveEmptyStateContext({
                search: filters.search,
                filters: {
                    report_type: filters.report_type,
                    year: filters.year,
                    department_id: filters.department_id,
                    period_start: filters.period_start,
                    period_end: filters.period_end,
                },
            }),
        );

        return;
    }

    hideEmptyState(emptyState);

    rows.forEach((row, index) => {
        tableBody.insertAdjacentHTML(
            "beforeend",
            rowTemplate(row, index, reportType),
        );
    });
}

function rowTemplate(row, index, reportType) {
    const originDestination =
        row.origin_destination ?? row.sender ?? row.recipient ?? "-";
    const cells = [];

    if (reportType === "registration") {
        cells.push(index + 1);
        cells.push(row.letter_number ?? "-");
        cells.push(row.index_code ?? "-");
        cells.push(row.letter_code ?? "-");
        cells.push(row.sequence_number ?? "-");
        cells.push(row.year ?? "-");
        cells.push(row.department ?? "-");
        cells.push(formatDate(row.date));
        cells.push(row.subject ?? "-");
        cells.push(originDestination);
        cells.push(row.letter_type_label ?? "-");
        cells.push(row.status_label ?? "-");
    } else if (reportType === "incoming") {
        cells.push(index + 1);
        cells.push(row.letter_number ?? "-");
        cells.push(formatDate(row.date));
        cells.push(originDestination);
        cells.push(row.department ?? "-");
        cells.push(row.subject ?? "-");
        cells.push(row.agenda_name ?? "-");
        cells.push(row.summary ?? "-");
        cells.push(row.letter_type_label ?? "-");
        cells.push(row.status_label ?? "-");
    } else if (reportType === "outgoing") {
        cells.push(index + 1);
        cells.push(row.letter_number ?? "-");
        cells.push(row.index_code ?? "-");
        cells.push(row.letter_code ?? "-");
        cells.push(row.sequence_number ?? "-");
        cells.push(row.year ?? "-");
        cells.push(row.department ?? "-");
        cells.push(formatDate(row.date));
        cells.push(row.subject ?? "-");
        cells.push(originDestination);
        cells.push(row.letter_type_label ?? "-");
        cells.push(row.status_label ?? "-");
    } else {
        cells.push(index + 1);
        cells.push(row.type_label ?? "-");
        cells.push(row.letter_number ?? "-");
        cells.push(formatDate(row.date));
        cells.push(row.department ?? "-");
        cells.push(originDestination);
        cells.push(row.subject ?? "-");
    }

    return `
        <tr>
            ${cells.map((cell) => `<td>${cell}</td>`).join("")}
        </tr>
    `;
}

function getColumnsForReportType(reportType) {
    return reportType === "registration"
        ? [
              "No",
              "Nomor Surat",
              "Kode Indeks",
              "Kode Surat",
              "Nomor Urut",
              "Tahun",
              "Bidang",
              "Tanggal",
              "Perihal",
              "Asal/Tujuan",
              "Jenis Surat",
              "Status",
          ]
        : reportType === "incoming"
          ? [
                "No",
                "Nomor Surat",
                "Tanggal Masuk",
                "Asal",
                "Bidang",
                "Perihal",
                "Nama Agenda",
                "Ringkasan",
                "Jenis Surat",
                "Status",
            ]
          : reportType === "outgoing"
            ? [
                  "No",
                  "Nomor Surat",
                  "Kode Indeks",
                  "Kode Surat",
                  "Nomor Urut",
                  "Tahun",
                  "Bidang",
                  "Tanggal Surat",
                  "Perihal",
                  "Tujuan",
                  "Jenis Surat",
                  "Status",
              ]
            : [
                  "No",
                  "Jenis Surat",
                  "Nomor Surat",
                  "Tanggal",
                  "Bidang",
                  "Asal/Tujuan",
                  "Perihal",
              ];
}

function buildQueryString() {
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== "") {
            params.append(key, value);
        }
    });

    return params.toString();
}

initializeEvents();
loadFilters();
loadReport();
