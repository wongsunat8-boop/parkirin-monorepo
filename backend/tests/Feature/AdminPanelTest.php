<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): array
    {
        $tenant = TempatParkir::create([
            'nama_tempat' => 'Parkir Stasiun Gambir',
            'kapasitas_reguler' => 50,
            'kuota_inap' => 10,
            'sediakan_booking_inap' => 1,
            'sediakan_member' => 1,
            'status_aktif' => 1,
        ]);

        $admin = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Admin Test',
            'username' => 'admintest',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status_aktif' => 1,
        ]);

        return [$tenant, $admin];
    }

    public function test_admin_dashboard_renders_without_user_input_or_kapasitas_errors(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 9999 TST',
            'jenis_kendaraan' => 'Mobil',
            'warna' => 'Hitam',
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Per Jam',
            'tarif_jam_pertama' => 5000,
            'tarif_berikutnya' => 3000,
            'status_aktif' => 1,
        ]);

        Transaksi::create([
            'kode_karcis' => 'KRC-TEST-001',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now(),
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);

        $response = $this->actingAs($admin, 'web')->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Parkir Stasiun Gambir');
        $response->assertSee('Kendaraan Aktif');
    }

    public function test_admin_pengendara_index_renders_successfully(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $response = $this->actingAs($admin, 'web')->get(route('admin.pengendara.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Pengendara');
    }

    public function test_admin_tarif_index_renders_successfully(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $response = $this->actingAs($admin, 'web')->get(route('admin.tarif.index'));

        $response->assertStatus(200);
    }

    public function test_admin_petugas_index_renders_with_transaksi_dilayani_count(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $petugas = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Petugas Jaga Operasional',
            'username' => 'petugasops',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'status_aktif' => 1,
        ]);

        $response = $this->actingAs($admin, 'web')->get(route('admin.petugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Petugas Jaga Operasional');
        $response->assertSee('petugasops');
    }

    public function test_petugas_dashboard_renders_with_transaksi_and_pengendara(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $petugas = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Petugas Jaga',
            'username' => 'petugasjaga',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 1234 PET',
            'jenis_kendaraan' => 'Motor',
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Per Jam',
            'tarif_jam_pertama' => 2000,
            'status_aktif' => 1,
        ]);

        Transaksi::create([
            'kode_karcis' => 'KRC-PET-001',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now(),
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);

        $response = $this->actingAs($petugas, 'web')->get(route('petugas.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Petugas Jaga');
        $response->assertSee('B 1234 PET');
        $response->assertSee('Walk-in / Karcis Fisik');
    }

    public function test_admin_log_aktivitas_renders_without_relation_not_found_error(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 5555 LOG',
            'jenis_kendaraan' => 'Mobil',
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Per Jam',
            'tarif_jam_pertama' => 5000,
            'status_aktif' => 1,
        ]);

        Transaksi::create([
            'kode_karcis' => 'KRC-LOG-001',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now(),
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);

        \App\Models\LogAktivitas::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_user' => $admin->id_user,
            'aktivitas' => 'Testing audit log aktivitas real-time timestamp',
            'waktu_aktivitas' => now(),
        ]);

        $response = $this->actingAs($admin, 'web')->get(route('admin.log-aktivitas.index'));

        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas Parkir');
        $response->assertSee('Aktivitas Transaksi');
        $response->assertSee('B 5555 LOG');
        $response->assertSee('Testing audit log aktivitas real-time timestamp');
        $response->assertSee(now()->format('d/m/Y'));
    }

    public function test_owner_dashboard_and_transaksi_render_successfully(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $owner = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Owner Bisnis',
            'username' => 'ownerbisnis',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 8888 OWN',
            'jenis_kendaraan' => 'Motor',
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Per Jam',
            'tarif_jam_pertama' => 3000,
            'status_aktif' => 1,
        ]);

        Transaksi::create([
            'kode_karcis' => 'KRC-OWN-001',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now(),
            'biaya_dasar' => 3000,
            'denda_overstay' => 0,
            'denda_karcis_hilang' => 0,
            'status' => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);

        $resDash = $this->actingAs($owner, 'web')->get(route('owner.dashboard'));
        $resDash->assertStatus(200);
        $resDash->assertSee('Owner Bisnis');

        $resTrx = $this->actingAs($owner, 'web')->get(route('owner.transaksi.index'));
        $resTrx->assertStatus(200);
        $resTrx->assertSee('Transaksi Parkir');
        $resTrx->assertSee('B 8888 OWN');
    }

    public function test_pengendara_dashboard_and_riwayat_render_successfully(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $pengendara = \App\Models\Pengendara::create([
            'nama_lengkap' => 'Pengendara Hebat',
            'username'     => 'pengendarahebat',
            'email'        => 'pengendara@test.com',
            'password'     => bcrypt('password'),
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor'      => 'B 7777 PNG',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara'   => $pengendara->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 2000,
            'status_aktif'      => 1,
        ]);

        Transaksi::create([
            'kode_karcis'       => 'KRC-PNG-001',
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'id_kendaraan'      => $kendaraan->id_kendaraan,
            'id_tarif'          => $tarif->id_tarif,
            'waktu_masuk'       => now(),
            'status'            => 'masuk',
            'status_pembayaran' => 'Belum Bayar',
        ]);

        $resDash = $this->actingAs($pengendara, 'pengendara')->get(route('pengendara.dashboard'));
        $resDash->assertStatus(200);
        $resDash->assertSee('Dashboard Pengendara');
        $resDash->assertSee('B 7777 PNG');

        $resRiw = $this->actingAs($pengendara, 'pengendara')->get(route('pengendara.riwayat.index'));
        $resRiw->assertStatus(200);
        $resRiw->assertSee('Riwayat Parkir');
        $resRiw->assertSee('B 7777 PNG');
    }

    public function test_admin_tarif_and_paket_accept_formatted_thousand_separator_and_mitigate_double_submit(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        // 1. Simpan tarif dengan format ribuan bertitik ('15.000')
        $postTarif = $this->actingAs($admin, 'web')->post(route('admin.tarif.store'), [
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => '15.000',
            'tarif_inap_harian' => '50.000',
            'denda_karcis_hilang' => '100.000',
            'status_aktif' => 1,
        ]);

        $postTarif->assertRedirect(route('admin.tarif.index'));
        $this->assertDatabaseHas('tarif', [
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Mobil',
            'tarif_per_jam' => 15000,
            'tarif_inap_harian' => 50000,
            'denda_karcis_hilang' => 100000,
        ]);

        // 2. Simulasi double submit race condition dalam hitungan detik
        $replayTarif = $this->actingAs($admin, 'web')->post(route('admin.tarif.store'), [
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => '15.000',
            'status_aktif' => 1,
        ]);
        $replayTarif->assertRedirect(route('admin.tarif.index'));
        $replayTarif->assertSessionMissing('errors');

        // 3. Simpan paket langganan dengan format ribuan bertitik ('75.000')
        $postPaket = $this->actingAs($admin, 'web')->post(route('admin.paket-langganan.store'), [
            'nama_paket' => 'Paket Mobil Hemat Bulanan',
            'jenis_kendaraan' => 'Mobil',
            'durasi_hari' => 30,
            'batas_masuk_harian' => 2,
            'harga_paket' => '75.000',
            'status_aktif' => 1,
        ]);

        $postPaket->assertRedirect(route('admin.paket-langganan.index'));
        $this->assertDatabaseHas('paket_langganan', [
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_paket' => 'Paket Mobil Hemat Bulanan',
            'harga_paket' => 75000,
        ]);
    }

    public function test_owner_dashboard_renders_multi_year_yoy_and_annual_flow_charts(): void
    {
        [$tenant, $admin] = $this->createAdmin();

        $owner = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap' => 'Owner Multi Tahun',
            'username' => 'ownermulti',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 9999 YOY',
            'jenis_kendaraan' => 'Mobil',
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 10000,
            'status_aktif' => 1,
        ]);

        // Buat data transaksi di tahun 2024, 2025, 2026
        foreach ([2024, 2025, 2026] as $yr) {
            Transaksi::create([
                'kode_karcis' => "KRC-YOY-{$yr}",
                'id_tempat_parkir' => $tenant->id_tempat_parkir,
                'id_kendaraan' => $kendaraan->id_kendaraan,
                'id_tarif' => $tarif->id_tarif,
                'waktu_masuk' => "{$yr}-05-10 10:00:00",
                'waktu_keluar' => "{$yr}-05-10 12:00:00",
                'biaya_dasar' => 10000,
                'denda_overstay' => 0,
                'denda_karcis_hilang' => 0,
                'status' => 'keluar',
                'status_pembayaran' => 'Sudah Bayar',
            ]);
        }

        $response = $this->actingAs($owner, 'web')->get(route('owner.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Perkembangan Pendapatan');
        $response->assertSee('Perbandingan Kendaraan Tiap Tahun');
        $response->assertSee('annualFlowChart');
        $response->assertSee('yoyOmzetChart');
        $response->assertSee('btnModeMonthly');
        $response->assertSee('btnModeYearly');
        $response->assertSee('yearTabGroup');

        $yoyData = $response->viewData('yoyChart');
        $this->assertIsArray($yoyData);
        $this->assertContains(2024, $yoyData['years']);
        $this->assertContains(2025, $yoyData['years']);
        $this->assertContains(2026, $yoyData['years']);
        $this->assertCount(3, $yoyData['annual_flow']['labels']);
        $this->assertArrayHasKey('year_analytics', $yoyData);
        $this->assertArrayHasKey('annual_trend', $yoyData);
        $this->assertArrayHasKey(2024, $yoyData['year_analytics']);
        $this->assertArrayHasKey(2025, $yoyData['year_analytics']);
        $this->assertArrayHasKey(2026, $yoyData['year_analytics']);
    }
}
