import { Chart } from "chart.js/auto";
import { get } from "../../api/api";
import { hideFeedback, buildEmptyStateHtml, refreshEmptyStateIcons, buildCardSkeletonHtml, buildChartSkeletonHtml } from "../admin/helper";

const dashboardRoot = document.getElementById("dashboardPage");
const widgetsContainer = document.getElementById("dashboardWidgets");
const currentDateElement = document.getElementById("dashboardCurrentDate");
const currentDateMobileElement = document.getElementById("dashboardCurrentDateMobile");
const chartsPrimary = document.getElementById("dashboardChartsPrimary");
const chartsSecondary = document.getElementById("dashboardChartsSecondary");
const tablesContainer = document.getElementById("dashboardTables");
const notificationBanner = document.getElementById("dashboardNotificationBanner");
const lastUpdatedElement = document.getElementById("dashboardLastUpdated");
const resetButton = document.getElementById("dashboardResetButton");
const departmentFilter = document.getElementById("dashboardDepartmentFilter");
const periodStartInput = document.getElementById("dashboardPeriodStart");
const periodEndInput = document.getElementById("dashboardPeriodEnd");
const granularityFilter = document.getElementById("dashboardGranularityFilter");
const errorAlert = document.getElementById("dashboardError");

const chartRegistry = new Map();

const COLORS = {
    ocean: "#0f3550",
    oceanMid: "#1f5f85",
    gold: "#c9a227",
    maroon: "#7b3f52",
    oceanFill: "rgba(15, 53, 80, 0.12)",
    goldFill: "rgba(201, 162, 39, 0.15)",
    maroonFill: "rgba(123, 63, 82, 0.15)",
};

const widgetLabels = {
    superadmin: {
        total_users: "Total User",
        total_departments: "Total Department",
        total_registration: "Registrasi Penomoran",
        total_incoming: "Surat Masuk",
        total_outgoing: "Surat Keluar",
        pending_approval_users: "Pending Approval",
        notifications_unread: "Notifikasi Belum Dibaca",
    },
    admin: {
        registration: "Registrasi Penomoran",
        incoming: "Surat Masuk",
        outgoing: "Surat Keluar",
        letters_today: "Surat Hari Ini",
        letters_this_week: "Surat Minggu Ini",
        letters_this_month: "Surat Bulan Ini",
        notifications_unread: "Notifikasi Belum Dibaca",
    },
    staff: {
        my_registration: "Registrasi Saya",
        my_incoming: "Surat Masuk Saya",
        my_outgoing: "Surat Keluar Saya",
        notifications_unread: "Notifikasi Saya",
    },
};

const widgetIcons = {
    total_users:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />',
    total_departments:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
    total_registration:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
    registration:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
    my_registration:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
    total_incoming:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />',
    incoming:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />',
    my_incoming:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />',
    total_outgoing:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />',
    outgoing:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />',
    my_outgoing:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />',
    pending_approval_users:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
    letters_today:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    letters_this_week:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    letters_this_month:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    notifications_unread:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
    storage_used_mb:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />',
};

const adminWidgetLinks = {
    registration: "/letter-number-registrations",
    incoming: "/incoming-letters",
    outgoing: "/outgoing-letters",
    letters_today: "/incoming-letters",
    letters_this_week: "/incoming-letters",
    letters_this_month: "/incoming-letters",
};

const staffWidgetLinks = {
    my_registration: "/letter-number-registrations",
    my_incoming: "/incoming-letters",
    my_outgoing: "/outgoing-letters",
};

const granularityLabels = {
    day: "per hari",
    week: "per minggu",
    month: "per bulan",
    year: "per tahun",
};

const superadminWidgetLinks = {
    total_users: "/users",
    total_departments: "/departments",
    total_registration: "/letter-number-registrations",
    total_incoming: "/incoming-letters",
    total_outgoing: "/outgoing-letters",
    pending_approval_users: "/registration-requests",
};

const widgetThemes = {
    total_users: {
        card: "bg-gradient-to-br from-ocean-800 to-ocean-900 text-white border-ocean-700 hover:from-ocean-700 hover:to-ocean-800",
        icon: "bg-white/15 text-white",
    },
    total_departments: {
        card: "bg-gradient-to-br from-gold-500 to-gold-600 text-ocean-900 border-gold-400 hover:from-gold-400 hover:to-gold-500",
        icon: "bg-ocean-900/10 text-ocean-900",
    },
    total_registration: {
        card: "bg-gradient-to-br from-ocean-600 to-ocean-700 text-white border-ocean-500 hover:from-ocean-500 hover:to-ocean-600",
        icon: "bg-white/15 text-white",
    },
    registration: {
        card: "bg-gradient-to-br from-ocean-600 to-ocean-700 text-white border-ocean-500 hover:from-ocean-500 hover:to-ocean-600",
        icon: "bg-white/15 text-white",
    },
    my_registration: {
        card: "bg-gradient-to-br from-ocean-600 to-ocean-700 text-white border-ocean-500 hover:from-ocean-500 hover:to-ocean-600",
        icon: "bg-white/15 text-white",
    },
    total_incoming: {
        card: "bg-gradient-to-br from-maroon-600 to-maroon-700 text-white border-maroon-500 hover:from-maroon-500 hover:to-maroon-600",
        icon: "bg-white/15 text-white",
    },
    incoming: {
        card: "bg-gradient-to-br from-maroon-600 to-maroon-700 text-white border-maroon-500 hover:from-maroon-500 hover:to-maroon-600",
        icon: "bg-white/15 text-white",
    },
    my_incoming: {
        card: "bg-gradient-to-br from-maroon-600 to-maroon-700 text-white border-maroon-500 hover:from-maroon-500 hover:to-maroon-600",
        icon: "bg-white/15 text-white",
    },
    total_outgoing: {
        card: "bg-gradient-to-br from-ocean-500 to-ocean-600 text-white border-ocean-400 hover:from-ocean-400 hover:to-ocean-500",
        icon: "bg-white/15 text-white",
    },
    outgoing: {
        card: "bg-gradient-to-br from-ocean-500 to-ocean-600 text-white border-ocean-400 hover:from-ocean-400 hover:to-ocean-500",
        icon: "bg-white/15 text-white",
    },
    my_outgoing: {
        card: "bg-gradient-to-br from-ocean-500 to-ocean-600 text-white border-ocean-400 hover:from-ocean-400 hover:to-ocean-500",
        icon: "bg-white/15 text-white",
    },
    pending_approval_users: {
        card: "bg-gradient-to-br from-maroon-500 to-gold-500 text-white border-maroon-400 hover:from-maroon-400 hover:to-gold-400",
        icon: "bg-white/15 text-white",
    },
    letters_today: {
        card: "bg-gradient-to-br from-gold-500 to-gold-600 text-ocean-900 border-gold-400 hover:from-gold-400 hover:to-gold-500",
        icon: "bg-ocean-900/10 text-ocean-900",
    },
    letters_this_week: {
        card: "bg-gradient-to-br from-ocean-700 to-ocean-800 text-white border-ocean-600 hover:from-ocean-600 hover:to-ocean-700",
        icon: "bg-white/15 text-white",
    },
    letters_this_month: {
        card: "bg-gradient-to-br from-maroon-700 to-maroon-800 text-white border-maroon-600 hover:from-maroon-600 hover:to-maroon-700",
        icon: "bg-white/15 text-white",
    },
    notifications_unread: {
        card: "bg-gradient-to-br from-charcoal-700 to-charcoal-800 text-white border-charcoal-600 hover:from-charcoal-600 hover:to-charcoal-700",
        icon: "bg-white/15 text-white",
    },
    storage_used_mb: {
        card: "bg-gradient-to-br from-charcoal-600 to-charcoal-700 text-white border-charcoal-500 hover:from-charcoal-500 hover:to-charcoal-600",
        icon: "bg-white/15 text-white",
    },
};

const widgetLinksByRole = {
    superadmin: superadminWidgetLinks,
    admin: adminWidgetLinks,
    staff: staffWidgetLinks,
};

async function initializeDashboard() {
    if (!dashboardRoot) {
        return;
    }

    attachEvents();

    await loadDashboard();
}

function attachEvents() {
    if (resetButton) {
        resetButton.addEventListener("click", () => {
            if (departmentFilter) {
                departmentFilter.value = "";
            }
            if (periodStartInput) {
                periodStartInput.value = "";
            }
            if (periodEndInput) {
                periodEndInput.value = "";
            }
            if (granularityFilter) {
                granularityFilter.value = "month";
            }
            loadDashboard();
        });
    }

    if (granularityFilter) {
        granularityFilter.addEventListener("change", loadDashboard);
    }

    if (departmentFilter) {
        departmentFilter.addEventListener("change", loadDashboard);
    }

    if (periodStartInput) {
        periodStartInput.addEventListener("change", loadDashboard);
    }

    if (periodEndInput) {
        periodEndInput.addEventListener("change", loadDashboard);
    }
}

async function loadDashboard() {
    const navigationPending = sessionStorage.getItem("app.feedback.navPending") === "1";

    if (!navigationPending) {
        renderDashboardSkeleton();
    }

    hideError();
    destroyCharts();

    try {
        const response = await get(buildDashboardUrl());
        const data = response.data;

        syncGranularityFilter(data.granularity);
        renderNotificationBanner(data.notifications_unread_count ?? 0);
        renderDateBar();
        renderWidgets(data.role, data.widgets ?? {}, data.storage ?? null, data.notifications_unread_count ?? 0);
        renderCharts(data);
        renderTables(data);
        mountActivityLogChart(data);
        attachChartExportHandlers();
        updateLastUpdated(data.last_updated_at);
    } catch (error) {
        showError("Gagal memuat data dashboard. Silakan coba lagi.");
        // eslint-disable-next-line no-console
        console.error(error);
    } finally {
        widgetsContainer?.setAttribute("aria-busy", "false");
        chartsPrimary?.setAttribute("aria-busy", "false");
        chartsSecondary?.setAttribute("aria-busy", "false");
        tablesContainer?.setAttribute("aria-busy", "false");
        hideFeedback();
    }
}

function renderDashboardSkeleton() {
    if (widgetsContainer) {
        widgetsContainer.innerHTML = buildCardSkeletonHtml(4);
        widgetsContainer.setAttribute("aria-busy", "true");
    }

    if (chartsPrimary) {
        chartsPrimary.innerHTML = buildChartSkeletonHtml(2);
        chartsPrimary.setAttribute("aria-busy", "true");
    }

    if (chartsSecondary) {
        chartsSecondary.innerHTML = "";
        chartsSecondary.classList.add("hidden");
        chartsSecondary.setAttribute("aria-busy", "true");
    }

    if (tablesContainer) {
        tablesContainer.innerHTML = "";
        tablesContainer.setAttribute("aria-busy", "true");
    }
}

function renderNotificationBanner(count) {
    if (!notificationBanner) {
        return;
    }

    if (!count || count <= 0) {
        notificationBanner.classList.add("hidden");
        notificationBanner.innerHTML = "";

        return;
    }

    notificationBanner.classList.remove("hidden");
    notificationBanner.innerHTML = `
        <div class="flex flex-wrap items-center justify-between gap-3">
            <span>
                Anda memiliki <strong>${Number(count).toLocaleString()}</strong> notifikasi yang belum dibaca.
            </span>
            <span class="ds-badge ds-badge--gold">
                ${Number(count).toLocaleString()} baru
            </span>
        </div>
    `;
}

function renderDateBar() {
    const formattedDate = new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });

    if (currentDateElement) {
        currentDateElement.textContent = formattedDate;
    }

    if (currentDateMobileElement) {
        currentDateMobileElement.textContent = formattedDate;
    }
}

function renderWidgets(role, widgets, storage, notificationCount) {
    if (!widgetsContainer) {
        return;
    }

    const labels = { ...(widgetLabels[role] ?? {}) };
    const entries = Object.entries(widgets);

    if (notificationCount > 0) {
        entries.unshift(["notifications_unread", notificationCount]);
    }

    if (storage?.used_mb) {
        entries.push(["storage_used_mb", storage.used_mb]);
        labels.storage_used_mb = storage.label ?? "Penyimpanan";
    }

    if (!entries.length) {
        widgetsContainer.innerHTML = buildEmptyStateHtml("dashboard", { compact: true, className: "col-span-full" });
        refreshEmptyStateIcons(widgetsContainer);
        return;
    }

    const links = widgetLinksByRole[role] ?? {};

    widgetsContainer.innerHTML = entries
        .map(([key, value]) => themedStatCard(key, value, labels[key] ?? key, links[key]))
        .join("");
}

function themedStatCard(key, value, label, href) {
    const theme = widgetThemes[key] ?? widgetThemes.total_users;
    const display =
        key === "storage_used_mb"
            ? `${Number(value).toLocaleString()} MB`
            : Number(value ?? 0).toLocaleString();
    const iconPath = widgetIcons[key] ?? widgetIcons.registration;
    const tag = href ? "a" : "div";
    const hrefAttr = href ? `href="${href}"` : "";
    const interactiveClass = href
        ? "cursor-pointer hover:-translate-y-1 hover:shadow-lg active:scale-[0.98] transition-transform duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2"
        : "";

    return `
        <${tag} ${hrefAttr} class="block rounded-xl border p-5 shadow-sm transition duration-200 ${theme.card} ${interactiveClass}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium opacity-90">${label}</p>
                    <p class="mt-2 text-3xl font-bold">${display}</p>
                    ${href ? `<p class="mt-2 text-xs font-medium opacity-80">Klik untuk buka menu</p>` : ""}
                </div>
                <div class="rounded-lg p-2.5 ${theme.icon}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        ${iconPath}
                    </svg>
                </div>
            </div>
        </${tag}>
    `;
}

function renderCharts(data) {
    if (!chartsPrimary || !chartsSecondary) {
        return;
    }

    chartsPrimary.innerHTML = "";
    chartsSecondary.innerHTML = "";

    const charts = normalizeLetterCharts(data.charts ?? {}, data.role);
    const periodLabel = granularityLabel(data.granularity);

    chartsPrimary.innerHTML = `
        ${buildChartCard(
            "dashboardRegistrationChart",
            "Registrasi Penomoran",
            `Tren registrasi penomoran surat ${periodLabel}.`,
        )}
        ${buildChartCard(
            "dashboardIncomingOutgoingChart",
            "Surat Masuk vs Surat Keluar",
            `Perbandingan arsip surat masuk dan surat keluar ${periodLabel}.`,
        )}
    `;

    if (charts.registration_monthly && hasSeriesData(charts.registration_monthly.data)) {
        renderBarChart(
            "dashboardRegistrationChart",
            charts.registration_monthly.labels,
            charts.registration_monthly.data,
            "Registrasi",
            COLORS.ocean,
        );
    } else {
        showChartEmptyState("dashboardRegistrationChart");
    }

    if (
        hasSeriesData(charts.incoming_monthly?.data) ||
        hasSeriesData(charts.outgoing_monthly?.data)
    ) {
        renderIncomingOutgoingChart(charts, data.monthly_trends);
    } else {
        showChartEmptyState("dashboardIncomingOutgoingChart");
    }

    updateChartsSecondaryVisibility();
}

function normalizeLetterCharts(charts, role) {
    if (role === "staff" && charts.my_letters_monthly) {
        const monthly = charts.my_letters_monthly;

        return {
            ...charts,
            registration_monthly: {
                labels: monthly.labels,
                data: monthly.registration,
            },
            incoming_monthly: {
                labels: monthly.labels,
                data: monthly.incoming,
            },
            outgoing_monthly: {
                labels: monthly.labels,
                data: monthly.outgoing,
            },
        };
    }

    return charts;
}

function renderTables(data) {
    if (!tablesContainer) {
        return;
    }

    const tables = data.tables ?? {};
    const role = data.role;

    const recentHtml = buildTableSection(
        "Aktivitas Terbaru",
        "Entri surat terbaru sesuai cakupan dashboard.",
        ["Jenis", "Nomor", "Bidang", "Perihal", "Tanggal"],
        (tables.recent_items ?? []).map((item) => [
            item.type_label,
            item.letter_number ?? "-",
            item.department ?? "-",
            item.subject ?? "-",
            formatDate(item.date),
        ]),
        5,
        "Belum ada aktivitas surat terbaru.",
    );

    const logsHtml = buildActivityLogSection(data);

    let topDeptHtml = "";

    if (role !== "staff" && tables.top_departments) {
        topDeptHtml = buildTableSection(
            "Top Bidang",
            "Bidang dengan aktivitas surat tertinggi.",
            ["#", "Bidang", "Jumlah"],
            tables.top_departments.map((dept, index) => [
                String(index + 1),
                dept.name,
                dept.count.toLocaleString(),
            ]),
            3,
            "Belum ada data bidang.",
        );
    }

    tablesContainer.innerHTML = `
        <div class="space-y-4">${recentHtml}</div>
        <div class="space-y-4">${logsHtml}${topDeptHtml}</div>
    `;
    refreshEmptyStateIcons(tablesContainer);
}

function buildActivityLogSection(data) {
    const role = data.role;
    const charts = data.charts ?? {};
    const tables = data.tables ?? {};
    const periodLabel = granularityLabel(data.granularity);
    const title = role === "staff" ? "Aktivitas Saya" : "Log Aktivitas";
    const description =
        role === "staff"
            ? `Grafik dan riwayat aktivitas Anda di sistem ${periodLabel}.`
            : `Grafik dan riwayat aktivitas pengguna ${periodLabel}.`;

    const activityTrend =
        role === "staff"
            ? charts.my_activity
            : charts.system_activity;
    const showChart = activityTrend && hasSeriesData(activityTrend.counts);
    const canvasId = "dashboardActivityLogChart";

    const rows = (tables.activity_logs ?? []).map((log) => [
        log.action,
        log.module,
        log.description,
        formatDateTime(log.logged_at),
    ]);

    const tableBody = rows.length
        ? rows
              .map(
                  (cells) => `
            <tr class="border-b border-charcoal-100 last:border-none transition hover:bg-ocean-50/50 dark:border-navy-700 dark:hover:bg-navy-900/40">
                ${cells
                    .map(
                        (cell) =>
                            `<td class="px-4 py-3 text-sm text-charcoal-700 dark:text-slate-300">${escapeHtml(String(cell))}</td>`,
                    )
                    .join("")}
            </tr>
        `,
              )
              .join("")
        : `<tr><td class="p-6 text-center text-sm text-charcoal-500 dark:text-slate-400" colspan="4">Belum ada log aktivitas.</td></tr>`;

    const chartBlock = showChart
        ? `
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs font-medium text-charcoal-500 dark:text-slate-400">Tren aktivitas</p>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-medium text-charcoal-500 dark:text-slate-400" for="export-format-${canvasId}">Format</label>
                    <select
                        id="export-format-${canvasId}"
                        data-chart-export-format="${canvasId}"
                        class="rounded-lg border border-charcoal-200 bg-white px-2 py-1.5 text-xs text-charcoal-800 dark:border-navy-600 dark:bg-navy-900 dark:text-slate-100"
                    >
                        <option value="png">PNG</option>
                        <option value="jpeg">JPG / JPEG</option>
                    </select>
                    <button
                        type="button"
                        data-chart-export="${canvasId}"
                        class="rounded-lg bg-ocean-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-ocean-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-400"
                    >
                        Unduh
                    </button>
                </div>
            </div>
            <div class="mt-2 h-52 relative" data-chart-wrapper="${canvasId}">
                <canvas id="${canvasId}" role="img" aria-label="${title}"></canvas>
            </div>
        `
        : "";

    return `
        <div class="app-panel">
            <div class="app-panel__header">
                <h2 class="app-panel__title">${title}</h2>
                <p class="app-panel__description">${description}</p>
            </div>
            <div class="app-panel__body">
            ${chartBlock}
            <div class="${showChart ? "mt-4" : ""} overflow-x-auto rounded-xl border border-charcoal-100 dark:border-navy-700">
                <table class="app-data-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="app-data-table__th">Aksi</th>
                            <th class="app-data-table__th">Modul</th>
                            <th class="app-data-table__th">Deskripsi</th>
                            <th class="app-data-table__th">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>${tableBody}</tbody>
                </table>
            </div>
            </div>
        </div>
    `;
}

function mountActivityLogChart(data) {
    const role = data.role;
    const charts = data.charts ?? {};
    const canvasId = "dashboardActivityLogChart";
    const canvas = document.getElementById(canvasId);

    if (!canvas) {
        return;
    }

    const activityTrend =
        role === "staff"
            ? charts.my_activity
            : charts.system_activity;

    if (!activityTrend || !hasSeriesData(activityTrend.counts)) {
        return;
    }

    const lineColor = role === "staff" ? COLORS.oceanMid : COLORS.gold;
    const fillColor = role === "staff" ? COLORS.oceanFill : COLORS.goldFill;

    renderLineChart(
        canvasId,
        activityTrend.labels,
        activityTrend.counts,
        "Aktivitas",
        lineColor,
        fillColor,
    );
}

function updateChartsSecondaryVisibility() {
    if (!chartsSecondary) {
        return;
    }

    chartsSecondary.classList.toggle("hidden", chartsSecondary.innerHTML.trim().length === 0);
}

function buildChartCard(canvasId, title, description, heightClass = "min-h-[320px]") {
    return `
        <div class="app-panel">
            <div class="app-panel__header app-panel__header--split">
                <div>
                    <h2 class="app-panel__title">${title}</h2>
                    <p class="app-panel__description">${description}</p>
                </div>
                <div class="app-panel__actions">
                    <label for="export-format-${canvasId}">Format</label>
                    <select
                        id="export-format-${canvasId}"
                        data-chart-export-format="${canvasId}"
                        class="rounded-lg border border-charcoal-200 bg-white px-2 py-1.5 text-xs text-charcoal-800 dark:border-navy-600 dark:bg-navy-800 dark:text-slate-100"
                    >
                        <option value="png">PNG</option>
                        <option value="jpeg">JPG / JPEG</option>
                    </select>
                    <button
                        type="button"
                        data-chart-export="${canvasId}"
                        class="rounded-lg bg-gold-500 px-3 py-1.5 text-xs font-semibold text-ocean-900 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-300"
                    >
                        Unduh
                    </button>
                </div>
            </div>
            <div class="app-panel__body">
                <div class="${heightClass} relative" data-chart-wrapper="${canvasId}">
                    <canvas id="${canvasId}" role="img" aria-label="${title}"></canvas>
                </div>
            </div>
        </div>
    `;
}

function buildTableSection(title, description, headers, rows, colSpan, emptyMessage) {
    const body = rows.length
        ? rows
              .map(
                  (cells) => `
            <tr class="border-b border-charcoal-100 last:border-none transition hover:bg-ocean-50/50 dark:border-navy-700 dark:hover:bg-navy-900/40">
                ${cells
                    .map(
                        (cell) =>
                            `<td class="px-4 py-3 text-sm text-charcoal-700 dark:text-slate-300">${escapeHtml(String(cell))}</td>`,
                    )
                    .join("")}
            </tr>
        `,
              )
              .join("")
        : `<tr><td colspan="${colSpan}"><div class="ds-empty-state ds-empty-state--compact ds-empty-state--inline">${buildEmptyStateHtml("activity", { compact: true })}</div></td></tr>`;

    return `
        <div class="app-panel">
            <div class="app-panel__header">
                <h2 class="app-panel__title">${title}</h2>
                <p class="app-panel__description">${description}</p>
            </div>
            <div class="app-panel__body">
                <div class="overflow-x-auto rounded-xl border border-charcoal-100 dark:border-navy-700">
                    <table class="app-data-table min-w-full text-sm">
                        <thead>
                            <tr>
                                ${headers.map((h) => `<th class="app-data-table__th">${h}</th>`).join("")}
                            </tr>
                        </thead>
                        <tbody>${body}</tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function emptyStateCard() {
    return buildEmptyStateHtml("dashboard", { compact: true, className: "col-span-full" });
}

function showChartEmptyState(canvasId) {
    const wrapper = document.querySelector(`[data-chart-wrapper="${canvasId}"]`);

    if (!wrapper) {
        return;
    }

    const canvas = document.getElementById(canvasId);

    if (canvas) {
        canvas.remove();
    }

    wrapper.innerHTML = `<p class="flex h-full min-h-[200px] items-center justify-center text-sm text-charcoal-500 dark:text-slate-400">Belum ada data untuk ditampilkan.</p>`;
}

function renderBarChart(canvasId, labels, data, label, color) {
    const canvas = document.getElementById(canvasId);

    if (!canvas || !Chart) {
        return;
    }

    const chart = new Chart(canvas, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label,
                    data,
                    backgroundColor: color,
                    borderRadius: 6,
                    maxBarThickness: 42,
                },
            ],
        },
        options: chartOptions(),
    });

    registerChart(canvas.canvas.id, chart);
}

function renderIncomingOutgoingChart(charts, monthlyTrends) {
    const canvas = document.getElementById("dashboardIncomingOutgoingChart");

    if (!canvas || !Chart) {
        return;
    }

    const labels = monthlyTrends?.labels ?? charts.incoming_monthly?.labels ?? [];
    const incoming = monthlyTrends?.incoming ?? charts.incoming_monthly?.data ?? [];
    const outgoing = monthlyTrends?.outgoing ?? charts.outgoing_monthly?.data ?? [];

    const chart = new Chart(canvas, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Surat Masuk",
                    data: incoming,
                    backgroundColor: COLORS.maroon,
                    borderRadius: 6,
                    maxBarThickness: 36,
                },
                {
                    label: "Surat Keluar",
                    data: outgoing,
                    backgroundColor: COLORS.gold,
                    borderRadius: 6,
                    maxBarThickness: 36,
                },
            ],
        },
        options: {
            ...chartOptions(),
            scales: {
                x: {
                    ticks: { color: chartPalette().text },
                    grid: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: chartPalette().text },
                    grid: { color: chartPalette().grid },
                },
            },
        },
    });

    registerChart(canvas.canvas.id, chart);
}

function renderLineChart(canvasId, labels, data, label, color, fillColor) {
    const canvas = document.getElementById(canvasId);

    if (!canvas || !Chart) {
        return;
    }

    const chart = new Chart(canvas, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label,
                    data,
                    borderColor: color,
                    backgroundColor: fillColor ?? `${color}33`,
                    fill: true,
                    tension: 0.3,
                },
            ],
        },
        options: chartOptions(),
    });

    registerChart(canvas.canvas.id, chart);
}

function chartOptions() {
    const palette = chartPalette();

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: chartLegendStyle(),
            },
            tooltip: {
                backgroundColor: palette.tooltipBg,
                titleColor: palette.text,
                bodyColor: palette.text,
            },
        },
        scales: {
            x: {
                ticks: { color: palette.text },
                grid: { display: false },
            },
            y: {
                beginAtZero: true,
                ticks: { color: palette.text },
                grid: { color: palette.grid },
            },
        },
    };
}

function chartLegendStyle() {
    const palette = chartPalette();

    return {
        color: palette.text,
        usePointStyle: true,
        padding: 16,
    };
}

function chartPalette() {
    const dark = document.documentElement.classList.contains("dark");

    return {
        text: dark ? "#94a3b8" : "#6b7178",
        grid: dark ? "rgba(148, 163, 184, 0.12)" : "rgba(107, 113, 120, 0.15)",
        tooltipBg: dark ? "#1a2635" : "#ffffff",
    };
}

function hasSeriesData(values) {
    return Array.isArray(values) && values.some((value) => Number(value) > 0);
}

function registerChart(canvasId, chart) {
    chartRegistry.set(canvasId, chart);
}

function attachChartExportHandlers() {
    document.querySelectorAll("[data-chart-export]").forEach((button) => {
        button.addEventListener("click", () => {
            const canvasId = button.getAttribute("data-chart-export");
            const formatSelect = document.querySelector(`[data-chart-export-format="${canvasId}"]`);
            const format = formatSelect?.value ?? "png";

            exportChart(canvasId, format);
        });
    });
}

function exportChart(canvasId, format) {
    const chart = chartRegistry.get(canvasId);

    if (!chart) {
        return;
    }

    const mimeType = format === "jpeg" ? "image/jpeg" : "image/png";
    const extension = format === "jpeg" ? "jpg" : "png";
    const exportCanvas = document.createElement("canvas");
    exportCanvas.width = chart.width;
    exportCanvas.height = chart.height;
    const context = exportCanvas.getContext("2d");

    if (!context) {
        return;
    }

    context.fillStyle = "#ffffff";
    context.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
    context.drawImage(chart.canvas, 0, 0);

    const link = document.createElement("a");
    link.download = `${canvasId}-${Date.now()}.${extension}`;
    link.href = exportCanvas.toDataURL(mimeType, 0.95);
    link.click();
}

function syncGranularityFilter(granularity) {
    if (granularityFilter && granularity) {
        granularityFilter.value = granularity;
    }
}

function granularityLabel(granularity) {
    return granularityLabels[granularity] ?? granularityLabels.month;
}

function destroyCharts() {
    chartRegistry.forEach((chart) => chart.destroy());
    chartRegistry.clear();
}

function showError(message) {
    if (!errorAlert) {
        return;
    }

    errorAlert.textContent = message;
    errorAlert.classList.remove("hidden");
}

function hideError() {
    if (!errorAlert) {
        return;
    }

    errorAlert.classList.add("hidden");
}

function buildDashboardUrl() {
    const query = new URLSearchParams();

    if (departmentFilter && departmentFilter.value) {
        query.set("department_id", departmentFilter.value);
    }

    if (periodStartInput && periodStartInput.value) {
        query.set("period_start", periodStartInput.value);
    }

    if (periodEndInput && periodEndInput.value) {
        query.set("period_end", periodEndInput.value);
    }

    if (granularityFilter && granularityFilter.value) {
        query.set("granularity", granularityFilter.value);
    }

    const queryString = query.toString();

    return queryString ? `/api/dashboard?${queryString}` : "/api/dashboard";
}

function updateLastUpdated(value) {
    if (!lastUpdatedElement) {
        return;
    }

    lastUpdatedElement.textContent = value ? formatDateTime(value) : "-";
}

function formatDate(value) {
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

function formatDateTime(value) {
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

function escapeHtml(value) {
    return value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

export { initializeDashboard as initDashboard };
