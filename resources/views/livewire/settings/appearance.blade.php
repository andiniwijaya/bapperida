<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Pengaturan Tampilan</flux:heading>

    <x-settings.layout :heading="'Tampilan'" :subheading="' Sesuaikan tampilan aplikasi untuk akun Anda'">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">Terang</flux:radio>
            <flux:radio value="dark" icon="moon">Gelap</flux:radio>
            <flux:radio value="system" icon="computer-desktop">Sistem</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
