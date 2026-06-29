/**
 * Alpine.js searchable dropdown synced with a native <select> for form compatibility.
 *
 * @param {{ clearable?: boolean, placeholder?: string }} config
 */
export function dsDropdown(config = {}) {
    return {
        open: false,
        query: "",
        highlightedIndex: 0,
        loading: false,
        options: [],
        selectedValue: "",
        selectedLabel: "",
        placeholder: config.placeholder ?? "Pilih...",
        clearable: Boolean(config.clearable),
        disabled: false,
        nativeSelect: null,
        observer: null,

        init() {
            this.nativeSelect = this.$refs.nativeSelect;

            if (!this.nativeSelect) {
                return;
            }

            this.syncFromNative();
            this.nativeSelect.addEventListener("change", () => this.syncFromNative());
            this.nativeSelect.addEventListener("ds-dropdown:sync", () => this.syncFromNative());

            this.observer = new MutationObserver(() => this.syncFromNative());
            this.observer.observe(this.nativeSelect, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ["disabled", "data-loading"],
            });
        },

        destroy() {
            this.observer?.disconnect();
        },

        syncFromNative() {
            if (!this.nativeSelect) {
                return;
            }

            this.disabled = this.nativeSelect.disabled;
            this.loading = this.nativeSelect.dataset.loading === "true";

            this.options = Array.from(this.nativeSelect.options).map((option) => ({
                value: option.value,
                label: option.textContent?.trim() ?? "",
                disabled: option.disabled,
            }));

            this.selectedValue = this.nativeSelect.value;
            const selected = this.options.find((option) => option.value === this.selectedValue);
            this.selectedLabel = selected?.label ?? "";

            if (this.open) {
                this.resetHighlight();
            }
        },

        get selectableOptions() {
            return this.options.filter((option) => option.value !== "");
        },

        get filteredOptions() {
            const options = this.selectableOptions;
            const term = this.query.trim().toLowerCase();

            if (!term) {
                return options;
            }

            return options.filter((option) => option.label.toLowerCase().includes(term));
        },

        get displayLabel() {
            if (this.selectedLabel) {
                return this.selectedLabel;
            }

            return this.placeholder;
        },

        get hasSelection() {
            return this.selectedValue !== "";
        },

        openDropdown() {
            if (this.disabled || this.loading) {
                return;
            }

            this.open = true;
            this.query = "";
            this.resetHighlight();

            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        },

        close() {
            this.open = false;
            this.query = "";
            this.highlightedIndex = 0;
        },

        toggle() {
            if (this.open) {
                this.close();
            } else {
                this.openDropdown();
            }
        },

        resetHighlight() {
            this.highlightedIndex = this.filteredOptions.length > 0 ? 0 : -1;
        },

        selectOption(option) {
            if (!option || option.disabled) {
                return;
            }

            this.nativeSelect.value = option.value;
            this.nativeSelect.dispatchEvent(new Event("change", { bubbles: true }));
            this.syncFromNative();
            this.close();
            this.$refs.trigger?.focus();
        },

        clearSelection(event) {
            event?.stopPropagation();

            if (!this.clearable || this.disabled) {
                return;
            }

            this.nativeSelect.value = "";
            this.nativeSelect.dispatchEvent(new Event("change", { bubbles: true }));
            this.syncFromNative();
            this.close();
        },

        onSearchInput() {
            this.resetHighlight();
        },

        onTriggerKeydown(event) {
            if (this.disabled || this.loading) {
                return;
            }

            if (["ArrowDown", "ArrowUp", "Enter", " "].includes(event.key)) {
                event.preventDefault();
            }

            if (event.key === "ArrowDown" || event.key === "Enter" || event.key === " ") {
                this.openDropdown();
            }
        },

        onSearchKeydown(event) {
            const options = this.filteredOptions;

            if (event.key === "ArrowDown") {
                event.preventDefault();

                if (!this.open) {
                    this.openDropdown();

                    return;
                }

                if (options.length === 0) {
                    return;
                }

                this.highlightedIndex = Math.min(this.highlightedIndex + 1, options.length - 1);
                this.scrollHighlightedIntoView();
            }

            if (event.key === "ArrowUp") {
                event.preventDefault();

                if (options.length === 0) {
                    return;
                }

                this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                this.scrollHighlightedIntoView();
            }

            if (event.key === "Enter") {
                event.preventDefault();

                if (options.length === 0) {
                    return;
                }

                const option = options[this.highlightedIndex] ?? options[0];
                this.selectOption(option);
            }

            if (event.key === "Escape") {
                event.preventDefault();
                event.stopPropagation();
                this.close();
                this.$refs.trigger?.focus();
            }
        },

        scrollHighlightedIntoView() {
            this.$nextTick(() => {
                const optionElement = this.$root.querySelector(
                    `[data-dropdown-option-index="${this.highlightedIndex}"]`,
                );
                optionElement?.scrollIntoView({ block: "nearest" });
            });
        },

        isHighlighted(index) {
            return this.highlightedIndex === index;
        },
    };
}

/**
 * Register the dropdown Alpine component globally.
 */
export function registerDsDropdown() {
    document.addEventListener("alpine:init", () => {
        if (!window.Alpine) {
            return;
        }

        window.Alpine.data("dsDropdown", dsDropdown);
    });
}

/**
 * Sync searchable dropdown UI after programmatic select updates.
 *
 * @param {HTMLSelectElement | null | undefined} selectElement
 */
export function syncDropdownSelect(selectElement) {
    selectElement?.dispatchEvent(new CustomEvent("ds-dropdown:sync", { bubbles: true }));
}

/**
 * Populate a native select and sync any searchable dropdown wrapper.
 *
 * @param {HTMLSelectElement | null | undefined} selectElement
 * @param {Array<{ value: string | number, label: string }>} options
 */
export function populateSelect(selectElement, options) {
    if (!selectElement) {
        return;
    }

    selectElement.innerHTML = options
        .map(
            (option) =>
                `<option value="${String(option.value).replace(/"/g, "&quot;")}">${String(option.label).replace(/</g, "&lt;")}</option>`,
        )
        .join("");

    syncDropdownSelect(selectElement);
}

/**
 * @param {HTMLSelectElement | null | undefined} selectElement
 * @param {string | number | null | undefined} value
 */
export function setSelectValue(selectElement, value) {
    if (!selectElement) {
        return;
    }

    selectElement.value = value === null || value === undefined ? "" : String(value);
    selectElement.dispatchEvent(new Event("change", { bubbles: true }));
    syncDropdownSelect(selectElement);
}

/**
 * @param {HTMLSelectElement | null | undefined} selectElement
 * @param {boolean} isLoading
 */
export function setSelectLoading(selectElement, isLoading) {
    if (!selectElement) {
        return;
    }

    if (isLoading) {
        selectElement.dataset.loading = "true";
    } else {
        delete selectElement.dataset.loading;
    }

    syncDropdownSelect(selectElement);
}
