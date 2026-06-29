@component('mail::message')
<div style="text-align: center; margin-bottom: 24px;">
    <img src="{{ $kabBandungLogoUrl }}" alt="Logo Kabupaten Bandung" width="72" style="display: inline-block; margin: 0 8px;">
    <img src="{{ $bapperidaLogoUrl }}" alt="Logo BAPPERIDA" width="72" style="display: inline-block; margin: 0 8px;">
</div>

# {{ $appName }}

**{{ $institutionName }}**

@if ($scenario === 'reset')
Halo, **{{ $notifiable->name }}**!

Password akun Anda telah direset oleh administrator. Untuk melanjutkan, buat kata sandi baru melalui tombol di bawah ini.
@elseif ($scenario === 'resent')
Halo, **{{ $notifiable->name }}**!

Berikut adalah kirim ulang email untuk mengatur kata sandi akun Anda.
@else
Halo, **{{ $notifiable->name }}**!

Akun Anda telah berhasil dibuat pada {{ $appName }}.
@endif

**Role:** {{ $roleLabel }}

Username: {{ $notifiable->username }}

Email: {{ $notifiable->email }}

Silakan atur kata sandi Anda melalui tombol di bawah ini. Kata sandi tidak dikirim melalui email demi keamanan akun Anda.

@component('mail::button', ['url' => $passwordSetupUrl, 'color' => 'primary'])
ATUR KATA SANDI
@endcomponent

Jika tombol tidak berfungsi, salin dan buka tautan berikut di browser Anda:

{{ $passwordSetupUrl }}

Terima kasih,<br>
{{ $appName }}
@endcomponent
