# ARCHITECTURE.md - Parkirin System Architecture, Database & Site Map

## 1. Architectural Principles & Monorepo Structure
The architecture of **Parkirin** is designed to be **intuitive, concise, to the point, and professional**. It strictly follows the platform-specific **Site Map** without adding unnecessary layers or unrequested pages.

```text
parkirin-monorepo/
├── AGENTS.md
├── PRD.md
├── DESIGN-SYSTEM.md
├── ARCHITECTURE.md
├── backend/                  # Laravel 11 (Blade Web Panels + Sanctum REST API)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Web/      # Blade Controllers (Landing, Auth, SuperAdmin, Owner, Admin, Petugas, Pengendara)
│   │   │   │   └── Api/      # REST API Controllers for Flutter Mobile (Auth, Pengendara, Petugas)
│   │   │   └── Middleware/   # RoleMiddleware, TenantIsolationMiddleware
│   │   ├── Models/           # 10 Eloquent Models matching the 10-table schema
│   │   └── Services/         # TariffCalculationService, ActivityLogService, BookingExpiryService
│   ├── database/
│   │   ├── migrations/       # 10 Tables + 1 MySQL Stored Function (fn_hitung_biaya_parkir)
│   │   └── seeders/          # DatabaseSeeder (Sample Tenants, Users, Tariffs, Vehicles)
│   ├── resources/views/      # Blade templates styled with Tailwind CSS (shadcn-inspired)
│   └── routes/
│       ├── web.php           # Web routes matching Website Site Map
│       └── api.php           # Sanctum API routes matching Mobile Site Map
└── mobile/                   # Flutter Native App (Android & iOS ONLY - No Web/PWA)
    └── lib/
        ├── core/             # app_theme.dart, api_client.dart, constants.dart
        ├── models/           # Dart data models
        └── features/
            ├── auth/         # Login Page & Registrasi Page
            ├── pengendara/   # 7 Rider screens + sub-screens matching Mobile Site Map
            └── petugas/      # 3 Petugas screens + OCR/QR/Manual/Print sub-screens
```

---

## 2. Final 10-Table Relational Database Schema (`db_parkirin`)
All migrations and Eloquent models MUST adhere strictly to this 10-table schema:

1. **`tempat_parkir`**:
   - `id_tempat_parkir` (PK, INT AUTO_INCREMENT)
   - `nama_tempat` (VARCHAR 100, NOT NULL)
   - `kapasitas` (INT 5, DEFAULT 0)
   - `terisi` (INT 5, DEFAULT 0)
   - `sediakan_booking` (TINYINT 1, DEFAULT 1 — `1` = Booking enabled, `0` = Walk-in only)
   - `status_aktif` (TINYINT 1, DEFAULT 1)
   - *Index*: `idx_tempat_aktif_booking (status_aktif, sediakan_booking)`
2. **`user`** (Staff & Management):
   - `id_user` (PK, INT AUTO_INCREMENT)
   - `id_tempat_parkir` (FK nullable -> `tempat_parkir`, NULL for `super_admin`)
   - `nama_lengkap` (VARCHAR 50, NOT NULL)
   - `username` (VARCHAR 50, UNIQUE, NOT NULL)
   - `password` (VARCHAR 255, NOT NULL)
   - `role` (`ENUM('super_admin', 'owner', 'admin', 'petugas')`, NOT NULL)
   - `status_aktif` (TINYINT 1, DEFAULT 1)
   - *Index*: `idx_user_tenant_role (id_tempat_parkir, role, status_aktif)`
3. **`pengendara`** (End-User / Customer):
   - `id_pengendara` (PK, INT AUTO_INCREMENT)
   - `nama_lengkap` (VARCHAR 50, NOT NULL)
   - `email` (VARCHAR 100, UNIQUE, NOT NULL)
   - `no_telepon` (VARCHAR 15, NULLABLE — non-unique)
   - `username` (VARCHAR 50, UNIQUE, NULLABLE)
   - `password` (VARCHAR 255, NULLABLE for Google OAuth)
   - `skor_reputasi` (INT, DEFAULT 100)
   - `status_aktif` (TINYINT 1, DEFAULT 1)
4. **`tarif`**:
   - `id_tarif` (PK, INT AUTO_INCREMENT)
   - `id_tempat_parkir` (FK -> `tempat_parkir`, NOT NULL)
   - `jenis_kendaraan` (`ENUM('Motor', 'Mobil', 'Sepeda')`, NOT NULL)
   - `tipe_tarif` (`ENUM('Per Jam', 'Flat Harian', 'Progresif Mal')`, DEFAULT `'Per Jam'`)
   - `tarif_per_jam` (DECIMAL 15,2, NOT NULL)
   - `tarif_jam_pertama` (DECIMAL 15,2, DEFAULT 0.00)
   - `tarif_berikutnya` (DECIMAL 15,2, DEFAULT 0.00)
   - `tarif_inap` (DECIMAL 15,2, DEFAULT 0.00)
   - `status_aktif` (TINYINT 1, DEFAULT 1)
   - *Unique Key*: `uq_tarif_tenant_kendaraan (id_tempat_parkir, jenis_kendaraan)`
5. **`kendaraan`**:
   - `id_kendaraan` (PK, INT AUTO_INCREMENT)
   - `plat_nomor` (VARCHAR 15, UNIQUE, NOT NULL)
   - `jenis_kendaraan` (`ENUM('Motor', 'Mobil', 'Sepeda')`, NOT NULL)
   - `warna` (VARCHAR 20, NOT NULL)
   - `id_pengendara` (FK nullable -> `pengendara`)
   - `id_user_input` (FK nullable -> `user`)
6. **`pengajuan_banned`**:
   - `id_banned` (PK, INT AUTO_INCREMENT)
   - `id_tempat_parkir` (FK -> `tempat_parkir`, NOT NULL)
   - `id_pengaju` (FK -> `user`, NOT NULL)
   - `id_pengendara` (FK nullable -> `pengendara`)
   - `id_kendaraan` (FK nullable -> `kendaraan`)
   - `alasan` (VARCHAR 100, NOT NULL)
   - `status_pengajuan` (`ENUM('Pending', 'Disetujui', 'Ditolak', 'Dicabut')`, DEFAULT `'Pending'`)
   - `tanggal_pengajuan` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
   - *Index*: `idx_banned_lookup (id_tempat_parkir, status_pengajuan, id_kendaraan, id_pengendara)`
7. **`pengajuan_klaim_kendaraan`**:
   - `id_klaim` (PK, INT AUTO_INCREMENT)
   - `id_kendaraan` (FK -> `kendaraan`, NOT NULL)
   - `id_pengaju` (FK -> `pengendara`, NOT NULL)
   - `id_pengendara_target` (FK -> `pengendara`, NOT NULL)
   - `status_klaim` (`ENUM('Pending', 'Disetujui', 'Ditolak', 'Dibatalkan')`, DEFAULT `'Pending'`)
   - `tanggal_pengajuan` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
8. **`booking`**:
   - `id_booking` (PK, INT AUTO_INCREMENT)
   - `id_pengendara` (FK -> `pengendara`, NOT NULL)
   - `id_tempat_parkir` (FK -> `tempat_parkir`, NOT NULL)
   - `id_kendaraan` (FK -> `kendaraan`, NOT NULL)
   - `waktu_booking` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
   - `waktu_rencana_kedatangan` (DATETIME, NOT NULL)
   - `kode_booking` (VARCHAR 50, UNIQUE, NOT NULL)
   - `status` (`ENUM('Menunggu', 'Selesai', 'Batal')`, DEFAULT `'Menunggu'`)
   - *Indexes*: `idx_booking_tenant_status (id_tempat_parkir, status, waktu_rencana_kedatangan)`, `idx_booking_pengendara_keyset (id_pengendara, id_booking)`
9. **`transaksi`**:
   - `id_transaksi` (PK, INT AUTO_INCREMENT)
   - `id_booking` (FK nullable, UNIQUE -> `booking`)
   - `id_kendaraan` (FK -> `kendaraan`, NOT NULL)
   - `id_tempat_parkir` (FK -> `tempat_parkir`, NOT NULL)
   - `id_tarif` (FK -> `tarif`, NOT NULL)
   - `id_pengendara` (FK nullable -> `pengendara`)
   - `id_petugas` (FK nullable -> `user`)
   - `waktu_masuk` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
   - `waktu_keluar` (DATETIME, NULLABLE)
   - `durasi_jam` (INT 5, NULLABLE)
   - `biaya_total` (DECIMAL 15,2, NULLABLE)
   - `status_pembayaran` (`ENUM('Belum Bayar', 'Sudah Bayar')`, DEFAULT `'Belum Bayar'`)
   - `status` (`ENUM('masuk', 'keluar')`, DEFAULT `'masuk'`)
   - *Indexes*: `idx_transaksi_tenant_keyset (id_tempat_parkir, status, id_transaksi)`, `idx_transaksi_pengendara_keyset (id_pengendara, id_transaksi)`, `idx_transaksi_waktu_rekap (id_tempat_parkir, waktu_masuk, status_pembayaran)`
10. **`log_aktivitas`**:
    - `id_log` (PK, INT AUTO_INCREMENT)
    - `id_tempat_parkir` (FK nullable -> `tempat_parkir`)
    - `id_user` (FK -> `user`, NOT NULL)
    - `aktivitas` (VARCHAR 150, NOT NULL)
    - `waktu_aktivitas` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
    - *Index*: `idx_log_tenant_keyset (id_tempat_parkir, id_log)`

---

## 3. Platform Hierarchy & Site Map Alignment (STRICT)
All routes, navigation menus, and screens MUST match the official Site Map for each platform.

### 3.1. Website Hierarchy (`Website Parkirin` - `/backend/routes/web.php`)
- **`Landing Page` (`/`)**:
  - `Hero Section`
  - `About Us`
  - `Fitur`
  - `Cara/Alur Kerja`
  - `Keunggulan`
  - `Testimoni` (21st.dev `testimonials-with-marquee`)
  - `CTA & Kontak` (21st.dev `cta-with-marquee`)
  - `Footer`
- **`Authentication` (`/login`, `/register`)**:
  - `Registrasi Page` (for `pengendara`)
  - `Login Page` (unified or tabbed login routing to the 5 role scopes below)
- **Scope: `Pengendara` (`/pengendara/*`)**:
  - `Dashboard Pengendara`
  - `Riwayat Parkir` (Keyset paginated history, digital ticket view, & online payment simulation)
  - `Katalog Parkir` (Browse tenants, quota, tariffs, & create/cancel booking)
  - `Notifikasi` (View & respond to incoming vehicle ownership claims)
  - `Profil Page` -> `Edit Profil & Password & No. Telp` (with OTP verification for phone number changes)
- **Scope: `Petugas` (`/petugas/*`)**:
  - `Dashboard Petugas` (Assigned tenant operational overview & active quota status)
- **Scope: `Admin` (`/admin/*`)**:
  - `Dashboard Admin`
  - `Kelola Tarif` (CRUD & toggle active tariffs: `Per Jam`, `Flat Harian`, `Progresif Mal`, `Tarif Inap`)
  - `Kelola Kapasitas` (Set initial capacity, edit capacity, toggle `sediakan_booking`)
  - `Kelola Petugas` (CRUD `petugas` accounts for this tenant)
  - `Kelola Kendaraan` (CRUD offline subscriber vehicles) -> sub-action: `Pengajuan Banned` (by `id_kendaraan`)
  - `Daftar Pengendara` (View riders & visit frequency at this tenant) -> sub-action: `Pengajuan Banned` (by `id_pengendara`)
  - `Log Aktivitas Parkir` (Keyset paginated audit trail from `log_aktivitas` & gate movements)
- **Scope: `Owner` (`/owner/*`)**:
  - `Dashboard Owner` (Revenue summary + Anti-Fraud Active Vehicles vs Inbound/Outbound comparison)
  - `Kelola Admin` (CRUD `admin` accounts for this tenant)
  - `Kelola Pengajuan Banned` (Approve `'Disetujui'`, Reject `'Ditolak'`, or Revoke `'Dicabut'` ban requests)
  - `Daftar Transaksi Parkir` (Date-filtered financial transaction recap & PDF/Print export)
- **Scope: `Super Admin` (`/super-admin/*`)**:
  - `Dashboard Super Admin` (Platform-wide tenant & owner metrics)
  - `Kelola Tenant` (CRUD & activate/deactivate `tempat_parkir`)
  - `Kelola Owner` (CRUD `owner` accounts bound to tenants)

### 3.2. Mobile App Hierarchy (`Aplikasi Parkirin` - `/mobile/lib/features/`)
- **Entry & Auth**:
  - `Registrasi Page` (for `pengendara`)
  - `Login Page` (routes to `Pengendara` or `Petugas` based on credentials)
- **Scope: `Pengendara`**:
  1. `Dashboard Pengendara`
  2. `Riwayat Parkir` (Digital ticket QR display, payment status, & transaction details)
  3. `Kelola Kendaraan Pribadi` (Add, Edit with immutable history lock rule, Unlink) -> sub-screen: `Pengajuan Klaim Kendaraan`
  4. `Katalog Parkir` (Browse tenants, quota, and tariff details)
  5. `Booking Page` (Create booking with arrival time & view 10-minute countdown / cancel booking)
  6. `Notifikasi` (Approve/Reject incoming vehicle ownership claims)
  7. `Profil Page` -> sub-screen: `Edit Profil & Password & No. Telp` (with OTP verification for phone number)
- **Scope: `Petugas`**:
  1. `Dashboard Petugas` (Assigned tenant quota counter & quick gate action buttons)
  2. `Transaksi Parkir Masuk Page (Scanner OCR)`:
     - Direct path: Scan plate via On-Device ML Kit OCR (with editable preview) -> `Cetak Karcis Fisik` (print thermal QR ticket or send `Karcis Digital`)
     - Sub-screen 1: `Verifikasi Booking` (Scan booking QR / input `kode_booking`) -> `Cetak Karcis Fisik`
     - Sub-screen 2: `Input Manual` (Manual plate & vehicle input fallback) -> `Cetak Karcis Fisik`
  3. `Transaksi Parkir Keluar Page (Scanner Karcis)`:
     - Primary path: Scan QR Code on physical/digital ticket
     - Fallback path: `Input Plat Nomor / Cari Data Transaksi` (for lost or damaged tickets)
     - Displays fee breakdown, checks `Sudah Bayar` / `Belum Bayar`, and executes `Konfirmasi Bayar`.