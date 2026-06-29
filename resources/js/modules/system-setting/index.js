import { createIcons, icons } from "lucide";
import { get, patch } from "../../api/api";
import { setButtonLoading, showToast,
    reportRequestFailure, LOADING_MESSAGES, runAction, SUCCESS_MESSAGES, ERROR_MESSAGES, clearFieldErrors, handleValidationError } from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const form = document.getElementById("systemSettingForm");
const saveActions = document.getElementById("saveActions");
const submitButton = form?.querySelector("button[type=submit]");

let canUpdate = false;

form?.addEventListener("submit", submitForm);

async function loadSettings() {
    try {
        const response = await get("/api/system-settings");
        const settings = response.data;

        canUpdate = Boolean(settings.can?.update);

        if (canUpdate) {
            saveActions?.classList.remove("hidden");
        }

        fillGeneral(settings.general ?? {});
        fillLetter(settings.letter ?? {});
        fillUpload(settings.upload ?? {});
        fillDashboard(settings.dashboard ?? {});
        fillReport(settings.report ?? {});
        fillActivityLog(settings.activity_log ?? {});
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat pengaturan sistem.");
    }
}

function fillGeneral(general) {
    setValue("app_name", general.app_name);
    setValue("institution_name", general.institution_name);
    setValue("institution_short_name", general.institution_short_name);
    setValue("email", general.email);
    setValue("phone", general.phone);
    setValue("website", general.website);
    setValue("address", general.address);
    setValue("city", general.city);
    setValue("postal_code", general.postal_code);
    setValue("copyright", general.copyright);
    setValue("timezone", general.timezone);
    setValue("locale", general.locale);
    setChecked("dark_mode_default", general.dark_mode_default);
    setChecked("is_active", general.is_active);
}

function fillLetter(letter) {
    setValue("letter_prefix", letter.letter_prefix);
    setValue("active_year", letter.active_year);
    setValue("letter_start_number", letter.letter_start_number);
    setValue("letter_number_template", letter.letter_number_template);
    setValue("default_letter_type", letter.default_letter_type);
    setValue("default_letter_priority", letter.default_letter_priority);
    setValue("head_of_agency", letter.head_of_agency);
    setValue("head_position", letter.head_position);
    setValue("head_nip", letter.head_nip);
}

function fillUpload(upload) {
    setValue("max_upload_size_kb", upload.max_upload_size_kb);
}

function fillDashboard(dashboard) {
    setValue("dashboard_default_period_days", dashboard.dashboard_default_period_days);
    setValue("dashboard_recent_activity_limit", dashboard.dashboard_recent_activity_limit);
    setValue("dashboard_table_row_limit", dashboard.dashboard_table_row_limit);
}

function fillReport(report) {
    setValue("report_signatory_name", report.report_signatory_name);
    setValue("report_signatory_position", report.report_signatory_position);
    setValue("report_footer", report.report_footer);
}

function fillActivityLog(activityLog) {
    setValue("activity_log_retention_days", activityLog.activity_log_retention_days);
    setValue("activity_log_max_export", activityLog.activity_log_max_export);
    setChecked("activity_log_audit_enabled", activityLog.activity_log_audit_enabled);
}

function setValue(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.value = value ?? "";
    }
}

function setChecked(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.checked = Boolean(value);
    }
}

async function submitForm(event) {
    event.preventDefault();

    if (!canUpdate) {
        showToast("warning", "Anda tidak memiliki izin untuk mengubah pengaturan.");

        return;
    }

    const data = {
        app_name: getValue("app_name"),
        institution_name: getValue("institution_name"),
        institution_short_name: getValue("institution_short_name"),
        email: getValue("email"),
        phone: getValue("phone"),
        website: getValue("website"),
        address: getValue("address"),
        city: getValue("city"),
        postal_code: getValue("postal_code"),
        copyright: getValue("copyright"),
        timezone: getValue("timezone"),
        locale: getValue("locale"),
        dark_mode_default: getChecked("dark_mode_default"),
        is_active: getChecked("is_active"),
        letter_prefix: getValue("letter_prefix"),
        active_year: Number(getValue("active_year")) || null,
        letter_start_number: Number(getValue("letter_start_number")),
        letter_number_template: getValue("letter_number_template"),
        default_letter_type: getValue("default_letter_type"),
        default_letter_priority: getValue("default_letter_priority"),
        head_of_agency: getValue("head_of_agency"),
        head_position: getValue("head_position"),
        head_nip: getValue("head_nip"),
        max_upload_size_kb: Number(getValue("max_upload_size_kb")),
        allowed_upload_mime_types: ["pdf"],
        dashboard_default_period_days: Number(getValue("dashboard_default_period_days")),
        dashboard_recent_activity_limit: Number(getValue("dashboard_recent_activity_limit")),
        dashboard_table_row_limit: Number(getValue("dashboard_table_row_limit")),
        report_signatory_name: getValue("report_signatory_name"),
        report_signatory_position: getValue("report_signatory_position"),
        report_footer: getValue("report_footer"),
        activity_log_retention_days: Number(getValue("activity_log_retention_days")) || null,
        activity_log_max_export: Number(getValue("activity_log_max_export")),
        activity_log_audit_enabled: getChecked("activity_log_audit_enabled"),
    };

    clearFieldErrors(form);
    setButtonLoading(submitButton, true, "Menyimpan...");

    try {
        await runAction({
            loadingMessage: LOADING_MESSAGES.save,
            action: () => patch("/api/system-settings", data),
            successMessage: "Pengaturan sistem berhasil disimpan.",
            errorMessage: "Gagal menyimpan pengaturan sistem.",
            onSuccess: () => loadSettings(),
        });
    } catch (error) {
        console.error(error);

        if (handleValidationError(form, error)) {
            return;
        }
    } finally {
        setButtonLoading(submitButton, false);
    }
}

function getValue(id) {
    return document.getElementById(id)?.value?.trim() ?? "";
}

function getChecked(id) {
    return Boolean(document.getElementById(id)?.checked);
}

loadSettings();
