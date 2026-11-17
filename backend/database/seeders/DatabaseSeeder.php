<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat 1 Tempat Parkir (Tenant)
        $tenantId = DB::table('tempat_parkir')->insertGetId([
            'nama_tempat' => 'Parkiran Stasiun Sudimara',
            'kapasitas_reguler' => 200,
            'kuota_inap' => 50,
            'sediakan_booking_inap' => 1,
            'sediakan_member' => 1,
            'status_aktif' => 1,
        ]);

        // 2. Buat Akun Users (Super Admin, Owner, Admin, Petugas)
        DB::table('user')->insert([
            ['id_tempat_parkir' => null, 'nama_lengkap' => 'Super Admin', 'username' => 'superadmin', 'password' => Hash::make('password'), 'role' => 'super_admin'],
            ['id_tempat_parkir' => $tenantId, 'nama_lengkap' => 'Pak Hendra (Owner)', 'username' => 'owner1', 'password' => Hash::make('password'), 'role' => 'owner'],
            ['id_tempat_parkir' => $tenantId, 'nama_lengkap' => 'Admin Operasional', 'username' => 'admin1', 'password' => Hash::make('password'), 'role' => 'admin'],
            ['id_tempat_parkir' => $tenantId, 'nama_lengkap' => 'Petugas Jaga', 'username' => 'petugas1', 'password' => Hash::make('password'), 'role' => 'petugas'],
        ]);

        // 3. Buat Tarif Parkir
        $tarifId = DB::table('tarif')->insertGetId([
            'id_tempat_parkir' => $tenantId,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 3000,
            'tarif_jam_pertama' => 0,
            'tarif_berikutnya' => 0,
            'tarif_inap_harian' => 10000,
            'denda_karcis_hilang' => 15000,
            'status_aktif' => 1,
        ]);

        // 4. Buat Data Pengendara & Kendaraan
        $pengendaraId = DB::table('pengendara')->insertGetId([
            'nama_lengkap' => 'Diana Barry',
            'email' => 'diana@example.com',
            'no_telepon' => '08123456789',
            'username' => 'dianab',
            'password' => Hash::make('password'),
            'status_aktif' => 1,
        ]);

        // Generate 10 Kendaraan Dummy
        $kendaraanIds = [];
        for ($i = 1; $i <= 10; $i++) {$kendaraanIds[] = DB::table('kendaraan')->insertGetId([
                'plat_nomor' => 'B ' . rand(1000, 9999) . ' ABC',
                'jenis_kendaraan' => 'Motor',
                'warna' => 'Hitam',
                'id_pengendara' => ($i === 1) ?$pengendaraId : null,
            ]);
        }

        // 5. Generate Ribuan Transaksi Dummy (Tahun 2025 & 2026 untuk Grafik YoY)
        $transaksi = [];$startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::now();$globalCounter = 1; // Kunci Perbaikan: Counter global agar kode karcis 100% unik

        while ($startDate->lte($endDate)) {$jumlahTransaksiHariIni = rand(50, 150);
            
            for ($i = 0; $i < $jumlahTransaksiHariIni; $i++) {
                $jamMasuk =$startDate->copy()->addHours(rand(6, 10))->addMinutes(rand(0, 59));
                $jamKeluar =$jamMasuk->copy()->addHours(rand(8, 12))->addMinutes(rand(0, 59));

                $transaksi[] = [
                    // Format unik: TRX-250101-00001
                    'kode_karcis' => 'TRX-' . $startDate->format('ymd') . '-' . sprintf('\%06d', $globalCounter),
                    'id_tempat_parkir' => $tenantId,
                    'id_kendaraan' => $kendaraanIds[array_rand($kendaraanIds)],
                    'id_tarif' => $tarifId,
                    'id_booking' => null,
                    'id_langganan' => null,
                    'id_petugas_masuk' => null, // Self Check-In
                    'id_petugas_keluar' => 4, // ID Petugas1
                    'waktu_masuk' => $jamMasuk,
                    'waktu_keluar' => $jamKeluar,
                    'biaya_dasar' => 3000.00,
                    'denda_overstay' => 0,
                    'denda_karcis_hilang' => 0,
                    'identitas_pengambil' => null,
                    'metode_pembayaran' => 'Tunai',
                    'status_pembayaran' => 'Sudah Bayar',
                    'status' => 'keluar',
                ];
                $globalCounter++;
            }
            $startDate->addDay();
        }

        // Insert menggunakan chunk agar memori aman
        $chunks = array_chunk($transaksi, 1000);
        foreach ($chunks as$chunk) {
            DB::table('transaksi')->insert($chunk);
        }
    }
}