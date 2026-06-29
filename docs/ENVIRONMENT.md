# Environment Variables — BAPPERIDA

Salin `.env.production.example` ke `.env` pada server production.

## Aplikasi

| Variabel | Production | Keterangan |
|----------|------------|------------|
| `APP_NAME` | Nama institusi | Tampil di UI dan email |
| `APP_ENV` | `production` | **Wajib** |
| `APP_DEBUG` | `false` | **Wajib** — nonaktifkan stack trace |
| `APP_KEY` | `php artisan key:generate` | **Wajib** — enkripsi session |
| `APP_URL` | `https://domain` | URL publik tanpa trailing slash |
| `APP_TIMEZONE` | `Asia/Jakarta` | Zona waktu aplikasi |
| `APP_LOCALE` | `id` | Bahasa default |
| `APP_FALLBACK_LOCALE` | `id` | Fallback locale |

## Database

| Variabel | Production |
|----------|------------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Host MySQL |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | Nama database |
| `DB_USERNAME` | User dengan hak terbatas |
| `DB_PASSWORD` | Password kuat |

## Session & Cookie

| Variabel | Production | Keterangan |
|----------|------------|------------|
| `SESSION_DRIVER` | `database` | Rekomendasi |
| `SESSION_LIFETIME` | `120` | Menit idle |
| `SESSION_SECURE_COOKIE` | `true` | **Wajib** dengan HTTPS |
| `SESSION_HTTP_ONLY` | `true` | Default |
| `SESSION_SAME_SITE` | `lax` | Sesuaikan jika subdomain berbeda |
| `SESSION_DOMAIN` | opsional | Domain cookie (mis. `.bandung.go.id`) |

## Sanctum (SPA / session API)

| Variabel | Contoh | Keterangan |
|----------|--------|------------|
| `SANCTUM_STATEFUL_DOMAINS` | `arsip.bapperida.bandung.go.id,localhost` | Domain tanpa `https://`, dipisah koma |

## Queue & Cache

| Variabel | Production |
|----------|------------|
| `QUEUE_CONNECTION` | `database` |
| `CACHE_STORE` | `database` |

## Mail

| Variabel | Keterangan |
|----------|------------|
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | Server SMTP institusi |
| `MAIL_PORT` | Biasanya `587` (TLS) |
| `MAIL_USERNAME` | Akun SMTP |
| `MAIL_PASSWORD` | Password SMTP |
| `MAIL_ENCRYPTION` | `tls` atau `ssl` |
| `MAIL_FROM_ADDRESS` | Email resmi institusi |
| `MAIL_FROM_NAME` | `${APP_NAME}` |

## Logging

| Variabel | Production |
|----------|------------|
| `LOG_CHANNEL` | `stack` |
| `LOG_LEVEL` | `warning` atau `error` |

## File & Build

| Variabel | Keterangan |
|----------|------------|
| `FILESYSTEM_DISK` | `local` (default) |
| `VITE_APP_NAME` | Sama dengan `APP_NAME` |

## Nilai yang TIDAK boleh di production

- `APP_DEBUG=true`
- `APP_ENV=local`
- `MAIL_MAILER=log` (email tidak terkirim)
- `QUEUE_CONNECTION=sync` (notifikasi blocking / tidak reliable)

## File Environment

| File | Penggunaan |
|------|------------|
| `.env.example` | Template pengembangan lokal |
| `.env.production.example` | Template server production |
| `.env` | **Tidak di-commit** — rahasia server |
