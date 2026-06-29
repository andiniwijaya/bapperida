<x-layouts::auth :title="'Daftar Akun'">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="'Daftar akun baru'"
            :description="'Lengkapi data berikut untuk mengajukan akun. Akun Anda akan ditinjau dan disetujui terlebih dahulu oleh Super Admin sebelum dapat digunakan.'"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('register.store') }}"
            class="flex flex-col gap-5 app-form-ux"
            data-form-ux
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf

            <x-input
                name="name"
                label="Nama Lengkap"
                value="{{ old('name') }}"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap"
                icon="user"
                :error="isset($errors) ? $errors->first('name') : null"
            />

            <x-input
                name="username"
                label="Nama Pengguna"
                value="{{ old('username') }}"
                type="text"
                required
                autocomplete="username"
                placeholder="Masukkan nama pengguna"
                icon="at-sign"
                :error="isset($errors) ? $errors->first('username') : null"
            />

            <x-input
                name="email"
                label="Email"
                value="{{ old('email') }}"
                type="email"
                required
                autocomplete="email"
                placeholder="contoh@gmail.com"
                icon="mail"
                :error="isset($errors) ? $errors->first('email') : null"
            />

            <x-select
                name="department_id"
                label="Bidang"
                :value="old('department_id')"
                :options="$departments"
                placeholder="Pilih bidang..."
                searchable
                icon="building-2"
                required
                :error="isset($errors) ? $errors->first('department_id') : null"
            />

            <x-input
                name="password"
                label="Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Masukkan kata sandi"
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

            <x-alert type="info">
                Setelah registrasi berhasil, akun Anda akan berstatus
                <strong>Menunggu Persetujuan</strong>.
                Super Admin akan melakukan verifikasi sebelum akun dapat digunakan.
            </x-alert>

            <x-button type="submit" class="w-full justify-center" x-bind:disabled="submitting">
                <span x-show="!submitting">Daftar Akun</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    Memproses...
                </span>
            </x-button>
        </form>

        <div class="text-center text-sm text-charcoal-600 dark:text-slate-400">
            Sudah memiliki akun?
            <a
                href="{{ route('login') }}"
                class="font-medium text-ocean-700 hover:text-gold-600 dark:text-gold-400 dark:hover:text-gold-300"
            >
                Masuk di sini
            </a>
        </div>
    </div>
</x-layouts::auth>
