@props([
    'title' => null,
])

<header class="app-header" role="banner">
    <div class="app-header__inner">
        <button
            type="button"
            class="app-header__toggle"
            data-sidebar-toggle
            data-app-tooltip="Menu"
            aria-label="Buka atau tutup menu samping"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <h1 class="app-header__title truncate">
            @hasSection('title')
                @yield('title')
            @else
                {{ $title ?? config('app.name') }}
            @endif
        </h1>

        <div class="app-header__actions">
            <button
                type="button"
                class="app-header__toggle"
                data-theme-toggle
                data-app-tooltip="Ubah Tema"
                aria-label="Ubah tema tampilan"
            >
                <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>

            <div class="relative" data-notification-root>
                <button
                    type="button"
                    class="app-header__toggle relative"
                    data-notification-toggle
                    data-app-tooltip="Notifikasi"
                    aria-label="Notifikasi"
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="app-notification__badge hidden" data-notification-badge>0</span>
                </button>

                <div
                    class="app-notification__panel hidden"
                    data-notification-panel
                    role="region"
                    aria-label="Notifikasi"
                >
                    <div class="app-notification__panel-header flex items-center justify-between px-4 py-3">
                        <p class="app-notification__panel-title text-sm font-semibold">Notifikasi</p>
                        <button
                            type="button"
                            class="app-notification__panel-action text-xs font-medium"
                            data-notification-mark-all
                        >
                            Tandai semua dibaca
                        </button>
                    </div>
                    <div class="max-h-72 overflow-y-auto" data-notification-list>
                        <p class="app-notification__panel-empty px-4 py-6 text-center text-sm">
                            Memuat notifikasi...
                        </p>
                    </div>
                    <div class="app-notification__panel-footer px-4 py-3">
                        <a
                            href="{{ route('dashboard') }}"
                            class="app-notification__panel-action text-sm font-medium"
                        >
                            Lihat Semua
                        </a>
                    </div>
                </div>
            </div>

            <div class="relative" data-user-menu-root>
                <button
                    type="button"
                    class="app-header__user-trigger"
                    data-user-menu-toggle
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ocean-700 text-xs font-semibold text-white">
                        {{ auth()->user()?->initials() }}
                    </span>
                    <span class="app-header__user-trigger-name hidden max-w-[10rem] truncate md:inline">{{ auth()->user()?->name }}</span>
                    <svg class="app-header__user-trigger-chevron h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    class="app-header__user-panel absolute right-0 top-[calc(100%+0.5rem)] z-50 hidden w-52 rounded-xl py-1 shadow-lg"
                    data-user-menu-panel
                    role="menu"
                >
                    <a
                        href="{{ route('profile.edit') }}"
                        class="app-header__user-menu-item"
                        role="menuitem"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Profil Saya</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="app-header__user-menu-item w-full"
                            role="menuitem"
                            data-test="logout-button"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
