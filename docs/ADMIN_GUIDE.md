# Panduan Administrator — BAPPERIDA

**Versi 1.0.0** · Untuk Super Admin dan Admin

## Akses Role

| Role | Kemampuan utama |
|------|-----------------|
| **Super Admin** | Seluruh modul + system setting + activity log + persetujuan registrasi |
| **Admin** | Manajemen staff, bidang (terbatas), laporan, arsip |
| **Staff** | Arsip surat bidang, registrasi penomoran, laporan bidang |

## Super Admin

### Persetujuan Registrasi

1. Menu **Persetujuan Registrasi**
2. Tinjau permintaan akun baru
3. Setujui atau tolak — user menerima notifikasi

### Manajemen Pengguna

- Tambah Admin/Staff
- Reset password (email atur kata sandi)
- Nonaktifkan akun

### Manajemen Bidang

- Kelola data bidang/departemen
- Bidang BAPPERIDA tidak dapat dipilih saat registrasi publik

### System Setting

- Nama institusi, alamat, kop laporan
- Konfigurasi penomoran surat
- Batas upload, periode dashboard default
- Penandatangan laporan

### Activity Log

- Audit trail seluruh aktivitas sistem
- Filter modul, aksi, periode
- Export Excel

## Admin

- Manajemen **Staff** (bukan Admin/Super Admin)
- Laporan dan arsip sesuai kebijakan organisasi
- Tidak dapat mengubah system setting global

## Operasional Harian

### Backup

Ikuti [BACKUP_RESTORE.md](BACKUP_RESTORE.md). Minimal backup database harian.

### Queue & Email

Pastikan tim infrastruktur memantau queue worker — lihat [MONITORING.md](MONITORING.md).

### Maintenance

Gunakan `php artisan down` sebelum update besar; informasikan pengguna.

## Setelah Instalasi Pertama

1. Login Super Admin (dari seeder — **ganti password segera**)
2. Perbarui System Setting (nama institusi, alamat, email)
3. Verifikasi bidang aktif
4. Uji email SMTP
5. Buat akun Admin operasional

## Dukungan Teknis

- Log error: `storage/logs/laravel.log`
- Dokumen deployment: [DEPLOYMENT.md](DEPLOYMENT.md)
- Checklist deploy: [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
