<?php

namespace Tests\Feature;

use App\Models\Pengendara;
use App\Models\TempatParkir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_with_8_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Section 1: Hero Section
        $response->assertSee('Kelola Parkir Lebih');
        $response->assertSee('Mulai Parkir Sekarang');
        // Section 2: About Us (4 Pilar: Aman, Fleksibel, Profesional, Terpercaya)
        $response->assertSee('4 Pilar Utama');
        $response->assertSee('Aman');
        $response->assertSee('Fleksibel');
        $response->assertSee('Profesional');
        $response->assertSee('Terpercaya');
        // Section 3: Fitur
        $response->assertSee('Fitur Lengkap');
        $response->assertSee('Scan OCR Plat On-Device');
        // Section 4: Cara Kerja
        $response->assertSee('Alur Operasional');
        $response->assertSee('Kendaraan Masuk');
        // Section 5: Keunggulan
        $response->assertSee('Mengapa Memilih');
        $response->assertSee('Isolasi Tenant & Anti-IDOR');
        // Section 6: Testimoni (marquee)
        $response->assertSee('Testimoni Pengguna');
        $response->assertSee('Budi Hartono');
        // Section 7: CTA & Kontak (marquee)
        $response->assertSee('Siap Memodernisasi');
        $response->assertSee('info@parkirin.id');
        // Section 8: Footer
        $response->assertSee('Parkirin SaaS');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang Kembali');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('Pengendara');
        $response->assertSee('Staf / Manajemen');
        $response->assertSee('Google');
    }

    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Buat Akun Pengendara');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('Daftar Sekarang');
    }

    public function test_guest_can_register_as_pengendara(): void
    {
        $response = $this->post('/register', [
            'nama_lengkap'          => 'Budi Pengendara',
            'email'                 => 'budi@example.com',
            'username'              => 'budipengendara',
            'no_telepon'            => '081234567890',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/pengendara');
        $this->assertDatabaseHas('pengendara', [
            'email'    => 'budi@example.com',
            'username' => 'budipengendara',
        ]);
        $this->assertAuthenticatedAs(Pengendara::where('email', 'budi@example.com')->first(), 'pengendara');
    }

    public function test_pengendara_can_login_with_email_or_username(): void
    {
        $pengendara = Pengendara::create([
            'nama_lengkap'  => 'Siti Pengendara',
            'email'         => 'siti@example.com',
            'username'      => 'sitipengendara',
            'password'      => Hash::make('password123'),
            'skor_reputasi' => 100,
            'status_aktif'  => 1,
        ]);

        // Login with email
        $response = $this->post('/login/pengendara', [
            'email'    => 'siti@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/pengendara');
        $this->assertAuthenticatedAs($pengendara, 'pengendara');

        // Logout
        $this->post('/logout');

        // Login with username
        $response2 = $this->post('/login/pengendara', [
            'email'    => 'sitipengendara',
            'password' => 'password123',
        ]);
        $response2->assertRedirect('/pengendara');
        $this->assertAuthenticatedAs($pengendara, 'pengendara');
    }

    public function test_staff_can_login_with_role_redirection(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkir Gambir',
            'kapasitas'        => 100,
            'terisi'           => 0,
            'sediakan_booking' => 1,
            'status_aktif'     => 1,
        ]);

        $admin = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap'     => 'Admin Gambir',
            'username'         => 'admingambir',
            'password'         => Hash::make('password123'),
            'role'             => 'admin',
            'status_aktif'     => 1,
        ]);

        $response = $this->post('/login/user', [
            'username' => 'admingambir',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_role_middleware_blocks_unauthorized_access(): void
    {
        $tenant = TempatParkir::create([
            'nama_tempat'      => 'Parkir Gambir',
            'kapasitas'        => 100,
            'terisi'           => 0,
            'sediakan_booking' => 1,
            'status_aktif'     => 1,
        ]);

        $petugas = User::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'nama_lengkap'     => 'Petugas Pos',
            'username'         => 'petugaspos',
            'password'         => Hash::make('password123'),
            'role'             => 'petugas',
            'status_aktif'     => 1,
        ]);

        // Petugas tries to access Admin dashboard -> 403 Forbidden
        $response = $this->actingAs($petugas, 'web')->get('/admin');
        $response->assertStatus(403);
    }

    public function test_tenant_isolation_middleware_blocks_cross_tenant_access(): void
    {
        $tenantA = TempatParkir::create([
            'nama_tempat'      => 'Parkir A',
            'kapasitas'        => 50,
            'terisi'           => 0,
            'sediakan_booking' => 1,
            'status_aktif'     => 1,
        ]);

        $adminA = User::create([
            'id_tempat_parkir' => $tenantA->id_tempat_parkir,
            'nama_lengkap'     => 'Admin Tenant A',
            'username'         => 'adminA',
            'password'         => Hash::make('password123'),
            'role'             => 'admin',
            'status_aktif'     => 1,
        ]);

        // Accessing with input id_tempat_parkir of a different tenant -> 403 Forbidden
        $response = $this->actingAs($adminA, 'web')->get('/admin?id_tempat_parkir=9999');
        $response->assertStatus(403);
    }

    public function test_google_auth_shows_dev_notice_when_client_id_missing(): void
    {
        config(['services.google.client_id' => '']);

        $response = $this->get('/auth/google');
        $response->assertStatus(200);
        $response->assertSee('Google Client ID Belum Diatur');
        $response->assertSee('ghaniyyalbar@gmail.com');
    }

    public function test_google_auth_dev_simulation_redirects_to_onboarding(): void
    {
        config(['services.google.client_id' => '']);

        $response = $this->post('/auth/google', [
            'name'  => 'Ghaniyy Albar',
            'email' => 'ghaniyyalbar@gmail.com',
        ]);

        $response->assertRedirect('/auth/google/onboarding');
        $this->assertDatabaseHas('pengendara', [
            'email'    => 'ghaniyyalbar@gmail.com',
            'username' => null,
        ]);
    }

    public function test_google_onboarding_stores_username_and_redirects(): void
    {
        $pengendara = Pengendara::create([
            'nama_lengkap'  => 'Google Rider',
            'email'         => 'googlerider@example.com',
            'username'      => null,
            'password'      => null,
            'skor_reputasi' => 100,
            'status_aktif'  => 1,
        ]);

        $response = $this->actingAs($pengendara, 'pengendara')
            ->post('/auth/google/onboarding', [
                'username' => 'googlerider123',
            ]);

        $response->assertRedirect('/pengendara');
        $this->assertDatabaseHas('pengendara', [
            'email'    => 'googlerider@example.com',
            'username' => 'googlerider123',
        ]);
    }

    public function test_api_auth_register_creates_pengendara_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nama_lengkap'          => 'Andi API',
            'email'                 => 'andi.api@example.com',
            'username'              => 'andiapi',
            'no_telepon'            => '081299887766',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'guard',
            'user' => ['id_pengendara', 'nama_lengkap', 'email', 'username'],
            'token',
        ]);
        $this->assertDatabaseHas('pengendara', ['email' => 'andi.api@example.com']);
    }

    public function test_api_auth_login_returns_token(): void
    {
        Pengendara::create([
            'nama_lengkap'  => 'Rina API',
            'email'         => 'rina.api@example.com',
            'username'      => 'rinaapi',
            'password'      => Hash::make('secret123'),
            'skor_reputasi' => 100,
            'status_aktif'  => 1,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'guard'    => 'pengendara',
            'email'    => 'rina.api@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'guard', 'user', 'token']);
    }

    public function test_api_auth_logout_revokes_token(): void
    {
        $pengendara = Pengendara::create([
            'nama_lengkap'  => 'Joko API',
            'email'         => 'joko.api@example.com',
            'username'      => 'jokoapi',
            'password'      => Hash::make('secret123'),
            'skor_reputasi' => 100,
            'status_aktif'  => 1,
        ]);

        $token = $pengendara->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
