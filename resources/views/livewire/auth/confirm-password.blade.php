<x-layouts::auth :title="'Konfirmasi Kata Sandi'">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="'Konfirmasi Kata Sandi'"
            :description="'Area aman aplikasi. Konfirmasi kata sandi Anda sebelum melanjutkan.'"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('password.confirm.store') }}"
            class="flex flex-col gap-6"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf

            <x-input
                name="password"
                label="Kata Sandi"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Kata Sandi"
                :error="isset($errors) ? $errors->first('password') : null"
            />

            <x-button type="submit" class="w-full justify-center" data-test="confirm-password-button" x-bind:disabled="submitting">
                <span x-show="!submitting">Konfirmasi</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    Memproses...
                </span>
            </x-button>
        </form>
    </div>
</x-layouts::auth>
