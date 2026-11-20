<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\GoogleAuthController;
use App\Http\Controllers\Web\PublicCheckInController;
use App\Http\Controllers\Web\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Web\SuperAdmin\OwnerController as SuperAdminOwnerController;
use App\Http\Controllers\Web\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Web\SuperAdmin\LogAktivitasController as SuperAdminLogAktivitasController;
use App\Http\Controllers\Web\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Web\Owner\AdminController as OwnerAdminController;
use App\Http\Controllers\Web\Owner\TransaksiController as OwnerTransaksiController;
use App\Http\Controllers\Web\Owner\AuditSetoranController as OwnerAuditSetoranController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\VerifikasiPembayaranController as AdminVerifikasiPembayaranController;
use App\Http\Controllers\Web\Admin\QrGateController as AdminQrGateController;
use App\Http\Controllers\Web\Admin\TarifController as AdminTarifController;
use App\Http\Controllers\Web\Admin\KapasitasController as AdminKapasitasController;
use App\Http\Controllers\Web\Admin\PaketLanggananController as AdminPaketLanggananController;
use App\Http\Controllers\Web\Admin\PetugasController as AdminPetugasController;
use App\Http\Controllers\Web\Admin\PengendaraController as AdminPengendaraController;
use App\Http\Controllers\Web\Admin\LogAktivitasController as AdminLogAktivitasController;
use App\Http\Controllers\Web\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Web\Petugas\ScannerKeluarController as PetugasScannerKeluarController;
use App\Http\Controllers\Web\Pengendara\DashboardController as PengendaraDashboardController;
use App\Http\Controllers\Web\Pengendara\RiwayatController as PengendaraRiwayatController;
use App\Http\Controllers\Web\Pengendara\KatalogController as PengendaraKatalogController;
use App\Http\Controllers\Web\Pengendara\ProfilController as PengendaraProfilController;
use App\Http\Controllers\Web\Pengendara\KendaraanController as PengendaraKendaraanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('landing'))->middleware('redirect.if.auth')->name('landing');
Route::get('/scan', [PublicCheckInController::class, 'scanner'])->name('checkin.scanner');
Route::get('/in/{id_tempat_parkir}', [PublicCheckInController::class, 'index'])->name('checkin.public');
Route::post('/in/{id_tempat_parkir}', [PublicCheckInController::class, 'store'])->name('checkin.store');
Route::get('/in/karcis/{kode_karcis}', [PublicCheckInController::class, 'ticket'])->name('checkin.ticket');
Route::get('/in/karcis/{kode_karcis}/status', [PublicCheckInController::class, 'ticketStatus'])->name('checkin.ticket.status');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:web,pengendara')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login/user', [AuthController::class, 'loginUser'])->name('login.user');
    Route::post('/login/pengendara', [AuthController::class, 'loginPengendara'])->name('login.pengendara');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    // Google OAuth
    Route::match(['get', 'post'], '/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

// Google OAuth Onboarding (pengendara must be logged in but username not yet set)
Route::middleware('auth:pengendara')->group(function () {
    Route::get('/auth/google/onboarding', [GoogleAuthController::class, 'showOnboarding'])->name('auth.google.onboarding');
    Route::post('/auth/google/onboarding', [GoogleAuthController::class, 'storeOnboarding'])->name('auth.google.onboarding.store');
});

/*
|--------------------------------------------------------------------------
| Logout (Any Authenticated User)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes — Pengendara (Customer)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:pengendara', 'role:pengendara', 'pengendara.active'])->prefix('pengendara')->name('pengendara.')->group(function () {
    Route::get('/', [PengendaraDashboardController::class, 'index'])->name('dashboard');

    // Kendaraan pribadi (Auto-Reassign & Immutable History)
    Route::post('/kendaraan', [PengendaraKendaraanController::class, 'store'])->name('kendaraan.store');
    Route::patch('/kendaraan/{kendaraan}', [PengendaraKendaraanController::class, 'update'])->name('kendaraan.update');
    Route::post('/kendaraan/{kendaraan}/unlink', [PengendaraKendaraanController::class, 'unlink'])->name('kendaraan.unlink');

    // Pembayaran karcis digital
    Route::post('/transaksi/{transaksi}/bayar', [PengendaraDashboardController::class, 'bayarCashless'])->name('transaksi.bayar');

    Route::get('/riwayat', [PengendaraRiwayatController::class, 'index'])->name('riwayat.index');
    Route::post('/riwayat/transaksi/{transaksi}/bayar', [PengendaraRiwayatController::class, 'bayarCashless'])->name('riwayat.bayar');
    Route::get('/riwayat/transaksi/{transaksi}/struk', [PengendaraRiwayatController::class, 'struk'])->name('riwayat.struk');

    // Katalog Parkir (Lihat, Beli Paket Member & Booking Inap via Auto-Lunas QRIS)
    Route::get('/katalog', [PengendaraKatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/{tempatParkir}', [PengendaraKatalogController::class, 'show'])->name('katalog.show');
    Route::post('/katalog/{tempatParkir}/member', [PengendaraKatalogController::class, 'beliMember'])->name('katalog.member.store');
    Route::post('/katalog/{tempatParkir}/booking', [PengendaraKatalogController::class, 'bookingInap'])->name('katalog.booking.store');

    Route::get('/profil', [PengendaraProfilController::class, 'index'])->name('profil.index');
    Route::patch('/profil', [PengendaraProfilController::class, 'updateProfil'])->name('profil.update');
    Route::patch('/profil/password', [PengendaraProfilController::class, 'updatePassword'])->name('profil.password');
});

/*
|--------------------------------------------------------------------------
| Protected Routes — Staff (User) with Role & Tenant Isolation
|--------------------------------------------------------------------------
*/

// Super Admin
Route::middleware(['auth:web', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', SuperAdminTenantController::class)->except(['show', 'destroy']);
    Route::patch('tenants/{tenant}/toggle', [SuperAdminTenantController::class, 'toggle'])->name('tenants.toggle');
    Route::resource('owners', SuperAdminOwnerController::class)->except(['show', 'destroy']);
    Route::patch('owners/{owner}/toggle', [SuperAdminOwnerController::class, 'toggle'])->name('owners.toggle');
    Route::get('log-aktivitas', [SuperAdminLogAktivitasController::class, 'index'])->name('log-aktivitas.index');
});

// Owner
Route::middleware(['auth:web', 'role:owner', 'tenant'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('admins', OwnerAdminController::class)->except(['show', 'destroy']);
    Route::patch('admins/{admin}/toggle', [OwnerAdminController::class, 'toggle'])->name('admins.toggle');
    Route::get('transaksi', [OwnerTransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('transaksi/print', [OwnerTransaksiController::class, 'print'])->name('transaksi.print');
    Route::get('transaksi/export', [OwnerTransaksiController::class, 'export'])->name('transaksi.export');
    Route::get('audit-setoran', [OwnerAuditSetoranController::class, 'index'])->name('audit-setoran.index');
    Route::get('audit-setoran/print', [OwnerAuditSetoranController::class, 'print'])->name('audit-setoran.print');
});

// Admin
Route::middleware(['auth:web', 'role:admin', 'tenant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('verifikasi-pembayaran', [AdminVerifikasiPembayaranController::class, 'index'])->name('verifikasi.index');
    Route::post('verifikasi-pembayaran/member/{langganan}/verifikasi', [AdminVerifikasiPembayaranController::class, 'verifikasiMember'])->name('verifikasi.member.verifikasi');
    Route::post('verifikasi-pembayaran/member/{langganan}/tolak', [AdminVerifikasiPembayaranController::class, 'tolakMember'])->name('verifikasi.member.tolak');
    Route::post('verifikasi-pembayaran/booking/{booking}/verifikasi', [AdminVerifikasiPembayaranController::class, 'verifikasiBooking'])->name('verifikasi.booking.verifikasi');
    Route::post('verifikasi-pembayaran/booking/{booking}/tolak', [AdminVerifikasiPembayaranController::class, 'tolakBooking'])->name('verifikasi.booking.tolak');
    Route::get('qr-gate', [AdminQrGateController::class, 'index'])->name('qr-gate.index');
    Route::post('qr-gate/generate', [AdminQrGateController::class, 'generate'])->name('qr-gate.generate');
    Route::get('qr-gate/print', [AdminQrGateController::class, 'print'])->name('qr-gate.print');
    Route::resource('tarif', AdminTarifController::class)->except(['show']);
    Route::get('kapasitas', [AdminKapasitasController::class, 'index'])->name('kapasitas.index');
    Route::post('kapasitas', [AdminKapasitasController::class, 'store'])->name('kapasitas.store');
    Route::patch('kapasitas', [AdminKapasitasController::class, 'update'])->name('kapasitas.update');
    Route::post('kapasitas/qris', [AdminKapasitasController::class, 'updateQris'])->name('kapasitas.qris');
    Route::patch('kapasitas/toggle-booking', [AdminKapasitasController::class, 'toggleBooking'])->name('kapasitas.toggle-booking');
    Route::patch('kapasitas/toggle-member', [AdminKapasitasController::class, 'toggleMember'])->name('kapasitas.toggle-member');
    Route::resource('paket-langganan', AdminPaketLanggananController::class)->except(['show']);
    Route::patch('paket-langganan/{paketLangganan}/toggle', [AdminPaketLanggananController::class, 'toggle'])->name('paket-langganan.toggle');
    Route::resource('petugas', AdminPetugasController::class)->except(['show', 'destroy'])->parameters(['petugas' => 'petugas']);
    Route::patch('petugas/{petugas}/toggle', [AdminPetugasController::class, 'toggle'])->name('petugas.toggle');
    Route::get('pengendara', [AdminPengendaraController::class, 'index'])->name('pengendara.index');
    Route::get('log-aktivitas', [AdminLogAktivitasController::class, 'index'])->name('log-aktivitas.index');
});

// Petugas
Route::middleware(['auth:web', 'role:petugas', 'tenant'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/', [PetugasDashboardController::class, 'index'])->name('dashboard');
    Route::post('/input-manual', [PetugasDashboardController::class, 'manualCheckIn'])->name('manual-checkin');

    Route::get('/scanner-keluar', [PetugasScannerKeluarController::class, 'index'])->name('scanner-keluar');
    Route::post('/scanner-keluar/lookup', [PetugasScannerKeluarController::class, 'lookup'])->name('scanner-keluar.lookup');
    Route::post('/scanner-keluar/checkout', [PetugasScannerKeluarController::class, 'processCheckout'])->name('scanner-keluar.checkout');
    Route::post('/scanner-keluar/search-plat', [PetugasScannerKeluarController::class, 'searchByPlat'])->name('scanner-keluar.search-plat');
});
