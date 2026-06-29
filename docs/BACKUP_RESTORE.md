# Backup & Restore — BAPPERIDA

## Cakupan Backup

| Komponen | Frekuensi rekomendasi | Lokasi |
|----------|----------------------|--------|
| Database MySQL | Harian (otomatis) | Server backup / off-site |
| `storage/app/` | Harian | Lampiran surat, upload |
| `.env` | Setiap perubahan | Vault / backup terenkripsi |
| `public/assets/` | Saat rilis | Sudah di Git |

**Tidak perlu backup:** `vendor/`, `node_modules/`, `public/build/` (dapat dibuild ulang).

## Backup Database

### Manual (mysqldump)

```bash
mysqldump -u bapperida -p \
  --single-transaction \
  --routines \
  --triggers \
  bapperida > backup/bapperida_$(date +%Y%m%d_%H%M%S).sql
```

Kompresi:

```bash
gzip backup/bapperida_*.sql
```

### Otomatis (contoh cron harian 02:00)

```cron
0 2 * * * mysqldump -u bapperida -p'PASSWORD' bapperida | gzip > /backup/bapperida_$(date +\%Y\%m\%d).sql.gz
```

Ganti kredensial dan path sesuai kebijakan TI Kabupaten Bandung.

## Restore Database

```bash
# Dari file SQL
mysql -u bapperida -p bapperida < backup/bapperida_20260101.sql

# Dari gzip
gunzip -c backup/bapperida_20260101.sql.gz | mysql -u bapperida -p bapperida
```

Setelah restore:

```bash
php artisan config:cache
php artisan cache:clear
```

## Backup Storage

```bash
tar -czf backup/storage_$(date +%Y%m%d).tar.gz -C /var/www/bapperida storage/app
```

## Restore Storage

```bash
tar -xzf backup/storage_20260101.tar.gz -C /var/www/bapperida
chown -R www-data:www-data storage
```

## Backup `.env`

```bash
cp .env /secure-backup/bapperida.env.$(date +%Y%m%d)
chmod 600 /secure-backup/bapperida.env.*
```

**Peringatan:** `.env` berisi kredensial — simpan di lokasi terenkripsi, tidak di repository.

## Uji Restore

Uji restore minimal **bulanan** pada environment staging:

1. Restore database ke staging
2. Restore storage
3. Verifikasi login dan akses lampiran surat
