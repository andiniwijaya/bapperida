/**
 * Design-system skeleton builders and table loading state helpers.
 */

/**
 * @param {number} widthPercent
 */
function skeletonBar(widthPercent = 100) {
    return `<div class="ds-skeleton ds-skeleton--bar" style="width: ${widthPercent}%"></div>`;
}

/**
 * @param {{ rows?: number, columns?: number }} options
 */
export function buildTableSkeletonHtml(options = {}) {
    const rows = options.rows ?? 8;
    const columns = options.columns ?? 6;

    const headerCells = Array.from({ length: columns }, (_, index) => {
        const width = index === 0 ? 40 : 55 + ((index % 3) * 10);

        return `<div class="ds-skeleton-table__cell">${skeletonBar(width)}</div>`;
    }).join("");

    const bodyRows = Array.from({ length: rows }, () => {
        const cells = Array.from({ length: columns }, (_, index) => {
            const width = 45 + ((index % 4) * 12);

            return `<div class="ds-skeleton-table__cell">${skeletonBar(width)}</div>`;
        }).join("");

        return `<div class="ds-skeleton-table__row">${cells}</div>`;
    }).join("");

    return `
        <div class="ds-skeleton-table" data-skeleton-root>
            <div class="ds-skeleton-table__header" aria-hidden="true">
                <div class="ds-skeleton-table__row ds-skeleton-table__row--header">${headerCells}</div>
            </div>
            <div class="ds-skeleton-table__body" aria-hidden="true">${bodyRows}</div>
            <div class="ds-skeleton-table__pagination" aria-hidden="true">
                ${skeletonBar(28)}
                <div class="ds-skeleton-table__pagination-actions">
                    ${skeletonBar(12)}
                    ${skeletonBar(12)}
                    ${skeletonBar(12)}
                </div>
            </div>
        </div>
    `;
}

/**
 * @param {number} count
 */
export function buildCardSkeletonHtml(count = 4) {
    const cards = Array.from({ length: count }, () => `
        <div class="ds-skeleton-card">
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--title"></div>
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--value"></div>
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--hint"></div>
        </div>
    `).join("");

    return `<div class="ds-skeleton-cards" data-skeleton-root>${cards}</div>`;
}

/**
 * @param {number} count
 */
export function buildChartSkeletonHtml(count = 2) {
    const charts = Array.from({ length: count }, () => `
        <div class="ds-skeleton-chart">
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--chart-title"></div>
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--chart-subtitle"></div>
            <div class="ds-skeleton ds-skeleton--chart-area"></div>
        </div>
    `).join("");

    return `<div class="ds-skeleton-charts" data-skeleton-root>${charts}</div>`;
}

/**
 * @param {number} count
 */
export function buildNotificationListSkeletonHtml(count = 5) {
    const items = Array.from({ length: count }, () => `
        <div class="ds-skeleton-list__item">
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--list-title"></div>
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--list-body"></div>
            <div class="ds-skeleton ds-skeleton--bar ds-skeleton--list-meta"></div>
        </div>
    `).join("");

    return `<div class="ds-skeleton-list" data-skeleton-root aria-busy="true">${items}</div>`;
}

/**
 * @param {string | null} tableBodyId
 */
function resolveTableWrapper(tableBodyId = null) {
    const tableElement = tableBodyId
        ? document.getElementById(tableBodyId)
        : document.getElementById("dataTable");

    if (!tableElement) {
        return document.querySelector(".app-data-table-wrapper");
    }

    return tableElement.closest(".app-data-table-wrapper")
        ?? tableElement.closest("table")?.parentElement
        ?? tableElement.parentElement;
}

/**
 * @param {string | null} tableBodyId
 */
function countTableColumns(tableBodyId = null) {
    const wrapper = resolveTableWrapper(tableBodyId);

    if (!wrapper) {
        return 6;
    }

    const headerCells = wrapper.querySelectorAll("thead th, thead .app-data-table__th");

    if (headerCells.length > 0) {
        return headerCells.length;
    }

    const firstRow = wrapper.querySelector("tbody tr");

    if (firstRow) {
        return firstRow.querySelectorAll("td, th").length;
    }

    return 6;
}

/**
 * @param {boolean} isLoading
 * @param {string | null} tableBodyId
 * @param {{ rows?: number, columns?: number }} options
 */
export function setLoadingState(isLoading, tableBodyId = null, options = {}) {
    const loadingState = document.getElementById("loadingState");
    const emptyState = document.getElementById("emptyState");
    const pagination = document.getElementById("pagination");
    const tableWrapper = resolveTableWrapper(tableBodyId);
    const tablePanel = document.querySelector(".crud-table-panel__table-area");

    if (isLoading) {
        if (loadingState) {
            if (!loadingState.querySelector("[data-skeleton-root]")) {
                const columns = options.columns ?? countTableColumns(tableBodyId);
                loadingState.innerHTML = buildTableSkeletonHtml({
                    rows: options.rows ?? 8,
                    columns,
                });
            }

            loadingState.classList.remove("hidden");
            loadingState.setAttribute("aria-busy", "true");
            loadingState.setAttribute("aria-live", "polite");
        }

        tableWrapper?.classList.add("hidden");
        emptyState?.classList.add("hidden");
        pagination?.classList.add("hidden");
        tablePanel?.setAttribute("aria-busy", "true");
    } else {
        if (loadingState) {
            loadingState.classList.add("hidden");
            loadingState.setAttribute("aria-busy", "false");
        }

        tableWrapper?.classList.remove("hidden");
        tablePanel?.setAttribute("aria-busy", "false");
    }
}
