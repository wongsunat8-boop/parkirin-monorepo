-- ==========================================
-- PEMBUATAN DATABASE
-- ==========================================
CREATE DATABASE IF NOT EXISTS `db_parkirin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_parkirin`;

-- ==========================================
-- 1. TABEL MASTER & PENGGUNA (MULTI-AUTH)
-- ==========================================

-- Tabel Tempat Parkir (Tenant)
CREATE TABLE `tempat_parkir` (
  `id_tempat_parkir` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_tempat` VARCHAR(100) NOT NULL,
  `kapasitas` INT(5) NOT NULL DEFAULT 0,
  `terisi` INT(5) NOT NULL DEFAULT 0,
  `sediakan_booking` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Menyediakan fitur booking, 0 = Hanya walk-in',
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Untuk fitur Kelola Tenant oleh Super Admin',
  INDEX `idx_tempat_aktif_booking` (`status_aktif`, `sediakan_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel User (Staf Manajerial & Operasional: Super Admin, Owner, Admin, Petugas)
CREATE TABLE `user` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NULL COMMENT 'NULL khusus Super Admin. Wajib isi untuk Owner, Admin, dan Petugas',
  `nama_lengkap` VARCHAR(50) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'owner', 'admin', 'petugas') NOT NULL,
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX `idx_user_tenant_role` (`id_tempat_parkir`, `role`, `status_aktif`),
  CONSTRAINT `fk_user_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pengendara (Customer Mobile & Web: Login Lokal & Google OAuth)
CREATE TABLE `pengendara` (
  `id_pengendara` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `no_telepon` VARCHAR(15) NULL,
  `username` VARCHAR(50) NULL UNIQUE COMMENT 'Diisi saat onboarding pertama jika daftar via Google',
  `password` VARCHAR(255) NULL COMMENT 'NULL jika pengguna mendaftar menggunakan Google OAuth',
  `skor_reputasi` INT NOT NULL DEFAULT 100 COMMENT 'Berkurang jika terkena Banned di salah satu tenant',
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX `idx_pengendara_aktif` (`status_aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Tarif Parkir per Tenant (Mendukung Per Jam, Flat Harian, Progresif Mal, & Inap)
CREATE TABLE `tarif` (
  `id_tarif` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NOT NULL,
  `jenis_kendaraan` ENUM('Motor', 'Mobil', 'Sepeda') NOT NULL,
  `tipe_tarif` ENUM('Per Jam', 'Flat Harian', 'Progresif Mal') NOT NULL DEFAULT 'Per Jam' COMMENT 'Model penetapan harga per tenant',
  `tarif_per_jam` DECIMAL(15,2) NOT NULL COMMENT 'Digunakan jika tipe Per Jam murni atau Flat Harian',
  `tarif_jam_pertama` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tarif khusus jam pertama untuk model Progresif Mal',
  `tarif_berikutnya` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tarif konstan per jam berikutnya untuk model Progresif Mal',
  `tarif_inap` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya tambahan per malam jika kendaraan menginap',
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Untuk fitur Nonaktifkan Tarif',
  UNIQUE KEY `uq_tarif_tenant_kendaraan` (`id_tempat_parkir`, `jenis_kendaraan`),
  CONSTRAINT `fk_tarif_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Master Kendaraan (Mendukung Akun & Non-Akun/Offline)
CREATE TABLE `kendaraan` (
  `id_kendaraan` INT AUTO_INCREMENT PRIMARY KEY,
  `plat_nomor` VARCHAR(15) NOT NULL UNIQUE,
  `jenis_kendaraan` ENUM('Motor', 'Mobil', 'Sepeda') NOT NULL,
  `warna` VARCHAR(20) NOT NULL,
  `id_pengendara` INT NULL COMMENT 'Terisi jika dimiliki/diklaim oleh akun Pengendara',
  `id_user_input` INT NULL COMMENT 'Terisi ID Admin/Petugas jika didaftarkan offline',
  INDEX `idx_kendaraan_pengendara` (`id_pengendara`),
  CONSTRAINT `fk_kendaraan_pengendara` FOREIGN KEY (`id_pengendara`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_kendaraan_user_input` FOREIGN KEY (`id_user_input`) 
    REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. TABEL BANNED & KLAIM (MULTI-TENANT)
-- ==========================================

-- Tabel Pengajuan Banned (Mendukung Akun & Plat Nomor Kendaraan Fisik)
CREATE TABLE `pengajuan_banned` (
  `id_banned` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NOT NULL COMMENT 'HANYA diblokir di lokasi parkir (tenant) ini',
  `id_pengaju` INT NOT NULL COMMENT 'ID User (Admin) yang mengajukan pemblokiran',
  `id_pengendara` INT NULL COMMENT 'Terisi jika Admin mem-banned dari menu Daftar Pengendara',
  `id_kendaraan` INT NULL COMMENT 'Terisi jika Admin mem-banned dari menu Kelola Kendaraan',
  `alasan` VARCHAR(100) NOT NULL,
  `status_pengajuan` ENUM('Pending', 'Disetujui', 'Ditolak', 'Dicabut') NOT NULL DEFAULT 'Pending',
  `tanggal_pengajuan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_banned_lookup` (`id_tempat_parkir`, `status_pengajuan`, `id_kendaraan`, `id_pengendara`),
  CONSTRAINT `fk_banned_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_banned_pengaju` FOREIGN KEY (`id_pengaju`) 
    REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_banned_pengendara` FOREIGN KEY (`id_pengendara`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_banned_kendaraan` FOREIGN KEY (`id_kendaraan`) 
    REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pengajuan Klaim Kendaraan Antar-Pengendara
CREATE TABLE `pengajuan_klaim_kendaraan` (
  `id_klaim` INT AUTO_INCREMENT PRIMARY KEY,
  `id_kendaraan` INT NOT NULL COMMENT 'Kendaraan yang ingin diambil alih kepemilikannya',
  `id_pengaju` INT NOT NULL COMMENT 'ID Pengendara baru yang mengajukan klaim',
  `id_pengendara_target` INT NOT NULL COMMENT 'ID Pengendara lama (pemilik saat ini)',
  `status_klaim` ENUM('Pending', 'Disetujui', 'Ditolak', 'Dibatalkan') NOT NULL DEFAULT 'Pending',
  `tanggal_pengajuan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_klaim_target_status` (`id_pengendara_target`, `status_klaim`),
  CONSTRAINT `fk_klaim_kendaraan` FOREIGN KEY (`id_kendaraan`) 
    REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_klaim_pengaju` FOREIGN KEY (`id_pengaju`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_klaim_target` FOREIGN KEY (`id_pengendara_target`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 3. TABEL TRANSAKSIONAL & LOG AKTIVITAS
-- ==========================================

-- Tabel Booking Parkir
CREATE TABLE `booking` (
  `id_booking` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pengendara` INT NOT NULL,
  `id_tempat_parkir` INT NOT NULL,
  `id_kendaraan` INT NOT NULL,
  `waktu_booking` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_rencana_kedatangan` DATETIME NOT NULL,
  `kode_booking` VARCHAR(50) NOT NULL UNIQUE,
  `status` ENUM('Menunggu', 'Selesai', 'Batal') NOT NULL DEFAULT 'Menunggu',
  INDEX `idx_booking_tenant_status` (`id_tempat_parkir`, `status`, `waktu_rencana_kedatangan`),
  INDEX `idx_booking_pengendara_keyset` (`id_pengendara`, `id_booking`),
  CONSTRAINT `fk_booking_pengendara` FOREIGN KEY (`id_pengendara`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_kendaraan` FOREIGN KEY (`id_kendaraan`) 
    REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Transaksi Parkir
CREATE TABLE `transaksi` (
  `id_transaksi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_booking` INT NULL UNIQUE COMMENT 'Relasi 1-to-1 ke tabel booking. NULL jika walk-in biasa',
  `id_kendaraan` INT NOT NULL,
  `id_tempat_parkir` INT NOT NULL,
  `id_tarif` INT NOT NULL,
  `id_pengendara` INT NULL COMMENT 'NULL jika pelanggan offline tanpa akun',
  `id_petugas` INT NULL COMMENT 'ID User (Petugas) yang melayani',
  `waktu_masuk` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_keluar` DATETIME NULL,
  `durasi_jam` INT(5) NULL,
  `biaya_total` DECIMAL(15,2) NULL,
  `status_pembayaran` ENUM('Belum Bayar', 'Sudah Bayar') NOT NULL DEFAULT 'Belum Bayar',
  `status` ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
  INDEX `idx_transaksi_tenant_keyset` (`id_tempat_parkir`, `status`, `id_transaksi`),
  INDEX `idx_transaksi_pengendara_keyset` (`id_pengendara`, `id_transaksi`),
  INDEX `idx_transaksi_waktu_rekap` (`id_tempat_parkir`, `waktu_masuk`, `status_pembayaran`),
  CONSTRAINT `fk_transaksi_booking` FOREIGN KEY (`id_booking`) 
    REFERENCES `booking` (`id_booking`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_kendaraan` FOREIGN KEY (`id_kendaraan`) 
    REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_tarif` FOREIGN KEY (`id_tarif`) 
    REFERENCES `tarif` (`id_tarif`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_pengendara` FOREIGN KEY (`id_pengendara`) 
    REFERENCES `pengendara` (`id_pengendara`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_petugas` FOREIGN KEY (`id_petugas`) 
    REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Log Aktivitas Sistem & Operasional (Standar Soal Negara UKK Paket 2)
CREATE TABLE `log_aktivitas` (
  `id_log` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tempat_parkir` INT NULL COMMENT 'Isolasi log per tenant. NULL jika aktivitas dilakukan Super Admin',
  `id_user` INT NOT NULL COMMENT 'ID User (Admin/Petugas/Owner/Super Admin) pelaku aktivitas',
  `aktivitas` VARCHAR(150) NOT NULL COMMENT 'Deskripsi aktivitas sistem atau operasional',
  `waktu_aktivitas` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_log_tenant_keyset` (`id_tempat_parkir`, `id_log`),
  CONSTRAINT `fk_log_tempat_parkir` FOREIGN KEY (`id_tempat_parkir`) 
    REFERENCES `tempat_parkir` (`id_tempat_parkir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) 
    REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;