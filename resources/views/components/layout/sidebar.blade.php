@php
    use App\Models\ActivityLog;
    use App\Models\Dashboard;
    use App\Models\Department;
    use App\Models\IncomingLetter;
    use App\Models\LetterNumberRegistration;
    use App\Models\OutgoingLetter;
    use App\Models\RegistrationRequest;
    use App\Models\Report;
    use App\Models\SystemSetting;
    use App\Models\User;
@endphp

<nav class="app-sidebar__nav" aria-label="Menu utama">
    @can('view', Dashboard::class)
        <p class="app-sidebar__group-title app-sidebar__nav-label">Utama</p>
        <a
            href="{{ route('dashboard') }}"
            class="app-sidebar__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="app-sidebar__nav-label">Beranda</span>
        </a>
    @endcan

    @if (
        Gate::check('viewAny', LetterNumberRegistration::class)
        || Gate::check('viewAny', IncomingLetter::class)
        || Gate::check('viewAny', OutgoingLetter::class)
    )
        <p class="app-sidebar__group-title app-sidebar__nav-label mt-4">Surat & Arsip</p>
    @endif

    @can('viewAny', LetterNumberRegistration::class)
        <a
            href="{{ route('letter-number-registrations.index') }}"
            class="app-sidebar__link {{ request()->routeIs('letter-number-registrations.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="app-sidebar__nav-label">Registrasi Penomoran</span>
        </a>
    @endcan

    @can('viewAny', IncomingLetter::class)
        <a
            href="{{ route('incoming-letters.index') }}"
            class="app-sidebar__link {{ request()->routeIs('incoming-letters.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span class="app-sidebar__nav-label">Surat Masuk</span>
        </a>
    @endcan

    @can('viewAny', OutgoingLetter::class)
        <a
            href="{{ route('outgoing-letters.index') }}"
            class="app-sidebar__link {{ request()->routeIs('outgoing-letters.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            <span class="app-sidebar__nav-label">Surat Keluar</span>
        </a>
    @endcan

    @can('viewAny', Report::class)
        <p class="app-sidebar__group-title app-sidebar__nav-label mt-4">Laporan</p>
        <a
            href="{{ route('reports.index') }}"
            class="app-sidebar__link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="app-sidebar__nav-label">Laporan</span>
        </a>
    @endcan

    @if (
        Gate::check('viewAny', User::class)
        || Gate::check('viewAny', Department::class)
        || Gate::check('viewAny', RegistrationRequest::class)
        || Gate::check('viewAny', ActivityLog::class)
        || Gate::check('viewAny', SystemSetting::class)
    )
        <p class="app-sidebar__group-title app-sidebar__nav-label mt-4">Administrasi</p>
    @endif

    @can('viewAny', User::class)
        <a
            href="{{ route('admin.users.index') }}"
            class="app-sidebar__link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="app-sidebar__nav-label">
                {{ auth()->user()->role === 'admin' ? 'Manajemen Staff' : 'Manajemen Pengguna' }}
            </span>
        </a>
    @endcan

    @can('viewAny', Department::class)
        <a
            href="{{ route('admin.departments.index') }}"
            class="app-sidebar__link {{ request()->routeIs('admin.departments.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="app-sidebar__nav-label">Bidang</span>
        </a>
    @endcan

    @can('viewAny', RegistrationRequest::class)
        <a
            href="{{ route('admin.registration-requests.index') }}"
            class="app-sidebar__link {{ request()->routeIs('admin.registration-requests.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="app-sidebar__nav-label">Persetujuan Registrasi</span>
        </a>
    @endcan

    @can('viewAny', ActivityLog::class)
        <a
            href="{{ route('admin.activity-logs.index') }}"
            class="app-sidebar__link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="app-sidebar__nav-label">Log Aktivitas</span>
        </a>
    @endcan

    @can('viewAny', SystemSetting::class)
        <a
            href="{{ route('admin.system-settings.index') }}"
            class="app-sidebar__link {{ request()->routeIs('admin.system-settings.*') ? 'is-active' : '' }}"
        >
            <svg class="app-sidebar__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="app-sidebar__nav-label">Pengaturan Sistem</span>
        </a>
    @endcan
</nav>
