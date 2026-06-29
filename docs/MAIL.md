# Email — BAPPERIDA

## Alur Email

| Kejadian | Mekanisme | Template |
|----------|-----------|----------|
| Verifikasi email (registrasi) | Fortify + Laravel notification | Framework default / branded |
| Reset password | Fortify | Framework |
| Akun dibuat / atur kata sandi | `UserCreatedNotification` | `resources/views/mail/user-account-created.blade.php` |
| Persetujuan / penolakan registrasi | `SystemNotification` | Notifikasi sistem (in-app + email jika dikonfigurasi) |

Template `user-account-created.blade.php` memakai:

- Logo Kabupaten Bandung
- Logo BAPPERIDA
- Nama aplikasi dan institusi dari System Setting

## Konfigurasi Production

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=<smtp-server>
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=<user>
MAIL_PASSWORD=<password>
MAIL_FROM_ADDRESS=noreply@domain-institusi.go.id
MAIL_FROM_NAME="${APP_NAME}"
```

## Queue

Email notifikasi memakai `ShouldQueue` — **wajib** queue worker aktif:

```bash
php artisan queue:work database --tries=3
```

## Uji Pengiriman

Setelah deploy:

1. Registrasi akun uji → cek email verifikasi
2. Reset password dari halaman login
3. Super Admin buat staff → cek email atur kata sandi
4. Setujui registrasi → cek notifikasi user

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Email tidak terkirim | Cek queue worker, `queue:failed`, log SMTP |
| From address ditolak | Sesuaikan SPF/DKIM dengan TI |
| Email masuk spam | Gunakan domain institusi resmi |

Lihat [MONITORING.md](MONITORING.md) dan [DEPLOYMENT.md](DEPLOYMENT.md).
