import { createIcons, icons } from "lucide";
import { get } from "../../api/api";
import { formatDateTime, showToast, reportRequestFailure } from "../admin/helper";

createIcons({ icons, selector: "i[data-lucide]" });

const activityLogId = document.getElementById("activity_log_id")?.value;
const detailRoot = document.getElementById("activityLogDetail");

async function loadDetail() {
    if (!activityLogId || !detailRoot) {
        return;
    }

    try {
        const response = await get(`/api/activity-logs/${activityLogId}`);
        const log = response.data;

        detailRoot.innerHTML = `
            <dl class="grid gap-4 md:grid-cols-2">
                ${field("Waktu", formatDateTime(log.logged_at ?? log.created_at))}
                ${field("User", log.user?.name ?? "-")}
                ${field("Email", log.user?.email ?? "-")}
                ${field("Role", log.user_role ?? "-")}
                ${field("Bidang", log.department?.name ?? "-")}
                ${field("Modul", log.module)}
                ${field("Aksi", log.action)}
                ${field("Entitas", `${log.entity_type ?? "-"} #${log.entity_id ?? "-"}`)}
                ${field("Deskripsi", log.description ?? "-")}
                ${field("URL", log.url ?? "-")}
                ${field("Method", log.method ?? "-")}
                ${field("IP Address", log.ip_address ?? "-")}
                ${field("Browser", log.browser ?? "-")}
                ${field("Platform", log.platform ?? "-")}
                ${field("Device", log.device ?? "-")}
            </dl>
            <div class="mt-6">
                <h3 class="mb-2 text-sm font-semibold ds-detail-label">Properties</h3>
                <pre class="overflow-x-auto rounded-lg p-4 text-xs">${JSON.stringify(log.properties ?? {}, null, 2)}</pre>
            </div>
        `;
    } catch (error) {
        reportRequestFailure(error, "Gagal memuat detail log aktivitas.");
    }
}

function field(label, value) {
    return `
        <div>
            <dt class="ds-detail-label">${label}</dt>
            <dd class="ds-detail-value">${value}</dd>
        </div>
    `;
}

loadDetail();
