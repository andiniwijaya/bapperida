# Deployment Checklist — BAPPERIDA v1.0.0

Gunakan checklist ini setiap kali deploy ke production.

## Persiapan Repository

- [ ] Clone repository ke server (`/var/www/bapperida` atau path yang disepakati)
- [ ] Pastikan branch/tag release **1.0.0** (Production Ready)
- [ ] Pastikan `.env` **tidak** ada di repository

## Dependensi & Build

- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm ci`
- [ ] `npm run build` (hasil di `public/build/`)

## Environment

- [ ] Salin `.env.production.example` → `.env`
- [ ] `php artisan key:generate`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` sesuai domain HTTPS
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SANCTUM_STATEFUL_DOMAINS` dikonfigurasi
- [ ] Kredensial database MySQL
- [ ] Konfigurasi SMTP mail

## Database

- [ ] Database MySQL dibuat
- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --force` (instalasi pertama / reset terkontrol)
- [ ] Ganti password Super Admin default setelah seed

## Storage & Permission

- [ ] `php artisan storage:link`
- [ ] `chown -R www-data:www-data storage bootstrap/cache`
- [ ] `chmod -R ug+rwx storage bootstrap/cache`

## Cache

- [ ] `php artisan optimize`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`

## Layanan Latar Belakang

- [ ] Queue worker Supervisor aktif (`deploy/supervisor-queue-worker.conf`)
- [ ] Cron scheduler aktif (`deploy/crontab.example`)
- [ ] Verifikasi `supervisorctl status`

## Uji Fungsional

- [ ] Login Super Admin / Admin / Staff
- [ ] Dashboard memuat data
- [ ] Registrasi penomoran — CRUD
- [ ] Arsip surat masuk/keluar — CRUD + upload
- [ ] Laporan — filter, print, PDF, Excel
- [ ] Notifikasi — dropdown + navigasi
- [ ] Email verifikasi / reset password (uji SMTP)
- [ ] Activity log (admin/superadmin)
- [ ] System setting
- [ ] Halaman error (404) — tanpa stack trace

## Keamanan & Log

- [ ] HTTPS aktif, sertifikat valid
- [ ] `storage/logs/laravel.log` dapat ditulis
- [ ] Tidak ada stack trace di halaman error
- [ ] Backup database pertama dijalankan (lihat BACKUP_RESTORE.md)

## Selesai

- [ ] Catat tanggal deploy dan versi (1.0.0)
- [ ] Informasikan admin BAPPERIDA bahwa sistem siap digunakan
