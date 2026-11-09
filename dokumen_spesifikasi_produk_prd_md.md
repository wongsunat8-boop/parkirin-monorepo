# PRD.md - Product Requirements Document (Parkirin)

## 1. Product Overview
**Parkirin** is a Multi-Tenant Software as a Service (SaaS) portable parking system designed for authorized informal/swadaya parking operators (such as commuter station parking lots, commercial areas, and industrial zones). It consists of a **Laravel Web Application & REST API** (`/backend`) and a **Flutter Native Mobile App** (`/mobile` for Android/iOS).

---

## 2. User Roles & Multi-Auth Architecture
The system separates authentication into two distinct tables supporting **5 user roles**:

### A. Table `user` (Staff & Management)
1. **`super_admin`** (`id_tempat_parkir = NULL`): Manages platform tenants (`tempat_parkir`) and creates/manages `owner` accounts.
2. **`owner`** (Bound to `id_tempat_parkir`): Views real-time financial & operational metrics on `Dashboard Owner` (including active vehicle anti-fraud discrepancy metrics), manages `admin` accounts (`Kelola Admin`), approves/rejects/revokes ban requests (`Kelola Pengajuan Banned`), and views/exports financial transaction recaps (`Daftar Transaksi Parkir`).
3. **`admin`** (Bound to `id_tempat_parkir`): Configures flexible tariffs (`Kelola Tarif`), manages parking capacity & booking availability (`Kelola Kapasitas` & `sediakan_booking`), manages `petugas` accounts (`Kelola Petugas`), registers offline subscriber vehicles (`Kelola Kendaraan`), views registered riders & visit frequency (`Daftar Pengendara`), submits `Pengajuan Banned` (from either `Kelola Kendaraan` or `Daftar Pengendara`), and monitors tenant audit logs (`Log Aktivitas Parkir`).
4. **`petugas`** (Bound to `id_tempat_parkir`): Operates primarily via the Mobile App (with Web `Dashboard Petugas` summary access) to process inbound parking (On-Device OCR / `Input Manual` / `Verifikasi Booking`), print physical tickets or send digital tickets (`Cetak Karcis Fisik`), and process outbound parking (QR Scan or Manual Plate/Transaction Lookup + Cash/Cashless validation).

### B. Table `pengendara` (End-User / Customer)
5. **`pengendara`**: Registers/Logs in (Local Auth or Google OAuth) via Web or Mobile App. Manages personal vehicles (`Kelola Kendaraan Pribadi` on Mobile), submits vehicle ownership claims (`Pengajuan Klaim Kendaraan`), browses the parking catalog (`Katalog Parkir`), books parking slots (`Booking Page`), views active digital tickets & parking history (`Riwayat Parkir`), responds to ownership claim notifications (`Notifikasi`), and updates profile, password, and phone number (`Edit Profil & Password & No. Telp`).
- **Phone Number Recycling & OTP Rule**: `no_telepon` is **NOT** a unique key in the database to prevent registration blocking when mobile carriers recycle inactive numbers. Primary unique identifiers are `id_pengendara` and `email`. Updating `no_telepon` in `Edit Profil & Password & No. Telp` uses an OTP verification flow.

---

## 3. Core Business Rules & Edge-Case Logic (STRICT)

### 3.1. Capacity & Booking Toggle Management (`tempat_parkir`)
- **Initial Capacity Lock**: If `kapasitas == 0`, Admin can input initial capacity, which disables the "Tambah Kapasitas" button and leaves only "Edit Kapasitas".
- **Booking Feature Toggle (`sediakan_booking`)**: Each tenant can independently enable (`1`) or disable (`0`) online booking via Admin settings. If `sediakan_booking == 0`, the "Booking" button on the rider's catalog is disabled (walk-in only).
- **Atomic Quota Counter (`terisi`)**:
  - `terisi` increments by `1` inside a locked DB transaction (`lockForUpdate()`) when a Booking is created OR when a Walk-in Vehicle enters (`Transaksi Parkir Masuk`).
  - `terisi` decrements by `1` when a Booking is cancelled/expired OR when a Vehicle exits (`Transaksi Parkir Keluar`).
  - **Verification Rule**: When a Booking is verified at the gate (`Verifikasi Booking`), `terisi` does **NOT** increment again because the slot was already reserved when the booking was created.

### 3.2. Booking & 10-Minute Expiry Rule (`booking`)
- Riders can only book if:
  1. `tempat_parkir.status_aktif == 1` and `tempat_parkir.sediakan_booking == 1`,
  2. `tempat_parkir.terisi < tempat_parkir.kapasitas`,
  3. The tenant has an active tariff (`tarif.status_aktif == 1`) for the selected vehicle's `jenis_kendaraan`,
  4. The rider (`status_aktif == 1`) and selected vehicle do **not** have an approved (`'Disetujui'`) ban in `pengajuan_banned` for that `id_tempat_parkir`.
- Upon booking, system generates a unique `kode_booking`, sets status to `'Menunggu'`, and reserves 1 slot.
- **10-Minute Grace Period**: A booking is valid until `waktu_rencana_kedatangan + 10 minutes`. If not verified by Petugas within this window, the system automatically marks status as `'Batal'` and decrements `terisi`.
- Riders can manually cancel an active `'Menunggu'` booking before expiry.

### 3.3. Flexible Multi-Model Tariff & Overnight Fee Calculation (`tarif`)
Each tenant can configure one active tariff per `jenis_kendaraan` (`Motor`, `Mobil`, `Sepeda`) choosing from 3 pricing models (`tipe_tarif`) plus an optional overnight fee (`tarif_inap`):
1. **Duration Calculation (`durasi_jam`)**:
   - Calculated from `waktu_masuk` to `waktu_keluar` rounded up to the nearest hour (`CEIL(TIMESTAMPDIFF(SECOND, waktu_masuk, waktu_keluar) / 3600)`), with a **minimum of 1 hour**.
2. **Overnight Stay Calculation (`jumlah_inap`)**:
   - Calculated via calendar date difference: `DATEDIFF(DATE(waktu_keluar), DATE(waktu_masuk))`.
   - If a vehicle exits on the same calendar day (`jumlah_inap == 0`), no overnight fee is added. If it crosses midnight (`jumlah_inap >= 1`), $\text{jumlah\_inap} \times \text{tarif\_inap}$ is added to the base fee.
3. **Total Fee Formula (`biaya_total`) by `tipe_tarif`**:
   - **`'Per Jam'`**:
     $$\text{biaya\_total} = (\text{durasi\_jam} \times \text{tarif\_per\_jam}) + (\text{jumlah\_inap} \times \text{tarif\_inap})$$
   - **`'Flat Harian'`**:
     $$\text{biaya\_total} = \text{tarif\_per\_jam} + (\text{jumlah\_inap} \times \text{tarif\_inap})$$
   - **`'Progresif Mal'`**:
     $$\text{biaya\_total} = \text{tarif\_jam\_pertama} + ((\text{durasi\_jam} - 1) \times \text{tarif\_berikutnya}) + (\text{jumlah\_inap} \times \text{tarif\_inap})$$

### 3.4. Inbound Parking, On-Device OCR & Ban Check (`transaksi` - Masuk)
- Petugas scans the vehicle's license plate using **Google ML Kit Text Recognition (On-Device OCR)**, verifies a booking (`Verifikasi Booking`), or selects **`Input Manual`**.
- **OCR Preview & Correction**: After scanning, the app displays the extracted plate string in an editable field so Petugas can fix any OCR misreads before lookup.
- System queries `kendaraan` by normalized `plat_nomor`:
  - Checks `pengajuan_banned` for this `id_tempat_parkir` where `status_pengajuan == 'Disetujui'` matching either `id_kendaraan` (supports banning non-account/offline vehicles) or `id_pengendara`.
  - If banned/problematic, system displays a warning alert to Petugas with an option to cancel (`Batalkan Transaksi`) or override/proceed (`Lanjutkan Transaksi`).
  - If the plate is unregistered, Petugas inputs `jenis_kendaraan` and `warna` to create a record in `kendaraan` (`id_pengendara = NULL`, `id_user_input = Auth::id()`).
- Upon confirmation, system inserts a `transaksi` record (`status = 'masuk'`, `status_pembayaran = 'Belum Bayar'`, `waktu_masuk = NOW()`), increments `terisi` (if walk-in), logs the action to `log_aktivitas`, and navigates to `Cetak Karcis Fisik` with two options:
  1. **Print Physical Ticket (`Cetak Karcis Fisik`)**: Generates printable thermal receipt with QR Code (`id_transaksi`).
  2. **Send Digital Ticket (`Karcis Digital`)**: Enabled **ONLY** if the vehicle is linked to a registered `pengendara` account (`id_pengendara != NULL`).

### 3.5. Outbound Parking & Lost/Damaged Ticket Mitigation (`transaksi` - Keluar)
- **Primary Flow (QR Scan)**: Petugas scans the QR Code on the physical or digital ticket.
- **Fallback Flow (Lost/Damaged Ticket or Unplated Vehicle Lookup)**: If the physical ticket is damaged, lost, or unreadable (`Karcis Terbaca? == False`), Petugas inputs the `plat_nomor` or searches active transactions (`status = 'masuk'`) at that tenant to locate the transaction.
- System validates the active transaction, executes the tariff calculation formula, and displays transaction details alongside `status_pembayaran` (`'Sudah Bayar'` if paid online via rider's digital ticket, or `'Belum Bayar'` if paying cash at the gate).
- If `'Belum Bayar'`, Petugas collects cash and clicks `Ubah Status Bayar`, then clicks `Konfirmasi`.
- System updates `waktu_keluar = NOW()`, `durasi_jam`, `biaya_total`, `status_pembayaran = 'Sudah Bayar'`, `status = 'keluar'`, decrements `tempat_parkir.terisi`, and records the action in `log_aktivitas`.

### 3.6. Internal Fraud Mitigation & Audit Trail (`Dashboard Owner` & `log_aktivitas`)
- **Active Vehicle Discrepancy Metric**: On `Dashboard Owner`, prominently display:
  - Total Inbound Today (`Transaksi Masuk Hari Ini`),
  - Total Outbound Today (`Transaksi Keluar Hari Ini`),
  - **Currently Parked Vehicles (`Kendaraan Aktif di Lahan`)** = count of `transaksi` where `status = 'masuk'`,
  - **Overstay Alert (`Parkir > 24 Jam`)** = count of active `'masuk'` transactions exceeding 24 hours.
  This allows the Owner to immediately spot anomalies between physical vehicles on the lot and recorded transactions.
- **System Audit Log (`log_aktivitas`)**: Records staff mutations (`id_user`, `id_tempat_parkir`, `aktivitas`, `waktu_aktivitas`) for Admin and Owner transparency.

### 3.7. Vehicle Management & Ownership Claim (`kendaraan` & `pengajuan_klaim_kendaraan`)
- **Immutable History Rule**: If a vehicle in `kendaraan` has ever been linked to any record in `transaksi`, the fields `plat_nomor` and `jenis_kendaraan` MUST be **locked (`disabled`)** on the edit form. Only `warna` can be updated.
- **Unlink Vehicle**: Riders can unlink a vehicle from their account (sets `id_pengendara = NULL` if transaction history exists, or deletes if no history exists).
- **Ownership Claim (`Pengajuan Klaim Kendaraan`)**:
  - Accessed from `Kelola Kendaraan Pribadi` on the Mobile App.
  - Rider inputs `plat_nomor` to search `kendaraan`. If found (`Data Ditemukan == True`) and owned by another rider (`id_pengendara_target`), Rider can confirm (`Lanjutkan Pengajuan == True`) and submit a claim (`status_klaim = 'Pending'`).
  - Target owner receives an in-app notification in `Notifikasi` to Approve (`'Disetujui'`, transferring `kendaraan.id_pengendara` to `id_pengaju`) or Reject (`'Ditolak'`).

### 3.8. Multi-Tenant Banned System (`pengajuan_banned`)
- Admin can submit a ban request from either `Kelola Kendaraan` (by `id_kendaraan`, enabling objective blacklisting of offline/non-account vehicles) or `Daftar Pengendara` (by `id_pengendara`) with an `alasan`.
- Status starts as `'Pending'`. Owner reviews the request in `Kelola Pengajuan Banned` and sets it to `'Disetujui'`, `'Ditolak'`, or `'Dicabut'`.
- When `'Disetujui'`, the ban applies **ONLY** to that specific `id_tempat_parkir` and reduces the rider's `skor_reputasi` (if linked to an account).

---

## 4. Out-of-Scope / Future Roadmap (DO NOT IMPLEMENT IN MVP)
To keep development strictly focused and on-schedule, the following post-MVP features must **NOT** be coded:
1. Geolocation nearest parking calculation & Google Maps route generation (`latitude`/`longitude`).
2. In-App Call / Bell button (`Tombol Panggil Petugas` via WebSockets).
3. Loyalty Points & Subscription Discounts.
4. Self-service Forgot Password recovery via email.
5. Offline-first SQLite local transaction caching.