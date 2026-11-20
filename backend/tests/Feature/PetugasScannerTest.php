<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\PaketLangganan;
use App\Models\Pengendara;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetugasScannerTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantAndPetugas(): array
    {
        $tenant = TempatParkir::create([
            'nama_tempat' => 'Parkiran Lapangan Utama',
            'kapasitas_reguler' => 20,
            'status_aktif' => 1,
        ]);

        $petugas = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Petugas Jaga Siang',
            'username' => 'petugas_siang',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'status_aktif' => 1,
        ]);

        $tarifMotor = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Per Jam',
            'tarif_jam_pertama' => 3000,
            'tarif_berikutnya' => 2000,
            'tarif_per_jam' => 2000,
            'denda_karcis_hilang' => 25000,
            'status_aktif' => 1,
        ]);

        $tarifMobil = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Per Jam',
            'tarif_jam_pertama' => 5000,
            'tarif_berikutnya' => 3000,
            'tarif_per_jam' => 3000,
            'denda_karcis_hilang' => 50000,
            'status_aktif' => 1,
        ]);

        return [$tenant, $petugas, $tarifMotor, $tarifMobil];
    }

    public function test_active_session_redirects_away_from_landing_login_and_register(): void
    {
        [$tenant, $petugas] = $this->createTenantAndPetugas();

        // 1. Petugas visit landing page -> redirect to /petugas
        $response = $this->actingAs($petugas, 'web')->get('/');
        $response->assertRedirect(route('petugas.dashboard'));

        // 2. Petugas visit login page -> redirect to /petugas
        $response = $this->actingAs($petugas, 'web')->get('/login');
        $response->assertRedirect(route('petugas.dashboard'));

        // 3. Petugas visit register page -> redirect to /petugas
        $response = $this->actingAs($petugas, 'web')->get('/register');
        $response->assertRedirect(route('petugas.dashboard'));

        // 4. Pengendara visit landing page -> redirect to /pengendara
        $pengendara = Pengendara::create([
            'nama_lengkap' => 'Pengendara Tes',
            'email' => 'rider@test.com',
            'username' => 'rider_tes',
            'password' => bcrypt('password'),
            'status_aktif' => 1,
        ]);

        $response = $this->actingAs($pengendara, 'pengendara')->get('/');
        $response->assertRedirect(route('pengendara.dashboard'));

        $response = $this->actingAs($pengendara, 'pengendara')->get('/login');
        $response->assertRedirect(route('pengendara.dashboard'));
    }

    public function test_petugas_dashboard_renders_with_fallback_manual_button(): void
    {
        [$tenant, $petugas] = $this->createTenantAndPetugas();

        $response = $this->actingAs($petugas, 'web')->get(route('petugas.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Catat Kendaraan Masuk (Manual)');
        $response->assertSee('Plat Nomor');
        $response->assertSee('Scan Keluar');
    }

    public function test_petugas_can_process_manual_checkin_fallback(): void
    {
        [$tenant, $petugas, $tarifMotor] = $this->createTenantAndPetugas();

        $response = $this->actingAs($petugas, 'web')->post(route('petugas.manual-checkin'), [
            'plat_nomor' => 'B 9999 MAN',
            'jenis_kendaraan' => 'Motor',
            'warna' => 'Biru',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kendaraan', [
            'plat_nomor' => 'B 9999 MAN',
            'jenis_kendaraan' => 'Motor',
        ]);

        $this->assertDatabaseHas('transaksi', [
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_petugas_masuk' => $petugas->id_user,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);
    }

    public function test_scanner_keluar_page_renders_with_html5_qrcode(): void
    {
        [$tenant, $petugas] = $this->createTenantAndPetugas();

        $response = $this->actingAs($petugas, 'web')->get(route('petugas.scanner-keluar'));

        $response->assertStatus(200);
        $response->assertSee('Pemindai Karcis QR');
        $response->assertSee('html5-qrcode.min.js');
        $response->assertSee('Cari Plat Nomor');
    }

    public function test_scanner_keluar_lookup_calculates_bill_via_stored_function(): void
    {
        [$tenant, $petugas, $tarifMotor] = $this->createTenantAndPetugas();

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 1111 TST',
            'jenis_kendaraan' => 'Motor',
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis' => 'PKR-TST-1111',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifMotor->id_tarif,
            'waktu_masuk' => now()->subMinutes(175), // 2 jam 55 menit = dihitung 3 jam
            'biaya_dasar' => 3000,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        $response = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.lookup'), [
            'kode_karcis' => 'PKR-TST-1111',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'transaksi' => [
                'kode_karcis' => 'PKR-TST-1111',
                'plat_nomor' => 'B 1111 TST',
                'is_member' => false,
            ],
        ]);
        // 3 jam motor: jam pertama 3000 + 2 * 2000 = 7000
        $this->assertEquals(7000, $response->json('transaksi.total_biaya'));
    }

    public function test_scanner_keluar_lookup_returns_zero_for_active_member(): void
    {
        [$tenant, $petugas, $tarifMotor] = $this->createTenantAndPetugas();

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 2222 MEM',
            'jenis_kendaraan' => 'Motor',
        ]);

        $paket = PaketLangganan::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_paket' => 'Member Bulanan Motor',
            'jenis_kendaraan' => 'Motor',
            'durasi_hari' => 30,
            'harga_paket' => 50000,
            'status_aktif' => 1,
        ]);

        $pengendara = Pengendara::create([
            'nama_lengkap' => 'Member Pengendara',
            'email' => 'member@test.com',
            'username' => 'member_user',
            'password' => bcrypt('password'),
            'status_aktif' => 1,
        ]);

        $member = LanggananMember::create([
            'kode_member' => 'MEM-2222',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_paket' => $paket->id_paket,
            'id_pengendara' => $pengendara->id_pengendara,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'biaya_langganan' => 50000,
            'bukti_bayar' => 'bukti_dummy.jpg',
            'waktu_unggah_bukti' => now()->subDays(5),
            'status_pembayaran' => 'Lunas',
            'status_langganan' => 'Aktif',
            'waktu_registrasi' => now()->subDays(5),
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis' => 'PKR-MEM-2222',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifMotor->id_tarif,
            'id_langganan' => $member->id_langganan,
            'waktu_masuk' => now()->subHours(2),
            'biaya_dasar' => 0,
            'status' => 'masuk',
            'status_pembayaran' => 'Sudah Bayar',
            'metode_pembayaran' => 'Member_Pass',
        ]);

        $response = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.lookup'), [
            'kode_karcis' => 'PKR-MEM-2222',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'transaksi' => [
                'kode_karcis' => 'PKR-MEM-2222',
                'is_member' => true,
                'total_biaya' => 0,
            ],
        ]);
    }

    public function test_scanner_keluar_processes_checkout_and_completes_transaction(): void
    {
        [$tenant, $petugas, $tarifMotor] = $this->createTenantAndPetugas();

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 3333 OUT',
            'jenis_kendaraan' => 'Motor',
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis' => 'PKR-OUT-3333',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifMotor->id_tarif,
            'waktu_masuk' => now()->subHours(1),
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        $response = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.checkout'), [
            'id_transaksi' => $transaksi->id_transaksi,
            'metode_bayar' => 'Tunai',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $transaksi->refresh();
        $this->assertEquals('keluar', $transaksi->status);
        $this->assertEquals('Sudah Bayar', $transaksi->status_pembayaran);
        $this->assertEquals($petugas->id_user, $transaksi->id_petugas_keluar);
        $this->assertNotNull($transaksi->waktu_keluar);
    }

    public function test_petugas_cannot_checkin_duplicate_vehicle_when_already_inside(): void
    {
        [$tenant, $petugas, $tarifMotor] = $this->createTenantAndPetugas();

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 6767 IYU',
            'jenis_kendaraan' => 'Motor',
        ]);

        // Kendaraan sudah berada di dalam area parkir
        Transaksi::create([
            'kode_karcis' => 'PKR-FIRST-6767',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifMotor->id_tarif,
            'waktu_masuk' => now()->subHours(1),
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        // Coba input manual lagi dengan plat yang sama
        $response = $this->actingAs($petugas, 'web')->post(route('petugas.manual-checkin'), [
            'plat_nomor' => 'B 6767 IYU',
            'jenis_kendaraan' => 'Motor',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('masih berstatus parkir aktif', session('error'));

        // Pastikan tidak ada transaksi kedua yang tercipta
        $this->assertEquals(1, Transaksi::where('id_kendaraan', $kendaraan->id_kendaraan)->count());
    }

    public function test_scanner_keluar_processes_checkout_for_member_pass_with_zero_fee(): void
    {
        [$tenant, $petugas, $tarifMotor] = $this->createTenantAndPetugas();

        $paket = PaketLangganan::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_paket' => 'Member Bulanan Motor',
            'jenis_kendaraan' => 'Motor',
            'durasi_hari' => 30,
            'harga_paket' => 50000,
            'status_aktif' => 1,
        ]);

        $pengendara = Pengendara::create([
            'nama_lengkap' => 'Member Pengendara',
            'email' => 'member6767@test.com',
            'username' => 'member6767',
            'password' => bcrypt('password'),
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 6767 IYU',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara' => $pengendara->id_pengendara,
        ]);

        $member = LanggananMember::create([
            'kode_member' => 'MEM-6767',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_paket' => $paket->id_paket,
            'id_pengendara' => $pengendara->id_pengendara,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'tanggal_mulai' => now()->subDays(2)->toDateString(),
            'biaya_langganan' => 50000,
            'bukti_bayar' => 'bukti_dummy.jpg',
            'waktu_unggah_bukti' => now()->subDays(2),
            'status_pembayaran' => 'Lunas',
            'status_langganan' => 'Aktif',
            'waktu_registrasi' => now()->subDays(2),
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis' => 'PKR-MEM-6767',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifMotor->id_tarif,
            'id_langganan' => $member->id_langganan,
            'waktu_masuk' => now()->subHours(1),
            'biaya_dasar' => 0,
            'status' => 'masuk',
            'status_pembayaran' => 'Sudah Bayar',
            'metode_pembayaran' => 'Member_Pass',
        ]);

        // Checkout dengan metode_bayar 'Member_Pass' (seperti yang dikirim frontend)
        $response = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.checkout'), [
            'id_transaksi' => $transaksi->id_transaksi,
            'metode_bayar' => 'Member_Pass',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'kode_karcis' => 'PKR-MEM-6767',
                'plat_nomor' => 'B 6767 IYU',
                'total_biaya' => 0,
            ],
        ]);

        $transaksi->refresh();
        $this->assertEquals('keluar', $transaksi->status);
        $this->assertEquals('Member_Pass', $transaksi->metode_pembayaran);
        $this->assertEquals('Sudah Bayar', $transaksi->status_pembayaran);
        $this->assertEquals(0, $transaksi->biaya_dasar);
        $this->assertNotNull($transaksi->waktu_keluar);
    }

    public function test_ticket_status_polling_endpoint_detects_checkout_realtime()
    {
        $tenant = TempatParkir::create([
            'nama_tempat' => 'Mall Kelapa Realtime',
            'alamat' => 'Jl. Boulevard',
            'status_langganan' => 'Active',
            'kapasitas_mobil' => 50,
            'kapasitas_motor' => 50,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tarif_jam_pertama' => 3000,
            'tarif_per_jam' => 2000,
            'tarif_maksimum_harian' => 20000,
            'denda_tiket_hilang' => 25000,
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 9999 RT',
            'jenis_kendaraan' => 'Motor',
            'warna' => 'Hitam',
        ]);

        $transaksi = Transaksi::create([
            'kode_karcis' => 'PKR-REALTIME-9999',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now()->subHour(),
            'biaya_dasar' => 3000,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        // Status saat masih masuk
        $response = $this->getJson(route('checkin.ticket.status', ['kode_karcis' => 'PKR-REALTIME-9999']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'kode_karcis' => 'PKR-REALTIME-9999',
            'status' => 'masuk',
            'is_keluar' => false,
            'plat_nomor' => 'B 9999 RT',
        ]);

        // Setelah checkout
        $transaksi->update([
            'status' => 'keluar',
            'waktu_keluar' => now(),
            'status_pembayaran' => 'Sudah Bayar',
        ]);

        $responseAfter = $this->getJson(route('checkin.ticket.status', ['kode_karcis' => 'PKR-REALTIME-9999']));
        $responseAfter->assertStatus(200);
        $responseAfter->assertJson([
            'success' => true,
            'kode_karcis' => 'PKR-REALTIME-9999',
            'status' => 'keluar',
            'is_keluar' => true,
        ]);
        $this->assertNotNull($responseAfter->json('waktu_keluar'));

        // 404 jika karcis tidak ditemukan
        $response404 = $this->getJson(route('checkin.ticket.status', ['kode_karcis' => 'PKR-NONEXISTENT']));
        $response404->assertStatus(404);
    }

    public function test_flat_harian_same_day_parking_charges_regular_flat_rate_not_inap(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat' => 'Parkiran Sudimara Test',
            'kapasitas_reguler' => 50,
            'status_aktif' => 1,
        ]);

        $petugas = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Petugas Sudimara',
            'username' => 'petugas_sudimara',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'status_aktif' => 1,
        ]);

        $tarifFlat = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 3000,
            'tarif_inap_harian' => 10000,
            'denda_karcis_hilang' => 15000,
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 1234 SUD',
            'jenis_kendaraan' => 'Motor',
        ]);

        // Simulasi parkir normal 1 menit (atau beberapa menit pada hari yang sama)
        $transaksi = Transaksi::create([
            'kode_karcis' => 'PKR-SUD-001',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifFlat->id_tarif,
            'waktu_masuk' => now()->subMinutes(1),
            'biaya_dasar' => 3000,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        $response = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.lookup'), [
            'kode_karcis' => 'PKR-SUD-001',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'transaksi' => [
                'kode_karcis' => 'PKR-SUD-001',
                'biaya_dasar' => 3000,
                'total_biaya' => 3000,
                'is_inap' => false,
                'jumlah_malam' => 0,
            ],
        ]);

        // Process checkout
        $responseCheckout = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.checkout'), [
            'id_transaksi' => $transaksi->id_transaksi,
            'metode_bayar' => 'Tunai',
        ]);

        $responseCheckout->assertStatus(200);
        $responseCheckout->assertJson([
            'success' => true,
            'data' => [
                'total_biaya' => 3000,
            ],
        ]);

        $transaksi->refresh();
        $this->assertEquals(3000, $transaksi->biaya_dasar);
        $this->assertEquals('keluar', $transaksi->status);
    }

    public function test_flat_harian_overnight_parking_charges_inap_rate(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat' => 'Parkiran Sudimara Inap Test',
            'kapasitas_reguler' => 50,
            'status_aktif' => 1,
        ]);

        $petugas = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Petugas Sudimara Inap',
            'username' => 'petugas_sudimara_inap',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'status_aktif' => 1,
        ]);

        $tarifFlat = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 3000,
            'tarif_inap_harian' => 10000,
            'denda_karcis_hilang' => 15000,
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 5678 INP',
            'jenis_kendaraan' => 'Motor',
        ]);

        // Simulasi inap 1 malam (masuk kemarin, keluar hari ini)
        $transaksi1Malam = Transaksi::create([
            'kode_karcis' => 'PKR-INP-001',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifFlat->id_tarif,
            'waktu_masuk' => now()->subDay()->setTime(10, 0, 0),
            'biaya_dasar' => 3000,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        $response1 = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.lookup'), [
            'kode_karcis' => 'PKR-INP-001',
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'success' => true,
            'transaksi' => [
                'kode_karcis' => 'PKR-INP-001',
                'biaya_dasar' => 10000,
                'total_biaya' => 10000,
                'is_inap' => true,
                'jumlah_malam' => 1,
            ],
        ]);

        // Simulasi inap 3 malam (melewati 3 kali 00:00)
        $transaksi3Malam = Transaksi::create([
            'kode_karcis' => 'PKR-INP-003',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarifFlat->id_tarif,
            'waktu_masuk' => now()->subDays(3)->setTime(10, 0, 0),
            'biaya_dasar' => 3000,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
            'metode_pembayaran' => 'Tunai',
        ]);

        $response3 = $this->actingAs($petugas, 'web')->postJson(route('petugas.scanner-keluar.lookup'), [
            'kode_karcis' => 'PKR-INP-003',
        ]);

        $response3->assertStatus(200);
        $response3->assertJson([
            'success' => true,
            'transaksi' => [
                'kode_karcis' => 'PKR-INP-003',
                'biaya_dasar' => 30000,
                'total_biaya' => 30000,
                'is_inap' => true,
                'jumlah_malam' => 3,
            ],
        ]);
    }
}
