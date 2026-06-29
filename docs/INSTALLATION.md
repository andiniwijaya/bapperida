# Panduan Instalasi — BAPPERIDA

**Versi:** 1.0.0

Dokumen ini untuk instalasi **development / staging**. Production: [DEPLOYMENT.md](DEPLOYMENT.md).

## Prasyarat

- PHP 8.3+ (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath, fileinfo)
- Composer 2.x
- Node.js 20+
- MySQL 8+ atau SQLite (development)

## Langkah Instalasi

```bash
git clone <repository-url> bapperida
cd bapperida

composer install
cp .env.example .env
php artisan key:generate

# Konfigurasi database di .env, lalu:
php artisan migrate --seed

npm install
npm run build

php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Queue (Development)

Email dan notifikasi memakai queue. Jalankan worker:

```bash
php artisan queue:work
```

## Akun Default (Seeder)

Setelah `db:seed`, login dengan kredensial Super Admin dari seeder. **Ganti password** sebelum production.

## Pengujian

```bash
php artisan test --compact
```

## Dokumentasi Terkait

- [ENVIRONMENT.md](ENVIRONMENT.md) — variabel environment
- [ARCHITECTURE.md](ARCHITECTURE.md) — arsitektur sistem
- [USER_GUIDE.md](USER_GUIDE.md) — panduan pengguna
- [ADMIN_GUIDE.md](ADMIN_GUIDE.md) — panduan administrator
