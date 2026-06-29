@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
        @vite('resources/js/modules/auth/index.js')
    </head>
    <body class="auth-layout">
        <div class="auth-layout__grid">
            <aside
                class="auth-branding"
                aria-label="Informasi institusi"
                style="--auth-bg-image: url('{{ asset('assets/images/background.png') }}')"
            >
                <div class="auth-branding__background" role="img" aria-label="Latar belakang institusi"></div>
                <div class="auth-branding__overlay" aria-hidden="true"></div>

                <div class="auth-branding__content">
                    <a href="{{ route('home') }}" class="auth-branding__logos">
                        <img
                            src="{{ asset('assets/images/logo-kab-bandung.png') }}"
                            alt="Logo Kabupaten Bandung"
                            class="auth-branding__logo-kab"
                        />
                        <img
                            src="{{ asset('assets/images/logo-bapperida.png') }}"
                            alt="Logo BAPPERIDA"
                            class="auth-branding__logo-bapperida"
                        />
                    </a>

                    <div class="space-y-4">
                        <h2 class="auth-branding__title">
                            Sistem Registrasi Penomoran dan Arsip Surat
                        </h2>
                        <p class="auth-branding__subtitle">
                            Badan Perencanaan Pembangunan Riset dan Inovasi Daerah Kabupaten Bandung
                        </p>
                    </div>
                </div>
            </aside>

            <main class="auth-form-panel">
                <div
                    class="auth-branding auth-branding--mobile"
                    style="--auth-bg-image: url('{{ asset('assets/images/background.png') }}')"
                >
                    <div class="auth-branding__background" role="img" aria-label="Latar belakang institusi"></div>
                    <div class="auth-branding__overlay" aria-hidden="true"></div>

                    <div class="auth-branding__content">
                        <a href="{{ route('home') }}" class="auth-branding__logos">
                            <img
                                src="{{ asset('assets/images/logo-kab-bandung.png') }}"
                                alt="Logo Kabupaten Bandung"
                                class="auth-branding__logo-kab"
                            />
                            <img
                                src="{{ asset('assets/images/logo-bapperida.png') }}"
                                alt="Logo BAPPERIDA"
                                class="auth-branding__logo-bapperida"
                            />
                        </a>

                        <div class="space-y-2">
                            <h2 class="auth-branding__title">
                                Sistem Registrasi Penomoran dan Arsip Surat
                            </h2>
                            <p class="auth-branding__subtitle">
                                Badan Perencanaan Pembangunan Riset dan Inovasi Daerah Kabupaten Bandung
                            </p>
                        </div>
                    </div>
                </div>

                <div class="auth-form-panel__inner">
                    <x-card class="p-6 shadow-md sm:p-8">
                        {{ $slot }}
                    </x-card>

                    <p class="auth-form-panel__copyright">
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </p>
                </div>
            </main>
        </div>

        <x-feedback.root />

        @fluxScripts
    </body>
</html>
