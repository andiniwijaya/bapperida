import { feedbackManager } from "./modules/feedback/feedback-manager";
import { ERROR_MESSAGES, LOADING_MESSAGES, SUCCESS_MESSAGES } from "./modules/feedback/presets";
import { createIcons, icons } from "lucide";
import { initFormUxScope, initModalFormUx } from "./modules/form/form-ux";
import { registerDsDropdown } from "./modules/form/ds-dropdown";
import { initEmptyStateActions } from "./modules/form/empty-state";
import { initAppShell } from "./modules/layout/app-shell";
import { initDashboard } from "./modules/dashboard/index";

registerDsDropdown();

function bootApp() {
    feedbackManager.init();
    createIcons({ icons });
    initFormUxScope();
    initEmptyStateActions();

    document.querySelectorAll("[data-form-modal]").forEach((modal) => {
        initModalFormUx(modal);
    });

    if (document.getElementById("app-shell")) {
        initAppShell();
    }

    if (document.getElementById("dashboardPage")) {
        initDashboard();
    }
}

document.addEventListener("DOMContentLoaded", bootApp);
document.addEventListener("livewire:navigated", bootApp);

export { feedbackManager };
