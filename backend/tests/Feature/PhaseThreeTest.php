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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    private TempatParkir $tenant;
    private User $superAdmin;
    private User $owner;
    private User $admin;
    private User $petugas;
    private Pengendara $pengendara;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TempatParkir::create([
            'nama_tempat' => 'Parkiran Uji Coba',
            'kapasitas_reguler' => 100,
            'kuota_inap' => 20,
            'sediakan_booking_inap' => 1,
            'sediakan_member' => 1,
            'status_aktif' => 1,
        ]);

        $this->superAdmin = User::create([
            'id_tempat_parkir' => null,
            'nama_lengkap' => 'Super Admin',
            'username' => 'superadmin_test',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status_aktif' => 1,
        ]);

        $this->owner = User::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'nama_lengkap' => 'Owner Test',
            'username' => 'owner_test',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status_aktif' => 1,
        ]);

        $this->admin = User::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'nama_lengkap' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status_aktif' => 1,
        ]);

        $this->petugas = User::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'nama_lengkap' => 'Petugas Test',
            'username' => 'petugas_test',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'status_aktif' => 1,
        ]);

        $this->pengendara = Pengendara::create([
            'nama_lengkap' => 'Rider Test',
            'email' => 'rider@test.com',
            'no_telepon' => '081299998888',
            'username' => 'ridertest',
            'password' => bcrypt('password'),
            'status_aktif' => 1,
        ]);
    }

    public function test_admin_can_manage_paket_langganan_with_daily_limit(): void
    {
        $response = $this->actingAs($this->admin, 'web')->post(route('admin.paket-langganan.store'), [
            'nama_paket' => 'Member Mobil Max 1x',
            'jenis_kendaraan' => 'Mobil',
            'durasi_hari' => 30,
            'batas_masuk_harian' => 1,
            'harga_paket' => 150000,
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('admin.paket-langganan.index'));
        $this->assertDatabaseHas('paket_langganan', [
            'nama_paket' => 'Member Mobil Max 1x',
            'batas_masuk_harian' => 1,
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
        ]);

        $paket = PaketLangganan::first();
        $resIndex = $this->actingAs($this->admin, 'web')->get(route('admin.paket-langganan.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Member Mobil Max 1x');

        // Toggle
        $this->actingAs($this->admin, 'web')->patch(route('admin.paket-langganan.toggle', $paket));
        $this->assertDatabaseHas('paket_langganan', ['id_paket' => $paket->id_paket, 'status_aktif' => 0]);
    }

    public function test_admin_can_verify_and_reject_online_payments(): void
    {
        $paket = PaketLangganan::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'nama_paket' => 'Member Bulanan',
            'jenis_kendaraan' => 'Motor',
            'durasi_hari' => 30,
            'batas_masuk_harian' => 2,
            'harga_paket' => 50000,
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 1234 TEST',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara' => $this->pengendara->id_pengendara,
        ]);

        $langganan = LanggananMember::create([
            'kode_member' => 'MBR-TEST-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_paket' => $paket->id_paket,
            'id_pengendara' => $this->pengendara->id_pengendara,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_user_verifikasi' => null,
            'tanggal_mulai' => now()->toDateString(),
            'biaya_langganan' => 50000,
            'bukti_bayar' => 'uploads/qris_dummy.png',
            'waktu_unggah_bukti' => now(),
            'status_pembayaran' => 'Pending',
            'status_langganan' => 'Aktif',
        ]);

        // Verifikasi Index
        $resIndex = $this->actingAs($this->admin, 'web')->get(route('admin.verifikasi.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('MBR-TEST-001');

        // Admin verifies
        $resVerify = $this->actingAs($this->admin, 'web')->post(route('admin.verifikasi.member.verifikasi', $langganan));
        $resVerify->assertSessionHas('success');
        $this->assertDatabaseHas('langganan_member', [
            'id_langganan' => $langganan->id_langganan,
            'status_pembayaran' => 'Lunas',
            'id_user_verifikasi' => $this->admin->id_user,
        ]);
    }

    public function test_owner_audit_setoran_calculates_cash_per_shift(): void
    {
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 9999 SHIFT',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara' => $this->pengendara->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 3000,
            'tarif_inap_harian' => 10000,
            'denda_karcis_hilang' => 15000,
            'status_aktif' => 1,
        ]);

        Transaksi::create([
            'kode_karcis' => 'TRX-SETOR-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'id_petugas_masuk' => null,
            'id_petugas_keluar' => $this->petugas->id_user,
            'waktu_masuk' => now()->subHours(2),
            'waktu_keluar' => now(),
            'biaya_dasar' => 3000,
            'denda_overstay' => 0,
            'denda_karcis_hilang' => 0,
            'metode_pembayaran' => 'Tunai',
            'status_pembayaran' => 'Sudah Bayar',
            'status' => 'keluar',
        ]);

        $resSetoran = $this->actingAs($this->owner, 'web')->get(route('owner.audit-setoran.index'));
        $resSetoran->assertStatus(200);
        $resSetoran->assertSee('Rekap Setoran Kasir');
        $resSetoran->assertSee('3.000');

        $resPrint = $this->actingAs($this->owner, 'web')->get(route('owner.audit-setoran.print'));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('LEMBAR REKAP DAN PENGECEKAN SETORAN KASIR');
    }

    public function test_owner_transaksi_print_renders_all_three_sections_and_a4_layout(): void
    {
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 7777 A4',
            'jenis_kendaraan' => 'Mobil',
            'id_pengendara' => $this->pengendara->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Mobil',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 5000,
            'tarif_inap_harian' => 25000,
            'denda_karcis_hilang' => 20000,
            'status_aktif' => 1,
        ]);

        // 2025 transaction
        Transaksi::create([
            'kode_karcis' => 'TRX-2025-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => '2025-05-10 10:00:00',
            'waktu_keluar' => '2025-05-10 12:00:00',
            'biaya_dasar' => 5000,
            'denda_overstay' => 0,
            'denda_karcis_hilang' => 0,
            'status_pembayaran' => 'Sudah Bayar',
            'status' => 'keluar',
        ]);

        // 2026 transaction
        Transaksi::create([
            'kode_karcis' => 'TRX-2026-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now()->subHours(3),
            'waktu_keluar' => now()->subHour(),
            'biaya_dasar' => 10000,
            'denda_overstay' => 0,
            'denda_karcis_hilang' => 0,
            'status_pembayaran' => 'Sudah Bayar',
            'status' => 'keluar',
        ]);

        $res = $this->actingAs($this->owner, 'web')->get(route('owner.transaksi.print'));
        $res->assertStatus(200);

        // Header & Kop Dokumen
        $res->assertSee('REKAPITULASI LAPORAN TRANSAKSI PARKIR');
        $res->assertSee('Parkiran Uji Coba');
        $res->assertSee('Sistem Manajemen Parkir Digital');

        // Bagian 1: Perbandingan Tahunan (YoY)
        $res->assertSee('Bagian 1');
        $res->assertSee('Rangkuman Transaksi Perbandingan Tahunan (2025 vs 2026)');
        $res->assertSee('Kesimpulan Analisis Tahunan:');

        // Bagian 2: 7 Hari Terakhir
        $res->assertSee('Bagian 2');
        $res->assertSee('Rangkuman Transaksi 7 Hari Terakhir');
        $res->assertSee('Kesimpulan Analisis 7 Hari:');

        // Bagian 3: Filter & Daftar Transaksi
        $res->assertSee('Bagian 3');
        $res->assertSee('Rangkuman Transaksi Terpilih (Sesuai Filter)');
        $res->assertSee('Kesimpulan Data Terfilter:');
        $res->assertSee('B 7777 A4');

        // Footer & Tanda Tangan
        $res->assertSee('Petugas / Operator Kasir');
        $res->assertSee('Pemilik Lahan / Penanggung Jawab');
    }

    public function test_owner_transaksi_export_streams_csv_with_all_three_sections(): void
    {
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 5555 CSV',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara' => $this->pengendara->id_pengendara,
        ]);

        $tarif = Tarif::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'jenis_kendaraan' => 'Motor',
            'tipe_tarif' => 'Flat Harian',
            'tarif_per_jam' => 3000,
            'tarif_inap_harian' => 10000,
            'denda_karcis_hilang' => 15000,
            'status_aktif' => 1,
        ]);

        Transaksi::create([
            'kode_karcis' => 'TRX-CSV-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'waktu_masuk' => now()->subHour(),
            'waktu_keluar' => null,
            'biaya_dasar' => 3000,
            'denda_overstay' => 0,
            'denda_karcis_hilang' => 0,
            'status_pembayaran' => 'Belum Bayar',
            'status' => 'masuk',
        ]);

        $response = $this->actingAs($this->owner, 'web')->get(route('owner.transaksi.export', [
            'status' => 'masuk',
            'pembayaran' => 'Belum Bayar',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        // Stream output checking
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // Check BOM UTF-8
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);

        // Check Header Identitas
        $this->assertStringContainsString('PARKIRIN - SISTEM MANAJEMEN PARKIR DIGITAL', $csvContent);
        $this->assertStringContainsString('Parkiran Uji Coba', $csvContent);

        // Check Bagian 1, 2, 3
        $this->assertStringContainsString('BAGIAN 1: RANGKUMAN TRANSAKSI PERBANDINGAN TAHUNAN (2025 vs 2026)', $csvContent);
        $this->assertStringContainsString('BAGIAN 2: RANGKUMAN TRANSAKSI 7 HARI TERAKHIR', $csvContent);
        $this->assertStringContainsString('BAGIAN 3: RANGKUMAN TRANSAKSI TERPILIH (SESUAI FILTER AKTIF)', $csvContent);
        $this->assertStringContainsString('DAFTAR DETAIL RIWAYAT TRANSAKSI SESUAI FILTER', $csvContent);
        $this->assertStringContainsString('B 5555 CSV', $csvContent);
    }

    public function test_owner_sees_approved_member_and_booking_as_non_tunai_payments(): void
    {
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 8888 NON',
            'jenis_kendaraan' => 'Motor',
            'id_pengendara' => $this->pengendara->id_pengendara,
        ]);

        $paket = PaketLangganan::create([
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'nama_paket' => 'Member Bulanan Motor',
            'jenis_kendaraan' => 'Motor',
            'durasi_hari' => 30,
            'batas_masuk_harian' => 2,
            'harga_paket' => 75000,
            'status_aktif' => 1,
        ]);

        // Member Pass Lunas (Non-Tunai Online)
        $member = LanggananMember::create([
            'kode_member' => 'MBR-LUNAS-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_paket' => $paket->id_paket,
            'id_pengendara' => $this->pengendara->id_pengendara,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_user_verifikasi' => $this->admin->id_user,
            'tanggal_mulai' => now()->toDateString(),
            'biaya_langganan' => 75000,
            'bukti_bayar' => 'uploads/bukti_member.png',
            'waktu_unggah_bukti' => now(),
            'waktu_registrasi' => now(),
            'status_pembayaran' => 'Lunas',
            'status_langganan' => 'Aktif',
        ]);

        // Booking Inap Lunas (Non-Tunai Online)
        $booking = Booking::create([
            'kode_booking' => 'BKG-LUNAS-001',
            'id_tempat_parkir' => $this->tenant->id_tempat_parkir,
            'id_pengendara' => $this->pengendara->id_pengendara,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_user_verifikasi' => $this->admin->id_user,
            'waktu_booking' => now(),
            'waktu_mulai_inap' => now()->startOfDay()->addHours(10),
            'waktu_rencana_keluar' => now()->addDays(3)->startOfDay()->addHours(10),
            'biaya_inap_dibayar' => 30000,
            'bukti_bayar' => 'uploads/bukti_booking.png',
            'waktu_unggah_bukti' => now(),
            'status_pembayaran' => 'Lunas',
            'status' => 'Terjadwal',
        ]);

        // 1. Audit Setoran Owner Check
        $resAudit = $this->actingAs($this->owner, 'web')->get(route('owner.audit-setoran.index', [
            'dari' => now()->subDay()->toDateString(),
            'sampai' => now()->addDay()->toDateString(),
        ]));
        $resAudit->assertStatus(200);
        $resAudit->assertSee('MBR-LUNAS-001');
        $resAudit->assertSee('BKG-LUNAS-001');
        $resAudit->assertSee('75.000');
        $resAudit->assertSee('30.000');
        // Total non-tunai card should include 105.000
        $resAudit->assertSee('105.000');

        // 2. Audit Setoran Print Check
        $resAuditPrint = $this->actingAs($this->owner, 'web')->get(route('owner.audit-setoran.print', [
            'dari' => now()->subDay()->toDateString(),
            'sampai' => now()->addDay()->toDateString(),
        ]));
        $resAuditPrint->assertStatus(200);
        $resAuditPrint->assertSee('MBR-LUNAS-001');
        $resAuditPrint->assertSee('BKG-LUNAS-001');
        $resAuditPrint->assertSee('105.000');

        // 3. Transaksi Index Owner Check
        $resTrx = $this->actingAs($this->owner, 'web')->get(route('owner.transaksi.index', [
            'dari' => now()->subDay()->toDateString(),
            'sampai' => now()->addDay()->toDateString(),
        ]));
        $resTrx->assertStatus(200);
        $resTrx->assertSee('105.000'); // Total non tunai

        // 4. Tab Member
        $resTrxMember = $this->actingAs($this->owner, 'web')->get(route('owner.transaksi.index', [
            'tab' => 'member',
            'dari' => now()->subDay()->toDateString(),
            'sampai' => now()->addDay()->toDateString(),
        ]));
        $resTrxMember->assertStatus(200);
        $resTrxMember->assertSee('MBR-LUNAS-001');
        $resTrxMember->assertSee('75.000');

        // 5. Tab Booking
        $resTrxBooking = $this->actingAs($this->owner, 'web')->get(route('owner.transaksi.index', [
            'tab' => 'booking',
            'dari' => now()->subDay()->toDateString(),
            'sampai' => now()->addDay()->toDateString(),
        ]));
        $resTrxBooking->assertStatus(200);
        $resTrxBooking->assertSee('BKG-LUNAS-001');
        $resTrxBooking->assertSee('30.000');
    }
}
