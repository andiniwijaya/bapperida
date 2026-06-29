import { LOADING_MESSAGES } from "./presets";

const NAV_PENDING_KEY = "app.feedback.navPending";

const PROGRESS_DURATION_MS = 900;
const SUCCESS_AUTO_CLOSE_MS = 2500;
const FADE_DURATION_MS = 280;

const RESULT_CONFIG = {
    success: {
        title: "Berhasil",
        cardClass: "is-success",
        icon: "check",
    },
    error: {
        title: "Gagal",
        cardClass: "is-error",
        icon: "x",
    },
    warning: {
        title: "Perhatian",
        cardClass: "is-warning",
        icon: "alert",
    },
    info: {
        title: "Informasi",
        cardClass: "is-info",
        icon: "info",
    },
};

/**
 * Global feedback manager — single popup for loading, results, and confirmations.
 */
class FeedbackManager {
    #root = null;
    #card = null;
    #loadingView = null;
    #resultView = null;
    #confirmView = null;
    #loadingMessage = null;
    #progressBar = null;
    #resultIcon = null;
    #resultTitle = null;
    #resultMessage = null;
    #resultCloseButton = null;
    #confirmTitle = null;
    #confirmMessage = null;
    #confirmInput = null;
    #confirmInputWrap = null;
    #confirmCancelButton = null;
    #confirmSubmitButton = null;
    #autoCloseTimer = null;
    #confirmResolver = null;
    #resultCloseResolver = null;
    #initialized = false;

    init() {
        if (this.#initialized) {
            return;
        }

        this.#root = document.getElementById("app-feedback-root");

        if (!this.#root) {
            return;
        }

        this.#card = this.#root.querySelector("[data-feedback-card]");
        this.#loadingView = this.#root.querySelector("[data-feedback-loading]");
        this.#resultView = this.#root.querySelector("[data-feedback-result]");
        this.#confirmView = this.#root.querySelector("[data-feedback-confirm]");
        this.#loadingMessage = this.#root.querySelector("[data-feedback-loading-message]");
        this.#progressBar = this.#root.querySelector("[data-feedback-progress-bar]");
        this.#resultIcon = this.#root.querySelector("[data-feedback-result-icon]");
        this.#resultTitle = this.#root.querySelector("[data-feedback-result-title]");
        this.#resultMessage = this.#root.querySelector("[data-feedback-result-message]");
        this.#resultCloseButton = this.#root.querySelector("[data-feedback-result-close]");
        this.#confirmTitle = this.#root.querySelector("[data-feedback-confirm-title]");
        this.#confirmMessage = this.#root.querySelector("[data-feedback-confirm-message]");
        this.#confirmInput = this.#root.querySelector("[data-feedback-confirm-input]");
        this.#confirmInputWrap = this.#root.querySelector("[data-feedback-confirm-input-wrap]");
        this.#confirmCancelButton = this.#root.querySelector("[data-feedback-confirm-cancel]");
        this.#confirmSubmitButton = this.#root.querySelector("[data-feedback-confirm-submit]");

        this.#resultCloseButton?.addEventListener("click", () => this.#closeResult());
        this.#confirmCancelButton?.addEventListener("click", () => this.#resolveConfirm(false));
        this.#confirmSubmitButton?.addEventListener("click", () => this.#handleConfirmSubmit());

        document.addEventListener("keydown", (event) => {
            if (event.key !== "Escape" || !this.#isOpen()) {
                return;
            }

            if (!this.#confirmView?.hidden) {
                this.#resolveConfirm(false);
            } else if (!this.#resultView?.hidden && this.#resultCloseButton && !this.#resultCloseButton.hidden) {
                this.#closeResult();
            }
        });

        window.addEventListener("pageshow", () => {
            if (sessionStorage.getItem(NAV_PENDING_KEY) !== "1") {
                this.#hide();
            }
        });

        if (sessionStorage.getItem(NAV_PENDING_KEY) === "1" && document.getElementById("dashboardPage")) {
            this.#showLoadingShell(LOADING_MESSAGES.page, { hideMessage: true });
            this.#animateProgress();
        } else {
            sessionStorage.removeItem(NAV_PENDING_KEY);
            this.#hide();
        }

        this.#initialized = true;
    }

    showPageLoading(message = LOADING_MESSAGES.page) {
        sessionStorage.setItem(NAV_PENDING_KEY, "1");
        this.#showNavigationLoading(message, { hideMessage: true });
    }

    showLoading(message = LOADING_MESSAGES.wait) {
        this.#showNavigationLoading(message);
    }

    hide() {
        sessionStorage.removeItem(NAV_PENDING_KEY);
        this.#hide();
    }

    #showNavigationLoading(message, options = {}) {
        this.init();

        if (!this.#root) {
            return;
        }

        this.#clearTimers();
        this.#showLoadingShell(message, options);
        this.#animateProgress();
    }

    /**
     * @param {{ loadingMessage?: string, action: () => Promise<unknown>, successMessage?: string, successTitle?: string, errorMessage?: string, errorTitle?: string, onSuccess?: (result: unknown) => void | Promise<void> }} options
     */
    async run(options) {
        this.init();

        if (!this.#root) {
            return options.action();
        }

        const loadingMessage = options.loadingMessage ?? LOADING_MESSAGES.wait;

        this.#clearTimers();
        this.#showLoadingShell(loadingMessage);

        const progressPromise = this.#animateProgress();

        try {
            const [result] = await Promise.all([options.action(), progressPromise]);

            if (options.successMessage) {
                await this.#showResultState(
                    "success",
                    options.successTitle,
                    options.successMessage,
                    { autoClose: true },
                );
            } else {
                await this.#fadeOut();
            }

            if (options.onSuccess) {
                await options.onSuccess(result);
            }

            return result;
        } catch (error) {
            await progressPromise;

            await this.#showResultState(
                "error",
                options.errorTitle,
                options.errorMessage ?? "Terjadi kesalahan.",
                { autoClose: false },
            );

            throw error;
        }
    }

    /**
     * @param {{ title?: string, message?: string, confirmText?: string, cancelText?: string, variant?: 'danger' | 'primary', requireInput?: boolean, inputLabel?: string, inputPlaceholder?: string }} options
     */
    confirm(options = {}) {
        this.init();

        if (!this.#root) {
            return Promise.resolve(false);
        }

        return new Promise((resolve) => {
            this.#confirmResolver = resolve;
            this.#clearTimers();
            this.#root.classList.remove("is-fading-out");
            this.#root.hidden = false;
            this.#root.setAttribute("aria-hidden", "false");

            this.#loadingView.hidden = true;
            this.#resultView.hidden = true;
            this.#confirmView.hidden = false;

            this.#setCardClass("app-feedback__card is-confirm");

            if (this.#confirmTitle) {
                this.#confirmTitle.textContent = options.title ?? "Apakah Anda yakin?";
            }

            if (this.#confirmMessage) {
                this.#confirmMessage.textContent = options.message ?? "";
                this.#confirmMessage.hidden = !options.message;
            }

            if (this.#confirmSubmitButton) {
                this.#confirmSubmitButton.textContent = options.confirmText ?? "Konfirmasi";
                this.#confirmSubmitButton.classList.toggle(
                    "app-feedback__button--danger",
                    options.variant === "danger",
                );
                this.#confirmSubmitButton.classList.toggle(
                    "app-feedback__button--primary",
                    options.variant !== "danger",
                );
            }

            if (this.#confirmCancelButton) {
                this.#confirmCancelButton.textContent = options.cancelText ?? "Batal";
            }

            if (options.requireInput && this.#confirmInputWrap && this.#confirmInput) {
                this.#confirmInputWrap.hidden = false;
                this.#confirmInput.value = "";
                this.#confirmInput.placeholder = options.inputPlaceholder ?? "";
                const label = this.#confirmInputWrap.querySelector("[data-feedback-confirm-input-label]");

                if (label) {
                    label.textContent = options.inputLabel ?? "Alasan";
                }
            } else if (this.#confirmInputWrap) {
                this.#confirmInputWrap.hidden = true;
            }
        });
    }

  /**
   * Quick result popup without loading phase (replaces legacy toast).
   */
    showResult(type, message, title = null) {
        this.init();

        if (!this.#root || !message) {
            return Promise.resolve();
        }

        this.#clearTimers();
        this.#root.classList.remove("is-fading-out");
        this.#root.hidden = false;
        this.#root.setAttribute("aria-hidden", "false");

        this.#loadingView.hidden = true;
        this.#confirmView.hidden = true;
        this.#resultView.hidden = false;

        const config = RESULT_CONFIG[type] ?? RESULT_CONFIG.info;

        this.#setCardClass(`app-feedback__card ${config.cardClass}`);
        this.#applyResultIcon(config.icon);

        if (this.#resultTitle) {
            this.#resultTitle.textContent = title ?? config.title;
        }

        if (this.#resultMessage) {
            this.#resultMessage.textContent = message;
        }

        const needsManualClose = type === "error";

        if (this.#resultCloseButton) {
            this.#resultCloseButton.hidden = !needsManualClose;
        }

        if (needsManualClose) {
            return new Promise((resolve) => {
                this.#resultCloseResolver = resolve;
            });
        }

        this.#autoCloseTimer = window.setTimeout(() => {
            this.#fadeOut().then(() => {
                if (this.#resultCloseResolver) {
                    this.#resultCloseResolver();
                    this.#resultCloseResolver = null;
                }
            });
        }, SUCCESS_AUTO_CLOSE_MS);

        return Promise.resolve();
    }

    #showLoadingShell(message, options = {}) {
        this.#root.classList.remove("is-fading-out");
        this.#root.hidden = false;
        this.#root.setAttribute("aria-hidden", "false");
        this.#root.setAttribute("aria-busy", "true");

        this.#loadingView.hidden = false;
        this.#resultView.hidden = true;
        this.#confirmView.hidden = true;

        this.#setCardClass("app-feedback__card is-loading");

        if (this.#loadingMessage) {
            if (options.hideMessage) {
                this.#loadingMessage.hidden = true;
                this.#loadingMessage.textContent = "";
            } else {
                this.#loadingMessage.hidden = false;
                this.#loadingMessage.textContent = message;
            }
        }
    }

    async #showResultState(type, title, message, { autoClose }) {
        this.#loadingView.hidden = true;
        this.#confirmView.hidden = true;
        this.#resultView.hidden = false;

        const config = RESULT_CONFIG[type] ?? RESULT_CONFIG.info;

        this.#setCardClass(`app-feedback__card ${config.cardClass}`);
        this.#applyResultIcon(config.icon);

        if (this.#resultTitle) {
            this.#resultTitle.textContent = title ?? config.title;
        }

        if (this.#resultMessage) {
            this.#resultMessage.textContent = message;
        }

        if (this.#resultCloseButton) {
            this.#resultCloseButton.hidden = autoClose;
        }

        if (autoClose) {
            await this.#wait(SUCCESS_AUTO_CLOSE_MS);
            await this.#fadeOut();

            return;
        }

        return new Promise((resolve) => {
            this.#resultCloseResolver = resolve;
        });
    }

    #setCardClass(className) {
        if (this.#card) {
            this.#card.className = className;
        }
    }

    #applyResultIcon(iconType) {
        if (!this.#resultIcon) {
            return;
        }

        const icons = {
            check: `<svg class="app-feedback__result-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            x: `<svg class="app-feedback__result-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            alert: `<svg class="app-feedback__result-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-10a8 8 0 100 16 8 8 0 000-16z"/></svg>`,
            info: `<svg class="app-feedback__result-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
        };

        this.#resultIcon.innerHTML = icons[iconType] ?? icons.info;
    }

    #animateProgress() {
        if (!this.#progressBar) {
            return this.#wait(PROGRESS_DURATION_MS);
        }

        this.#progressBar.style.transition = "none";
        this.#progressBar.style.width = "0%";
        this.#progressBar.offsetHeight;
        this.#progressBar.style.transition = `width ${PROGRESS_DURATION_MS}ms ease-out`;
        this.#progressBar.style.width = "100%";

        return this.#wait(PROGRESS_DURATION_MS);
    }

    #handleConfirmSubmit() {
        if (this.#confirmInputWrap && !this.#confirmInputWrap.hidden) {
            const value = this.#confirmInput?.value?.trim() ?? "";

            if (!value) {
                if (this.#confirmInput) {
                    this.#confirmInput.classList.add("is-invalid");
                }

                return;
            }

            if (this.#confirmInput) {
                this.#confirmInput.classList.remove("is-invalid");
            }

            this.#resolveConfirm(true, value);

            return;
        }

        this.#resolveConfirm(true);
    }

    #resolveConfirm(confirmed, inputValue = null) {
        if (!this.#confirmResolver) {
            return;
        }

        const resolver = this.#confirmResolver;
        this.#confirmResolver = null;

        this.#fadeOut().then(() => {
            resolver(confirmed ? inputValue ?? true : false);
        });
    }

    #closeResult() {
        this.#fadeOut().then(() => {
            if (this.#resultCloseResolver) {
                this.#resultCloseResolver();
                this.#resultCloseResolver = null;
            }
        });
    }

    async #fadeOut() {
        if (!this.#root || this.#root.hidden) {
            return;
        }

        this.#root.classList.add("is-fading-out");
        await this.#wait(FADE_DURATION_MS);
        this.#hide();
    }

    #hide() {
        if (!this.#root) {
            return;
        }

        this.#root.classList.remove("is-fading-out");
        this.#root.hidden = true;
        this.#root.setAttribute("aria-hidden", "true");
    }

    #isOpen() {
        return this.#root && !this.#root.hidden;
    }

    #clearTimers() {
        if (this.#autoCloseTimer) {
            window.clearTimeout(this.#autoCloseTimer);
            this.#autoCloseTimer = null;
        }
    }

    #wait(ms) {
        return new Promise((resolve) => {
            window.setTimeout(resolve, ms);
        });
    }
}

export const feedbackManager = new FeedbackManager();

if (typeof window !== "undefined") {
    window.AppFeedback = feedbackManager;
}
