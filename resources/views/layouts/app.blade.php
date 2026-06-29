@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title ?? null])
</head>
<body class="antialiased">
    <a href="#app-main-content" class="app-skip-link">Langsung ke konten utama</a>
    <div
        id="app-shell"
        class="app-shell"
        data-sidebar-collapsed="false"
    >
        {{-- Mobile drawer --}}
        <div class="app-mobile-drawer lg:hidden" data-mobile-drawer aria-hidden="true">
            <div class="app-mobile-drawer__backdrop" data-mobile-drawer-backdrop></div>
            <aside class="app-sidebar h-full shadow-xl" aria-label="Menu navigasi">
                <div class="app-sidebar__inner">
                    <a href="{{ route('dashboard') }}" class="app-sidebar__brand">
                        <img
                            src="{{ asset('assets/images/logo-bapperida.png') }}"
                            alt="Logo BAPPERIDA"
                            class="app-sidebar__logo"
                        />
                        <span class="app-sidebar__app-name">{{ config('app.name') }}</span>
                    </a>
                    <x-layout.sidebar />
                </div>
            </aside>
        </div>

        <x-layout.sidebar-shell />

        <div class="app-main-column">
            <x-layout.header :title="$title ?? null" />

            <main class="app-content animate-fade-in" id="app-main-content" role="main">
                @yield('content')
                @isset($slot)
                    {{ $slot }}
                @endisset
            </main>

            <x-layout.footer />
        </div>
    </div>

    <x-feedback.root />

    @fluxScripts
    @stack('scripts')
</body>
</html>
