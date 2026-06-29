@props([
    'title' => 'Terjadi Kesalahan',
    'code' => null,
    'description' => null,
    'icon' => 'alert-circle',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="error-page antialiased">
    <header class="error-page__header" role="banner">
        <div class="error-page__header-inner">
            <a href="{{ route('home') }}" class="error-page__brand">
                <img
                    src="{{ asset('assets/images/logo-kab-bandung.png') }}"
                    alt="Logo Kabupaten Bandung"
                    class="error-page__logo-kab"
                />
                <img
                    src="{{ asset('assets/images/logo-bapperida.png') }}"
                    alt="Logo BAPPERIDA"
                    class="error-page__logo-bapperida"
                />
                <span class="error-page__brand-name">{{ config('app.name') }}</span>
            </a>
        </div>
    </header>

    <main class="error-page__main" id="app-main-content" role="main">
        <div class="error-page__card animate-fade-in">
            <div class="error-page__icon-wrap" aria-hidden="true">
                <i data-lucide="{{ $icon }}" class="error-page__icon"></i>
            </div>

            @if ($code)
                <p class="error-page__code">{{ $code }}</p>
            @endif

            <h1 class="error-page__title">{{ $title }}</h1>

            @if ($description)
                <p class="error-page__description">{{ $description }}</p>
            @endif

            @if (isset($slot) && ! $slot->isEmpty())
                <div class="error-page__extra">
                    {{ $slot }}
                </div>
            @endif

            @if (isset($actions) && ! $actions->isEmpty())
                <div class="error-page__actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </main>

    <footer class="error-page__footer" role="contentinfo">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }} · Versi 1.0.0</p>
    </footer>

    <x-feedback.root />

    @fluxScripts
</body>
</html>
