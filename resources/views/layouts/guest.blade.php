@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
        @vite('resources/js/modules/landing/index.js')
    </head>
    <body class="min-h-svh flex flex-col">
        <x-landing.navbar />

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-landing.footer />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
