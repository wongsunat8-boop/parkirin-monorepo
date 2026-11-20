<?php

namespace Tests\Feature;

use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SelfCheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanner_page_renders_successfully(): void
    {
        $response = $this->get('/scan');
        $response->assertStatus(200);
        $response->assertSee('Pindai QR Gerbang Masuk');
        $response->assertSee('Ganti Kamera');
        $response->assertSee('Upload Foto QR');
    }

    public function test_admin_can_generate_gate_qr_only_once(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkiran Sudimara',
            'kapasitas_reguler'=> 50,
            'kuota_inap'       => 10,
            'status_aktif'     => 1,
        ]);

        $admin = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap'     => 'Admin Sudimara',
            'username'         => 'adminsudimara',
            'password'         => Hash::make('password123'),
            'role'             => 'admin',
            'status_aktif'     => 1,
        ]);

        // 1. Visit QR Gate management page
        $resIndex = $this->actingAs($admin, 'web')->get('/admin/qr-gate');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('QR Gerbang Belum Dibuat');
        $resIndex->assertSee('Generate QR Gerbang Masuk (1x Saja)');

        // 2. Generate QR for the first time
        $resGen = $this->actingAs($admin, 'web')->post('/admin/qr-gate/generate');
        $resGen->assertRedirect();

        $tenant->refresh();
        $this->assertNotNull($tenant->kode_qr_gate);
        $this->assertStringStartsWith('PKR-GATE-', $tenant->kode_qr_gate);
        $this->assertDatabaseHas('log_aktivitas', [
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_user'          => $admin->id_user,
        ]);

        // 3. Page now shows QR is active and locked
        $resIndexAfter = $this->actingAs($admin, 'web')->get('/admin/qr-gate');
        $resIndexAfter->assertStatus(200);
        $resIndexAfter->assertSee('QR Resmi Aktif dan Terkunci');
        $resIndexAfter->assertSee($tenant->kode_qr_gate);
        $resIndexAfter->assertSee('Cetak Standee');

        // 4. Attempt to generate again must fail (blocked 1x policy)
        $resGenSecond = $this->actingAs($admin, 'web')->post('/admin/qr-gate/generate');
        $resGenSecond->assertSessionHasErrors(['qr']);

        // 5. Printable standee page renders
        $resPrint = $this->actingAs($admin, 'web')->get('/admin/qr-gate/print');
        $resPrint->assertStatus(200);
        $resPrint->assertSee('Pos Masuk Mandiri');
        $resPrint->assertSee($tenant->kode_qr_gate);
    }

    public function test_guest_can_input_vehicle_after_scanning_tenant_qr(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkiran Stasiun Sudimara',
            'kapasitas_reguler'=> 100,
            'kuota_inap'       => 20,
            'status_aktif'     => 1,
        ]);
        $gateCode = $tenant->generateGateQr();

        $tarif = Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 3000,
            'tarif_per_jam'     => 2000,
            'status_aktif'      => 1,
        ]);

        // 1. Guest accesses check-in by scanned gate code URL (/in/PKR-GATE-...)
        $response = $this->get('/in/' . $gateCode);
        $response->assertStatus(200);
        $response->assertSee('Parkiran Stasiun Sudimara');
        $response->assertSee($gateCode);
        $response->assertSee('Nomor Plat Kendaraan');
        $response->assertSee('Motor');

        // 2. Guest submits vehicle data
        $postRes = $this->post('/in/' . $gateCode, [
            'plat_nomor'      => 'B 9999 XYZ',
            'jenis_kendaraan' => 'Motor',
        ]);

        $postRes->assertRedirect();
        
        $transaksi = Transaksi::where('id_tempat_parkir', $tenant->id_tempat_parkir)->first();
        $this->assertNotNull($transaksi);
        $this->assertNull($transaksi->id_petugas_masuk);
        $this->assertEquals('masuk', $transaksi->status);

        // 3. Digital ticket page renders successfully without relation error
        $ticketRes = $this->get(route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis]));
        $ticketRes->assertStatus(200);
        $ticketRes->assertSee('KARCIS RESMI AKTIF');
        $ticketRes->assertSee($transaksi->kode_karcis);
        $ticketRes->assertSee('Parkiran Stasiun Sudimara');
        $ticketRes->assertSee('Unduh PNG');
        $ticketRes->assertSee('Unduh PDF');
        $ticketRes->assertSee('Cetak Karcis Fisik');
    }

    public function test_existing_vehicle_type_is_updated_when_guest_inputs_correct_vehicle_type(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkiran Stasiun Pondok Ranji',
            'kapasitas_reguler'=> 50,
            'kuota_inap'       => 10,
            'status_aktif'     => 1,
        ]);
        $gateCode = $tenant->generateGateQr();

        Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 2000,
            'tarif_per_jam'     => 1000,
            'status_aktif'      => 1,
        ]);

        // Pre-create vehicle with old wrong type Mobil
        $oldVehicle = \App\Models\Kendaraan::create([
            'plat_nomor' => 'B6767IYU',
            'jenis_kendaraan' => 'Mobil',
            'warna' => 'Hitam',
        ]);

        // Guest checks in with Motor
        $postRes = $this->post('/in/' . $gateCode, [
            'plat_nomor'      => 'B 6767 IYU',
            'jenis_kendaraan' => 'Motor',
        ]);

        $postRes->assertRedirect();

        $oldVehicle->refresh();
        $this->assertEquals('Motor', $oldVehicle->jenis_kendaraan);

        $transaksi = Transaksi::where('id_kendaraan', $oldVehicle->id_kendaraan)->first();
        $this->assertNotNull($transaksi);

        $ticketRes = $this->get(route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis]));
        $ticketRes->assertStatus(200);
        $ticketRes->assertSee('Motor');
        $ticketRes->assertDontSee('🚗 Mobil');
    }

    public function test_plate_number_is_formatted_according_to_indonesian_tnkb_standard(): void
    {
        // 1. Direct model normalization tests
        $this->assertEquals('B 6767 IYU', \App\Models\Kendaraan::formatPlatNomor('b6767iyu'));
        $this->assertEquals('B 6767 IYU', \App\Models\Kendaraan::formatPlatNomor('B 6767 IYU'));
        $this->assertEquals('DK 1234 XY', \App\Models\Kendaraan::formatPlatNomor('dk1234xy'));
        $this->assertEquals('AB 123 CD',  \App\Models\Kendaraan::formatPlatNomor('AB123CD'));
        $this->assertEquals('D 1 A',      \App\Models\Kendaraan::formatPlatNomor('d1a'));

        // 2. Check-in flow automatically normalizes unspaced plate to spaced TNKB format
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkiran Stasiun Jurang Mangu',
            'kapasitas_reguler'=> 50,
            'kuota_inap'       => 10,
            'status_aktif'     => 1,
        ]);
        $gateCode = $tenant->generateGateQr();

        Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 2000,
            'tarif_per_jam'     => 1000,
            'status_aktif'      => 1,
        ]);

        $postRes = $this->post('/in/' . $gateCode, [
            'plat_nomor'      => 'b9812ygt', // raw unspaced lowercase
            'jenis_kendaraan' => 'Motor',
        ]);
        $postRes->assertRedirect();

        $vehicle = \App\Models\Kendaraan::where('plat_nomor', 'B 9812 YGT')->first();
        $this->assertNotNull($vehicle, 'Vehicle should be stored with spaced Indonesian TNKB format.');
        $this->assertEquals('B 9812 YGT', $vehicle->plat_nomor);

        $transaksi = Transaksi::where('id_kendaraan', $vehicle->id_kendaraan)->first();
        $this->assertNotNull($transaksi);

        $ticketRes = $this->get(route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis]));
        $ticketRes->assertStatus(200);
        $ticketRes->assertSee('B 9812 YGT');
    }

    public function test_cannot_checkin_duplicate_vehicle_when_already_inside(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkiran Sudimara Utama',
            'kapasitas_reguler'=> 50,
            'status_aktif'     => 1,
        ]);
        $gateCode = $tenant->generateGateQr();

        $tarif = Tarif::create([
            'id_tempat_parkir'  => $tenant->id_tempat_parkir,
            'jenis_kendaraan'   => 'Motor',
            'tipe_tarif'        => 'Per Jam',
            'tarif_jam_pertama' => 2000,
            'tarif_per_jam'     => 1000,
            'status_aktif'      => 1,
        ]);

        $kendaraan = \App\Models\Kendaraan::create([
            'plat_nomor' => 'B 6767 IYU',
            'jenis_kendaraan' => 'Motor',
        ]);

        // Check in pertama kali (aktif di dalam)
        Transaksi::create([
            'kode_karcis'      => 'PKR-ACTIVE-6767',
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_kendaraan'     => $kendaraan->id_kendaraan,
            'id_tarif'         => $tarif->id_tarif,
            'waktu_masuk'      => now()->subHours(1),
            'status'           => 'masuk',
            'status_pembayaran'=> 'Belum Bayar',
            'metode_pembayaran'=> 'Tunai',
        ]);

        // Percobaan check-in kedua dengan plat yang sama
        $resSecond = $this->post('/in/' . $gateCode, [
            'plat_nomor'      => 'B 6767 IYU',
            'jenis_kendaraan' => 'Motor',
        ]);

        $resSecond->assertSessionHasErrors(['plat_nomor']);
        $this->assertEquals(1, Transaksi::where('id_kendaraan', $kendaraan->id_kendaraan)->count());
    }
}
