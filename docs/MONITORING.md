# Monitoring Operasional — BAPPERIDA

## 1. Queue Worker

| Cek | Perintah / Lokasi |
|-----|-------------------|
| Status Supervisor | `supervisorctl status bapperida-queue:*` |
| Log worker | `storage/logs/queue-worker.log` |
| Job gagal | `php artisan queue:failed` |
| Retry gagal | `php artisan queue:retry all` |

**Alert:** Worker `FATAL` atau `STOPPED` — email/notifikasi tidak terkirim.

## 2. Application Log

| File | Isi |
|------|-----|
| `storage/logs/laravel.log` | Error aplikasi, exception |
| `storage/logs/browser.log` | Log frontend (jika diaktifkan) |

Rotasi log (contoh `logrotate`):

```
/var/www/bapperida/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
}
```

## 3. Disk Usage

Monitor rutin:

```bash
df -h
du -sh storage/app/*
du -sh storage/logs
```

**Alert:** Disk > 80% — risiko upload/export gagal.

## 4. Mail Delivery

- Uji bulanan: verifikasi email, reset password, notifikasi akun.
- Monitor log SMTP / mail server institusi.
- Pastikan `MAIL_FROM_ADDRESS` domain valid (SPF/DKIM jika tersedia).

## 5. Error Monitoring

Dengan `APP_DEBUG=false`:

- Error web → halaman 500/403 custom (tanpa stack trace)
- Error API → JSON `{ success: false, message: "..." }`
- Detail teknis hanya di `laravel.log`

Opsional: integrasi Sentry, Laravel Cloud, atau log aggregator institusi.

## 6. Uptime & Health

Laravel health endpoint:

```bash
curl -f https://domain-anda/up
```

Monitor HTTP 200 dari endpoint `/up` via uptime checker.

## 7. Maintenance

Sebelum maintenance:

```bash
php artisan down --secret="token-ops" --refresh=15
```

Setelah selesai:

```bash
php artisan up
```

## 8. Indikator Masalah Umum

| Gejala | Kemungkinan penyebab |
|--------|---------------------|
| Email tidak terkirim | Queue worker mati, SMTP salah |
| Notifikasi lambat | Queue backlog |
| Upload gagal | Permission storage / disk penuh |
| 419 pada form | Session/CSRF — cek cookie HTTPS |
| 500 setelah deploy | Cache config — `php artisan optimize:clear` lalu cache ulang |
