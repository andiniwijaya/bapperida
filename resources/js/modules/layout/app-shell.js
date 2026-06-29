import { get, patch } from "../../api/api";
import { confirmAction, showPageLoading } from "../admin/helper";
import { buildNotificationEmptyStateHtml, refreshEmptyStateIcons } from "../form/empty-state";
import { buildNotificationListSkeletonHtml } from "../form/skeleton";

const STORAGE_SIDEBAR = "bapperida.sidebar.collapsed";
const STORAGE_THEME = "flux.appearance";
const NOTIFICATION_POLL_MS = 15000;

let shellInitialized = false;
let notificationPollTimer = null;

function initAppShell() {
    const shell = document.getElementById("app-shell");

    if (!shell) {
        return;
    }

    syncThemeFromStorage();

    if (!shellInitialized) {
        shell.addEventListener("click", handleShellClick);
        shellInitialized = true;
    }

    applySidebarCollapsedState(shell);
    initFloatingTooltips(shell);
    initUserMenu();
    initNotifications();
    initThemeToggle();
    initFormGuards();
    initKeyboardNavigation();
    initViewportResizeHandler();
    initPageNavigation();
    initLogoutConfirmation();
}

function handleShellClick(event) {
    const shell = document.getElementById("app-shell");

    if (!shell) {
        return;
    }

    const toggle = event.target.closest("[data-sidebar-toggle]");

    if (toggle) {
        event.preventDefault();

        if (window.innerWidth < 1024) {
            toggleMobileDrawer(shell);

            return;
        }

        const isCollapsed = shell.classList.contains("is-sidebar-collapsed");
        setSidebarCollapsed(shell, !isCollapsed);

        return;
    }

    const mobileLink = event.target.closest(
        ".app-mobile-drawer .app-sidebar__link[href]",
    );

    if (mobileLink) {
        closeMobileDrawer(shell);

        return;
    }

    if (event.target.closest("[data-mobile-drawer-backdrop]")) {
        closeMobileDrawer(shell);
    }
}

function applySidebarCollapsedState(shell) {
    const collapsed = window.localStorage.getItem(STORAGE_SIDEBAR) === "true";
    setSidebarCollapsed(shell, collapsed);
}

function setSidebarCollapsed(shell, collapsed) {
    shell.classList.toggle("is-sidebar-collapsed", collapsed);
    shell.dataset.sidebarCollapsed = collapsed ? "true" : "false";
    window.localStorage.setItem(STORAGE_SIDEBAR, collapsed ? "true" : "false");

    document.querySelectorAll("[data-sidebar-toggle]").forEach((button) => {
        button.setAttribute("aria-expanded", collapsed ? "false" : "true");
    });

    hideFloatingTooltip();
}

/**
 * Show floating tooltips on sidebar icons (when collapsed) and header icon buttons.
 */
function initFloatingTooltips(shell) {
    if (shell.dataset.floatingTooltipsBound === "true") {
        return;
    }

    shell.dataset.floatingTooltipsBound = "true";

    let tooltip = document.getElementById("app-floating-tooltip");

    if (!tooltip) {
        tooltip = document.createElement("div");
        tooltip.id = "app-floating-tooltip";
        tooltip.className = "app-floating-tooltip";
        tooltip.setAttribute("role", "tooltip");
        tooltip.hidden = true;
        document.body.appendChild(tooltip);
    }

    document.querySelectorAll(".app-sidebar__link").forEach((link) => {
        const label = link.querySelector(".app-sidebar__nav-label");
        const text = label?.textContent?.trim() ?? "";

        if (text) {
            link.dataset.sidebarTooltip = text;
            link.setAttribute("aria-label", text);
        }

        bindFloatingTooltip(link, tooltip, () => shouldShowSidebarLinkTooltip());
    });

    const brand = shell.querySelector(".app-sidebar__brand");

    if (brand) {
        const brandLabel = brand.querySelector(".app-sidebar__app-name");

        if (brandLabel?.textContent?.trim()) {
            brand.dataset.sidebarTooltip = brandLabel.textContent.trim();
        }

        bindFloatingTooltip(brand, tooltip, () => shouldShowSidebarLinkTooltip());
    }

    document.querySelectorAll("[data-app-tooltip]").forEach((element) => {
        bindFloatingTooltip(element, tooltip, () => true);
    });
}

function bindFloatingTooltip(element, tooltip, shouldShow) {
    const show = () => {
        if (!shouldShow()) {
            hideFloatingTooltip(tooltip);

            return;
        }

        const text = element.dataset.sidebarTooltip ?? element.dataset.appTooltip ?? "";

        if (!text) {
            return;
        }

        showFloatingTooltip(element, tooltip, text);
    };

    const hide = () => hideFloatingTooltip(tooltip);

    element.addEventListener("mouseenter", show);
    element.addEventListener("mouseleave", hide);
    element.addEventListener("focus", show);
    element.addEventListener("blur", hide);
}

function shouldShowSidebarLinkTooltip() {
    if (window.innerWidth < 1024) {
        return false;
    }

    const shell = document.getElementById("app-shell");

    return shell?.classList.contains("is-sidebar-collapsed")
        || shell?.dataset.sidebarCollapsed === "true";
}

function showFloatingTooltip(anchor, tooltip, text) {
    tooltip.textContent = text;
    tooltip.hidden = false;
    tooltip.classList.add("is-visible");

    const rect = anchor.getBoundingClientRect();
    tooltip.style.top = `${rect.top + rect.height / 2}px`;
    tooltip.style.left = `${rect.right + 10}px`;
    tooltip.style.transform = "translateY(-50%)";
}

function hideFloatingTooltip(tooltip = document.getElementById("app-floating-tooltip")) {
    if (!tooltip) {
        return;
    }

    tooltip.classList.remove("is-visible");
    tooltip.hidden = true;
}

function toggleMobileDrawer(shell) {
    const drawer = shell.querySelector("[data-mobile-drawer]");

    if (!drawer) {
        return;
    }

    if (drawer.classList.contains("is-open")) {
        closeMobileDrawer(shell);
    } else {
        openMobileDrawer(shell);
    }
}

function openMobileDrawer(shell) {
    const drawer = shell.querySelector("[data-mobile-drawer]");

    if (!drawer) {
        return;
    }

    drawer.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");
}

function closeMobileDrawer(shell) {
    const drawer = shell.querySelector("[data-mobile-drawer]");

    if (!drawer) {
        return;
    }

    drawer.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");
}

function initNotifications() {
    const root = document.querySelector("[data-notification-root]");

    if (!root) {
        return;
    }

    const toggle = root.querySelector("[data-notification-toggle]");
    const panel = root.querySelector("[data-notification-panel]");
    const list = root.querySelector("[data-notification-list]");
    const badge = root.querySelector("[data-notification-badge]");
    const markAllButton = root.querySelector("[data-notification-mark-all]");

    if (!toggle || !panel || !list || !badge) {
        return;
    }

    if (root.dataset.bound !== "true") {
        root.dataset.bound = "true";

        toggle.addEventListener("click", async (event) => {
            event.stopPropagation();
            const isOpen = !panel.classList.contains("hidden");
            panel.classList.toggle("hidden", isOpen);
            toggle.setAttribute("aria-expanded", isOpen ? "false" : "true");

            if (!isOpen) {
                await loadNotificationList();
            }
        });

        if (markAllButton) {
            markAllButton.addEventListener("click", async () => {
                try {
                    await patch("/api/notifications/mark-all-read");
                    await refreshNotifications();
                } catch (error) {
                    // eslint-disable-next-line no-console
                    console.error(error);
                }
            });
        }

        document.addEventListener("click", (event) => {
            if (!root.contains(event.target)) {
                panel.classList.add("hidden");
                toggle.setAttribute("aria-expanded", "false");
            }
        });

        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible") {
                refreshNotifications();
            }
        });
    }

    refreshNotifications();
    startNotificationPolling();
}

function startNotificationPolling() {
    if (notificationPollTimer) {
        clearInterval(notificationPollTimer);
    }

    notificationPollTimer = window.setInterval(() => {
        if (document.visibilityState === "visible") {
            refreshNotifications();
        }
    }, NOTIFICATION_POLL_MS);
}

async function refreshNotifications() {
    const root = document.querySelector("[data-notification-root]");

    if (!root) {
        return;
    }

    const panel = root.querySelector("[data-notification-panel]");
    const badge = root.querySelector("[data-notification-badge]");
    const list = root.querySelector("[data-notification-list]");

    if (!badge) {
        return;
    }

    try {
        const countResponse = await get("/api/notifications/unread-count");
        const unread = countResponse?.data?.unread_count ?? 0;
        updateNotificationBadge(badge, unread);

        const isPanelOpen = panel && !panel.classList.contains("hidden");

        if (isPanelOpen && list) {
            await loadNotificationList();
        }
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error(error);
    }
}

function updateNotificationBadge(badge, unread) {
    if (unread > 0) {
        badge.textContent = String(unread);
        badge.classList.remove("hidden");
    } else {
        badge.classList.add("hidden");
    }
}

async function loadNotificationList() {
    const root = document.querySelector("[data-notification-root]");

    if (!root) {
        return;
    }

    const list = root.querySelector("[data-notification-list]");

    if (!list) {
        return;
    }

    list.innerHTML = buildNotificationListSkeletonHtml(5);
    list.setAttribute("aria-busy", "true");

    try {
        const listResponse = await get("/api/notifications?per_page=8");
        const items = listResponse?.data?.data ?? [];

        if (!items.length) {
            list.innerHTML = buildNotificationEmptyStateHtml();
            refreshEmptyStateIcons(list);

            return;
        }

        list.innerHTML = items
            .map((item) => {
                const isUnread = !item.read_at;
                const title = escapeHtml(item.title ?? "Notifikasi");
                const message = escapeHtml(item.message ?? "");
                const time = formatDateTime(item.created_at);
                const targetUrl = item.url ?? "";

                return `
                    <button
                        type="button"
                        class="app-notification__item ${isUnread ? "is-unread" : ""}"
                        data-notification-id="${item.id}"
                        data-notification-url="${escapeHtml(targetUrl)}"
                    >
                        <p class="text-sm font-medium text-slate-100">${title}</p>
                        <p class="mt-0.5 text-xs text-slate-400">${message}</p>
                        <p class="mt-1 text-[11px] text-slate-500">${time}</p>
                    </button>
                `;
            })
            .join("");

        list.querySelectorAll("[data-notification-id]").forEach((button) => {
            button.addEventListener("click", async () => {
                const id = button.getAttribute("data-notification-id");
                const targetUrl = button.getAttribute("data-notification-url");

                if (!id) {
                    return;
                }

                try {
                    await patch(`/api/notifications/${id}/read`);

                    if (targetUrl) {
                        navigateToNotificationUrl(targetUrl);

                        return;
                    }

                    await refreshNotifications();
                } catch (error) {
                    // eslint-disable-next-line no-console
                    console.error(error);
                }
            });
        });
    } catch (error) {
        list.innerHTML =
            '<p class="app-notification__panel-empty px-4 py-6 text-center text-sm text-maroon-300">Gagal memuat notifikasi.</p>';
        // eslint-disable-next-line no-console
        console.error(error);
    } finally {
        list.setAttribute("aria-busy", "false");
    }
}

function applyAppearance(appearance) {
    const applyDark = () => document.documentElement.classList.add("dark");
    const applyLight = () => document.documentElement.classList.remove("dark");

    if (appearance === "system") {
        window.localStorage.removeItem(STORAGE_THEME);
        window.matchMedia("(prefers-color-scheme: dark)").matches ? applyDark() : applyLight();
    } else if (appearance === "dark") {
        window.localStorage.setItem(STORAGE_THEME, "dark");
        applyDark();
    } else {
        window.localStorage.setItem(STORAGE_THEME, "light");
        applyLight();
    }

    if (typeof window.Flux?.applyAppearance === "function") {
        window.Flux.applyAppearance(appearance);
    }
}

function syncThemeFromStorage() {
    const stored = window.localStorage.getItem(STORAGE_THEME);

    if (stored === "dark" || stored === "light") {
        applyAppearance(stored);

        return;
    }

    applyAppearance("system");
}

function initThemeToggle() {
    const button = document.querySelector("[data-theme-toggle]");

    if (!button) {
        return;
    }

    if (button.dataset.bound === "true") {
        return;
    }

    button.dataset.bound = "true";

    button.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        const isDark = document.documentElement.classList.contains("dark");
        applyAppearance(isDark ? "light" : "dark");
    });
}

function initPageNavigation() {
    if (document.body.dataset.pageNavBound === "true") {
        return;
    }

    document.body.dataset.pageNavBound = "true";

    document.addEventListener("click", (event) => {
        const link = event.target.closest("a[href]");

        if (!link) {
            return;
        }

        if (link.target === "_blank" || link.hasAttribute("download")) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const href = link.getAttribute("href");

        if (!href || href.startsWith("#") || href.startsWith("javascript:")) {
            return;
        }

        try {
            const url = new URL(link.href, window.location.origin);

            if (url.origin !== window.location.origin) {
                return;
            }

            if (
                url.pathname === window.location.pathname
                && url.search === window.location.search
                && !url.hash
            ) {
                return;
            }
        } catch {
            return;
        }

        showPageLoading();
    });
}

function initLogoutConfirmation() {
    document.querySelectorAll("form[action*='logout']").forEach((form) => {
        if (form.dataset.logoutBound === "true") {
            return;
        }

        form.dataset.logoutBound = "true";

        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            const confirmed = await confirmAction({
                title: "Keluar dari akun?",
                message: "Anda akan keluar dari sesi aplikasi.",
                confirmText: "Keluar",
                variant: "danger",
            });

            if (!confirmed) {
                return;
            }

            showPageLoading();
            HTMLFormElement.prototype.submit.call(form);
        });
    });
}

function initKeyboardNavigation() {
    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        document.querySelectorAll("[data-notification-panel]:not(.hidden)").forEach((panel) => {
            panel.classList.add("hidden");
            const toggle = panel.closest("[data-notification-root]")?.querySelector("[data-notification-toggle]");
            if (toggle) {
                toggle.setAttribute("aria-expanded", "false");
            }
        });

        document.querySelectorAll("[data-user-menu-panel]:not(.hidden)").forEach((panel) => {
            panel.classList.add("hidden");
            const toggle = panel.closest("[data-user-menu-root]")?.querySelector("[data-user-menu-toggle]");
            if (toggle) {
                toggle.setAttribute("aria-expanded", "false");
            }
        });

        const shell = document.getElementById("app-shell");
        if (shell) {
            closeMobileDrawer(shell);
        }
    });
}

/**
 * Close the mobile drawer when the viewport crosses the desktop breakpoint.
 */
function initViewportResizeHandler() {
    if (window.__bapperidaViewportResizeBound) {
        return;
    }

    window.__bapperidaViewportResizeBound = true;

    window.addEventListener("resize", () => {
        if (window.innerWidth >= 1024) {
            const shell = document.getElementById("app-shell");

            if (shell) {
                closeMobileDrawer(shell);
            }
        }
    });
}

function initFormGuards() {
    document.querySelectorAll("form").forEach((form) => {
        if (form.dataset.guardBound === "true") {
            return;
        }

        form.dataset.guardBound = "true";

        form.addEventListener("submit", () => {
            const submitButton = form.querySelector("[type='submit']");

            if (!submitButton || submitButton.disabled) {
                return;
            }

            submitButton.disabled = true;
            submitButton.classList.add("opacity-60", "cursor-not-allowed");

            window.setTimeout(() => {
                submitButton.disabled = false;
                submitButton.classList.remove("opacity-60", "cursor-not-allowed");
            }, 8000);
        });
    });
}

function initUserMenu() {
    const root = document.querySelector("[data-user-menu-root]");

    if (!root || root.dataset.bound === "true") {
        return;
    }

    root.dataset.bound = "true";

    const toggle = root.querySelector("[data-user-menu-toggle]");
    const panel = root.querySelector("[data-user-menu-panel]");

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener("click", (event) => {
        event.stopPropagation();
        const isOpen = !panel.classList.contains("hidden");
        panel.classList.toggle("hidden", isOpen);
        toggle.setAttribute("aria-expanded", isOpen ? "false" : "true");
    });

    document.addEventListener("click", (event) => {
        if (!root.contains(event.target)) {
            panel.classList.add("hidden");
            toggle.setAttribute("aria-expanded", "false");
        }
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

function navigateToNotificationUrl(url) {
    if (!url) {
        return;
    }

    try {
        const target = new URL(url, window.location.origin);

        if (target.origin !== window.location.origin) {
            return;
        }

        showPageLoading();
        window.location.assign(`${target.pathname}${target.search}${target.hash}`);
    } catch {
        // Ignore invalid notification URLs.
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

export { initAppShell };
