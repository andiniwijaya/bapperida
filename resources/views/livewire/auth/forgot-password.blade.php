<x-layouts::auth :title="'Lupa Kata Sandi'">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="'Lupa Kata Sandi'"
            :description="'Masukkan alamat email Anda untuk menerima tautan reset kata sandi.'"
        />
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('password.email') }}"
            class="flex flex-col gap-6 app-form-ux"
            data-form-ux
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            <x-input
                name="email"
                label="Alamat Email"
                type="email"
                required
                autofocus
                placeholder="Masukkan alamat email..."
                data-validate="email"
                icon="mail"
                :error="isset($errors) ? $errors->first('email') : null"
            />
            <x-button type="submit" class="w-full justify-center" data-test="email-password-reset-link-button" x-bind:disabled="submitting">
                <span x-show="!submitting">Kirim Tautan Reset Kata Sandi</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    Memproses...
                </span>
            </x-button>
        </form>

        <div class="text-center text-sm text-charcoal-600 dark:text-slate-400">
            <span>Atau kembali ke</span>
            <a
                href="{{ route('login') }}"
                class="ml-1 font-medium text-ocean-700 hover:text-gold-600 dark:text-gold-400 dark:hover:text-gold-300"
            >
                halaman masuk
            </a>
        </div>
    </div>
</x-layouts::auth>
