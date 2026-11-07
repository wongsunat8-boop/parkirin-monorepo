<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\GoogleAuthController;
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
Route::middleware(['auth:web', 'role:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/', fn () => 'Dashboard Super Admin — Coming soon')->name('super-admin.dashboard');
});

// Owner
Route::middleware(['auth:web', 'role:owner', 'tenant'])->prefix('owner')->group(function () {
    Route::get('/', fn () => 'Dashboard Owner — Coming soon')->name('owner.dashboard');
});

// Admin
Route::middleware(['auth:web', 'role:admin', 'tenant'])->prefix('admin')->group(function () {
    Route::get('/', fn () => 'Dashboard Admin — Coming soon')->name('admin.dashboard');
});

// Petugas
Route::middleware(['auth:web', 'role:petugas', 'tenant'])->prefix('petugas')->group(function () {
    Route::get('/', fn () => 'Dashboard Petugas — Coming soon')->name('petugas.dashboard');
});
