<div id="dashboardPage" class="animate-fade-in space-y-6" data-dashboard-role="{{ $role }}">
    <header class="dashboard-page-header">
        <div class="dashboard-page-header__main">
            <div class="dashboard-page-header__content">
                <h2 class="dashboard-page-header__title">
                    {{ $title }}
                </h2>

                @if ($description)
                    <p class="dashboard-page-header__description">
                        {{ $description }}
                    </p>
                @endif
            </div>

            <div
                class="dashboard-page-header__date-badge dashboard-page-header__date--desktop"
            >
                <i data-lucide="calendar-days" class="dashboard-page-header__date-icon" aria-hidden="true"></i>
                <div class="dashboard-page-header__date-text">
                    <span class="dashboard-page-header__date-label">Hari ini</span>
                    <span id="dashboardCurrentDate" class="dashboard-page-header__date-value">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                </div>
            </div>

            <div
                class="dashboard-page-header__date-badge dashboard-page-header__date--mobile"
            >
                <i data-lucide="calendar-days" class="dashboard-page-header__date-icon" aria-hidden="true"></i>
                <div class="dashboard-page-header__date-text">
                    <span class="dashboard-page-header__date-label">Hari ini</span>
                    <span id="dashboardCurrentDateMobile" class="dashboard-page-header__date-value">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                </div>
            </div>
        </div>
    </header>

    <div
        id="dashboardNotificationBanner"
        class="hidden rounded-xl border border-gold-200 bg-gold-50 px-4 py-3 text-sm text-charcoal-800 dark:border-gold-800/50 dark:bg-gold-950/30 dark:text-slate-200"
        role="status"
    ></div>

    <div id="dashboardWidgets" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"></div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <p class="text-sm text-charcoal-600 dark:text-slate-400">
            Data terbaru:
            <span id="dashboardLastUpdated" class="font-medium text-charcoal-800 dark:text-slate-200">-</span>
        </p>
        <div
            id="dashboardStatusMessage"
            class="hidden rounded-xl border border-ocean-200 bg-ocean-50 px-4 py-3 text-sm text-ocean-900 dark:border-ocean-800 dark:bg-ocean-950/40 dark:text-ocean-100"
        ></div>
    </div>

    @if ($showFilters ?? false)
        <x-panel
            title="Filter Grafik"
            description="Sesuaikan periode dan pengelompokan data pada grafik."
        >
            <x-slot:header>
                <button
                    type="button"
                    id="dashboardResetButton"
                    class="app-icon-action app-icon-action--on-panel"
                    data-app-tooltip="Atur Ulang Filter"
                    aria-label="Atur Ulang Filter"
                >
                    <i data-lucide="rotate-ccw" class="h-4 w-4" aria-hidden="true"></i>
                </button>
            </x-slot:header>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @if ($showDepartmentFilter ?? true)
                    <x-select
                        id="dashboardDepartmentFilter"
                        name="department"
                        label="Bidang"
                        :options="$departments->pluck('name', 'id')->toArray()"
                        placeholder="Semua Bidang"
                    />
                @endif
                <x-select
                    id="dashboardGranularityFilter"
                    name="granularity"
                    label="Kelompokkan"
                    :options="[
                        'day' => 'Per Hari',
                        'week' => 'Per Minggu',
                        'month' => 'Per Bulan',
                        'year' => 'Per Tahun',
                    ]"
                    value="month"
                />
                <x-input id="dashboardPeriodStart" name="period_start" label="Mulai" type="date" />
                <x-input id="dashboardPeriodEnd" name="period_end" label="Selesai" type="date" />
            </div>
        </x-panel>
    @endif

    <div id="dashboardChartsPrimary" class="grid gap-4 xl:grid-cols-2"></div>

    <div id="dashboardChartsSecondary" class="grid gap-4 xl:grid-cols-[2fr_1fr]"></div>

    <div id="dashboardTables" class="grid gap-4 xl:grid-cols-[2fr_1fr]"></div>

    <div
        id="dashboardError"
        class="hidden rounded-xl border border-maroon-200 bg-maroon-50 p-4 text-sm text-maroon-800 dark:border-maroon-900/50 dark:bg-maroon-950/40 dark:text-maroon-200"
        role="alert"
    ></div>
</div>
