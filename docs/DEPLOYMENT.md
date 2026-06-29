# Panduan Deployment — BAPPERIDA

**Versi:** 1.0.0 · **Release:** Production Ready

Dokumen ini menjelaskan langkah memasang aplikasi pada server production (Linux + Nginx/Apache + PHP-FPM + MySQL).

## 1. Prasyarat Server

| Komponen | Versi minimum |
|----------|----------------|
| PHP | 8.3+ (extensions: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath, fileinfo) |
| Composer | 2.x |
| Node.js | 20+ (hanya saat build asset) |
| MySQL | 8.0+ |
| Supervisor | untuk queue worker |
| Cron | untuk scheduler |

## 2. Clone & Dependensi

```bash
cd /var/www
git clone <repository-url> bapperida
cd bapperida

composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

**Catatan:** `vendor/` dan `node_modules/` tidak ada di repository; `public/build/` dihasilkan oleh `npm run build`.

## 3. Environment Production

```bash
cp .env.production.example .env
php artisan key:generate
```

Edit `.env` sesuai server. Lihat [ENVIRONMENT.md](ENVIRONMENT.md) untuk daftar lengkap variabel.

Nilai wajib production:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domain-anda>
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
LOG_LEVEL=warning
```

## 4. Database

```bash
# Buat database MySQL terlebih dahulu
php artisan migrate --force
php artisan db:seed --force   # Super Admin, bidang default, system settings
```

**Backup & restore:** lihat [BACKUP_RESTORE.md](BACKUP_RESTORE.md).

## 5. Storage

```bash
php artisan storage:link
```

Verifikasi:

| Path | Fungsi |
|------|--------|
| `public/assets/images/` | Logo aplikasi (statis) |
| `storage/app/public/` | Lampiran surat, upload |
| `storage/app/exports/` | File export sementara (jika digunakan) |

Pastikan `public/storage` mengarah ke `storage/app/public`.

## 6. Cache & Optimasi

Setelah deploy atau perubahan `.env`:

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Membersihkan cache (setelah update kode atau troubleshooting):

```bash
php artisan optimize:clear
# atau per komponen:
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## 7. Queue Worker (Wajib)

Notifikasi dan email memakai `ShouldQueue`. Tanpa worker, email tidak terkirim.

### Supervisor (Linux)

```bash
sudo cp deploy/supervisor-queue-worker.conf /etc/supervisor/conf.d/bapperida-queue.conf
# Sesuaikan path dan user di file konfigurasi
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bapperida-queue:*
```

Monitor:

```bash
sudo supervisorctl status bapperida-queue:*
tail -f storage/logs/queue-worker.log
```

## 8. Scheduler

Tambahkan ke crontab user aplikasi (lihat `deploy/crontab.example`):

```cron
* * * * * cd /var/www/bapperida && php artisan schedule:run >> /dev/null 2>&1
```

**Catatan:** Job purge activity log (`activity_log_retention_days`) belum diimplementasi — rencana pengembangan berikutnya.

## 9. HTTPS & Keamanan

- Aktifkan TLS di reverse proxy (Nginx/Apache).
- Set `SESSION_SECURE_COOKIE=true` dan `APP_URL` dengan `https://`.
- Konfigurasi `SANCTUM_STATEFUL_DOMAINS` tanpa skema (domain saja).
- Pastikan `.env` tidak dapat diakses dari web (`public/` tidak memuat `.env`).

## 10. Permission

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

## 11. Maintenance Mode

```bash
php artisan down --secret="token-ops" --refresh=15
# Akses bypass: https://domain-anda/token-ops
php artisan up
```

Halaman maintenance memakai template error 503 aplikasi.

## 12. Verifikasi Setelah Deploy

Gunakan [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md).

## 13. Monitoring

Lihat [MONITORING.md](MONITORING.md).
