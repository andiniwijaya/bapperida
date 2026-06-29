<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Pengaturan Profil</flux:heading>

    <x-settings.layout :heading="'Profil Saya'" :subheading="'Perbarui nama dan alamat email Anda'">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" data-form-ux>
            <flux:input wire:model="name" :label="'Nama Lengkap'" type="text" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap..." />

            <div>
                <flux:input wire:model="email" :label="'Alamat Email'" type="email" required autocomplete="email" placeholder="Masukkan alamat email..." />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            Alamat email Anda belum diverifikasi.

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                Klik di sini untuk kirim ulang email verifikasi.
                            </flux:link>
                        </flux:text>

                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">Simpan</flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
