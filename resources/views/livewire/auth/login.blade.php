<x-layouts::auth :title="'Masuk ke Sistem'">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Masuk ke akun Anda'" :description="'Masukkan email atau nama pengguna beserta kata sandi untuk mengakses Sistem Registrasi Penomoran dan Arsip Surat BAPPERIDA Kabupaten Bandung.'" />
        @if (session(\App\Support\ExceptionResponder::SESSION_EXPIRED_FLASH))
            <span data-session-expired hidden></span>
        @endif
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6 app-form-ux" data-form-ux x-data="{ submitting: false }"
            @submit="submitting = true">
            @csrf
            <x-input name="login" label="Email atau Nama Pengguna" value="{{ old('login') }}" type="text" required
                autofocus autocomplete="username" placeholder="Masukkan email atau nama pengguna" icon="user"
                :error="isset($errors) ? $errors->first('login') : null" />
            <div class="space-y-2">
                <x-input name="password" label="Kata Sandi" type="password" required autocomplete="current-password"
                    placeholder="Masukkan kata sandi..." icon="lock" :error="isset($errors) ? $errors->first('password') : null" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-medium text-ocean-700 hover:text-gold-600 dark:text-gold-400 dark:hover:text-gold-300">
                        Lupa Kata Sandi?
                    </a>
                @endif
            </div>
            <x-checkbox name="remember" label="Ingat Saya" :checked="old('remember')" />
            <x-button type="submit" class="w-full justify-center" data-test="login-button"
                x-bind:disabled="submitting">
                <span x-show="!submitting" x-cloak>Masuk</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    Memproses...
                </span>
            </x-button>
        </form>

        <div class="text-center text-sm text-charcoal-600 dark:text-slate-400">
            <span>Belum memiliki akun?</span>
            <a href="{{ route('register') }}"
                class="ml-1 font-medium text-ocean-700 hover:text-gold-600 dark:text-gold-400 dark:hover:text-gold-300">
                Daftar
            </a>
        </div>
    </div>
</x-layouts::auth>
