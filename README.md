# Sistem Registrasi Penomoran dan Arsip Surat BAPPERIDA

**Versi:** 1.0.0 · **Release:** Production Ready

Aplikasi web untuk registrasi penomoran surat, arsip surat masuk/keluar, laporan, notifikasi, dan audit trail — Badan Perencanaan Pembangunan, Riset dan Inovasi Daerah Kabupaten Bandung.

## Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Panduan deployment production |
| [docs/INSTALLATION.md](docs/INSTALLATION.md) | Panduan instalasi lokal/staging |
| [docs/DEPLOYMENT_CHECKLIST.md](docs/DEPLOYMENT_CHECKLIST.md) | Checklist deploy |
| [docs/ENVIRONMENT.md](docs/ENVIRONMENT.md) | Daftar environment variable |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Arsitektur sistem |
| [docs/ADMIN_GUIDE.md](docs/ADMIN_GUIDE.md) | Panduan administrator |
| [docs/USER_GUIDE.md](docs/USER_GUIDE.md) | Panduan pengguna |
| [docs/BACKUP_RESTORE.md](docs/BACKUP_RESTORE.md) | Backup & restore |
| [docs/MONITORING.md](docs/MONITORING.md) | Monitoring operasional |
| [docs/MAIL.md](docs/MAIL.md) | Konfigurasi email |
| [AGENTS.md](AGENTS.md) | Panduan pengembang |

## Persyaratan

- PHP 8.3+, Composer 2.x, Node.js 20+ (build), MySQL 8+ (production)

## Instalasi Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Production (ringkas)

```bash
cp .env.production.example .env
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Detail lengkap: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Queue & Scheduler

```bash
# Worker (Supervisor — lihat deploy/supervisor-queue-worker.conf)
php artisan queue:work database --tries=3

# Cron (lihat deploy/crontab.example)
* * * * * cd /path/to/app && php artisan schedule:run
```

## Role

| Role | Akses |
|------|-------|
| Super Admin | Seluruh modul administrasi |
| Admin | Staff, laporan, arsip |
| Staff | Arsip bidang, registrasi penomoran |

## Pengujian

```bash
php artisan test --compact
vendor/bin/pint --dirty
```

## Struktur Folder

```
app/           # PHP application (Controllers, Services, Models)
resources/     # Views, CSS, JavaScript
database/      # Migrations, seeders
docs/          # Dokumentasi operasional
deploy/        # Contoh Supervisor & cron
public/        # Web root (build assets, storage link)
```

## Lisensi & Institusi

Badan Perencanaan Pembangunan, Riset dan Inovasi Daerah Kabupaten Bandung.
