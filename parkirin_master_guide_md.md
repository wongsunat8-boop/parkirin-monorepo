# 📘 PARKIRIN MASTER GUIDE
**Unified Project Documentation & AI Agent Directives**

---

## 🛑 PART 1: AI AGENT GUIDELINES (`AGENTS.md`)

### 1. Project Identity & Role
You are an elite Senior Full-Stack Web Developer (Laravel 11, Tailwind CSS) working on **Parkirin**, a Multi-Tenant Portable Parking SaaS designed for authorized informal/swadaya parking operators. **The project is entirely Web-Based (Responsive/PWA). There is NO mobile/Flutter app.**

### 2. Core Directives (MANDATORY & STRICT)
1. **ENVIRONMENT SECURITY (ABSOLUTE RULE): You MUST NOT read, expose, or modify any `.env`, `.env.example`, or `.env.backup` files.** Assume the environment is properly configured.
2. **Read Context First**: Always check this MASTER GUIDE for business rules, route mapping, the 10-table singular schema, and visual styling.
3. **To-The-Point Execution (Zero Scope Creep)**: Development MUST be intuitive, concise, and professional. Build ONLY the pages, features, and flows defined in the Site Map. Do not invent unrequested menus or bloated abstractions.
4. **Strict Multi-Tenant Isolation (Anti-IDOR)**: Every query in the `owner`, `admin`, and `petugas` scopes MUST be filtered by `id_tempat_parkir`. Implement a Tenant Global Scope or Middleware.
5. **Database Schema Lock (10 Tables)**: Stick strictly to the 10 defined tables with singular naming. Do not invent new columns or tables.

### 3. Coding Standards & Performance
- **HTML5 QR Scanner**: Use lightweight browser-based JS libraries (e.g., `html5-qrcode`) for Petugas to scan outbound digital tickets. No native dependencies.
- **Fast Loading & Query Optimization**: Use Eloquent ORM with eager loading (`with()`) and selective column fetching (`select()`). Avoid N+1 query problems.
- **Concurrency Prevention**: Wrap all capacity-mutating operations inside `DB::transaction()` with row-level locking (`lockForUpdate()`) on `tempat_parkir`.
- **Hybrid Pagination**: Use Offset pagination (`paginate(10)`) for master tables (`tempat_parkir`, `user`), and Keyset/Cursor pagination (`cursorPaginate(15)`) for high-volume tables (`transaksi`, `booking`, `log_aktivitas`).

---

## 🎯 PART 2: PRODUCT REQUIREMENTS (`PRD.md`)

### 1. User Roles & Multi-Auth Architecture
The system supports 5 user roles via two tables (`user` and `pengendara`):
1. **`super_admin`**: Manages tenants and creates `owner` accounts.
2. **`owner`**: Views analytics (YoY, Slot Turnover), Audit Logs, and manages `admin` accounts.
3. **`admin`**: Manages tariffs, capacity, `petugas`, Multi-Tier Membership packages, and performs **Post-Audit Verification** for online payments.
4. **`petugas`**: Operates via Mobile Web browser. Monitors Self Check-In, provides manual entry fallback, and processes Outbound Parking via HTML5 QR Scanner.
5. **`pengendara`**: End-user. Books parking, buys membership, manages digital tickets, and uses Self Check-In. Phone numbers are recycled via OTP; unique identifiers are `id_pengendara` and `email`.

### 2. Core Business Rules & Logic
- **Self Check-In (Inbound Parking)**:
  - **Walk-In (Guest)**: Scans a static QR Poster at the gate $\rightarrow$ Opens a public URL (`/in/{id_tempat_parkir}`) without login $\rightarrow$ Inputs `plat_nomor` $\rightarrow$ System validates capacity $\rightarrow$ Returns a **Digital QR Ticket** on the browser.
  - **Member/Booking (Fast Track)**: Authenticated riders scan the same poster. System identifies their active contract, logs the entry, and displays a Green Digital Pass.
  - *Fallback*: Petugas has a manual input button on their dashboard if a rider's phone is dead.
- **Outbound Parking (HTML5 QR Scan)**:
  - Petugas scans the Digital QR Ticket from the rider's phone using a browser-based scanner.
  - System calculates durations and fees. If Member/Booking is fully paid, fee is Rp0. If regular, Petugas collects cash.
  - *Lost Ticket SOP*: Petugas searches by plate number, adds `denda_karcis_hilang`, and inputs the rider's ID (STNK/KTP).
- **Optimistic Check-In & Post-Audit (Online Payments)**:
  - **Auto-Lunas Instan**: When buying a Member package or booking, riders upload a QRIS receipt. System immediately sets status to `'Lunas'` and generates the QR ticket to prevent gate queues.
  - **Post-Audit Verification**: Admin receives a Toast Alert for unverified payments. Admin compares `waktu_unggah_bukti` with bank mutations. If fake, Admin clicks 'Tolak', contract cancels, and Petugas collects full cash at the exit.
- **Multi-Tier Membership & Auto-Fallback**:
  - Packages can have a `batas_masuk_harian` (e.g., Max 1x/Day).
  - If a member exceeds this daily limit, the system **Auto-Fallbacks** the next entry into a Walk-In Regular transaction (cash payment required at exit) instead of blocking the gate.
- **Vehicle Auto-Reassign**:
  - If Rider B adds a `plat_nomor` previously registered to Rider A (and it has no active contracts), the system automatically transfers ownership (`id_pengendara`) to Rider B seamlessly.

---

## 🏗️ PART 3: ARCHITECTURE & SITE MAP (`ARCHITECTURE.md`)

### 1. Final 10-Table Database Schema (3NF)
1. **`tempat_parkir`**: `id_tempat_parkir` (PK), `nama_tempat`, `kapasitas_reguler`, `kuota_inap`, `sediakan_booking_inap`, `sediakan_member`, `qris_image`, `status_aktif`.
2. **`user`**: `id_user` (PK), `id_tempat_parkir` (FK null), `nama_lengkap`, `username`, `password`, `role` (super_admin, owner, admin, petugas), `status_aktif`.
3. **`pengendara`**: `id_pengendara` (PK), `nama_lengkap`, `email`, `no_telepon`, `username`, `password`, `status_aktif`.
4. **`kendaraan`**: `id_kendaraan` (PK), `plat_nomor` (UNIQUE), `jenis_kendaraan` (Motor, Mobil, Sepeda), `warna`, `id_pengendara` (FK null).
5. **`tarif`**: `id_tarif` (PK), `id_tempat_parkir` (FK), `jenis_kendaraan`, `tipe_tarif` (Per Jam, Flat Harian, Progresif Mal), `tarif_per_jam`, `tarif_jam_pertama`, `tarif_berikutnya`, `tarif_inap_harian`, `denda_karcis_hilang`, `status_aktif`.
6. **`paket_langganan`**: `id_paket` (PK), `id_tempat_parkir` (FK), `nama_paket`, `jenis_kendaraan`, `durasi_hari`, `batas_masuk_harian` (INT null), `harga_paket`, `status_aktif`.
7. **`langganan_member`**: `id_langganan` (PK), `kode_member` (UNIQUE), `id_tempat_parkir` (FK), `id_paket` (FK), `id_pengendara` (FK), `id_kendaraan` (FK), `id_user_verifikasi` (FK null), `tanggal_mulai`, `biaya_langganan`, `bukti_bayar`, `waktu_unggah_bukti`, `status_pembayaran` (Pending, Lunas, Ditolak), `status_langganan` (Aktif, Kedaluwarsa, Dibatalkan), `waktu_registrasi`.
8. **`booking`**: `id_booking` (PK), `kode_booking` (UNIQUE), `id_tempat_parkir` (FK), `id_pengendara` (FK), `id_kendaraan` (FK), `id_user_verifikasi` (FK null), `waktu_booking`, `waktu_mulai_inap`, `waktu_rencana_keluar`, `biaya_inap_dibayar`, `bukti_bayar`, `waktu_unggah_bukti`, `status_pembayaran` (Pending, Lunas, Ditolak), `status` (Terjadwal, Aktif_Inap, Overstay, Selesai, Batal).
9. **`transaksi`**: `id_transaksi` (PK), `kode_karcis` (UNIQUE - Payload Karcis Digital), `id_tempat_parkir` (FK), `id_kendaraan` (FK), `id_tarif` (FK), `id_booking` (FK null), `id_langganan` (FK null), **`id_petugas_masuk` (FK null - NULL = Self Check-In)**, `id_petugas_keluar` (FK null), `waktu_masuk`, `waktu_keluar`, `biaya_dasar`, `denda_overstay`, `denda_karcis_hilang`, `identitas_pengambil`, `metode_pembayaran` (Tunai, QRIS_Mitra, Prepaid_Booking, Member_Pass), `status_pembayaran` (Belum Bayar, Sudah Bayar), `status` (masuk, keluar).
10. **`log_aktivitas`**: `id_log` (PK), `id_tempat_parkir` (FK null), `id_user` (FK), `aktivitas`, `waktu_aktivitas`.

### 2. Website Hierarchy & Site Map (Full Web/PWA)
- **`Landing Page` (`/`)**: Hero, About Us, Fitur, Cara Kerja, Keunggulan, Testimoni (marquee), CTA & Kontak (marquee), Footer.
- **`Check-In Mandiri` (`/in/{id_tempat_parkir}`)**: Public page for Walk-In Self Check-In (returns Digital QR).
- **`Authentication` (`/login`, `/register`)**: Unified tabbed login routing to scopes below.
- **Scope: `Pengendara` (`/pengendara/*`)**: Dashboard, Riwayat Parkir, Katalog Parkir (Auto-Lunas), Kelola Kendaraan Pribadi, Profil Page.
- **Scope: `Petugas` (`/petugas/*`)**: Dashboard (Monitor Check-In & Fallback Manual Input), Scanner Keluar (HTML5 Barcode Scanner).
- **Scope: `Admin` (`/admin/*`)**: Dashboard, Verifikasi Pembayaran Online (Post-Audit), Kelola Tarif, Kelola Kapasitas, Kelola Paket Langganan, Kelola Petugas, Daftar Pengendara, Log Aktivitas.
- **Scope: `Owner` (`/owner/*`)**: Dashboard (Anti-Fraud Metrics), Rekap Transaksi & Analitik YoY, Audit Log & Setoran Shift, Kelola Admin.
- **Scope: `Super Admin` (`/super-admin/*`)**: Dashboard, Kelola Tenant, Kelola Owner.

---

## 🎨 PART 4: DESIGN SYSTEM (`DESIGN-SYSTEM.md`)

### 1. Brand & Color Palette (60-30-10)
- **Primary (60%)**: Parkirin Blue `#0D63A5` (Security & Trust)
- **Secondary (30%)**: Soft Off-White `#F9F9F9` (Professionalism, backgrounds)
- **Accent (10%)**: Traffic Yellow `#FFD717` (Flexibility & CTA Highlights)
- **Neutral Dark**: Charcoal Black `#212121`
- **Pure White**: Surface White `#FFFFFF`

### 2. Typography
- **Heading/Brand**: `Nunito` (Bold 700, Black 900)
- **UI/Body**: `Inter` (Regular 400, Medium 500, SemiBold 600)

### 3. UI References
- **Aesthetic**: `shadcn/ui` & `Refero` (Clean lines, 1px borders `border-slate-200`, subtle shadows `shadow-sm`, rounded corners `rounded-xl`).
- **Landing Page Elements**: Use `21st.dev` style infinite CSS marquee scrolling for Testimonials and CTA sections.

---

## 🚀 PART 5: VIBE CODING STRATEGY & 5-PHASE PROMPTS

### A. `.aiignore` Configuration
Save this EXACT content in a `.aiignore` or `.cursorignore` file in your root folder:
```text
# === CREDENTIALS & SECRETS (STRICTLY FORBIDDEN) ===
.env
.env.*
*.pem
*.key

# === LARAVEL HEAVY DIRECTORIES ===
vendor/
node_modules/
public/storage/
storage/framework/
storage/logs/
bootstrap/cache/*
!bootstrap/cache/.gitignore
package-lock.json
composer.lock

# === SYSTEM & GIT ===
.git/
.DS_Store
.idea/
.vscode/
*.log
*.sql
```

### B. Token Saving Tactics
1. **Clear Context (/clear)**: Always clear chat history after successfully completing and committing a phase.
2. **@Mention Files**: Only tag specific files needed for the current prompt.
3. **One Phase = One Git Commit**: Never jump to the next phase if there are bugs. Fix them, commit, then proceed.

### C. The 5-Phase Prompt Sequences (Copy-Paste to AI)

**FASE 1: Database & Seeder YoY**
> "Baca file `PARKIRIN_MASTER_GUIDE.md` bagian PART 3.1. Buatkan file Migration untuk 10 tabel (nama singular, pastikan `id_petugas_masuk` nullable untuk Self Check-In). Buatkan 1 migration khusus untuk MySQL Stored Function `fn_hitung_biaya_parkir`. Buatkan 10 Eloquent Models dengan relasi dan Observer untuk `log_aktivitas`. Terakhir, buatkan `DatabaseSeeder` yang men-generate 1 Super Admin, 1 Tenant, Akun staf, Tarif, dan Ribuan Data Transaksi Dummy dari tahun 2025-2026 untuk keperluan Analitik YoY. Pastikan `php artisan migrate:fresh --seed` berjalan sukses."

**FASE 2: Design System, Landing Page & Multi-Auth**
> "Baca `PARKIRIN_MASTER_GUIDE.md` bagian PART 4 dan PART 3.2. Konfigurasikan Tailwind CSS (Nunito, Inter, palet warna Parkirin). Buatkan Landing Page publik (`/`) dengan 8 section termasuk animasi marquee (Testimoni & CTA). Buatkan sistem Login/Register Multi-Auth (tabel user dan pengendara) menggunakan Laravel Breeze/UI, lengkap dengan Middleware untuk otorisasi 5 Role dan isolasi tenant (`id_tempat_parkir`)."

**FASE 3: Panel Web Manajemen (Super Admin, Owner, Admin)**
> "Baca `PARKIRIN_MASTER_GUIDE.md` bagian PART 2.2, PART 2.4, dan PART 3.2. Buatkan Controller dan Blade Views bergaya shadcn/ui untuk: 1) Super Admin (Kelola Tenant & Owner). 2) Owner (Dashboard Analitik YoY, Rekap Transaksi, Audit Setoran). 3) Admin (Dashboard, Verifikasi Pembayaran Online Post-Audit dengan Alert Toast, Kelola Tarif, Kelola Kapasitas, Kelola Paket Langganan dengan input batas masuk harian, Daftar Pengendara). Pastikan Hybrid Pagination dan Isolasi Tenant berjalan."

**FASE 4: Panel Web Pengendara & Self Check-In Publik**
> "Baca `PARKIRIN_MASTER_GUIDE.md` bagian PART 2 dan PART 3.2. Buatkan fitur Web untuk: 1) Halaman Publik Self Check-In (`/in/{id}`) yang menerima input plat nomor dan mereturn Karcis Digital QR. 2) Panel Pengendara: Dashboard, Katalog Parkir (pembelian member/booking dengan Auto-Lunas Instan via upload QRIS), Riwayat Parkir, Kelola Kendaraan (dengan fitur Auto-Reassign), dan Edit Profil (termasuk OTP No. Telp). Gunakan DB::transaction() saat mengubah kapasitas."

**FASE 5: Panel Web Petugas & HTML5 Scanner**
> "Baca `PARKIRIN_MASTER_GUIDE.md` bagian PART 2.2 dan PART 3.2. Buatkan Panel Web khusus Petugas (dioptimalkan untuk browser HP). Fitur meliputi: 1) Dashboard pemantauan kapasitas dan tombol Input Manual (Fallback). 2) Halaman Scanner Keluar yang mengintegrasikan library JS `html5-qrcode` untuk memindai QR Karcis Digital pengendara. Setelah scan, sistem memanggil Stored Function tarif, menampilkan tagihan (Rp0 jika member lunas), memproses pembayaran kasir, dan menyelesaikan transaksi."