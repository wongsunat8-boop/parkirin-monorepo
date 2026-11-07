@extends('layouts.app')

@section('title', 'Parkirin — Sistem Parkir Cerdas Multi-Tenant & Portable')

@section('content')

{{-- ═══════════════════════════════════════════
    NAVBAR
═══════════════════════════════════════════ --}}
<nav class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-md shadow-primary/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <span class="font-heading font-black text-2xl text-dark tracking-tight">Park<span class="text-primary">irin</span></span>
        </a>

        {{-- Desktop Navigation Links --}}
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-dark/75">
            <a href="#about" class="hover:text-primary transition">Tentang</a>
            <a href="#fitur" class="hover:text-primary transition">Fitur</a>
            <a href="#cara-kerja" class="hover:text-primary transition">Cara Kerja</a>
            <a href="#keunggulan" class="hover:text-primary transition">Keunggulan</a>
            <a href="#testimoni" class="hover:text-primary transition">Testimoni</a>
            <a href="#kontak" class="hover:text-primary transition">Kontak</a>
        </div>

        {{-- Auth Actions --}}
        <div class="hidden sm:flex items-center gap-3">
            @if(auth('pengendara')->check())
                <a href="{{ route('pengendara.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark transition shadow-sm">
                    <svg class="w-4 h-4 text-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Dashboard Pengendara</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-2 text-xs font-semibold text-danger hover:bg-danger/10 rounded-lg transition">Keluar</button>
                </form>
            @elseif(auth('web')->check())
                @php
                    $role = auth('web')->user()->peran;
                    $dashRoute = match($role) {
                        'super_admin' => route('super-admin.dashboard'),
                        'owner' => route('owner.dashboard'),
                        'admin' => route('admin.dashboard'),
                        'petugas' => route('petugas.dashboard'),
                        default => route('login'),
                    };
                @endphp
                <a href="{{ $dashRoute }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary-dark transition shadow-sm">
                    <span>Portal {{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                    <span>&rarr;</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-2 text-xs font-semibold text-danger hover:bg-danger/10 rounded-lg transition">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-primary border border-primary/30 rounded-xl hover:bg-primary/5 transition">Masuk</a>
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 text-sm font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark transition shadow-sm">Daftar Pengendara</a>
            @endif
        </div>

        {{-- Mobile Hamburger Button --}}
        <div class="flex items-center sm:hidden">
            <button type="button" onclick="toggleMobileMenu()" class="p-2 text-dark/70 hover:text-dark focus:outline-none" aria-label="Toggle Navigation">
                <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu Dropdown --}}
    <div id="mobile-menu" class="hidden sm:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-2.5 shadow-lg">
        <a href="#about" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary">Tentang</a>
        <a href="#fitur" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary">Fitur</a>
        <a href="#cara-kerja" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary">Cara Kerja</a>
        <a href="#keunggulan" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary">Keunggulan</a>
        <a href="#testimoni" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary">Testimoni</a>
        <a href="#kontak" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary">Kontak</a>
        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
            @if(auth('pengendara')->check())
                <a href="{{ route('pengendara.dashboard') }}" class="w-full text-center py-2.5 text-sm font-bold text-dark bg-accent rounded-xl shadow-sm">Buka Dashboard Pengendara</a>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full text-center py-2 text-xs font-semibold text-danger">Keluar Akun</button>
                </form>
            @elseif(auth('web')->check())
                @php
                    $roleMobile = auth('web')->user()->peran;
                    $dashRouteMobile = match($roleMobile) {
                        'super_admin' => route('super-admin.dashboard'),
                        'owner' => route('owner.dashboard'),
                        'admin' => route('admin.dashboard'),
                        'petugas' => route('petugas.dashboard'),
                        default => route('login'),
                    };
                @endphp
                <a href="{{ $dashRouteMobile }}" class="w-full text-center py-2.5 text-sm font-bold text-white bg-primary rounded-xl shadow-sm">Buka Portal {{ ucfirst(str_replace('_', ' ', $roleMobile)) }}</a>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full text-center py-2 text-xs font-semibold text-danger">Keluar Akun</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full text-center py-2.5 text-sm font-semibold text-primary border border-primary/30 rounded-xl">Masuk Portal</a>
                <a href="{{ route('register') }}" class="w-full text-center py-2.5 text-sm font-bold text-dark bg-accent rounded-xl">Daftar Pengendara</a>
            @endif
        </div>
    </div>
</nav>

<div class="pt-16">

{{-- ═══════════════════════════════════════════
    1. HERO SECTION
═══════════════════════════════════════════ --}}
<section class="relative bg-gradient-to-br from-primary via-primary-dark to-primary overflow-hidden text-white">
    <div class="absolute inset-0 opacity-15 pointer-events-none">
        <div class="absolute -top-32 -right-32 w-[550px] h-[550px] rounded-full bg-accent blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-[450px] h-[450px] rounded-full bg-white blur-3xl"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 flex flex-col lg:flex-row items-center gap-12">
        <div class="flex-1 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 mb-6 text-xs font-bold bg-accent/20 text-accent border border-accent/30 rounded-full backdrop-blur-sm">
                <span>🚗</span>
                <span>Platform SaaS Parkir Multi-Tenant Indonesia</span>
            </div>
            <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                Kelola Parkir Lebih <br class="hidden sm:inline">
                <span class="text-accent underline decoration-accent/40 decoration-wavy">Cerdas</span> & Transparan
            </h1>
            <p class="mt-6 text-base sm:text-lg text-white/85 max-w-xl mx-auto lg:mx-0 leading-relaxed font-sans font-light">
                Parkirin menghadirkan solusi portable lengkap untuk operator parkir swadaya: Scan OCR plat nomor di perangkat, karcis QR digital instan, multi-model tarif dinamis, hingga pencegahan kecurangan dengan audit real-time.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark transition shadow-lg shadow-accent/25 text-base">
                    Mulai Parkir Sekarang
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="#cara-kerja" class="inline-flex items-center justify-center px-8 py-4 font-semibold text-white border-2 border-white/30 rounded-xl hover:bg-white/10 transition text-base">
                    Lihat Cara Kerja
                </a>
            </div>
            {{-- Quick metric highlights --}}
            <div class="mt-12 pt-8 border-t border-white/15 grid grid-cols-3 gap-4 text-center lg:text-left">
                <div>
                    <p class="font-heading font-black text-2xl lg:text-3xl text-accent">99.8%</p>
                    <p class="text-xs text-white/70 mt-1">Akurasi Scan OCR</p>
                </div>
                <div>
                    <p class="font-heading font-black text-2xl lg:text-3xl text-white">&lt; 10 dtk</p>
                    <p class="text-xs text-white/70 mt-1">Kecepatan Masuk Gate</p>
                </div>
                <div>
                    <p class="font-heading font-black text-2xl lg:text-3xl text-accent">100%</p>
                    <p class="text-xs text-white/70 mt-1">Audit Trail Real-Time</p>
                </div>
            </div>
        </div>

        {{-- Live Mockup Card (shadcn/ui aesthetic) --}}
        <div class="flex-1 w-full max-w-md lg:max-w-lg">
            <div class="relative bg-white/10 backdrop-blur-md rounded-3xl p-5 sm:p-7 border border-white/20 shadow-2xl">
                <div class="bg-white rounded-2xl p-6 shadow-lg text-dark">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-heading font-bold text-sm text-dark">Tiket Digital Aktif</p>
                                <p class="text-xs text-dark/50">Tenant: Parkir Area Stasiun</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold bg-success-bg text-success rounded-full border border-success/30">Terverifikasi</span>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex justify-between items-center p-3 bg-secondary rounded-xl border border-slate-100">
                            <span class="text-xs font-medium text-dark/60">Plat Nomor</span>
                            <span class="badge-plat">B 1234 XYZ</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-secondary rounded-xl border border-slate-100">
                            <span class="text-xs font-medium text-dark/60">Jenis Kendaraan</span>
                            <span class="text-xs font-semibold text-dark">Sepeda Motor (Matic)</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-secondary rounded-xl border border-slate-100">
                            <span class="text-xs font-medium text-dark/60">Waktu Masuk</span>
                            <span class="text-xs font-semibold text-dark">07:45 WIB (Hari Ini)</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-secondary rounded-xl border border-slate-100">
                            <span class="text-xs font-medium text-dark/60">Skema Tarif</span>
                            <span class="text-xs font-bold text-primary">Per Jam + Anti Overstay</span>
                        </div>
                    </div>

                    <div class="mt-5 p-3.5 bg-accent/15 border border-accent/40 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-xs text-dark/60">Estimasi Biaya Saat Ini</p>
                            <p class="font-heading font-black text-lg text-dark">Rp 4.000 <span class="text-xs font-normal text-dark/60">(2 Jam)</span></p>
                        </div>
                        <a href="{{ route('login') }}" class="px-3 py-1.5 bg-dark text-white text-xs font-bold rounded-lg shadow-sm hover:bg-black transition flex items-center gap-1.5">
                            <span>Masuk & Coba</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    2. ABOUT US (4 Pilar: Aman, Fleksibel, Profesional, Terpercaya)
═══════════════════════════════════════════ --}}
<section id="about" class="scroll-mt-16 py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold text-primary uppercase tracking-widest bg-primary/10 px-3.5 py-1.5 rounded-full">Filosofi & Identitas</span>
            <h2 class="mt-4 font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
                Dibangun di Atas <span class="text-primary">4 Pilar Utama</span>
            </h2>
            <p class="mt-4 text-base text-dark/65 leading-relaxed">
                Parkirin dirancang dari bawah ke atas untuk menjawab tantangan operasional lapangan bagi operator parkir swadaya mandiri.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Pilar 1: Aman --}}
            <div class="group p-7 bg-secondary rounded-2xl border border-slate-200/80 hover:border-primary/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="w-13 h-13 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="inline-block px-2.5 py-0.5 mb-2 text-xs font-bold text-primary bg-primary/10 rounded-md">Pilar 01</div>
                    <h3 class="font-heading font-extrabold text-xl text-dark">Aman</h3>
                    <p class="mt-2.5 text-sm text-dark/65 leading-relaxed">
                        Validasi status jelas di setiap gerbang, karcis QR digital terenkripsi, dan proteksi anti-IDOR multi-tenant menjamin data tiap lokasi terisolasi sempurna.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-slate-200/60">
                    <a href="#fitur" class="inline-flex items-center text-xs font-bold text-primary hover:underline">
                        Lihat Fitur Keamanan &rarr;
                    </a>
                </div>
            </div>

            {{-- Pilar 2: Fleksibel --}}
            <div class="group p-7 bg-secondary rounded-2xl border border-slate-200/80 hover:border-accent/60 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="w-13 h-13 rounded-2xl bg-accent/20 text-accent-dark flex items-center justify-center mb-5 group-hover:bg-accent group-hover:text-dark transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    </div>
                    <div class="inline-block px-2.5 py-0.5 mb-2 text-xs font-bold text-accent-dark bg-accent/20 rounded-md">Pilar 02</div>
                    <h3 class="font-heading font-extrabold text-xl text-dark">Fleksibel</h3>
                    <p class="mt-2.5 text-sm text-dark/65 leading-relaxed">
                        Mendukung ragam model tarif (Per Jam, Flat Harian, Progresif Mal, plus Tarif Inap 24 jam) serta toggle fitur booking slot per tempat parkir secara mandiri.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-slate-200/60">
                    <a href="#fitur" class="inline-flex items-center text-xs font-bold text-primary hover:underline">
                        Lihat Skema Tarif &rarr;
                    </a>
                </div>
            </div>

            {{-- Pilar 3: Profesional --}}
            <div class="group p-7 bg-secondary rounded-2xl border border-slate-200/80 hover:border-primary/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="w-13 h-13 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="inline-block px-2.5 py-0.5 mb-2 text-xs font-bold text-primary bg-primary/10 rounded-md">Pilar 03</div>
                    <h3 class="font-heading font-extrabold text-xl text-dark">Profesional</h3>
                    <p class="mt-2.5 text-sm text-dark/65 leading-relaxed">
                        Desain antarmuka bersih bergaya shadcn/ui & Refero yang intuitif dan to-the-point, memudahkan petugas lapangan memproses kendaraan dalam hitungan detik.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-slate-200/60">
                    <a href="#cara-kerja" class="inline-flex items-center text-xs font-bold text-primary hover:underline">
                        Lihat Alur Kerja &rarr;
                    </a>
                </div>
            </div>

            {{-- Pilar 4: Terpercaya --}}
            <div class="group p-7 bg-secondary rounded-2xl border border-slate-200/80 hover:border-accent/60 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="w-13 h-13 rounded-2xl bg-accent/20 text-accent-dark flex items-center justify-center mb-5 group-hover:bg-accent group-hover:text-dark transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="inline-block px-2.5 py-0.5 mb-2 text-xs font-bold text-accent-dark bg-accent/20 rounded-md">Pilar 04</div>
                    <h3 class="font-heading font-extrabold text-xl text-dark">Terpercaya</h3>
                    <p class="mt-2.5 text-sm text-dark/65 leading-relaxed">
                        Transparansi kuota & rincian biaya sebelum booking, audit log aktivitas mutasi staf otomatis, dan metrik anti-fraud discrepancy untuk ketenangan pemilik lahan.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-slate-200/60">
                    <a href="#keunggulan" class="inline-flex items-center text-xs font-bold text-primary hover:underline">
                        Lihat Keunggulan &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    3. FITUR
═══════════════════════════════════════════ --}}
<section id="fitur" class="scroll-mt-16 py-20 lg:py-28 bg-secondary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold text-primary uppercase tracking-widest bg-primary/10 px-3.5 py-1.5 rounded-full">Fitur Lengkap</span>
            <h2 class="mt-4 font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
                Solusi End-to-End untuk <span class="text-primary">Semua Peran</span>
            </h2>
            <p class="mt-4 text-base text-dark/65 leading-relaxed">
                Mulai dari petugas lapangan hingga pemilik bisnis, setiap fitur dirancang fokus, akurat, dan minim kesalahan.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
            $features = [
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'title' => 'Scan OCR Plat On-Device',
                    'desc' => 'Petugas dapat memindai plat nomor kendaraan secara instan dengan Google ML Kit, dilengkapi modal preview koreksi jika ada karakter buram.',
                    'link_text' => 'Lihat Alur Kerja',
                    'link_url' => '#cara-kerja',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>',
                    'title' => 'Karcis QR Digital & Thermal',
                    'desc' => 'Dukungan ganda: Cetak struk termal fisik dengan QR Code atau kirim karcis digital langsung ke akun pengendara terdaftar.',
                    'link_text' => 'Coba Registrasi',
                    'link_url' => route('register'),
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => 'Tarif Multi-Model & Inap',
                    'desc' => 'Pilih skema Per Jam, Flat Harian, atau Progresif Mal dengan kalkulasi inap kalender otomatis melalui MySQL Stored Function.',
                    'link_text' => 'Lihat Keunggulan',
                    'link_url' => '#keunggulan',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                    'title' => 'Booking Slot Online',
                    'desc' => 'Pengendara dapat memesan tempat parkir sebelum tiba dengan countdown grace period 10 menit dan auto-cancellation otomatis.',
                    'link_text' => 'Booking Sekarang',
                    'link_url' => route('register'),
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                    'title' => 'Dashboard Anti-Fraud Owner',
                    'desc' => 'Bandingkan jumlah fisik kendaraan aktif di lahan vs total transaksi masuk/keluar serta deteksi kendaraan overstay di atas 24 jam.',
                    'link_text' => 'Login Portal Owner',
                    'link_url' => route('login') . '?tab=user',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'title' => 'Multi-Tenant & 5 Role Access',
                    'desc' => 'Hierarki lengkap: Super Admin, Owner lahan, Admin operasional, Petugas pos gerbang, dan Pengendara dengan isolasi data ketat.',
                    'link_text' => 'Login Portal Staf',
                    'link_url' => route('login') . '?tab=user',
                ],
            ];
            @endphp
            @foreach($features as $f)
            <div class="p-7 bg-white rounded-2xl border border-slate-200/90 hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $f['icon'] !!}</svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-dark">{{ $f['title'] }}</h3>
                    <p class="mt-2 text-sm text-dark/65 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
                <div class="pt-5 mt-5 border-t border-slate-100">
                    <a href="{{ $f['link_url'] }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:text-primary-dark transition">
                        <span>{{ $f['link_text'] }}</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition shadow-lg shadow-primary/20 text-sm">
                <span>Daftar Sekarang untuk Mencoba Semua Fitur</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    4. CARA / ALUR KERJA
═══════════════════════════════════════════ --}}
<section id="cara-kerja" class="scroll-mt-16 py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold text-primary uppercase tracking-widest bg-primary/10 px-3.5 py-1.5 rounded-full">Alur Operasional</span>
            <h2 class="mt-4 font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
                Alur Transaksi dalam <span class="text-primary">4 Langkah Mudah</span>
            </h2>
            <p class="mt-4 text-base text-dark/65 leading-relaxed">
                Dari pintu masuk hingga kendaraan keluar, semua proses dirancang efisien dan terotomatisasi penuh.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
            $steps = [
                [
                    'num' => '01',
                    'title' => 'Kendaraan Masuk',
                    'desc' => 'Petugas scan plat nomor via OCR, cek booking online, atau input manual. Sistem otomatis memvalidasi status banned & mencatat waktu masuk.',
                    'badge_bg' => 'bg-primary text-white shadow-primary/25',
                ],
                [
                    'num' => '02',
                    'title' => 'Penerbitan Karcis',
                    'desc' => 'Karcis QR dicetak via printer thermal portabel atau dikirim digital ke akun pengendara. Kuota terisi bertambah secara atomik.',
                    'badge_bg' => 'bg-accent text-dark shadow-accent/25',
                ],
                [
                    'num' => '03',
                    'title' => 'Scan Karcis Keluar',
                    'desc' => 'Petugas memindai QR karcis (atau fallback pencarian nomor plat jika tiket hilang). Sistem menghitung durasi & biaya otomatis.',
                    'badge_bg' => 'bg-primary text-white shadow-primary/25',
                ],
                [
                    'num' => '04',
                    'title' => 'Konfirmasi & Selesai',
                    'desc' => 'Petugas memvalidasi pembayaran tunai/cashless. Kuota terisi berkurang, log transaksi tersimpan permanen di database.',
                    'badge_bg' => 'bg-accent text-dark shadow-accent/25',
                ],
            ];
            @endphp
            @foreach($steps as $s)
            <div class="relative bg-secondary p-7 rounded-2xl border border-slate-200 text-center flex flex-col items-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl font-heading font-black text-xl mb-5 shadow-lg {{ $s['badge_bg'] }}">
                    {{ $s['num'] }}
                </div>
                <h3 class="font-heading font-extrabold text-lg text-dark">{{ $s['title'] }}</h3>
                <p class="mt-2.5 text-sm text-dark/65 leading-relaxed">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-accent text-dark font-bold rounded-xl hover:bg-accent-dark transition shadow-lg shadow-accent/25 text-sm">
                <span>Mulai dari Langkah 1: Buat Akun Pengendara</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    5. KEUNGGULAN
═══════════════════════════════════════════ --}}
<section id="keunggulan" class="scroll-mt-16 py-20 lg:py-28 bg-dark text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold text-accent uppercase tracking-widest bg-accent/20 px-3.5 py-1.5 rounded-full">Keunggulan Sistem</span>
            <h2 class="mt-4 font-heading text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Mengapa Memilih <span class="text-accent">Parkirin</span>?
            </h2>
            <p class="mt-4 text-base text-white/70 leading-relaxed font-light">
                Teknologi yang dirancang tepat sasaran untuk kehandalan tinggi di lapangan tanpa kompromi performa.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
            $advantages = [
                ['title' => 'Isolasi Tenant & Anti-IDOR', 'desc' => 'Setiap tempat parkir memiliki ruang data tersendiri. Kueri otomatis difilter id_tempat_parkir dengan proteksi 403 Forbidden.'],
                ['title' => 'Scan On-Device Cepat', 'desc' => 'Pengenalan plat nomor berjalan lokal di perangkat Android & iOS tanpa jeda upload server atau ketergantungan sinyal.'],
                ['title' => 'MySQL Stored Function', 'desc' => 'Penghitungan tarif dihitung langsung di database engine untuk menjamin integritas nominal dan menghindari bug di sisi klien.'],
                ['title' => 'Mitigasi Tiket Hilang', 'desc' => 'Alur pencarian darurat berdasarkan plat nomor atau transaksi aktif memudahkan penanganan saat karcis fisik tercecer.'],
                ['title' => 'Pencegahan Balap Kuota', 'desc' => 'Mutasi kapasitas dilakukan dalam transaksi database ber-lock (lockForUpdate) sehingga slot tidak pernah over-capacity.'],
                ['title' => 'Audit Trail Tak Terhapus', 'desc' => 'Setiap perubahan tarif, mutasi kapasitas, maupun persetujuan banned tercatat secara kronologis di tabel log_aktivitas.'],
            ];
            @endphp
            @foreach($advantages as $a)
            <div class="p-7 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-accent/20 flex items-center justify-center text-accent mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="font-heading font-bold text-lg text-white">{{ $a['title'] }}</h3>
                <p class="mt-2 text-sm text-white/65 leading-relaxed">{{ $a['desc'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="#kontak" class="inline-flex items-center gap-2 px-8 py-3.5 border-2 border-accent text-accent font-bold rounded-xl hover:bg-accent hover:text-dark transition text-sm">
                <span>Konsultasikan Kebutuhan Lahan Anda Bersama Kami</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    6. TESTIMONI (Marquee berjalan referensi 21st.dev)
═══════════════════════════════════════════ --}}
<section id="testimoni" class="scroll-mt-16 py-20 lg:py-28 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-14 text-center">
        <span class="text-xs font-bold text-primary uppercase tracking-widest bg-primary/10 px-3.5 py-1.5 rounded-full">Testimoni Pengguna</span>
        <h2 class="mt-4 font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
            Dipercaya oleh <span class="text-primary">Mitra Operator & Pengendara</span>
        </h2>
        <p class="mt-4 text-base text-dark/65 max-w-2xl mx-auto">
            Dengar langsung pengalaman mereka yang telah merasakan kemudahan sistem Parkirin setiap hari.
        </p>
    </div>

    @php
    $testimonials = [
        ['name' => 'Budi Hartono', 'role' => 'Owner Lahan Parkir Stasiun', 'text' => 'Parkirin membantu saya memantau petugas lapangan secara transparan. Fitur anti-fraud sangat berguna untuk mendeteksi ketidaksesuaian jumlah kendaraan di lapangan.'],
        ['name' => 'Rina Marlina', 'role' => 'Pengendara Komuter Harian', 'text' => 'Booking slot parkir dari aplikasi sangat praktis! Tidak perlu was-was kehabisan tempat saat mengejar jadwal kereta pagi.'],
        ['name' => 'Andi Wijaya', 'role' => 'Owner Area Parkir Komersial', 'text' => 'Tarif progresif dan tarif inap otomatis memudahkan kalkulasi biaya harian. Pendapatan kami tercatat rapi tanpa selisih manual.'],
        ['name' => 'Siti Aminah', 'role' => 'Admin Pengelola Tenant', 'text' => 'Pengaturan tarif dan kapasitas sangat simpel. Saya bisa mengaktifkan atau menonaktifkan fitur booking secara mandiri sesuai kepadatan hari itu.'],
        ['name' => 'Dimas Prasetya', 'role' => 'Pengendara Sepeda Motor', 'text' => 'Karcis digital QR Code membuat saya tidak perlu khawatir lagi kehilangan struk kertas. Riwayat pembayarannya pun tercatat jelas.'],
        ['name' => 'Joko Susilo', 'role' => 'Petugas Pos Gerbang', 'text' => 'Scan OCR plat nomor sangat cepat dan akurat. Waktu antrean masuk berkurang drastis sehingga tidak ada penumpukan kendaraan di pintu masuk.'],
    ];
    @endphp

    {{-- Row 1: Horizontal Marquee (Left to Right) --}}
    <div class="relative mb-6">
        <div class="absolute inset-y-0 left-0 w-24 sm:w-36 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute inset-y-0 right-0 w-24 sm:w-36 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
        <div class="flex animate-marquee w-max gap-6">
            @for($i = 0; $i < 2; $i++)
                @foreach($testimonials as $t)
                <div class="w-[340px] sm:w-[380px] shrink-0 p-6 bg-secondary rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <p class="text-sm text-dark/75 leading-relaxed font-sans italic">"{{ $t['text'] }}"</p>
                    <div class="mt-5 flex items-center gap-3 pt-4 border-t border-slate-200/50">
                        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-heading font-black text-sm">
                            {{ mb_substr($t['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-heading font-bold text-sm text-dark">{{ $t['name'] }}</p>
                            <p class="text-xs text-dark/50">{{ $t['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @endfor
        </div>
    </div>

    {{-- Row 2: Reverse Marquee (Right to Left) --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 w-24 sm:w-36 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute inset-y-0 right-0 w-24 sm:w-36 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
        <div class="flex animate-marquee-reverse w-max gap-6">
            @for($i = 0; $i < 2; $i++)
                @foreach(array_reverse($testimonials) as $t)
                <div class="w-[340px] sm:w-[380px] shrink-0 p-6 bg-secondary rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <p class="text-sm text-dark/75 leading-relaxed font-sans italic">"{{ $t['text'] }}"</p>
                    <div class="mt-5 flex items-center gap-3 pt-4 border-t border-slate-200/50">
                        <div class="w-10 h-10 rounded-full bg-accent flex items-center justify-center text-dark font-heading font-black text-sm">
                            {{ mb_substr($t['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-heading font-bold text-sm text-dark">{{ $t['name'] }}</p>
                            <p class="text-xs text-dark/50">{{ $t['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @endfor
        </div>
    </div>

    <div class="mt-10 text-center">
        <p class="text-sm text-dark/65">
            Ingin bergabung bersama mitra dan pengendara lainnya?
            <a href="{{ route('register') }}" class="text-primary font-bold hover:underline ml-1">Daftar Pengendara</a>
            <span class="mx-2 text-dark/30">&bull;</span>
            <a href="#kontak" class="text-primary font-bold hover:underline">Daftarkan Lahan Mitra</a>
        </p>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    7. CTA & KONTAK (Gaya CTA with Marquee referensi 21st.dev)
═══════════════════════════════════════════ --}}
<section id="kontak" class="scroll-mt-16 py-20 lg:py-28 bg-primary overflow-hidden text-white relative">
    {{-- Highlight Marquee Ribbon Strip --}}
    <div class="relative mb-12 bg-primary-dark/80 py-3 border-y border-white/10">
        <div class="flex animate-marquee w-max gap-8">
            @for($i = 0; $i < 4; $i++)
                @foreach(['Aman', 'Fleksibel', 'Profesional', 'Terpercaya', 'Scan OCR Cepat', 'Karcis QR Digital', 'Multi-Tenant Anti-IDOR', 'MySQL Stored Function'] as $tag)
                <span class="inline-flex items-center gap-2 text-xs uppercase tracking-widest font-extrabold text-white/90 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-accent"></span>{{ $tag }}
                </span>
                @endforeach
            @endfor
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black leading-tight tracking-tight">
            Siap Memodernisasi <span class="text-accent underline decoration-accent/40 decoration-wavy">Lahan Parkir</span> Anda?
        </h2>
        <p class="mt-6 text-base sm:text-lg text-white/80 max-w-2xl mx-auto font-light leading-relaxed">
            Bergabunglah sekarang dengan platform parkir terintegrasi. Akses gratis untuk pengendara dan kemudahan integrasi cepat bagi operator parkir swadaya.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark transition shadow-xl shadow-accent/25 text-base">
                Daftar Pengendara Gratis
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Parkirin,%20saya%20tertarik%20mendaftarkan%20lahan%20parkir%20mitra" target="_blank" class="inline-flex items-center justify-center px-8 py-4 font-semibold text-white border-2 border-white/40 rounded-xl hover:bg-white/10 transition text-base">
                Daftarkan Lahan Parkir Mitra
            </a>
        </div>

        {{-- Contact Details --}}
        <div class="mt-14 pt-8 border-t border-white/15 flex flex-wrap justify-center gap-6 sm:gap-10 text-white/80 text-sm">
            <a href="mailto:info@parkirin.id" class="flex items-center gap-2.5 hover:text-accent transition">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-accent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span>info@parkirin.id</span>
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Parkirin" target="_blank" class="flex items-center gap-2.5 hover:text-accent transition">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-accent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <span>+62 812-3456-7890</span>
            </a>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-accent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span>Jakarta, Indonesia</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    8. FOOTER
═══════════════════════════════════════════ --}}
<footer class="bg-dark text-white py-14 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Brand Column --}}
            <div>
                <a href="{{ route('landing') }}" class="flex items-center gap-2 mb-4 group">
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center group-hover:bg-primary-light transition">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
                    </div>
                    <span class="font-heading font-black text-xl text-white">Park<span class="text-primary-light">irin</span></span>
                </a>
                <p class="text-sm text-white/55 leading-relaxed">
                    Sistem parkir cerdas multi-tenant portable untuk operator parkir swadaya di seluruh Indonesia. Aman, fleksibel, profesional, dan terpercaya.
                </p>
            </div>

            {{-- Navigasi Produk --}}
            <div>
                <h4 class="font-heading font-bold text-white text-sm mb-4 uppercase tracking-wider">Navigasi Halaman</h4>
                <ul class="space-y-2.5 text-sm text-white/60">
                    <li><a href="#about" class="hover:text-accent transition">Tentang Kami</a></li>
                    <li><a href="#fitur" class="hover:text-accent transition">Fitur Unggulan</a></li>
                    <li><a href="#cara-kerja" class="hover:text-accent transition">Alur Cara Kerja</a></li>
                    <li><a href="#keunggulan" class="hover:text-accent transition">Keunggulan Sistem</a></li>
                    <li><a href="#testimoni" class="hover:text-accent transition">Testimoni Pengguna</a></li>
                </ul>
            </div>

            {{-- Akun & Portal --}}
            <div>
                <h4 class="font-heading font-bold text-white text-sm mb-4 uppercase tracking-wider">Portal Akses</h4>
                <ul class="space-y-2.5 text-sm text-white/60">
                    <li><a href="{{ route('login') }}?tab=pengendara" class="hover:text-accent transition">Masuk Akun Pengendara</a></li>
                    <li><a href="{{ route('login') }}?tab=user" class="hover:text-accent transition">Portal Staf / Petugas</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-accent transition">Pendaftaran Pengendara</a></li>
                    <li><a href="https://wa.me/6281234567890?text=Halo%20Admin%20Parkirin,%20saya%20ingin%20konsultasi%20mitra%20lahan" target="_blank" class="hover:text-accent transition">Daftar Mitra Lahan</a></li>
                </ul>
            </div>

            {{-- Info Kontak --}}
            <div>
                <h4 class="font-heading font-bold text-white text-sm mb-4 uppercase tracking-wider">Kontak & Bantuan</h4>
                <ul class="space-y-2.5 text-sm text-white/60">
                    <li><a href="mailto:info@parkirin.id" class="hover:text-accent transition flex items-center gap-2"><span>✉️</span> info@parkirin.id</a></li>
                    <li><a href="https://wa.me/6281234567890" target="_blank" class="hover:text-accent transition flex items-center gap-2"><span>💬</span> +62 812-3456-7890</a></li>
                    <li class="flex items-center gap-2"><span>📍</span> Jakarta, Indonesia</li>
                    <li class="text-xs text-white/40 pt-1">Layanan Bantuan 24/7</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/40">
            <p>&copy; {{ date('Y') }} Parkirin SaaS. Hak Cipta Dilindungi Undang-Undang.</p>
            <p>Dibangun dengan dedikasi untuk kemajuan operator parkir swadaya Indonesia.</p>
        </div>
    </div>
</footer>

</div>

@push('scripts')
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
