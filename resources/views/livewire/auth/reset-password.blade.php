<x-layouts::auth :title="'Atur Ulang Kata Sandi'">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="'Atur Ulang Kata Sandi'"
            :description="'Masukkan kata sandi baru Anda di bawah ini.'"
        />
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('password.update') }}"
            class="flex flex-col gap-6 app-form-ux"
            data-form-ux
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">
            <x-input
                name="email"
                value="{{ request('email') }}"
                label="Alamat Email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="Masukkan alamat email..."
                data-validate="email"
                icon="mail"
                :error="isset($errors) ? $errors->first('email') : null"
            />
            <x-input
                name="password"
                label="Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Masukkan kata sandi baru"
                icon="lock"
                :error="isset($errors) ? $errors->first('password') : null"
            />
            <x-input
                name="password_confirmation"
                label="Konfirmasi Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Masukkan ulang kata sandi"
                icon="lock-keyhole"
            />
            <x-button type="submit" class="w-full justify-center" data-test="reset-password-button" x-bind:disabled="submitting">
                <span x-show="!submitting">Atur Ulang Kata Sandi</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    Memproses...
                </span>
            </x-button>
        </form>
    </div>
</x-layouts::auth>
