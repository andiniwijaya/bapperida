<header class="landing-navbar">
    <div class="landing-navbar__inner">
        <a href="{{ route('home') }}" class="landing-navbar__brand">
            <img
                src="{{ asset('assets/images/logo-kab-bandung.png') }}"
                alt="Logo Kabupaten Bandung"
                class="landing-navbar__logo-kab"
            />
            <img
                src="{{ asset('assets/images/logo-bapperida.png') }}"
                alt="Logo BAPPERIDA"
                class="landing-navbar__logo-bapperida"
            />
            <span class="landing-navbar__title">
                {{ config('app.name') }}
            </span>
        </a>

        <nav class="landing-navbar__actions" aria-label="Navigasi utama">
            @auth
                @if (auth()->user()->isActive() && auth()->user()->hasVerifiedEmail())
                    <x-button href="{{ route('dashboard') }}" variant="outline" size="sm">
                        Beranda
                    </x-button>
                @endif
            @else
                <x-button href="{{ route('login') }}" variant="primary" size="sm">
                    Masuk
                </x-button>
                @if (Route::has('register'))
                    <x-button href="{{ route('register') }}" variant="secondary" size="sm">
                        Daftar
                    </x-button>
                @endif
            @endauth
        </nav>
    </div>
</header>
