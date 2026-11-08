<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\GoogleAuthController;
use App\Http\Controllers\Web\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Web\SuperAdmin\OwnerController as SuperAdminOwnerController;
use App\Http\Controllers\Web\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Web\SuperAdmin\LogAktivitasController as SuperAdminLogAktivitasController;
use App\Http\Controllers\Web\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Web\Owner\AdminController as OwnerAdminController;
use App\Http\Controllers\Web\Owner\PengajuanBannedController as OwnerPengajuanBannedController;
use App\Http\Controllers\Web\Owner\TransaksiController as OwnerTransaksiController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\TarifController as AdminTarifController;
use App\Http\Controllers\Web\Admin\KapasitasController as AdminKapasitasController;
use App\Http\Controllers\Web\Admin\PetugasController as AdminPetugasController;
use App\Http\Controllers\Web\Admin\KendaraanController as AdminKendaraanController;
use App\Http\Controllers\Web\Admin\PengendaraController as AdminPengendaraController;
use App\Http\Controllers\Web\Admin\LogAktivitasController as AdminLogAktivitasController;
use App\Http\Controllers\Web\Petugas\DashboardController as PetugasDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('landing'))->name('landing');

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
Route::middleware(['auth:pengendara'])->prefix('pengendara')->group(function () {
    Route::get('/', fn () => 'Dashboard Pengendara — Coming soon')->name('pengendara.dashboard');
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
    Route::get('pengajuan-banned', [OwnerPengajuanBannedController::class, 'index'])->name('pengajuan-banned.index');
    Route::patch('pengajuan-banned/{pengajuan}/approve', [OwnerPengajuanBannedController::class, 'approve'])->name('pengajuan-banned.approve');
    Route::patch('pengajuan-banned/{pengajuan}/reject', [OwnerPengajuanBannedController::class, 'reject'])->name('pengajuan-banned.reject');
    Route::patch('pengajuan-banned/{pengajuan}/revoke', [OwnerPengajuanBannedController::class, 'revoke'])->name('pengajuan-banned.revoke');
    Route::get('transaksi', [OwnerTransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('transaksi/print', [OwnerTransaksiController::class, 'print'])->name('transaksi.print');
    Route::get('transaksi/export', [OwnerTransaksiController::class, 'export'])->name('transaksi.export');
});

// Admin
Route::middleware(['auth:web', 'role:admin', 'tenant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('tarif', AdminTarifController::class)->except(['show']);
    Route::get('kapasitas', [AdminKapasitasController::class, 'index'])->name('kapasitas.index');
    Route::post('kapasitas', [AdminKapasitasController::class, 'store'])->name('kapasitas.store');
    Route::patch('kapasitas', [AdminKapasitasController::class, 'update'])->name('kapasitas.update');
    Route::patch('kapasitas/toggle-booking', [AdminKapasitasController::class, 'toggleBooking'])->name('kapasitas.toggle-booking');
    Route::resource('petugas', AdminPetugasController::class)->except(['show', 'destroy'])->parameters(['petugas' => 'petugas']);
    Route::patch('petugas/{petugas}/toggle', [AdminPetugasController::class, 'toggle'])->name('petugas.toggle');
    Route::get('kendaraan', [AdminKendaraanController::class, 'index'])->name('kendaraan.index');
    Route::post('kendaraan', [AdminKendaraanController::class, 'store'])->name('kendaraan.store');
    Route::post('kendaraan/{kendaraan}/pengajuan-banned', [AdminKendaraanController::class, 'ajukanBanned'])->name('kendaraan.pengajuan-banned');
    Route::get('pengendara', [AdminPengendaraController::class, 'index'])->name('pengendara.index');
    Route::post('pengendara/{pengendara}/pengajuan-banned', [AdminPengendaraController::class, 'ajukanBanned'])->name('pengendara.pengajuan-banned');
    Route::get('log-aktivitas', [AdminLogAktivitasController::class, 'index'])->name('log-aktivitas.index');
});

// Petugas
Route::middleware(['auth:web', 'role:petugas', 'tenant'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/', [PetugasDashboardController::class, 'index'])->name('dashboard');
});
