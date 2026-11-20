-- ==========================================
-- PEMBUATAN DATABASE PARKIRIN (VERSI 2 - SELF CHECK-IN & AUTO-LUNAS)
-- ==========================================
CREATE DATABASE IF NOT EXISTS `db_parkirin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_parkirin`;

-- ==========================================
-- 1. TABEL TEMPAT PARKIR (TENANT)
-- ==========================================
CREATE TABLE `tempat_parkir` (
  `id_tempat_parkir` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_tempat` VARCHAR(100) NOT NULL,
  `kode_qr_gate` VARCHAR(64) NULL UNIQUE COMMENT 'Token QR unik pos gerbang fisik (1x generate)',
  `qr_generated_at` DATETIME NULL COMMENT 'Waktu pembuatan QR gerbang',
  `kapasitas_reguler` INT NOT NULL DEFAULT 0 COMMENT 'Kuota maksimal parkir harian/walk-in',
  `kuota_inap` INT NOT NULL DEFAULT 0 COMMENT 'Kuota maksimal zona parkir menginap >= 2 hari',
  `sediakan_booking_inap` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Terima inap, 0 = Tidak',
  `sediakan_member` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Sediakan paket member, 0 = Tidak',
  `qris_image` VARCHAR(255) NULL COMMENT 'Path gambar QRIS statis milik mitra',
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX `idx_tenant_layanan` (`status_aktif`, `sediakan_booking_inap`, `sediakan_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. TABEL USER (STAF MANAJEMEN & OPERASIONAL)
-- ==========================================
CREATE TABLE `user` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NULL COMMENT 'NULL khusus untuk super_admin',
  `nama_lengkap` VARCHAR(50) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'owner', 'admin', 'petugas') NOT NULL,
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX `idx_user_tenant_role` (`id_tempat_parkir`, `role`, `status_aktif`),
  CONSTRAINT `fk_user_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 3. TABEL PENGENDARA
-- ==========================================
CREATE TABLE `pengendara` (
  `id_pengendara` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `no_telepon` VARCHAR(15) NULL,
  `username` VARCHAR(50) NULL UNIQUE,
  `password` VARCHAR(255) NULL COMMENT 'NULL jika login via Google OAuth',
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 4. TABEL KENDARAAN
-- ==========================================
CREATE TABLE `kendaraan` (
  `id_kendaraan` INT AUTO_INCREMENT PRIMARY KEY,
  `plat_nomor` VARCHAR(15) NOT NULL UNIQUE,
  `jenis_kendaraan` ENUM('Motor', 'Mobil', 'Sepeda') NOT NULL DEFAULT 'Motor',
  `warna` VARCHAR(20) NULL COMMENT 'NULLABLE agar input walk-in cepat',
  `id_pengendara` INT NULL COMMENT 'Terisi jika didaftarkan Member/Inap, NULL jika walk-in',
  INDEX `idx_kendaraan_pengendara` (`id_pengendara`),
  CONSTRAINT `fk_kendaraan_pengendara` FOREIGN KEY (`id_pengendara`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 5. TABEL TARIF
-- ==========================================
CREATE TABLE `tarif` (
  `id_tarif` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NOT NULL,
  `jenis_kendaraan` ENUM('Motor', 'Mobil', 'Sepeda') NOT NULL,
  `tipe_tarif` ENUM('Per Jam', 'Flat Harian', 'Progresif Mal') NOT NULL DEFAULT 'Flat Harian',
  `tarif_per_jam` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tarif_jam_pertama` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tarif_berikutnya` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tarif_inap_harian` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `denda_karcis_hilang` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY `uq_tarif_tenant_kendaraan` (`id_tempat_parkir`, `jenis_kendaraan`),
  CONSTRAINT `fk_tarif_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 6. TABEL PAKET LANGGANAN
-- ==========================================
CREATE TABLE `paket_langganan` (
  `id_paket` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NOT NULL,
  `nama_paket` VARCHAR(50) NOT NULL,
  `jenis_kendaraan` ENUM('Motor', 'Mobil', 'Sepeda') NOT NULL,
  `durasi_hari` INT NOT NULL DEFAULT 30,
  `batas_masuk_harian` INT NULL COMMENT 'NULL = Bebas/Unlimited; >=1 = Maksimal masuk per hari',
  `harga_paket` DECIMAL(15,2) NOT NULL,
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX `idx_paket_tenant` (`id_tempat_parkir`, `jenis_kendaraan`, `status_aktif`),
  CONSTRAINT `fk_paket_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 7. TABEL LANGGANAN MEMBER
-- ==========================================
CREATE TABLE `langganan_member` (
  `id_langganan` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_member` VARCHAR(30) NOT NULL UNIQUE,
  `id_tempat_parkir` INT NOT NULL,
  `id_paket` INT NOT NULL,
  `id_pengendara` INT NOT NULL,
  `id_kendaraan` INT NOT NULL,
  `id_user_verifikasi` INT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `biaya_langganan` DECIMAL(15,2) NOT NULL,
  `bukti_bayar` VARCHAR(255) NOT NULL,
  `waktu_unggah_bukti` DATETIME NOT NULL,
  `status_pembayaran` ENUM('Pending', 'Lunas', 'Ditolak') NOT NULL DEFAULT 'Pending',
  `status_langganan` ENUM('Aktif', 'Kedaluwarsa', 'Dibatalkan') NOT NULL DEFAULT 'Aktif',
  `waktu_registrasi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_validasi_member_gerbang` (`id_tempat_parkir`, `id_kendaraan`, `status_langganan`, `tanggal_mulai`),
  INDEX `idx_alert_verifikasi_member` (`id_tempat_parkir`, `status_pembayaran`),
  CONSTRAINT `fk_member_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_member_paket` FOREIGN KEY (`id_paket`) REFERENCES `paket_langganan` (`id_paket`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_member_pengendara` FOREIGN KEY (`id_pengendara`) REFERENCES `pengendara` (`id_pengendara`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_member_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_member_user_verif` FOREIGN KEY (`id_user_verifikasi`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 8. TABEL BOOKING INAP
-- ==========================================
CREATE TABLE `booking` (
  `id_booking` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_booking` VARCHAR(30) NOT NULL UNIQUE,
  `id_tempat_parkir` INT NOT NULL,
  `id_pengendara` INT NOT NULL,
  `id_kendaraan` INT NOT NULL,
  `id_user_verifikasi` INT NULL,
  `waktu_booking` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_mulai_inap` DATETIME NOT NULL,
  `waktu_rencana_keluar` DATETIME NOT NULL,
  `biaya_inap_dibayar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `bukti_bayar` VARCHAR(255) NULL,
  `waktu_unggah_bukti` DATETIME NULL,
  `status_pembayaran` ENUM('Pending', 'Lunas', 'Ditolak') NOT NULL DEFAULT 'Pending',
  `status` ENUM('Terjadwal', 'Aktif_Inap', 'Overstay', 'Selesai', 'Batal') NOT NULL DEFAULT 'Terjadwal',
  INDEX `idx_monitoring_inap_overstay` (`id_tempat_parkir`, `status`, `waktu_rencana_keluar`),
  INDEX `idx_alert_verifikasi_booking` (`id_tempat_parkir`, `status_pembayaran`),
  CONSTRAINT `fk_booking_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_pengendara` FOREIGN KEY (`id_pengendara`) REFERENCES `pengendara` (`id_pengendara`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_user_verif` FOREIGN KEY (`id_user_verifikasi`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 9. TABEL TRANSAKSI (INTI POS)
-- ==========================================
CREATE TABLE `transaksi` (
  `id_transaksi` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_karcis` VARCHAR(30) NOT NULL UNIQUE,
  `id_tempat_parkir` INT NOT NULL,
  `id_kendaraan` INT NOT NULL,
  `id_tarif` INT NOT NULL,
  `id_booking` INT NULL UNIQUE,
  `id_langganan` INT NULL,
  `id_petugas_masuk` INT NULL COMMENT 'NULL = Self Check-In Mandiri; Terisi ID = Input Cadangan Petugas',
  `id_petugas_keluar` INT NULL,
  `waktu_masuk` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_keluar` DATETIME NULL,
  `biaya_dasar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `denda_overstay` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `denda_karcis_hilang` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `identitas_pengambil` VARCHAR(60) NULL COMMENT 'NULL jika keluar normal pakai QR',
  `metode_pembayaran` ENUM('Tunai', 'QRIS_Mitra', 'Prepaid_Booking', 'Member_Pass') NOT NULL DEFAULT 'Tunai',
  `status_pembayaran` ENUM('Belum Bayar', 'Sudah Bayar') NOT NULL DEFAULT 'Belum Bayar',
  `status` ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
  INDEX `idx_anti_passback_aktif` (`id_tempat_parkir`, `status`, `id_kendaraan`),
  INDEX `idx_analitik_yoy` (`id_tempat_parkir`, `waktu_masuk`, `status_pembayaran`),
  INDEX `idx_audit_setoran_shift` (`id_tempat_parkir`, `id_petugas_keluar`, `waktu_keluar`),
  CONSTRAINT `fk_transaksi_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_tarif` FOREIGN KEY (`id_tarif`) REFERENCES `tarif` (`id_tarif`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_langganan` FOREIGN KEY (`id_langganan`) REFERENCES `langganan_member` (`id_langganan`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_petugas_masuk` FOREIGN KEY (`id_petugas_masuk`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_petugas_keluar` FOREIGN KEY (`id_petugas_keluar`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 10. TABEL LOG AKTIVITAS
-- ==========================================
CREATE TABLE `log_aktivitas` (
  `id_log` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NULL,
  `id_user` INT NOT NULL,
  `aktivitas` VARCHAR(150) NOT NULL,
  `waktu_aktivitas` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_log_tenant_keyset` (`id_tempat_parkir`, `id_log`),
  CONSTRAINT `fk_log_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;