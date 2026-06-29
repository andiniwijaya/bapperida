import { createIcons, icons } from "lucide";

/** @type {Record<string, Record<string, { icon: string, title: string, description?: string, action?: { label: string, href: string, icon?: string }, resetFilter?: boolean }>>} */
export const EMPTY_PAGE_PRESETS = {
    "incoming-letters": {
        table: {
            icon: "inbox",
            title: "Belum ada arsip surat masuk.",
            description:
                "Silakan klik \"Tambah Surat Masuk\" untuk membuat data pertama.",
            action: {
                label: "Tambah Surat Masuk",
                href: "/incoming-letters/create",
                icon: "plus",
            },
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat data lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    "outgoing-letters": {
        table: {
            icon: "send",
            title: "Belum ada arsip surat keluar.",
            description:
                "Silakan klik \"Tambah Surat Keluar\" untuk membuat data pertama.",
            action: {
                label: "Tambah Surat Keluar",
                href: "/outgoing-letters/create",
                icon: "plus",
            },
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat data lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    "letter-number-registrations": {
        table: {
            icon: "folder-open",
            title: "Belum ada registrasi penomoran.",
            description:
                "Silakan klik \"Tambah Registrasi\" untuk membuat data pertama.",
            action: {
                label: "Tambah Registrasi",
                href: "/letter-number-registrations/create",
                icon: "plus",
            },
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat data lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    users: {
        table: {
            icon: "users",
            title: "Belum ada data pengguna.",
            description: "Silakan tambah pengguna baru untuk memulai manajemen akun.",
            action: {
                label: "Tambah Pengguna",
                href: "/users/create",
                icon: "plus",
            },
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat data lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    departments: {
        table: {
            icon: "building-2",
            title: "Belum ada data bidang.",
            description: "Silakan klik \"Tambah Bidang\" untuk membuat data pertama.",
            action: {
                label: "Tambah Bidang",
                href: "/departments/create",
                icon: "plus",
            },
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat data lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    "activity-logs": {
        table: {
            icon: "scroll-text",
            title: "Belum ada aktivitas yang tercatat.",
            description: "Log aktivitas akan muncul setelah ada aksi di sistem.",
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat data lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    reports: {
        table: {
            icon: "file-text",
            title: "Belum ada laporan yang dapat ditampilkan.",
            description: "Data laporan akan muncul setelah ada arsip surat di sistem.",
        },
        filter: {
            icon: "filter-x",
            title: "Tidak ada data yang sesuai dengan filter yang dipilih.",
            description: "Coba ubah filter atau atur ulang untuk melihat hasil lain.",
            resetFilter: true,
        },
        search: {
            icon: "search-x",
            title: "Tidak ditemukan data untuk kata kunci tersebut.",
            description: "Periksa kata kunci pencarian atau coba kata lain.",
        },
    },
    "registration-requests": {
        table: {
            icon: "user-plus",
            title: "Belum ada permintaan registrasi.",
            description: "Permintaan registrasi akun baru akan muncul di sini.",
        },
    },
};

/** @type {Record<string, { icon: string, title: string, description?: string }>} */
export const EMPTY_VARIANT_PRESETS = {
    dashboard: {
        icon: "layout-dashboard",
        title: "Belum ada aktivitas yang dapat ditampilkan.",
        description: "Data akan muncul setelah ada aktivitas di sistem.",
    },
    notification: {
        icon: "bell",
        title: "Belum ada notifikasi.",
        description: "Notifikasi baru akan muncul di sini.",
    },
    activity: {
        icon: "scroll-text",
        title: "Belum ada aktivitas yang tercatat.",
        description: "Log aktivitas akan muncul setelah ada aksi di sistem.",
    },
    report: {
        icon: "file-text",
        title: "Belum ada laporan yang dapat ditampilkan.",
        description: "Data laporan akan muncul setelah ada arsip surat di sistem.",
    },
    file: {
        icon: "paperclip",
        title: "Belum ada file yang diunggah.",
        description: "Unggah file untuk melihat pratinjau di sini.",
    },
};

/**
 * @param {{ search?: string, filters?: Record<string, unknown> }} options
 */
export function resolveEmptyStateContext(options = {}) {
    const search = String(options.search ?? "").trim();

    if (search) {
        return "search";
    }

    const filters = options.filters ?? {};
    const hasActiveFilters = Object.entries(filters).some(([key, value]) => {
        if (value === null || value === undefined || value === "") {
            return false;
        }

        if (key === "report_type" && value === "all") {
            return false;
        }

        return true;
    });

    if (hasActiveFilters) {
        return "filter";
    }

    return "table";
}

/**
 * @param {HTMLElement | null | undefined} root
 * @param {{ icon: string, title: string, description?: string, action?: { label: string, href: string, icon?: string }, resetFilter?: boolean }} preset
 */
function applyEmptyStatePreset(root, preset) {
    if (!root || !preset) {
        return;
    }

    const iconElement = root.querySelector("[data-empty-state-icon] i");
    const titleElement = root.querySelector("[data-empty-state-title]");
    const descriptionElement = root.querySelector("[data-empty-state-description]");
    const actionsElement = root.querySelector("[data-empty-state-actions]");

    if (iconElement) {
        iconElement.setAttribute("data-lucide", preset.icon);
    }

    if (titleElement) {
        titleElement.textContent = preset.title;
    }

    if (descriptionElement) {
        if (preset.description) {
            descriptionElement.textContent = preset.description;
            descriptionElement.classList.remove("hidden");
        } else {
            descriptionElement.textContent = "";
            descriptionElement.classList.add("hidden");
        }
    }

    if (actionsElement) {
        let actionsHtml = "";

        if (preset.action?.href) {
            const actionIcon = preset.action.icon ?? "plus";
            actionsHtml += `<a href="${preset.action.href}" class="ds-btn ds-btn--primary"><i data-lucide="${actionIcon}" class="h-4 w-4" aria-hidden="true"></i>${preset.action.label}</a>`;
        }

        if (preset.resetFilter) {
            actionsHtml += `<button type="button" class="ds-btn ds-btn--secondary" data-trigger-reset-filter><i data-lucide="rotate-ccw" class="h-4 w-4" aria-hidden="true"></i>Atur Ulang Filter</button>`;
        }

        actionsElement.innerHTML = actionsHtml;
    }

    createIcons({ icons, selector: root.querySelectorAll("i[data-lucide]") });
}

/**
 * @param {HTMLElement | null | undefined} root
 * @param {string} context
 */
export function renderTableEmptyState(root, context) {
    if (!root) {
        return;
    }

    const pageKey = root.dataset.emptyPage;

    if (!pageKey || !EMPTY_PAGE_PRESETS[pageKey]) {
        root.classList.remove("hidden");

        return;
    }

    const preset = EMPTY_PAGE_PRESETS[pageKey][context] ?? EMPTY_PAGE_PRESETS[pageKey].table;

    applyEmptyStatePreset(root, preset);
    root.classList.remove("hidden");
}

/**
 * @param {HTMLElement | null | undefined} root
 */
export function hideEmptyState(root) {
    root?.classList.add("hidden");
}

/**
 * @param {string} variant
 * @param {{ compact?: boolean, className?: string }} options
 */
export function buildEmptyStateHtml(variant, options = {}) {
    const preset = EMPTY_VARIANT_PRESETS[variant] ?? EMPTY_VARIANT_PRESETS.dashboard;
    const compactClass = options.compact ? " ds-empty-state--compact" : "";
    const extraClass = options.className ?? "";

    return `
        <div class="ds-empty-state ds-empty-state--inline${compactClass} ${extraClass}" data-empty-state-root>
            <div class="ds-empty-state__icon" aria-hidden="true">
                <i data-lucide="${preset.icon}" class="h-10 w-10"></i>
            </div>
            <h3 class="ds-empty-state__title">${preset.title}</h3>
            ${preset.description ? `<p class="ds-empty-state__description">${preset.description}</p>` : ""}
        </div>
    `;
}

/**
 * Build notification panel empty state markup.
 */
export function buildNotificationEmptyStateHtml() {
    const preset = EMPTY_VARIANT_PRESETS.notification;

    return `
        <div class="ds-empty-state ds-empty-state--compact ds-empty-state--panel" data-empty-state-root>
            <div class="ds-empty-state__icon" aria-hidden="true">
                <i data-lucide="${preset.icon}" class="h-8 w-8"></i>
            </div>
            <p class="ds-empty-state__title">${preset.title}</p>
        </div>
    `;
}

/**
 * @param {HTMLElement | null | undefined} root
 */
export function refreshEmptyStateIcons(root = document) {
    createIcons({ icons, selector: root.querySelectorAll?.("i[data-lucide]") ?? "i[data-lucide]" });
}

let emptyStateActionsInitialized = false;

/**
 * Wire empty-state action buttons (e.g. reset filter from table empty state).
 */
export function initEmptyStateActions() {
    if (emptyStateActionsInitialized) {
        return;
    }

    emptyStateActionsInitialized = true;

    document.addEventListener("click", (event) => {
        const trigger = event.target.closest("[data-trigger-reset-filter]");

        if (!trigger) {
            return;
        }

        document.getElementById("reset-filter")?.click();
    });
}
