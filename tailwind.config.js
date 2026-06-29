/** @type {import('tailwindcss').Config} */
export default {
    theme: {
        extend: {
            colors: {
                // Navy-Gold Theme
                navy: {
                    50: "#f0f2f8",
                    100: "#e1e5f1",
                    200: "#c3cbe3",
                    300: "#a5b1d5",
                    400: "#8797c7",
                    500: "#697db9",
                    600: "#4a5fa0",
                    700: "#2b4188",
                    800: "#0a1633",
                    900: "#050b1a",
                    950: "#020609",
                },
                gold: {
                    50: "#fefde8",
                    100: "#fffbcc",
                    200: "#fff599",
                    300: "#ffef66",
                    400: "#ffe933",
                    500: "#EAB308",
                    600: "#ca9004",
                    700: "#9f6c04",
                    800: "#7a5008",
                    900: "#63410a",
                    950: "#3a2500",
                },
                slate: {
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                    300: "#cbd5e1",
                    400: "#94a3b8",
                    500: "#64748b",
                    600: "#475569",
                    700: "#334155",
                    800: "#1e293b",
                    900: "#0f172a",
                },
            },
            backgroundColor: {
                dark: "#050b1a",
                "dark-secondary": "#0a1633",
            },
            textColor: {
                light: "#f1f5f9", // slate-100
            },
            borderColor: {
                dark: "#1e293b",
            },
            ringColor: {
                gold: "#EAB308",
            },
            animation: {
                "spin-slow": "spin 2s linear infinite",
                "pulse-slow": "pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite",
            },
        },
    },
};
