# Arsitektur Sistem — BAPPERIDA

**Versi:** 1.0.0

## Gambaran Umum

Aplikasi monolith Laravel 13 dengan frontend Blade + Livewire + Vite + Tailwind CSS v4. API internal (`/api/*`) melayani halaman web melalui JavaScript (fetch + Sanctum session).

```
Browser
   │
   ├── Web Routes (Blade + Livewire) ──► Page Controllers ──► Views
   │
   └── API Routes (JSON) ──► API Controllers ──► Services ──► Models / DB
```

## Lapisan Aplikasi

| Lapisan | Lokasi | Tanggung jawab |
|---------|--------|----------------|
| Routes | `routes/web.php`, `routes/api.php` | Routing, middleware |
| Controllers | `app/Http/Controllers/` | Validasi request, authorize, response |
| Form Requests | `app/Http/Requests/` | Validasi input |
| Services | `app/Services/` | Business logic |
| Models | `app/Models/` | Eloquent, relasi |
| Policies | `app/Policies/` | Authorization |
| Resources | `app/Http/Resources/` | Transformasi API JSON |
| Views | `resources/views/` | UI Blade |
| JS Modules | `resources/js/modules/` | CRUD client, dashboard |

## Modul Utama

| Modul | Service namespace | Policy |
|-------|-------------------|--------|
| Dashboard | `Services\Dashboard\` | `DashboardPolicy` |
| Registrasi Penomoran | `Services\LetterNumberRegistration\` | `LetterNumberRegistrationPolicy` |
| Surat Masuk | `Services\IncomingLetter\` | `IncomingLetterPolicy` |
| Surat Keluar | `Services\OutgoingLetter\` | `OutgoingLetterPolicy` |
| Laporan | `Services\Report\` | `ReportPolicy` |
| Pengguna | `Services\User\` | `UserPolicy` |
| Bidang | `Services\Department\` | `DepartmentPolicy` |
| Notifikasi | `Services\Notification\` | Query scoped |
| Activity Log | `Services\ActivityLog\` | `ActivityLogPolicy` |
| System Setting | `Services\SystemSetting\` | `SystemSettingPolicy` |

## Autentikasi & Otorisasi

- **Fortify** — login, register, reset password, verifikasi email
- **Sanctum** — session API untuk SPA-like fetch dari halaman yang sama
- **Middleware** — `auth`, `verified`, `active`, `role`
- **Policies** — per-model authorization

## Alur Integrasi

```
System Setting ──► Kop laporan, template nomor, branding email
        │
        ▼
Letter Registration ──► Outgoing Letter (penomoran)
        │
        ▼
Incoming / Outgoing Letter ──► Report ──► Print/PDF/Excel
        │
        ├──► Activity Log (audit)
        └──► Notification (event-driven)
```

## Struktur Folder Penting

```
app/
├── Http/Controllers/     # Web + API controllers
├── Services/             # Business logic per modul
├── Models/
├── Policies/
├── Notifications/
└── Support/              # ExceptionResponder, helpers

resources/
├── views/                # Blade, components, errors
├── js/modules/           # Frontend per halaman
└── css/                  # Design system

database/
├── migrations/
├── seeders/              # SuperAdmin, Department, SystemSetting
└── factories/

docs/                     # Dokumentasi operasional
deploy/                   # Supervisor, cron contoh
```

## Stack Teknologi

- PHP 8.3, Laravel 13, Livewire 4, Flux UI
- MySQL 8 (production)
- Queue: database driver + Supervisor worker
- Mail: SMTP institusi

## Keamanan Production

- `APP_DEBUG=false`
- CSRF pada web + Sanctum stateful API
- Policy pada setiap operasi sensitif
- Download file melalui authorize + service
- Exception handler — JSON/HTML tanpa stack trace di production

Lihat [ENVIRONMENT.md](ENVIRONMENT.md) dan [DEPLOYMENT.md](DEPLOYMENT.md).
