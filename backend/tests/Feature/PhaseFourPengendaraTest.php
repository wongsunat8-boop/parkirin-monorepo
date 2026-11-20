<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\PaketLangganan;
use App\Models\Pengendara;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseFourPengendaraTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(): TempatParkir
    {
        return TempatParkir::create([
            'nama_tempat'           => 'Stasiun Sudimara Park',
            'kapasitas_reguler'     => 50,
            'kuota_inap'            => 10,
            'sediakan_booking_inap' => 1,
            'sediakan_member'       => 1,
            'status_aktif'          => 1,
        ]);
    }

    private function createPengendara(string $name = 'Rider Satu', string $email = 'rider1@test.com'): Pengendara
    {
        return Pengendara::create([
            'nama_lengkap' => $name,
            'username'     => explode('@', $email)[0],
            'email'        => $email,
            'password'     => bcrypt('password123'),
            'status_aktif' => 1,
        ]);
    }

    public function test_pengendara_can_update_profile_and_phone_without_otp(): void
    {
        $pengendara = $this->createPengendara();

        $res = $this->actingAs($pengendara, 'pengendara')->patch(route('pengendara.profil.update'), [
            'nama_lengkap' => 'Nama Baru Pengendara',
            'email'        => 'emailbaru@test.com',
            'username'     => 'pengendarabaru',
            'no_telepon'   => '081299998888',
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('pengendara', [
            'id_pengendara' => $pengendara->id_pengendara,
            'nama_lengkap'  => 'Nama Baru Pengendara',
            'email'         => 'emailbaru@test.com',
            'no_telepon'    => '081299998888',
        ]);
    }

    public function test_vehicle_auto_reassign_when_no_active_contract(): void
    {
        $riderA = $this->createPengendara('Rider A', 'ridera@test.com');
        $riderB = $this->createPengendara('Rider B', 'riderb@test.com');

        // Rider A registers vehicle
        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 1111 AAA',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Hitam',
            'id_pengendara'   => $riderA->id_pengendara,
        ]);

        // Rider B adds the exact same plate
        $res = $this->actingAs($riderB, 'pengendara')->post(route('pengendara.kendaraan.store'), [
            'plat_nomor'      => 'B 1111 AAA',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Merah',
        ]);

        $res->assertRedirect();
        $vehicle->refresh();

        // Ownership seamlessly reassigned to Rider B
        $this->assertEquals($riderB->id_pengendara, $vehicle->id_pengendara);
        $this->assertEquals('Merah', $vehicle->warna);
    }

    public function test_vehicle_auto_reassign_blocked_when_active_parking_contract_exists(): void
    {
        $tenant = $this->createTenant();
        $riderA = $this->createPengendara('Rider A', 'ridera@test.com');
        $riderB = $this->createPengendara('Rider B', 'riderb@test.com');

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 2222 BBB',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Hitam',
            'id_pengendara'   => $riderA->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 3000,
            'status_aktif'      => 1,
        ]);

        // Active transaction (status = 'masuk')
        Transaksi::create([
            'kode_karcis'       => 'TRX-ACTIVE-01',
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'id_tarif'          => $tarif->id_tarif,
            'waktu_masuk'       => now(),
            'status'            => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);

        // Rider B tries to add the same plate
        $res = $this->actingAs($riderB, 'pengendara')->post(route('pengendara.kendaraan.store'), [
            'plat_nomor'      => 'B 2222 BBB',
            'jenis_kendaraan' => 'Motor',
            'warna'           => 'Hitam',
        ]);

        $res->assertSessionHas('error');
        $vehicle->refresh();

        // Ownership MUST remain with Rider A
        $this->assertEquals($riderA->id_pengendara, $vehicle->id_pengendara);
    }

    public function test_pengendara_can_buy_member_package_with_auto_lunas_instan(): void
    {
        Storage::fake('public');
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 3333 CCC',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $paket = PaketLangganan::create([
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'nama_paket'         => 'Member Bulanan Motor',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 30,
            'batas_masuk_harian' => 2,
            'harga_paket'        => 75000,
            'status_aktif'       => 1,
        ]);

        $file = UploadedFile::fake()->create('qris_transfer.jpg', 150, 'image/jpeg');

        $res = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.member.store', $tenant), [
            'id_paket'      => $paket->id_paket,
            'id_kendaraan'  => $vehicle->id_kendaraan,
            'tanggal_mulai' => now()->toDateString(),
            'bukti_bayar'   => $file,
        ]);

        $res->assertRedirect(route('pengendara.dashboard'));
        $this->assertDatabaseHas('langganan_member', [
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_pengendara'     => $rider->id_pengendara,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'status_pembayaran' => 'Lunas', // Auto-Lunas Instan
            'status_langganan'  => 'Aktif',
            'id_user_verifikasi'=> null,    // Post-Audit Admin
        ]);
    }

    public function test_vehicle_cannot_buy_second_active_member_package_at_same_tenant(): void
    {
        Storage::fake('public');
        $tenant = $this->createTenant();
        $rider = $this->createPengendara('Rider Double', 'double@test.com');

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 5555 EEE',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $paket1 = PaketLangganan::create([
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'nama_paket'         => 'Member Paket A',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 30,
            'harga_paket'        => 50000,
            'status_aktif'       => 1,
        ]);

        $paket2 = PaketLangganan::create([
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'nama_paket'         => 'Member Paket B',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 60,
            'harga_paket'        => 90000,
            'status_aktif'       => 1,
        ]);

        // Pembelian pertama berhasil
        $file1 = UploadedFile::fake()->create('bukti1.jpg', 150, 'image/jpeg');
        $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.member.store', $tenant), [
            'id_paket'      => $paket1->id_paket,
            'id_kendaraan'  => $vehicle->id_kendaraan,
            'tanggal_mulai' => now()->toDateString(),
            'bukti_bayar'   => $file1,
        ])->assertRedirect(route('pengendara.dashboard'));

        $this->assertEquals(1, LanggananMember::where('id_kendaraan', $vehicle->id_kendaraan)->count());

        // Percobaan pembelian kedua untuk kendaraan yang sama di tenant yang sama harus DITOLAK
        $file2 = UploadedFile::fake()->create('bukti2.jpg', 150, 'image/jpeg');
        $resSecond = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.member.store', $tenant), [
            'id_paket'      => $paket2->id_paket,
            'id_kendaraan'  => $vehicle->id_kendaraan,
            'tanggal_mulai' => now()->toDateString(),
            'bukti_bayar'   => $file2,
        ]);

        $resSecond->assertSessionHasErrors(['id_kendaraan']);
        $this->assertEquals(1, LanggananMember::where('id_kendaraan', $vehicle->id_kendaraan)->count());
    }

    public function test_vehicle_can_buy_member_package_at_different_tenant(): void
    {
        Storage::fake('public');
        $tenantA = $this->createTenant();
        $tenantB = TempatParkir::create([
            'nama_tempat'           => 'Stasiun Rawa Buntu Park',
            'kapasitas_reguler'     => 60,
            'kuota_inap'            => 10,
            'sediakan_booking_inap' => 1,
            'sediakan_member'       => 1,
            'status_aktif'          => 1,
        ]);

        $rider = $this->createPengendara('Rider Multi', 'multi@test.com');

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 7777 FFF',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $paketA = PaketLangganan::create([
            'id_tempat_parkir'   => $tenantA->id_tempat_parkir,
            'nama_paket'         => 'Member Stasiun Sudimara',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 30,
            'harga_paket'        => 50000,
            'status_aktif'       => 1,
        ]);

        $paketB = PaketLangganan::create([
            'id_tempat_parkir'   => $tenantB->id_tempat_parkir,
            'nama_paket'         => 'Member Stasiun Rawa Buntu',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 30,
            'harga_paket'        => 60000,
            'status_aktif'       => 1,
        ]);

        // 1. Beli member di Tenant A -> Berhasil
        $fileA = UploadedFile::fake()->create('buktiA.jpg', 150, 'image/jpeg');
        $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.member.store', $tenantA), [
            'id_paket'      => $paketA->id_paket,
            'id_kendaraan'  => $vehicle->id_kendaraan,
            'tanggal_mulai' => now()->toDateString(),
            'bukti_bayar'   => $fileA,
        ])->assertRedirect(route('pengendara.dashboard'));

        // 2. Beli member untuk kendaraan yang SAMA di Tenant B (lokasi berbeda) -> Harus BERHASIL
        $fileB = UploadedFile::fake()->create('buktiB.jpg', 150, 'image/jpeg');
        $resB = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.member.store', $tenantB), [
            'id_paket'      => $paketB->id_paket,
            'id_kendaraan'  => $vehicle->id_kendaraan,
            'tanggal_mulai' => now()->toDateString(),
            'bukti_bayar'   => $fileB,
        ]);

        $resB->assertRedirect(route('pengendara.dashboard'));

        // Pastikan kendaraan memiliki 2 member di 2 tempat parkir berbeda
        $this->assertEquals(2, LanggananMember::where('id_kendaraan', $vehicle->id_kendaraan)->count());
        $this->assertEquals(1, LanggananMember::where('id_kendaraan', $vehicle->id_kendaraan)->where('id_tempat_parkir', $tenantA->id_tempat_parkir)->count());
        $this->assertEquals(1, LanggananMember::where('id_kendaraan', $vehicle->id_kendaraan)->where('id_tempat_parkir', $tenantB->id_tempat_parkir)->count());
    }

    public function test_pengendara_can_book_inap_with_auto_lunas_instan_and_db_transaction(): void
    {
        Storage::fake('public');
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 4444 DDD',
            'jenis_kendaraan' => 'Mobil',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Mobil',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 5000,
            'tarif_inap_harian' => 30000,
            'status_aktif'      => 1,
        ]);

        $file = UploadedFile::fake()->create('qris_booking.jpg', 150, 'image/jpeg');

        $res = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.booking.store', $tenant), [
            'id_kendaraan'         => $vehicle->id_kendaraan,
            'waktu_mulai_inap'     => now()->addHour()->format('Y-m-d H:i:s'),
            'waktu_rencana_keluar' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'bukti_bayar'          => $file,
        ]);

        $res->assertRedirect(route('pengendara.dashboard'));
        $this->assertDatabaseHas('booking', [
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_pengendara'     => $rider->id_pengendara,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'biaya_inap_dibayar'=> 60000, // 2 malam x 30.000
            'status_pembayaran' => 'Lunas',      // Auto-Lunas Instan
            'status'            => 'Terjadwal',
            'id_user_verifikasi'=> null,
        ]);
    }

    public function test_booking_inap_rejected_if_less_than_two_nights(): void
    {
        Storage::fake('public');
        $tenant = $this->createTenant();
        $rider = $this->createPengendara('Rider Inap Short', 'short@test.com');

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 9999 SHT',
            'jenis_kendaraan' => 'Mobil',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Mobil',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 5000,
            'tarif_inap_harian' => 30000,
            'status_aktif'      => 1,
        ]);

        $file = UploadedFile::fake()->create('qris_short.jpg', 150, 'image/jpeg');

        // 1. Coba booking 1 malam saja (misal masuk hari ini jam 08:00, keluar besok jam 20:00) -> Harus DITOLAK
        $resOneNight = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.booking.store', $tenant), [
            'id_kendaraan'         => $vehicle->id_kendaraan,
            'waktu_mulai_inap'     => now()->addHour()->format('Y-m-d H:i:s'),
            'waktu_rencana_keluar' => now()->addDay()->format('Y-m-d H:i:s'),
            'bukti_bayar'          => $file,
        ]);

        $resOneNight->assertSessionHasErrors(['waktu_rencana_keluar']);
        $this->assertEquals(0, \App\Models\Booking::where('id_kendaraan', $vehicle->id_kendaraan)->count());

        // 2. Coba booking hari yang sama (0 malam) -> Harus DITOLAK
        $resZeroNight = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.booking.store', $tenant), [
            'id_kendaraan'         => $vehicle->id_kendaraan,
            'waktu_mulai_inap'     => now()->addHour()->format('Y-m-d H:i:s'),
            'waktu_rencana_keluar' => now()->addHours(6)->format('Y-m-d H:i:s'),
            'bukti_bayar'          => $file,
        ]);

        $resZeroNight->assertSessionHasErrors(['waktu_rencana_keluar']);
        $this->assertEquals(0, \App\Models\Booking::where('id_kendaraan', $vehicle->id_kendaraan)->count());
    }

    public function test_booking_inap_calculates_midnights_accurately_for_three_nights(): void
    {
        Storage::fake('public');
        $tenant = $this->createTenant();
        $rider = $this->createPengendara('Rider 3 Nights', 'three@test.com');

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 8888 TRI',
            'jenis_kendaraan' => 'Mobil',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Mobil',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 5000,
            'tarif_inap_harian' => 10000, // Rp 10.000 / malam
            'status_aktif'      => 1,
        ]);

        $file = UploadedFile::fake()->create('qris_three.jpg', 150, 'image/jpeg');

        // Contoh kasus pengguna: masuk besok 08:00, keluar 3 hari kemudian jam 20:00 (sebelum 00:00)
        // Melewati 3 malam (Hari 1->2, 2->3, 3->4)
        // Harus dikenakan biaya 3 malam x 10.000 = 30.000 (BUKAN 4 hari = 40.000)
        $waktuMulai = now()->addDays(1)->setTime(8, 0, 0);
        $waktuKeluar = $waktuMulai->copy()->addDays(3)->setTime(20, 0, 0);

        $res = $this->actingAs($rider, 'pengendara')->post(route('pengendara.katalog.booking.store', $tenant), [
            'id_kendaraan'         => $vehicle->id_kendaraan,
            'waktu_mulai_inap'     => $waktuMulai->format('Y-m-d H:i:s'),
            'waktu_rencana_keluar' => $waktuKeluar->format('Y-m-d H:i:s'),
            'bukti_bayar'          => $file,
        ]);

        $res->assertRedirect(route('pengendara.dashboard'));
        $this->assertDatabaseHas('booking', [
            'id_kendaraan'       => $vehicle->id_kendaraan,
            'biaya_inap_dibayar' => 30000, // Tepat 30.000 untuk 3 malam
        ]);
    }

    public function test_self_checkin_fast_track_for_active_member(): void
    {
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 5555 EEE',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 3000,
            'status_aktif'      => 1,
        ]);

        $paket = PaketLangganan::create([
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'nama_paket'         => 'Member Bebas',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 30,
            'batas_masuk_harian' => 5,
            'harga_paket'        => 50000,
            'status_aktif'       => 1,
        ]);

        $member = LanggananMember::create([
            'kode_member'        => 'MBR-FAST-01',
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'id_paket'           => $paket->id_paket,
            'id_pengendara'      => $rider->id_pengendara,
            'id_kendaraan'       => $vehicle->id_kendaraan,
            'tanggal_mulai'      => now()->toDateString(),
            'biaya_langganan'    => 50000,
            'bukti_bayar'        => 'storage/dummy.jpg',
            'waktu_unggah_bukti' => now(),
            'status_pembayaran'  => 'Lunas',
            'status_langganan'   => 'Aktif',
        ]);

        // Self Check-in gate request
        $res = $this->post(route('checkin.store', $tenant), [
            'plat_nomor'      => 'B 5555 EEE',
            'jenis_kendaraan' => 'Motor',
        ]);

        $res->assertRedirect();

        // Transaction is created with Member_Pass, Sudah Bayar, biaya 0
        $this->assertDatabaseHas('transaksi', [
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'id_langganan'      => $member->id_langganan,
            'metode_pembayaran' => 'Member_Pass',
            'status_pembayaran' => 'Sudah Bayar',
            'biaya_dasar'       => 0,
            'status'            => 'masuk',
        ]);
    }

    public function test_self_checkin_auto_fallback_when_daily_limit_exceeded(): void
    {
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 6666 FFF',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 3000,
            'status_aktif'      => 1,
        ]);

        // Member package with max 1 entry per day
        $paket = PaketLangganan::create([
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'nama_paket'         => 'Member 1x Sehari',
            'jenis_kendaraan'    => 'Motor',
            'durasi_hari'        => 30,
            'batas_masuk_harian' => 1,
            'harga_paket'        => 40000,
            'status_aktif'       => 1,
        ]);

        $member = LanggananMember::create([
            'kode_member'        => 'MBR-LIMIT-01',
            'id_tempat_parkir'   => $tenant->id_tempat_parkir,
            'id_paket'           => $paket->id_paket,
            'id_pengendara'      => $rider->id_pengendara,
            'id_kendaraan'       => $vehicle->id_kendaraan,
            'tanggal_mulai'      => now()->toDateString(),
            'biaya_langganan'    => 40000,
            'bukti_bayar'        => 'storage/dummy.jpg',
            'waktu_unggah_bukti' => now(),
            'status_pembayaran'  => 'Lunas',
            'status_langganan'   => 'Aktif',
        ]);

        // Transaction 1 today (uses the daily quota)
        Transaksi::create([
            'kode_karcis'       => 'TRX-MBR-01',
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'id_tarif'          => $tarif->id_tarif,
            'id_langganan'      => $member->id_langganan,
            'waktu_masuk'       => now()->subHours(4),
            'waktu_keluar'      => now()->subHours(2),
            'biaya_dasar'       => 0,
            'metode_pembayaran' => 'Member_Pass',
            'status_pembayaran' => 'Sudah Bayar',
            'status'            => 'keluar',
        ]);

        // Self Check-in gate entry 2 today -> EXCEEDS 1x/day limit!
        $res = $this->post(route('checkin.store', $tenant), [
            'plat_nomor'      => 'B 6666 FFF',
            'jenis_kendaraan' => 'Motor',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('warning');

        // AUTO-FALLBACK: Created as regular Tunai & Belum Bayar
        $this->assertDatabaseHas('transaksi', [
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'id_langganan'      => null,
            'metode_pembayaran' => 'Tunai',
            'status_pembayaran' => 'Belum Bayar',
            'biaya_dasar'       => 3000,
            'status'            => 'masuk',
        ]);
    }

    public function test_admin_can_upload_and_update_tenant_qris_image(): void
    {
        Storage::fake('public');
        $tenant = $this->createTenant();

        $admin = \App\Models\User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap'     => 'Admin Tenant Sudimara',
            'username'         => 'adminsudimara',
            'password'         => bcrypt('password123'),
            'role'             => 'admin',
            'status_aktif'     => 1,
        ]);

        $file = UploadedFile::fake()->create('qris_official.png', 200, 'image/png');

        $res = $this->actingAs($admin, 'web')->post(route('admin.kapasitas.qris'), [
            'qris_image' => $file,
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('success');

        $tenant->refresh();
        $this->assertNotNull($tenant->qris_image);
        $this->assertStringStartsWith('storage/qris/', $tenant->qris_image);
    }

    public function test_pengendara_dashboard_has_scanner_menu_and_quick_action(): void
    {
        $rider = $this->createPengendara();

        $res = $this->actingAs($rider, 'pengendara')->get(route('pengendara.dashboard'));

        $res->assertStatus(200);
        $res->assertSee('Scan Gerbang');
        $res->assertSee('Sedang di Depan Gerbang Masuk?');
        $res->assertSee(route('checkin.scanner'));
    }

    public function test_scanner_page_renders_with_logged_in_rider_context(): void
    {
        $rider = $this->createPengendara();

        $res = $this->actingAs($rider, 'pengendara')->get(route('checkin.scanner'));

        $res->assertStatus(200);
        $res->assertSee('Dashboard Pengendara');
        $res->assertSee($rider->nama_lengkap);
    }

    public function test_checkin_page_shows_saved_vehicles_chips_for_logged_in_rider(): void
    {
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        Kendaraan::create([
            'plat_nomor'      => 'B 9999 VIP',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Flat Harian',
            'tarif_per_jam'     => 3000,
            'status_aktif'      => 1,
        ]);

        $res = $this->actingAs($rider, 'pengendara')->get(route('checkin.public', $tenant->id_tempat_parkir));

        $res->assertStatus(200);
        $res->assertSee('B 9999 VIP');
        $res->assertSee('Kendaraan di Akun Anda:');
        $res->assertSee('Dashboard');
    }

    public function test_riwayat_bayar_cashless_updates_payment_status_and_method(): void
    {
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 7777 BYR',
            'jenis_kendaraan' => 'Mobil',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan'  => 'Mobil',
            'tipe_tarif'       => 'Flat Harian',
            'tarif_per_jam'    => 10000,
            'status_aktif'     => 1,
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis'       => 'PKR-TEST-9988',
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'id_tarif'          => $tarif->id_tarif,
            'waktu_masuk'       => now()->subHour(),
            'biaya_dasar'       => 10000,
            'metode_pembayaran' => 'Tunai',
            'status_pembayaran' => 'Belum Bayar',
            'status'            => 'masuk',
        ]);

        $res = $this->actingAs($rider, 'pengendara')->post(route('pengendara.riwayat.bayar', $transaksi));

        $res->assertRedirect();
        $res->assertSessionHas('success');

        $transaksi->refresh();
        $this->assertEquals('Sudah Bayar', $transaksi->status_pembayaran);
        $this->assertEquals('QRIS_Mitra', $transaksi->metode_pembayaran);
    }

    public function test_digital_ticket_page_renders_offline_libs_and_download_buttons(): void
    {
        $tenant = $this->createTenant();
        $rider = $this->createPengendara();

        $vehicle = Kendaraan::create([
            'plat_nomor'      => 'B 1111 PDF',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $rider->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan'  => 'Motor',
            'tipe_tarif'       => 'Flat Harian',
            'tarif_per_jam'    => 3000,
            'status_aktif'     => 1,
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis'       => 'PKR-TKT-1234',
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $vehicle->id_kendaraan,
            'id_tarif'          => $tarif->id_tarif,
            'waktu_masuk'       => now(),
            'biaya_dasar'       => 3000,
            'metode_pembayaran' => 'Tunai',
            'status_pembayaran' => 'Belum Bayar',
            'status'            => 'masuk',
        ]);

        $res = $this->get(route('checkin.ticket', $transaksi->kode_karcis));

        $res->assertStatus(200);
        $res->assertSee('PKR-TKT-1234');
        $res->assertSee('B 1111 PDF');
        $res->assertSee('Unduh PNG');
        $res->assertSee('Unduh PDF');
        $res->assertSee('downloadTicketPDF');
        $res->assertSee('downloadTicketPNG');
        $res->assertSee('renderDedicatedTicketCanvas');
        $res->assertSee('assets/js/jspdf.umd.min.js');
        $res->assertSee('assets/js/html2canvas.min.js');
    }
}
