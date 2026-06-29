@props([
    'title' => null,
])

<aside class="app-sidebar app-sidebar--desktop h-full" aria-label="Sidebar navigasi">
    <div class="app-sidebar__inner">
        <a href="{{ route('dashboard') }}" class="app-sidebar__brand">
            <img
                src="{{ asset('assets/images/logo-bapperida.png') }}"
                alt="Logo BAPPERIDA"
                class="app-sidebar__logo"
            />
            <span class="app-sidebar__app-name app-sidebar__nav-label">{{ config('app.name') }}</span>
        </a>

        <x-layout.sidebar />
    </div>
</aside>
