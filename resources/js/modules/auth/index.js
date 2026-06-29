import { createIcons, icons } from "lucide";
import { SESSION_EXPIRED_STORAGE_KEY } from "../../api/api";
import { feedbackManager } from "../feedback/feedback-manager";
import { initFormUxScope } from "../form/form-ux";
import { registerDsDropdown } from "../form/ds-dropdown";

registerDsDropdown();

const SESSION_EXPIRED_MESSAGE = "Sesi Anda telah berakhir. Silakan masuk kembali.";

export function initAuthPage() {
    feedbackManager.init();
    createIcons({ icons, selector: "i[data-lucide]" });
    initFormUxScope();
    showSessionExpiredNotice();
}

function showSessionExpiredNotice() {
    const storedMessage = sessionStorage.getItem(SESSION_EXPIRED_STORAGE_KEY);

    if (storedMessage) {
        sessionStorage.removeItem(SESSION_EXPIRED_STORAGE_KEY);
        feedbackManager.showResult("warning", storedMessage);

        return;
    }

    if (document.querySelector("[data-session-expired]")) {
        feedbackManager.showResult("warning", SESSION_EXPIRED_MESSAGE);
    }
}

initAuthPage();
