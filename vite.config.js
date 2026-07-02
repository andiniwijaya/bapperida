import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/modules/landing/index.js",
                "resources/js/modules/auth/index.js",
                "resources/js/modules/letter-number-registration/index.js",
                "resources/js/modules/letter-number-registration/create.js",
                "resources/js/modules/letter-number-registration/edit.js",
                "resources/js/modules/letter-number-registration/show.js",
                "resources/js/modules/incoming-letter/index.js",
                "resources/js/modules/incoming-letter/create.js",
                "resources/js/modules/incoming-letter/edit.js",
                "resources/js/modules/incoming-letter/show.js",
                "resources/js/modules/outgoing-letter/index.js",
                "resources/js/modules/outgoing-letter/create.js",
                "resources/js/modules/outgoing-letter/edit.js",
                "resources/js/modules/outgoing-letter/show.js",
                "resources/js/modules/report/index.js",
                "resources/js/modules/user/index.js",
                "resources/js/modules/user/create.js",
                "resources/js/modules/user/show.js",
                "resources/js/modules/user/edit.js",
                "resources/js/modules/department/index.js",
                "resources/js/modules/department/create.js",
                "resources/js/modules/department/edit.js",
                "resources/js/modules/registration-request/index.js",
                "resources/js/modules/activity-log/index.js",
                "resources/js/modules/activity-log/show.js",
                "resources/js/modules/system-setting/index.js",
            ],
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
