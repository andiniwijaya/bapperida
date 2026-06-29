import { createIcons, icons } from "lucide";
import { destroy, get } from "../../api/api";
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
let searchTerm = "";

const searchInput = document.getElementById("search");
const resetButton = document.getElementById("reset-filter");

const tableControls = initTableControls({
    onChange: () => {
        currentPage = 1;
        loadUsers();
    },
    perPage: 15,
});

searchInput?.addEventListener("input", () => {
    clearTimeout(searchInput.dataset.timer);

    searchInput.dataset.timer = setTimeout(() => {
        searchTerm = searchInput.value.trim();
        currentPage = 1;
        loadUsers();
    }, 300);
});

async function loadUsers(page = 1) {
    setLoadingState(true);

    try {
        currentPage = page;
        const params = buildListQueryParams(currentPage, tableControls);

        if (searchTerm) {
            params.append("search", searchTerm);
        }

        const response = await get(`/api/users?${params.toString()}`);
        const { items, meta } = extractPaginatedItems(response);

        renderTable(items);
        renderPagination(meta, loadUsers);
        document.getElementById("totalCount").textContent = String(meta?.total ?? items.length);
    } catch (error) {
        reportRequestFailure(error, ERROR_MESSAGES.load);
    } finally {
        setLoadingState(false);
    }
}

function renderTable(users) {
    const tbody = document.getElementById("dataTable");
    const emptyState = document.getElementById("emptyState");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = "";

    if (!users.length) {
        renderTableEmptyState(
            emptyState,
            resolveEmptyStateContext({ search: searchTerm, filters: {} }),
        );

        return;
    }

    hideEmptyState(emptyState);

    users.forEach((user, index) => {
        tbody.insertAdjacentHTML("beforeend", rowTemplate(user, index));
    });

    tbody.querySelectorAll("[data-delete-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            await deleteUser(Number(button.dataset.deleteId));
        });
    });

    createIcons({ icons, selector: "i[data-lucide]" });
}

function rowTemplate(user, index) {
    const statusClass =
        user.status === "active"
            ? dsBadgeClass("success")
            : user.status === "pending"
              ? dsBadgeClass("warning")
              : dsBadgeClass("danger");

    const passwordOnboardingClass =
        user.password_onboarding_status === "pending"
            ? dsBadgeClass("warning")
            : dsBadgeClass("success");

    return `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td>${user.name}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td class="uppercase">${user.role}</td>
            <td>${user.department?.name ?? "-"}</td>
            <td class="text-center">
                <span class="${statusClass}">${user.status_label}</span>
            </td>
            <td class="text-center">
                <span class="${passwordOnboardingClass}">${user.password_onboarding_status_label}</span>
            </td>
            <td class="text-center">
                <div class="flex items-center justify-center gap-2">
                    <a href="/users/${user.id}" class="rounded-md p-2 text-charcoal-500 hover:bg-charcoal-100"><i data-lucide="eye" class="h-4 w-4"></i></a>
                    ${user.can?.update ? `<a href="/users/${user.id}/edit" class="rounded-md p-2 text-charcoal-500 hover:bg-charcoal-100"><i data-lucide="square-pen" class="h-4 w-4"></i></a>` : ""}
                    ${user.can?.delete ? `<button type="button" data-delete-id="${user.id}" class="rounded-md p-2 text-charcoal-500 hover:bg-charcoal-100"><i data-lucide="trash-2" class="h-4 w-4"></i></button>` : ""}
                </div>
            </td>
        </tr>
    `;
}

async function deleteUser(id) {
    const confirmed = await confirmAction({
        title: "Hapus pengguna?",
        message: "Apakah Anda yakin ingin menghapus pengguna ini?",
        confirmText: "Hapus",
        variant: "danger",
    });

    if (!confirmed) {
        return;
    }

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.delete,
            action: () => destroy(`/api/users/${id}`),
            successMessage: SUCCESS_MESSAGES.delete,
            errorMessage: ERROR_MESSAGES.delete,
            onSuccess: () => loadUsers(currentPage),
        });
    } catch (error) {
        console.error(error);
    }
}

resetButton?.addEventListener("click", () => {
    if (searchInput) {
        searchInput.value = "";
    }

    searchTerm = "";
    currentPage = 1;
    loadUsers();
});

loadUsers();
