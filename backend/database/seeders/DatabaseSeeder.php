<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\Pengendara;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic data.
     *
     * - 1 Super Admin
     * - 2 Tenants (1 Flat Harian stasiun, 1 Progresif Mal)
     * - Each tenant: 1 Owner, 1 Admin, 1 Petugas, 3 active Tariffs
     * - 3 Pengendara accounts with vehicles
     */
    public function run(): void
    {
        // ══════════════════════════════════════
        // 1. SUPER ADMIN
        // ══════════════════════════════════════
        User::create([
            'id_tempat_parkir' => null,
            'nama_lengkap'     => 'Super Admin Parkirin',
            'username'         => 'superadmin',
            'password'         => Hash::make('password123'),
            'role'             => 'super_admin',
            'status_aktif'     => 1,
        ]);

        // ══════════════════════════════════════
        // 2. TENANT 1 — Stasiun Gambir (Flat Harian)
        // ══════════════════════════════════════
        $tenant1 = TempatParkir::create([
            'nama_tempat'      => 'Parkir Stasiun Gambir',
            'kapasitas'        => 150,
            'terisi'           => 0,
            'sediakan_booking' => 1,
            'status_aktif'     => 1,
        ]);

        $owner1 = User::create([
            'id_tempat_parkir' => $tenant1->id_tempat_parkir,
            'nama_lengkap'     => 'Budi Hartono',
            'username'         => 'owner_gambir',
            'password'         => Hash::make('password123'),
            'role'             => 'owner',
            'status_aktif'     => 1,
        ]);

        $admin1 = User::create([
            'id_tempat_parkir' => $tenant1->id_tempat_parkir,
            'nama_lengkap'     => 'Siti Aminah',
            'username'         => 'admin_gambir',
            'password'         => Hash::make('password123'),
            'role'             => 'admin',
            'status_aktif'     => 1,
        ]);

        $petugas1 = User::create([
            'id_tempat_parkir' => $tenant1->id_tempat_parkir,
            'nama_lengkap'     => 'Joko Susilo',
            'username'         => 'petugas_gambir',
            'password'         => Hash::make('password123'),
            'role'             => 'petugas',
            'status_aktif'     => 1,
        ]);

        // Tarif Tenant 1 — Flat Harian
        Tarif::create([
            'id_tempat_parkir'  => $tenant1->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 5000.00,
            'tarif_jam_pertama' => 0.00,
            'tarif_berikutnya'  => 0.00,
            'tarif_inap'        => 3000.00,
            'status_aktif'      => 1,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant1->id_tempat_parkir,
            'jenis_kendaraan'   => 'Mobil',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 10000.00,
            'tarif_jam_pertama' => 0.00,
            'tarif_berikutnya'  => 0.00,
            'tarif_inap'        => 5000.00,
            'status_aktif'      => 1,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant1->id_tempat_parkir,
            'jenis_kendaraan'   => 'Sepeda',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 2000.00,
            'tarif_jam_pertama' => 0.00,
            'tarif_berikutnya'  => 0.00,
            'tarif_inap'        => 1000.00,
            'status_aktif'      => 1,
        ]);

        // ══════════════════════════════════════
        // 3. TENANT 2 — Mall Grand Indonesia (Progresif Mal)
        // ══════════════════════════════════════
        $tenant2 = TempatParkir::create([
            'nama_tempat'      => 'Parkir Mall Grand Indonesia',
            'kapasitas'        => 500,
            'terisi'           => 0,
            'sediakan_booking' => 1,
            'status_aktif'     => 1,
        ]);

        $owner2 = User::create([
            'id_tempat_parkir' => $tenant2->id_tempat_parkir,
            'nama_lengkap'     => 'Andi Wijaya',
            'username'         => 'owner_grandindonesia',
            'password'         => Hash::make('password123'),
            'role'             => 'owner',
            'status_aktif'     => 1,
        ]);

        $admin2 = User::create([
            'id_tempat_parkir' => $tenant2->id_tempat_parkir,
            'nama_lengkap'     => 'Dewi Lestari',
            'username'         => 'admin_grandindonesia',
            'password'         => Hash::make('password123'),
            'role'             => 'admin',
            'status_aktif'     => 1,
        ]);

        $petugas2 = User::create([
            'id_tempat_parkir' => $tenant2->id_tempat_parkir,
            'nama_lengkap'     => 'Rudi Prasetyo',
            'username'         => 'petugas_grandindonesia',
            'password'         => Hash::make('password123'),
            'role'             => 'petugas',
            'status_aktif'     => 1,
        ]);

        // Tarif Tenant 2 — Progresif Mal
        Tarif::create([
            'id_tempat_parkir'  => $tenant2->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Progresif Mal',
            'tarif_per_jam'     => 0.00,
            'tarif_jam_pertama' => 3000.00,
            'tarif_berikutnya'  => 2000.00,
            'tarif_inap'        => 5000.00,
            'status_aktif'      => 1,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant2->id_tempat_parkir,
            'jenis_kendaraan'   => 'Mobil',
            'tipe_tarif'        => 'Progresif Mal',
            'tarif_per_jam'     => 0.00,
            'tarif_jam_pertama' => 5000.00,
            'tarif_berikutnya'  => 3000.00,
            'tarif_inap'        => 10000.00,
            'status_aktif'      => 1,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant2->id_tempat_parkir,
            'jenis_kendaraan'   => 'Sepeda',
            'tipe_tarif'        => 'Progresif Mal',
            'tarif_per_jam'     => 0.00,
            'tarif_jam_pertama' => 1000.00,
            'tarif_berikutnya'  => 500.00,
            'tarif_inap'        => 2000.00,
            'status_aktif'      => 1,
        ]);

        // ══════════════════════════════════════
        // 4. PENGENDARA (3 accounts + vehicles)
        // ══════════════════════════════════════
        $pengendara1 = Pengendara::create([
            'nama_lengkap'   => 'Ahmad Fauzi',
            'email'          => 'ahmad.fauzi@gmail.com',
            'no_telepon'     => '081234567890',
            'username'       => 'ahmadfauzi',
            'password'       => Hash::make('password123'),
            'skor_reputasi'  => 100,
            'status_aktif'   => 1,
        ]);

        Kendaraan::create([
            'plat_nomor'      => 'B1234XYZ',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Hitam',
            'id_pengendara'   => $pengendara1->id_pengendara,
            'id_user_input'   => null,
        ]);

        Kendaraan::create([
            'plat_nomor'      => 'B5678ABC',
            'jenis_kendaraan' => 'Mobil',
            'warna'           => 'Putih',
            'id_pengendara'   => $pengendara1->id_pengendara,
            'id_user_input'   => null,
        ]);

        $pengendara2 = Pengendara::create([
            'nama_lengkap'   => 'Rina Marlina',
            'email'          => 'rina.marlina@yahoo.com',
            'no_telepon'     => '085678901234',
            'username'       => 'rinamarlina',
            'password'       => Hash::make('password123'),
            'skor_reputasi'  => 100,
            'status_aktif'   => 1,
        ]);

        Kendaraan::create([
            'plat_nomor'      => 'D2345QWE',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Merah',
            'id_pengendara'   => $pengendara2->id_pengendara,
            'id_user_input'   => null,
        ]);

        Kendaraan::create([
            'plat_nomor'      => 'D6789RTY',
            'jenis_kendaraan' => 'Sepeda',
            'warna'           => 'Biru',
            'id_pengendara'   => $pengendara2->id_pengendara,
            'id_user_input'   => null,
        ]);

        $pengendara3 = Pengendara::create([
            'nama_lengkap'   => 'Dimas Prasetya',
            'email'          => 'dimas.prasetya@outlook.com',
            'no_telepon'     => '087890123456',
            'username'       => 'dimasprasetya',
            'password'       => Hash::make('password123'),
            'skor_reputasi'  => 100,
            'status_aktif'   => 1,
        ]);

        Kendaraan::create([
            'plat_nomor'      => 'F3456UIO',
            'jenis_kendaraan' => 'Mobil',
            'warna'           => 'Silver',
            'id_pengendara'   => $pengendara3->id_pengendara,
            'id_user_input'   => null,
        ]);

        Kendaraan::create([
            'plat_nomor'      => 'F7890PAS',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Kuning',
            'id_pengendara'   => $pengendara3->id_pengendara,
            'id_user_input'   => null,
        ]);
    }
}
