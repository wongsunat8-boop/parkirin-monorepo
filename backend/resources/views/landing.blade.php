@extends('layouts.app')

@section('title', 'Parkirin — Sistem Parkir Cerdas Multi-Tenant & Portable')

@section('content')

{{-- ═══════════════════════════════════════════
    1. NAVBAR (Relational Toggle Tab Navigation)
═══════════════════════════════════════════ --}}
<nav class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 sm:h-20">
        {{-- Logo Resmi Parkirin --}}
        <a href="{{ route('landing') }}" class="flex items-center group py-1 transition-transform duration-200 hover:scale-[1.02]" aria-label="Beranda Parkirin">
            <img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" class="h-8 sm:h-10 w-auto object-contain">
        </a>

        {{-- Desktop Relational Toggle Tab Navigation (Inspired by React Reference) --}}
        <div id="nav-toggle-container" class="hidden md:flex items-center relative p-1.5 rounded-full border border-slate-200/90 bg-slate-100/90 shadow-inner backdrop-blur-md">
            {{-- Animated Sliding Active Indicator Pill --}}
            <div id="nav-active-pill" class="absolute h-9 rounded-full bg-primary shadow-sm transition-all duration-300 ease-out pointer-events-none" style="left: 6px; width: 0px; opacity: 0;"></div>

            <a href="#about" data-tab="about" onclick="handleNavTabClick('about', this)" class="nav-tab-btn relative z-10 flex items-center gap-1.5 px-3.5 py-1.5 text-xs lg:text-sm font-semibold rounded-full transition-colors duration-200 text-dark/75 hover:text-dark">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="hidden lg:inline">Tentang</span>
            </a>
            <a href="#fitur" data-tab="fitur" onclick="handleNavTabClick('fitur', this)" class="nav-tab-btn relative z-10 flex items-center gap-1.5 px-3.5 py-1.5 text-xs lg:text-sm font-semibold rounded-full transition-colors duration-200 text-dark/75 hover:text-dark">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span class="hidden lg:inline">Fitur</span>
            </a>
            <a href="#cara-kerja" data-tab="cara-kerja" onclick="handleNavTabClick('cara-kerja', this)" class="nav-tab-btn relative z-10 flex items-center gap-1.5 px-3.5 py-1.5 text-xs lg:text-sm font-semibold rounded-full transition-colors duration-200 text-dark/75 hover:text-dark">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="hidden lg:inline">Cara Kerja</span>
            </a>
            <a href="#testimoni" data-tab="testimoni" onclick="handleNavTabClick('testimoni', this)" class="nav-tab-btn relative z-10 flex items-center gap-1.5 px-3.5 py-1.5 text-xs lg:text-sm font-semibold rounded-full transition-colors duration-200 text-dark/75 hover:text-dark">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="hidden lg:inline">Testimoni</span>
            </a>
            <a href="#kontak" data-tab="kontak" onclick="handleNavTabClick('kontak', this)" class="nav-tab-btn relative z-10 flex items-center gap-1.5 px-3.5 py-1.5 text-xs lg:text-sm font-semibold rounded-full transition-colors duration-200 text-dark/75 hover:text-dark">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="hidden lg:inline">Kontak</span>
            </a>
        </div>

        {{-- Desktop Auth Actions with Hover Transitions --}}
        <div class="hidden sm:flex items-center gap-3">
            @if(auth('pengendara')->check())
                <a href="{{ route('pengendara.dashboard') }}" class="group inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark hover:scale-[1.03] hover:shadow-lg hover:shadow-accent/25 transition-all duration-200 active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Dashboard Pengendara</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-danger hover:bg-danger/10 rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">Keluar</button>
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
                <a href="{{ $dashRoute }}" class="group inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary-dark hover:scale-[1.03] hover:shadow-lg hover:shadow-primary/25 transition-all duration-200 active:scale-95 shadow-sm">
                    <span>Portal {{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                    <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-danger hover:bg-danger/10 rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-dark hover:text-primary hover:bg-slate-100 rounded-xl transition-all duration-200 active:scale-95">Masuk</a>
                <a href="{{ route('register') }}" class="group inline-flex items-center px-5 py-2.5 text-sm font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark hover:scale-[1.03] hover:shadow-lg hover:shadow-accent/25 transition-all duration-200 active:scale-95 shadow-sm">
                    Daftar Pengendara
                </a>
            @endif
        </div>

        {{-- Mobile Hamburger Button --}}
        <div class="flex items-center sm:hidden">
            <button type="button" onclick="toggleMobileMenu()" class="p-2.5 rounded-lg text-dark/80 hover:text-dark hover:bg-slate-100 transition-colors focus:outline-none" aria-label="Toggle Navigation">
                <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu Dropdown --}}
    <div id="mobile-menu" class="hidden sm:hidden border-t border-slate-200 bg-white px-5 pt-4 pb-6 space-y-3 shadow-xl">
        <a href="#about" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary transition-colors">Tentang</a>
        <a href="#fitur" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary transition-colors">Fitur</a>
        <a href="#cara-kerja" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary transition-colors">Cara Kerja</a>
        <a href="#testimoni" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary transition-colors">Testimoni</a>
        <a href="#kontak" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-dark/80 hover:text-primary transition-colors">Kontak</a>
        <div class="pt-4 border-t border-slate-100 flex flex-col gap-2.5">
            @if(auth('pengendara')->check())
                <a href="{{ route('pengendara.dashboard') }}" class="w-full text-center py-3 text-sm font-bold text-dark bg-accent rounded-xl shadow-sm hover:bg-accent-dark transition-colors">Buka Dashboard Pengendara</a>
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
                <a href="{{ $dashRouteMobile }}" class="w-full text-center py-3 text-sm font-bold text-white bg-primary rounded-xl shadow-sm hover:bg-primary-dark transition-colors">Buka Portal {{ ucfirst(str_replace('_', ' ', $roleMobile)) }}</a>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full text-center py-2 text-xs font-semibold text-danger">Keluar Akun</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full text-center py-2.5 text-sm font-semibold text-dark border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">Masuk Portal</a>
                <a href="{{ route('register') }}" class="w-full text-center py-3 text-sm font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark transition-colors">Daftar Pengendara</a>
            @endif
        </div>
    </div>
</nav>

<div class="pt-16 sm:pt-20">

{{-- ═══════════════════════════════════════════
    2. HERO SECTION (Linea Style + Extreme Z-Rotation + No Chip Clipping)
═══════════════════════════════════════════ --}}
<section class="relative bg-secondary overflow-hidden pt-12 sm:pt-16 lg:pt-20 pb-20 lg:pb-32 border-b border-slate-200">
    {{-- Soft background photography banner --}}
    <div class="absolute inset-0 pointer-events-none opacity-5">
        <img src="https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=1920&q=80" alt="Urban Mobility" class="w-full h-full object-cover">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Hero Header Content --}}
        <div class="text-center max-w-3xl mx-auto">
            {{-- Gojek-style bold headline with light entrance transition --}}
            <h1 class="reveal-hero font-heading text-4xl sm:text-5xl lg:text-6xl font-black text-dark tracking-tight leading-[1.15] opacity-0 translate-y-4 transition-all duration-700 ease-out">
                Kelola Parkir Lebih Cerdas, <br class="hidden sm:inline">
                <span class="text-primary">Cepat & Transparan</span>
            </h1>

            {{-- Crisp, honest copy --}}
            <p class="reveal-hero mt-5 text-base sm:text-lg text-dark/70 font-sans leading-relaxed max-w-2xl mx-auto opacity-0 translate-y-4 transition-all duration-700 ease-out delay-100">
                Solusi parkir terintegrasi untuk operator swadaya dan pengendara. Scan OCR plat nomor otomatis, karcis QR digital instan, dan pantau pendapatan tanpa celah kebocoran.
            </p>

            {{-- Metrics Line --}}
            <div class="reveal-hero mt-6 flex flex-wrap items-center justify-center gap-y-2 gap-x-6 text-sm text-dark/75 font-medium opacity-0 translate-y-4 transition-all duration-700 ease-out delay-200">
                <div><span class="font-bold text-dark">4.9/5</span> dari 1.200+ ulasan</div>
                <div class="hidden sm:inline text-slate-300">&bull;</div>
                <div>&lt; 10 detik masuk gate</div>
                <div class="hidden sm:inline text-slate-300">&bull;</div>
                <div>Audit log real-time</div>
            </div>

            {{-- Hero Call to Actions with Hover Transitions --}}
            <div class="reveal-hero mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5 opacity-0 translate-y-4 transition-all duration-700 ease-out delay-300">
                <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark hover:scale-[1.03] hover:shadow-xl hover:shadow-accent/30 transition-all duration-200 active:scale-95 text-sm">
                    <span>Mulai Parkir Sekarang</span>
                    <svg class="ml-2 w-4 h-4 transition-transform duration-200 group-hover:translate-x-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="#cara-kerja" class="w-full sm:w-auto inline-flex items-center justify-center px-7 py-3.5 font-semibold text-dark bg-white border border-slate-300 rounded-xl hover:border-dark hover:bg-slate-50 hover:scale-[1.03] hover:shadow-md transition-all duration-200 active:scale-95 text-sm">
                    Lihat Cara Kerja
                </a>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
            CONTAINER SCROLL ANIMATION & DYNAMIC FLOATING CHIPS
            Ample vertical padding to prevent ANY chip from being clipped/cut-off!
        ═══════════════════════════════════════════ --}}
        <div id="container-scroll-area" class="relative mt-12 sm:mt-16 pt-10 sm:pt-14 pb-24 sm:pb-32 flex flex-col items-center justify-center max-w-full">
            
            {{-- Floating Chip 1 (Top Left): Scan OCR Kamera --}}
            <div id="hero-chip-1" class="hidden md:flex absolute top-12 left-1 lg:left-6 z-30 bg-white rounded-2xl p-4 shadow-2xl border border-slate-200/90 pointer-events-none items-center gap-3.5 max-w-[250px] transition-transform duration-75 ease-out will-change-transform" style="transform: rotateZ(-26deg) translateY(0px); transform-origin: center center;">
                <div class="w-11 h-11 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-dark">Scan OCR Kamera</p>
                    <p class="text-sm font-mono font-black text-primary">B 1234 XYZ</p>
                    <p class="text-[11px] text-dark/60">Waktu proses 0.2 dtk</p>
                </div>
            </div>

            {{-- Floating Chip 2 (Top Right): Karcis Digital QR --}}
            <div id="hero-chip-2" class="hidden md:flex absolute top-8 right-1 lg:right-6 z-30 bg-white rounded-2xl p-4 shadow-2xl border border-slate-200/90 pointer-events-none items-center gap-3.5 max-w-[250px] transition-transform duration-75 ease-out will-change-transform" style="transform: rotateZ(26deg) translateY(0px); transform-origin: center center;">
                <div class="w-11 h-11 rounded-xl bg-accent text-dark flex items-center justify-center shrink-0 font-black text-sm shadow-sm">
                    QR
                </div>
                <div>
                    <p class="text-xs font-bold text-dark">Karcis Digital QR</p>
                    <p class="text-xs font-bold text-emerald-600">Gate Terbuka Otomatis</p>
                    <p class="text-[11px] text-dark/60">Tersimpan di Aplikasi</p>
                </div>
            </div>

            {{-- Floating Chip 3 (Bottom Left): Placed with ample bottom clearance so it NEVER clips! --}}
            <div id="hero-chip-3" class="hidden lg:flex absolute bottom-16 sm:bottom-20 left-4 lg:left-8 z-30 bg-white rounded-2xl p-4 shadow-2xl border border-slate-200/90 pointer-events-none items-center gap-3.5 max-w-[250px] transition-transform duration-75 ease-out will-change-transform" style="transform: rotateZ(-18deg) translateY(0px); transform-origin: center center;">
                <div class="w-11 h-11 rounded-xl bg-dark text-white flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-dark">Kapasitas Slot</p>
                    <p class="text-xs font-bold text-dark">42 / 50 Slot Terisi</p>
                    <p class="text-[11px] text-dark/60">Audit log tanpa selisih</p>
                </div>
            </div>

            {{-- Floating Chips for Mobile Viewport (< 768px) --}}
            <div class="flex md:hidden justify-center gap-3 mb-5 w-full px-2">
                <div id="hero-chip-m1" class="flex-1 bg-white rounded-xl p-3 border border-slate-200 shadow-md flex items-center gap-2.5 transition-transform duration-75 ease-out will-change-transform" style="transform: rotateZ(-14deg); transform-origin: center center;">
                    <div class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-dark">OCR Kamera</p>
                        <p class="text-xs font-mono font-bold text-primary">B 1234 XYZ</p>
                    </div>
                </div>
                <div id="hero-chip-m2" class="flex-1 bg-white rounded-xl p-3 border border-slate-200 shadow-md flex items-center gap-2.5 transition-transform duration-75 ease-out will-change-transform" style="transform: rotateZ(14deg); transform-origin: center center;">
                    <div class="w-8 h-8 rounded-lg bg-accent text-dark flex items-center justify-center shrink-0 font-black text-xs">
                        QR
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-dark">Karcis Digital</p>
                        <p class="text-xs font-bold text-emerald-600">Gate Terbuka</p>
                    </div>
                </div>
            </div>

            {{-- 3D Device Card with smooth entrance reveal --}}
            <div class="reveal-hero w-full max-w-5xl [perspective:1000px] sm:[perspective:1200px] opacity-0 translate-y-6 transition-all duration-800 ease-out delay-300">
                <div id="container-scroll-card" class="transition-transform duration-100 ease-out will-change-transform rounded-2xl sm:rounded-3xl border border-slate-300 sm:border-2 sm:border-slate-300/80 bg-white shadow-2xl p-3 sm:p-5 mx-auto" style="transform: rotateX(20deg) scale(0.93); transform-origin: top center;">
                    
                    {{-- Device Header Bar --}}
                    <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-100 text-xs text-dark/70">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-dark/50 hidden sm:inline">pos-gate-01.parkirin.id</span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold text-[11px] sm:text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>POS Gerbang Utama &bull; Stasiun Gambir</span>
                        </div>
                    </div>

                    {{-- Mockup Main Body: 2-Column Operational View --}}
                    <div class="mt-4 grid md:grid-cols-12 gap-4 text-left">
                        
                        {{-- Left Column: Live OCR Scan Viewfinder --}}
                        <div class="md:col-span-7 bg-slate-900 rounded-xl overflow-hidden relative border border-slate-800 flex flex-col justify-between p-4 min-h-[260px] sm:min-h-[300px]">
                            <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80" alt="Kamera Pos Parkir" class="absolute inset-0 w-full h-full object-cover opacity-40">
                            
                            {{-- Viewfinder UI overlay --}}
                            <div class="relative z-10 flex items-center justify-between">
                                <span class="text-xs font-mono font-bold text-white flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                    <span>KAMERA POS 01 [LIVE]</span>
                                </span>
                                <span class="text-xs font-mono text-emerald-400 font-bold">
                                    OCR 99.8%
                                </span>
                            </div>

                            {{-- Target Bounding Box on License Plate --}}
                            <div class="relative z-10 my-auto self-center text-center p-3 sm:p-4 rounded-xl border-2 border-dashed border-accent bg-dark/75 backdrop-blur-sm max-w-xs w-full">
                                <p class="text-[10px] uppercase font-mono text-accent mb-1 tracking-wider">Hasil Pemindaian Otomatis</p>
                                <span class="badge-plat text-base sm:text-lg">B 1234 XYZ</span>
                                <p class="text-[11px] text-white/80 mt-1 font-sans">Toyota Avanza (Hitam) &bull; 07:45 WIB</p>
                            </div>

                            {{-- Footer metrics --}}
                            <div class="relative z-10 flex items-center justify-between text-[11px] text-white/75 bg-dark/80 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-white/10">
                                <span>Waktu Proses: <strong class="text-accent font-mono">0.24 detik</strong></span>
                                <span>Status: <strong class="text-emerald-400">Plat Valid</strong></span>
                            </div>
                        </div>

                        {{-- Right Column: Active Ticket & Rate Information --}}
                        <div class="md:col-span-5 bg-secondary rounded-xl p-4 border border-slate-200/80 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                                    <div>
                                        <p class="font-heading font-bold text-sm text-dark">Karcis QR Terbit</p>
                                        <p class="text-[11px] text-dark/50">ID Transaksi: #TRX-88291</p>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-600">Status: Aktif</span>
                                </div>

                                <div class="mt-3 space-y-2 text-xs">
                                    <div class="flex justify-between items-center py-1.5 border-b border-slate-200/60">
                                        <span class="text-dark/60">Jenis Tarif</span>
                                        <span class="font-bold text-primary">Per Jam + Progresif</span>
                                    </div>
                                    <div class="flex justify-between items-center py-1.5 border-b border-slate-200/60">
                                        <span class="text-dark/60">Tarif Dasar</span>
                                        <span class="font-semibold text-dark">Rp 4.000 (1 Jam Pertama)</span>
                                    </div>
                                    <div class="flex justify-between items-center py-1.5 border-b border-slate-200/60">
                                        <span class="text-dark/60">Kapasitas Slot</span>
                                        <span class="font-bold text-dark">Sisa 8 dari 50 Mobil</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center gap-3">
                                <div class="w-14 h-14 bg-white p-1 rounded-lg border border-slate-200 shadow-sm flex items-center justify-center shrink-0">
                                    <svg class="w-full h-full text-dark" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v2h-4v-2zm-4 0h2v4h-2v-4zm2 4h4v4h-4v-4zm-4 2h2v2h-2v-2zm6-2h2v2h-2v-2z"/>
                                    </svg>
                                </div>
                                <div class="text-[11px] text-dark/70">
                                    <p class="font-bold text-dark">Tersinkronisasi ke Cloud</p>
                                    <p>Tercatat otomatis di audit trail owner.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════
    3. ABOUT US (Deskripsi Singkat & Target Pengelola)
═══════════════════════════════════════════ --}}
<section id="about" class="scroll-mt-20 py-16 sm:py-20 lg:py-24 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Section Header: Deskripsi Maksimal 20 Kata --}}
        <div class="reveal-on-scroll text-center max-w-3xl mx-auto mb-10 sm:mb-14 opacity-0 translate-y-8 transition-all duration-700 ease-out">
            <span class="text-xs sm:text-sm font-bold tracking-wider uppercase text-primary font-mono">Tentang Parkirin</span>
            <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight leading-tight mt-1.5">
                Solusi Parkir Cerdas untuk Pengelolaan Mandiri yang Modern
            </h2>
            <p class="mt-3.5 text-base sm:text-lg text-dark/75 font-sans leading-relaxed max-w-2xl mx-auto">
                Platform parkir digital portabel untuk membantu pengelola swadaya mencatat kendaraan, mencegah kebocoran setoran, dan melayani pengendara lebih cepat.
            </p>
        </div>

        {{-- Target Audiens: Foto Sekilas Tiap Contoh Target (Tanpa Paragraf Panjang) --}}
        <div class="reveal-on-scroll opacity-0 translate-y-8 transition-all duration-700 ease-out delay-100">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-6">
                
                {{-- Card 1: Penitipan Stasiun & Terminal --}}
                <div class="group p-3 sm:p-4 rounded-2xl bg-secondary border border-slate-200/90 hover:bg-white hover:border-primary/50 hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                    <div class="relative h-32 sm:h-44 w-full rounded-xl overflow-hidden bg-slate-100 mb-3">
                        <img src="https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=600&q=80" alt="Penitipan Stasiun dan Terminal" class="w-full h-full object-cover transition-transform duration-300 ease-out group-hover:scale-105">
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-sm sm:text-base text-dark leading-snug">Penitipan Stasiun & Terminal</h4>
                        <p class="text-[11px] sm:text-xs font-semibold text-primary mt-1 font-sans">Melayani komuter & penitipan harian</p>
                    </div>
                </div>

                {{-- Card 2: Ruko & Area Komersial --}}
                <div class="group p-3 sm:p-4 rounded-2xl bg-secondary border border-slate-200/90 hover:bg-white hover:border-accent/60 hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                    <div class="relative h-32 sm:h-44 w-full rounded-xl overflow-hidden bg-slate-100 mb-3">
                        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80" alt="Ruko dan Area Komersial" class="w-full h-full object-cover transition-transform duration-300 ease-out group-hover:scale-105">
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-sm sm:text-base text-dark leading-snug">Ruko, Kafe & Restoran</h4>
                        <p class="text-[11px] sm:text-xs font-semibold text-accent-dark mt-1 font-sans">Mengakomodir lahan depan usaha</p>
                    </div>
                </div>

                {{-- Card 3: Pasar & Area Wisata / Event --}}
                <div class="group p-3 sm:p-4 rounded-2xl bg-secondary border border-slate-200/90 hover:bg-white hover:border-primary/50 hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                    <div class="relative h-32 sm:h-44 w-full rounded-xl overflow-hidden bg-slate-100 mb-3">
                        <img src="https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&w=600&q=80" alt="Pasar dan Tempat Wisata" class="w-full h-full object-cover transition-transform duration-300 ease-out group-hover:scale-105">
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-sm sm:text-base text-dark leading-snug">Pasar & Lokasi Wisata</h4>
                        <p class="text-[11px] sm:text-xs font-semibold text-primary mt-1 font-sans">Melayani lonjakan pengunjung akhir pekan</p>
                    </div>
                </div>

                {{-- Card 4: Perkantoran & Komplek Mandiri --}}
                <div class="group p-3 sm:p-4 rounded-2xl bg-secondary border border-slate-200/90 hover:bg-white hover:border-accent/60 hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                    <div class="relative h-32 sm:h-44 w-full rounded-xl overflow-hidden bg-slate-100 mb-3">
                        <img src="https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=600&q=80" alt="Gedung dan Komplek Mandiri" class="w-full h-full object-cover transition-transform duration-300 ease-out group-hover:scale-105">
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-sm sm:text-base text-dark leading-snug">Gedung & Hunian Mandiri</h4>
                        <p class="text-[11px] sm:text-xs font-semibold text-accent-dark mt-1 font-sans">Mengakomodir akses penghuni & tamu</p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════
    4. FITUR UTAMA (Bahasa Lugas, Mudah Dipahami & Ringkas di Mobile)
═══════════════════════════════════════════ --}}
<section id="fitur" class="scroll-mt-20 py-16 sm:py-20 lg:py-24 bg-secondary border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="reveal-on-scroll text-center max-w-2xl mx-auto mb-12 sm:mb-16 opacity-0 translate-y-8 transition-all duration-700 ease-out">
            <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
                Semua Kebutuhan Parkir dalam Satu Aplikasi
            </h2>
            <p class="mt-3 text-base text-dark/70 font-sans leading-relaxed">
                Dirancang praktis agar petugas mudah bekerja, pemilik lahan tenang, dan pengendara merasa nyaman.
            </p>
        </div>

        {{-- Bento Grid Layout --}}
        <div class="grid md:grid-cols-3 gap-5 sm:gap-6">

            {{-- Feature 1 (Large Bento with Photo): Pindai Plat Nomor --}}
            <div class="reveal-on-scroll group md:col-span-2 bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out">
                <div class="p-5 sm:p-8">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3 sm:mb-4 group-hover:bg-primary group-hover:text-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg sm:text-2xl text-dark">Pindai Plat Nomor Otomatis</h3>
                    <p class="mt-2 text-xs sm:text-sm text-dark/70 font-sans leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Arahkan kamera HP ke plat kendaraan, sistem langsung membaca nomor plat dalam sekejap tanpa repot mengetik manual.</span>
                        <span class="hidden sm:inline">Arahkan kamera ponsel ke plat kendaraan, sistem langsung mengenali nomor plat secara instan tanpa perlu diketik manual. Jika plat kotor atau berdebu, petugas dapat mengecek dan membetulkannya dengan satu sentuhan.</span>
                    </p>
                </div>
                {{-- Feature photo banner --}}
                <div class="relative h-28 sm:h-52 overflow-hidden bg-slate-900 border-t border-slate-100">
                    <img src="https://images.unsplash.com/photo-1590674899484-d5640e854abe?auto=format&fit=crop&w=1200&q=80" alt="Pindai Plat Nomor" class="w-full h-full object-cover opacity-80 transition-transform duration-500 ease-out group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent flex items-end p-4 sm:p-6">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <span class="badge-plat text-xs sm:text-sm">B 1234 XYZ</span>
                            <span class="text-[11px] sm:text-xs text-white/90 font-medium">Terbaca kilat, kendaraan masuk tanpa antre panjang</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 2: Karcis QR Digital & Kertas Struk --}}
            <div class="reveal-on-scroll group bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-8 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-100">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3 sm:mb-4 group-hover:bg-primary group-hover:text-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg sm:text-xl text-dark">Karcis Digital & Struk Kertas</h3>
                    <p class="mt-2 text-xs sm:text-sm text-dark/70 font-sans leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Karcis bisa langsung masuk ke HP pengendara atau dicetak pada struk kertas lewat printer saku.</span>
                        <span class="hidden sm:inline">Pilihan fleksibel: karcis langsung tersimpan di aplikasi pengendara, atau dicetak fisik via printer Bluetooth portabel untuk pengendara umum.</span>
                    </p>
                </div>
                <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100">
                    <p class="text-xs text-dark/65 font-medium">Bebas kertas hilang & kompatibel semua printer saku</p>
                </div>
            </div>

            {{-- Feature 3: Hitung Tarif Otomatis --}}
            <div class="reveal-on-scroll group bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-8 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-150">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3 sm:mb-4 group-hover:bg-primary group-hover:text-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg sm:text-xl text-dark">Hitung Tarif Otomatis & Bebas Salah</h3>
                    <p class="mt-2 text-xs sm:text-sm text-dark/70 font-sans leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Biaya per jam, tarif harian, hingga tarif inap menginap dihitung otomatis tanpa risiko salah hitung.</span>
                        <span class="hidden sm:inline">Tarif per jam, flat harian, hingga tarif menginap 24 jam langsung dihitung otomatis oleh sistem saat kendaraan keluar. Petugas tidak perlu menghitung manual dan pengendara terhindar dari salah tarif.</span>
                    </p>
                </div>
                <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100">
                    <p class="text-xs text-dark/65 font-medium">Mendukung tarif per jam, flat, dan inap 24 jam</p>
                </div>
            </div>

            {{-- Feature 4: Pesan Tempat Parkir --}}
            <div class="reveal-on-scroll group bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-8 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-200">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3 sm:mb-4 group-hover:bg-primary group-hover:text-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg sm:text-xl text-dark">Pesan Tempat Sebelum Berangkat</h3>
                    <p class="mt-2 text-xs sm:text-sm text-dark/70 font-sans leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Pengendara bisa cek sisa tempat kosong dan memesan tempat parkir terlebih dahulu dari aplikasi.</span>
                        <span class="hidden sm:inline">Pengendara dapat memantau sisa slot kosong secara langsung dan memesan tempat parkir sebelum tiba, sehingga tidak perlu cemas kehabisan tempat di jam sibuk.</span>
                    </p>
                </div>
                <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100">
                    <p class="text-xs text-dark/65 font-medium">Kapasitas kuota selalu akurat & tidak pernah penuh melebihi batas</p>
                </div>
            </div>

            {{-- Feature 5: Laporan Bersih Anti-Bocor --}}
            <div class="reveal-on-scroll group bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-8 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-250">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3 sm:mb-4 group-hover:bg-primary group-hover:text-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg sm:text-xl text-dark">Laporan Setoran Jelas & Anti-Bocor</h3>
                    <p class="mt-2 text-xs sm:text-sm text-dark/70 font-sans leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Pemilik lahan dapat memantau pemasukan uang dan jumlah kendaraan setiap hari langsung dari HP.</span>
                        <span class="hidden sm:inline">Pemilik lahan dapat memantau total pemasukan tunai maupun non-tunai, jumlah kendaraan keluar-masuk, serta rekapan harian secara real-time dari ponsel untuk mencegah kebocoran setoran.</span>
                    </p>
                </div>
                <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100">
                    <p class="text-xs text-dark/65 font-medium">Data terpisah aman per lokasi lahan & riwayat transaksi permanen</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    5. CARA / ALUR KERJA (Interaktif & Informatif dengan Ikon di Setiap Step)
═══════════════════════════════════════════ --}}
<section id="cara-kerja" class="scroll-mt-20 py-20 lg:py-28 bg-white border-b border-slate-200" x-data="{
    activeStep: 1,
    setStep(step) {
        this.activeStep = step;
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="reveal-on-scroll text-center max-w-2xl mx-auto mb-14 opacity-0 translate-y-8 transition-all duration-700 ease-out">
            <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
                Empat Langkah Mudah di Lapangan
            </h2>
            <p class="mt-3.5 text-base text-dark/70 font-sans leading-relaxed">
                Pilih atau klik setiap langkah di bawah ini untuk melihat simulasi operasionalnya secara interaktif dan informatif.
            </p>
        </div>

        {{-- 4 Interactive Step Selector Cards with Custom Icons (2x2 on Mobile, 4 Cols on Desktop) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 relative">
            
            {{-- Step 1 Card: Kendaraan Masuk --}}
            <button type="button" @click="setStep(1)" 
                :class="activeStep === 1 ? 'border-primary bg-white shadow-xl -translate-y-1 sm:-translate-y-2 ring-2 ring-primary/20' : 'border-slate-200/90 bg-secondary hover:bg-white hover:border-slate-300 hover:-translate-y-1'"
                class="reveal-on-scroll group p-4 sm:p-6 rounded-2xl border text-left transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out cursor-pointer relative">
                <div>
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div :class="activeStep === 1 ? 'bg-primary text-white scale-105 sm:scale-110 shadow-md' : 'bg-primary/10 text-primary group-hover:scale-105'"
                             class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center transition-all duration-300 shrink-0">
                            {{-- Icon: Car + Camera OCR --}}
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span :class="activeStep === 1 ? 'text-primary font-black' : 'text-dark/40 font-bold'" class="text-xl sm:text-2xl font-heading transition-colors">01</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm sm:text-lg text-dark">Kendaraan Masuk</h3>
                    <p class="text-[11px] sm:text-xs font-semibold text-primary mt-0.5">Scan Plat Otomatis</p>
                    <p class="hidden sm:block mt-2 text-xs sm:text-sm text-dark/70 leading-relaxed font-sans">
                        Petugas memindai plat nomor secara instan. Cek daftar kendaraan dan konfirmasi pemesanan tempat parkir.
                    </p>
                </div>
                <div class="hidden sm:flex mt-4 pt-3 border-t border-slate-100 items-center justify-between text-xs font-semibold" :class="activeStep === 1 ? 'text-primary' : 'text-dark/50'">
                    <span>Simulasi Masuk</span>
                    <span :class="activeStep === 1 ? 'translate-x-1' : ''" class="transition-transform duration-200">&rarr;</span>
                </div>
            </button>

            {{-- Step 2 Card: Penerbitan Karcis --}}
            <button type="button" @click="setStep(2)"
                :class="activeStep === 2 ? 'border-accent bg-white shadow-xl -translate-y-1 sm:-translate-y-2 ring-2 ring-accent/30' : 'border-slate-200/90 bg-secondary hover:bg-white hover:border-slate-300 hover:-translate-y-1'"
                class="reveal-on-scroll group p-4 sm:p-6 rounded-2xl border text-left transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-100 cursor-pointer relative">
                <div>
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div :class="activeStep === 2 ? 'bg-accent text-dark scale-105 sm:scale-110 shadow-md font-black' : 'bg-accent/20 text-dark group-hover:scale-105'"
                             class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center transition-all duration-300 shrink-0">
                            {{-- Icon: QR Digital Ticket & Thermal Printer --}}
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <span :class="activeStep === 2 ? 'text-accent-dark font-black' : 'text-dark/40 font-bold'" class="text-xl sm:text-2xl font-heading transition-colors">02</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm sm:text-lg text-dark">Penerbitan Karcis</h3>
                    <p class="text-[11px] sm:text-xs font-semibold text-accent-dark mt-0.5">QR & Kertas Struk</p>
                    <p class="hidden sm:block mt-2 text-xs sm:text-sm text-dark/70 leading-relaxed font-sans">
                        Karcis digital terbit di aplikasi atau dicetak via printer saku. Sisa kuota berkurang otomatis.
                    </p>
                </div>
                <div class="hidden sm:flex mt-4 pt-3 border-t border-slate-100 items-center justify-between text-xs font-semibold" :class="activeStep === 2 ? 'text-dark font-bold' : 'text-dark/50'">
                    <span>Simulasi Karcis</span>
                    <span :class="activeStep === 2 ? 'translate-x-1' : ''" class="transition-transform duration-200">&rarr;</span>
                </div>
            </button>

            {{-- Step 3 Card: Scan Karcis Keluar --}}
            <button type="button" @click="setStep(3)"
                :class="activeStep === 3 ? 'border-primary bg-white shadow-xl -translate-y-1 sm:-translate-y-2 ring-2 ring-primary/20' : 'border-slate-200/90 bg-secondary hover:bg-white hover:border-slate-300 hover:-translate-y-1'"
                class="reveal-on-scroll group p-4 sm:p-6 rounded-2xl border text-left transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-200 cursor-pointer relative">
                <div>
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div :class="activeStep === 3 ? 'bg-primary text-white scale-105 sm:scale-110 shadow-md' : 'bg-primary/10 text-primary group-hover:scale-105'"
                             class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center transition-all duration-300 shrink-0">
                            {{-- Icon: Exit Scanner & Calculator --}}
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span :class="activeStep === 3 ? 'text-primary font-black' : 'text-dark/40 font-bold'" class="text-xl sm:text-2xl font-heading transition-colors">03</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm sm:text-lg text-dark">Scan Keluar</h3>
                    <p class="text-[11px] sm:text-xs font-semibold text-primary mt-0.5">Hitung Tarif Akurat</p>
                    <p class="hidden sm:block mt-2 text-xs sm:text-sm text-dark/70 leading-relaxed font-sans">
                        Scan karcis keluar kurang dari 1 detik. Sistem menghitung durasi dan biaya secara akurat tanpa salah hitung.
                    </p>
                </div>
                <div class="hidden sm:flex mt-4 pt-3 border-t border-slate-100 items-center justify-between text-xs font-semibold" :class="activeStep === 3 ? 'text-primary' : 'text-dark/50'">
                    <span>Simulasi Tarif</span>
                    <span :class="activeStep === 3 ? 'translate-x-1' : ''" class="transition-transform duration-200">&rarr;</span>
                </div>
            </button>

            {{-- Step 4 Card: Konfirmasi & Selesai --}}
            <button type="button" @click="setStep(4)"
                :class="activeStep === 4 ? 'border-accent bg-white shadow-xl -translate-y-1 sm:-translate-y-2 ring-2 ring-accent/30' : 'border-slate-200/90 bg-secondary hover:bg-white hover:border-slate-300 hover:-translate-y-1'"
                class="reveal-on-scroll group p-4 sm:p-6 rounded-2xl border text-left transition-all duration-300 flex flex-col justify-between opacity-0 translate-y-8 ease-out delay-300 cursor-pointer relative">
                <div>
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div :class="activeStep === 4 ? 'bg-accent text-dark scale-105 sm:scale-110 shadow-md font-black' : 'bg-accent/20 text-dark group-hover:scale-105'"
                             class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center transition-all duration-300 shrink-0">
                            {{-- Icon: Verified Payment Checkmark --}}
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span :class="activeStep === 4 ? 'text-accent-dark font-black' : 'text-dark/40 font-bold'" class="text-xl sm:text-2xl font-heading transition-colors">04</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm sm:text-lg text-dark">Pembayaran</h3>
                    <p class="text-[11px] sm:text-xs font-semibold text-accent-dark mt-0.5">Gerbang Terbuka</p>
                    <p class="hidden sm:block mt-2 text-xs sm:text-sm text-dark/70 leading-relaxed font-sans">
                        Bayar tunai atau non-tunai. Palang terbuka, kuota tempat parkir kembali kosong, dan setoran tercatat rapi.
                    </p>
                </div>
                <div class="hidden sm:flex mt-4 pt-3 border-t border-slate-100 items-center justify-between text-xs font-semibold" :class="activeStep === 4 ? 'text-dark font-bold' : 'text-dark/50'">
                    <span>Simulasi Selesai</span>
                    <span :class="activeStep === 4 ? 'translate-x-1' : ''" class="transition-transform duration-200">&rarr;</span>
                </div>
            </button>

        </div>

        {{-- ═══════════════════════════════════════════
            INTERACTIVE OPERATIONAL FLOW STAGE
            Penggambaran alur interaktif & informatif (Compact di Mobile)
        ═══════════════════════════════════════════ --}}
        <div class="reveal-on-scroll mt-6 sm:mt-10 bg-secondary rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-10 shadow-md relative overflow-hidden opacity-0 translate-y-8 transition-all duration-700 ease-out">
            
            {{-- Stepper Progress & Controller --}}
            <div class="flex items-center justify-between pb-4 sm:pb-6 border-b border-slate-200 gap-3">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-dark text-white font-mono text-xs font-bold flex items-center justify-center shrink-0">
                        <span x-text="'0' + activeStep"></span>
                    </span>
                    <div>
                        <p class="font-heading font-bold text-sm sm:text-base text-dark">
                            <span x-show="activeStep === 1">Tahap 1: Deteksi Kamera Pos Gerbang Masuk</span>
                            <span x-show="activeStep === 2">Tahap 2: Karcis QR Digital & Cetak Struk</span>
                            <span x-show="activeStep === 3">Tahap 3: Scan Karcis Keluar & Hitung Biaya</span>
                            <span x-show="activeStep === 4">Tahap 4: Pembayaran Lunas & Palang Terbuka</span>
                        </p>
                        <p class="hidden sm:block text-xs text-dark/60 font-sans">Alur transaksi real-time terintegrasi aman di cloud Parkirin.</p>
                    </div>
                </div>

                {{-- Previous / Next Stepper Buttons --}}
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <button type="button" @click="activeStep = activeStep > 1 ? activeStep - 1 : 4" 
                        class="p-1.5 sm:p-2 rounded-xl bg-white border border-slate-200 text-dark/70 hover:text-dark hover:border-dark hover:bg-slate-50 transition-colors shadow-sm" aria-label="Langkah Sebelumnya">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-xs font-mono font-bold text-dark/60 px-1 sm:px-2">
                        <span x-text="activeStep"></span> / 4
                    </span>
                    <button type="button" @click="activeStep = activeStep < 4 ? activeStep + 1 : 1" 
                        class="p-1.5 sm:p-2 rounded-xl bg-primary text-white hover:bg-primary-dark transition-colors shadow-sm" aria-label="Langkah Berikutnya">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ── Stage 1: Kendaraan Masuk ── --}}
            <div x-show="activeStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 sm:mt-6 grid lg:grid-cols-12 gap-5 sm:gap-8 items-center">
                <div class="lg:col-span-6 bg-slate-900 rounded-xl sm:rounded-2xl p-3.5 sm:p-5 text-white relative overflow-hidden border border-slate-800">
                    <div class="flex items-center justify-between text-[11px] sm:text-xs font-mono pb-2 sm:pb-3 border-b border-white/10 text-white/70">
                        <span>POS-MASUK-01 // KAMERA POS</span>
                        <span class="text-emerald-400 font-bold">ONLINE &bull; SIAP</span>
                    </div>
                    <div class="my-3 sm:my-5 p-3 sm:p-4 rounded-xl border border-dashed border-accent/70 bg-white/5 text-center">
                        <p class="text-[10px] text-accent font-mono uppercase tracking-wider mb-1">Hasil Pembacaan Otomatis</p>
                        <span class="badge-plat text-base sm:text-lg">B 1234 XYZ</span>
                        <p class="text-[11px] sm:text-xs text-white/80 mt-1 font-sans">Toyota Avanza (Hitam) &bull; 07:45 WIB</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[10px] sm:text-[11px] font-sans text-white/75 bg-white/5 p-2 sm:p-3 rounded-lg border border-white/10">
                        <div>Akurasi Pembacaan: <strong class="text-emerald-400 font-mono">99.8%</strong></div>
                        <div>Waktu Eksekusi: <strong class="text-accent font-mono">0.2 dtk</strong></div>
                    </div>
                </div>
                <div class="lg:col-span-6 space-y-2.5 sm:space-y-3.5 text-xs sm:text-sm text-dark/75">
                    <h4 class="font-heading font-black text-base sm:text-xl text-dark">Alur Masuk Cepat & Verifikasi Otomatis</h4>
                    <p class="leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Kamera pos langsung membaca plat nomor dalam hitungan detik. Cek pemesanan tempat parkir dan kuota aman terdata.</span>
                        <span class="hidden sm:inline">Kendaraan yang tiba di pos gerbang langsung dipindai tanpa perlu pengemudi membuka jendela terlalu lama. Sistem memverifikasi data secara lokal di perangkat petugas tanpa jeda jaringan internet.</span>
                    </p>
                    {{-- Mobile Compact Checklist --}}
                    <div class="sm:hidden flex flex-wrap gap-1.5 pt-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Validasi Plat Kilat</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Cek Booking Online</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Kuota Terjaga</span>
                    </div>
                    {{-- Desktop Rich List --}}
                    <ul class="hidden sm:block space-y-2 pt-2 border-t border-slate-200/80 font-medium text-xs sm:text-sm">
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Pencegahan Banned:</strong> Validasi otomatis daftar plat kendaraan bermasalah.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Pencocokan Booking:</strong> Jika pengguna sudah pesan lebih awal, slot langsung terkonfirmasi.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Kapasitas Terjaga:</strong> Kuota berkurang akurat tanpa risiko lahan melebihi batas.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ── Stage 2: Penerbitan Karcis ── --}}
            <div x-show="activeStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 sm:mt-6 grid lg:grid-cols-12 gap-5 sm:gap-8 items-center" style="display: none;">
                <div class="lg:col-span-6 bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between pb-2 sm:pb-3 border-b border-slate-100 text-[11px] sm:text-xs text-dark/60">
                        <span class="font-bold text-dark font-mono">KARCIS RESMI PARKIRIN</span>
                        <span class="text-primary font-bold">DIGITAL PASS #TRX-88291</span>
                    </div>
                    <div class="my-3 sm:my-4 flex items-center gap-3 sm:gap-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-secondary p-1.5 rounded-xl border border-slate-200 flex items-center justify-center shrink-0">
                            <svg class="w-full h-full text-dark" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v2h-4v-2zm-4 0h2v4h-2v-4zm2 4h4v4h-4v-4zm-4 2h2v2h-2v-2zm6-2h2v2h-2v-2z"/>
                            </svg>
                        </div>
                        <div class="text-xs space-y-1">
                            <p class="font-bold text-dark">Plat: <span class="font-mono">B 1234 XYZ</span></p>
                            <p class="text-dark/60">Jam Masuk: <span class="font-semibold text-dark">07:45 WIB</span></p>
                            <p class="text-dark/60">Tarif: <span class="font-semibold text-primary">Rp 4.000 / Jam</span></p>
                            <p class="text-[11px] text-emerald-600 font-semibold">Tersimpan di Aplikasi Pengendara</p>
                        </div>
                    </div>
                    <div class="pt-2 sm:pt-3 border-t border-slate-100 text-[10px] sm:text-[11px] text-dark/60 flex justify-between">
                        <span>Format: QR Digital & Kertas Struk</span>
                        <span class="font-bold text-dark">Status: Palang Terbuka</span>
                    </div>
                </div>
                <div class="lg:col-span-6 space-y-2.5 sm:space-y-3.5 text-xs sm:text-sm text-dark/75">
                    <h4 class="font-heading font-black text-base sm:text-xl text-dark">Karcis QR Praktis Tanpa Takut Hilang</h4>
                    <p class="leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Karcis digital langsung masuk ke ponsel pengendara atau dicetak pada struk kertas lewat printer saku.</span>
                        <span class="hidden sm:inline">Sistem mendukung fleksibilitas ganda: karcis langsung terkirim secara digital ke akun pengendara, atau dicetak fisik via printer Bluetooth portabel bagi pengendara umum.</span>
                    </p>
                    {{-- Mobile Compact Checklist --}}
                    <div class="sm:hidden flex flex-wrap gap-1.5 pt-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Tanpa Kertas Hilang</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Printer Saku Bluetooth</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Karcis di Ponsel</span>
                    </div>
                    {{-- Desktop Rich List --}}
                    <ul class="hidden sm:block space-y-2 pt-2 border-t border-slate-200/80 font-medium text-xs sm:text-sm">
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Bebas Kertas Kusut:</strong> Pengendara cukup scan QR di layar ponsel saat keluar gerbang.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Printer Saku Standar:</strong> Mendukung semua jenis printer kasir mobile Bluetooth (58mm & 80mm).</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Pemberitahuan Otomatis:</strong> Konfirmasi masuk diterima pengendara secara instan.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ── Stage 3: Scan Karcis Keluar ── --}}
            <div x-show="activeStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 sm:mt-6 grid lg:grid-cols-12 gap-5 sm:gap-8 items-center" style="display: none;">
                <div class="lg:col-span-6 bg-slate-900 rounded-xl sm:rounded-2xl p-3.5 sm:p-5 text-white relative overflow-hidden border border-slate-800">
                    <div class="flex items-center justify-between text-[11px] sm:text-xs font-mono pb-2 sm:pb-3 border-b border-white/10 text-white/70">
                        <span>POS-KELUAR-01 // HITUNG TARIF</span>
                        <span class="text-accent font-bold">HITUNG OTOMATIS</span>
                    </div>
                    <div class="my-3 sm:my-4 space-y-1.5 sm:space-y-2 text-xs font-sans">
                        <div class="flex justify-between py-1 border-b border-white/10">
                            <span class="text-white/60">Durasi Parkir</span>
                            <span class="font-bold text-white">2 Jam 15 Menit (3 Jam)</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/10">
                            <span class="text-white/60">Skema Tarif</span>
                            <span class="font-bold text-primary-light">Per Jam + Progresif</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/10">
                            <span class="text-white/60">Denda / Inap</span>
                            <span class="text-emerald-400 font-bold">Rp 0 (Normal)</span>
                        </div>
                        <div class="flex justify-between py-1.5 pt-2 text-sm font-bold">
                            <span class="text-accent">TOTAL BIAYA</span>
                            <span class="font-heading font-black text-base sm:text-lg text-white">Rp 8.000</span>
                        </div>
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-sans text-white/60 pt-2 border-t border-white/10">
                        Dihitung otomatis oleh sistem &bull; Terhindar dari salah hitung
                    </div>
                </div>
                <div class="lg:col-span-6 space-y-2.5 sm:space-y-3.5 text-xs sm:text-sm text-dark/75">
                    <h4 class="font-heading font-black text-base sm:text-xl text-dark">Hitung Tarif Akurat & Tanpa Sengketa</h4>
                    <p class="leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Scan karcis keluar kurang dari 1 detik. Biaya durasi dan tarif inap dihitung otomatis tanpa takut selisih.</span>
                        <span class="hidden sm:inline">Saat keluar, karcis dipindai dalam waktu kurang dari 1 detik. Seluruh perhitungan biaya berjalan langsung di server pusat untuk menjamin nominal rupiah transparan tanpa selisih.</span>
                    </p>
                    {{-- Mobile Compact Checklist --}}
                    <div class="sm:hidden flex flex-wrap gap-1.5 pt-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Hitung Otomatis</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Cek Plat Jika Tiket Hilang</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Tarif Transparan</span>
                    </div>
                    {{-- Desktop Rich List --}}
                    <ul class="hidden sm:block space-y-2 pt-2 border-t border-slate-200/80 font-medium text-xs sm:text-sm">
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Pencarian Plat Nomor:</strong> Jika karcis fisik tercecer, petugas tetap bisa mengecek lewat nomor plat.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Tarif Inap 24 Jam:</strong> Akumulasi hari menginap dihitung otomatis tanpa kalkulasi manual.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Transparansi Rincian:</strong> Pengendara dapat melihat rincian jam dan biaya di layar pos.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ── Stage 4: Konfirmasi & Selesai ── --}}
            <div x-show="activeStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 sm:mt-6 grid lg:grid-cols-12 gap-5 sm:gap-8 items-center" style="display: none;">
                <div class="lg:col-span-6 bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between pb-2 sm:pb-3 border-b border-slate-100 text-[11px] sm:text-xs">
                        <span class="font-bold text-dark font-mono">STATUS TRANSAKSI</span>
                        <span class="text-emerald-600 font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>LUNAS & SELESAI</span>
                        </span>
                    </div>
                    <div class="my-3 sm:my-4 p-3 sm:p-4 rounded-xl bg-secondary border border-slate-200/80 text-xs space-y-1.5 sm:space-y-2">
                        <div class="flex justify-between">
                            <span class="text-dark/60">Metode Pembayaran</span>
                            <span class="font-bold text-dark">QRIS / Tunai</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dark/60">Status Kuota Lahan</span>
                            <span class="font-bold text-emerald-600">+1 Slot Kosong Kembali</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dark/60">Rekapan Setoran</span>
                            <span class="font-bold text-primary">Tercatat di Laporan Harian</span>
                        </div>
                    </div>
                    <div class="pt-2 sm:pt-3 border-t border-slate-100 text-[10px] sm:text-[11px] text-dark/60 flex justify-between">
                        <span>Palang Terbuka Otomatis</span>
                        <span class="font-bold text-dark">Struk Tersimpan di HP</span>
                    </div>
                </div>
                <div class="lg:col-span-6 space-y-2.5 sm:space-y-3.5 text-xs sm:text-sm text-dark/75">
                    <h4 class="font-heading font-black text-base sm:text-xl text-dark">Penyelesaian Cepat & Setoran Aman</h4>
                    <p class="leading-relaxed">
                        <span class="sm:hidden font-medium text-dark/80">Begitu pembayaran selesai, palang terbuka, kuota tempat parkir kembali kosong, dan setoran tercatat rapi.</span>
                        <span class="hidden sm:inline">Begitu pembayaran disahkan, kuota tempat parkir langsung bertambah kembali. Transaksi tersimpan rapi di riwayat permanen sehingga pemilik lahan terhindar dari kebocoran setoran harian.</span>
                    </p>
                    {{-- Mobile Compact Checklist --}}
                    <div class="sm:hidden flex flex-wrap gap-1.5 pt-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Kuota Real-time</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Setoran Anti-Bocor</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-dark/80"><span class="text-emerald-500 font-bold">&check;</span> Struk Digital di HP</span>
                    </div>
                    {{-- Desktop Rich List --}}
                    <ul class="hidden sm:block space-y-2 pt-2 border-t border-slate-200/80 font-medium text-xs sm:text-sm">
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Pelepasan Kuota Real-Time:</strong> Pengendara berikutnya di jalan bisa langsung melihat slot kosong.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Rekapan Setoran Bersih:</strong> Tidak ada selisih uang masuk pos vs total riwayat kendaraan keluar.</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-dark">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span><strong>Bukti Digital Pengendara:</strong> Struk tersimpan rapi di ponsel untuk kebutuhan klaim operasional.</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════
    6. TESTIMONI (Dual Marquee dengan Foto Asli)
═══════════════════════════════════════════ --}}
<section id="testimoni" class="scroll-mt-20 py-20 lg:py-28 bg-white overflow-hidden border-b border-slate-200">
    <div class="reveal-on-scroll max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-14 text-center opacity-0 translate-y-8 transition-all duration-700 ease-out">
        <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-dark tracking-tight">
            Dipercaya Pengendara & Operator Lahan
        </h2>
        <p class="mt-3.5 text-base text-dark/70 font-sans max-w-xl mx-auto">
            Dengar langsung pengalaman mereka yang telah merasakan kemudahan sistem Parkirin setiap hari.
        </p>
    </div>

    @php
    $testimonials = [
        [
            'name' => 'Budi Hartono',
            'role' => 'Owner Parkir Area Stasiun',
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&h=200&q=80',
            'text' => 'Parkirin membantu saya memantau pos jaga secara transparan. Fitur audit discrepancy sangat ampuh mencegah selisih uang masuk di akhir hari.'
        ],
        [
            'name' => 'Rina Marlina',
            'role' => 'Pengendara Komuter Harian',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&h=200&q=80',
            'text' => 'Booking slot dari rumah membuat saya tenang saat mengejar jadwal kereta pagi. Tidak pernah lagi kehabisan tempat parkir di stasiun.'
        ],
        [
            'name' => 'Andi Wijaya',
            'role' => 'Pengelola Parkir Ruko',
            'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&h=200&q=80',
            'text' => 'Penerapan tarif progresif otomatis memudahkan staf lapangan. Tidak ada lagi keluhan pelanggan mengenai salah hitung jam parkir.'
        ],
        [
            'name' => 'Siti Aminah',
            'role' => 'Admin Operasional Tenant',
            'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&h=200&q=80',
            'text' => 'Pengaturan kuota kapasitas sangat simpel. Saya bisa mengaktifkan atau menonaktifkan fitur booking dalam hitungan detik sesuai kepadatan hari itu.'
        ],
        [
            'name' => 'Dimas Prasetya',
            'role' => 'Pengendara Sepeda Motor',
            'avatar' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=200&h=200&q=80',
            'text' => 'Karcis digital QR Code membuat saya tidak perlu repot menyimpan kertas struk kecil yang rawan hilang di saku jaket.'
        ],
        [
            'name' => 'Joko Susilo',
            'role' => 'Petugas Pos Gerbang',
            'avatar' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=200&h=200&q=80',
            'text' => 'Scan OCR kamera sangat cepat. Antrean motor yang biasanya mengular di pagi hari sekarang langsung terurai dengan cepat.'
        ],
    ];
    @endphp

    {{-- Row 1: Marquee Forward with Hover Pause and Card Elevate --}}
    <div class="relative mb-6">
        <div class="absolute inset-y-0 left-0 w-20 sm:w-32 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute inset-y-0 right-0 w-20 sm:w-32 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
        <div class="flex animate-marquee w-max gap-5">
            @for($i = 0; $i < 2; $i++)
                @foreach($testimonials as $t)
                <div class="w-[320px] sm:w-[360px] shrink-0 p-6 bg-secondary rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                    <p class="text-sm text-dark/80 leading-relaxed font-sans italic">"{{ $t['text'] }}"</p>
                    <div class="mt-5 flex items-center gap-3 pt-4 border-t border-slate-200/60">
                        <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}" class="w-11 h-11 rounded-full object-cover border border-slate-200 shrink-0">
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

    {{-- Row 2: Marquee Reverse with Hover Pause and Card Elevate --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 w-20 sm:w-32 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute inset-y-0 right-0 w-20 sm:w-32 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
        <div class="flex animate-marquee-reverse w-max gap-5">
            @for($i = 0; $i < 2; $i++)
                @foreach(array_reverse($testimonials) as $t)
                <div class="w-[320px] sm:w-[360px] shrink-0 p-6 bg-secondary rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                    <p class="text-sm text-dark/80 leading-relaxed font-sans italic">"{{ $t['text'] }}"</p>
                    <div class="mt-5 flex items-center gap-3 pt-4 border-t border-slate-200/60">
                        <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}" class="w-11 h-11 rounded-full object-cover border border-slate-200 shrink-0">
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
</section>

{{-- ═══════════════════════════════════════════
    7. CTA & KONTAK (Aksi Cepat & Ramah)
═══════════════════════════════════════════ --}}
<section id="kontak" class="scroll-mt-20 py-20 lg:py-28 bg-primary text-white relative overflow-hidden">
    <div class="reveal-on-scroll max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center opacity-0 translate-y-8 transition-all duration-700 ease-out">
        <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black leading-tight tracking-tight">
            Siap Memodernisasi Lahan Parkir Anda?
        </h2>
        <p class="mt-4 text-base sm:text-lg text-white/85 font-sans font-light leading-relaxed max-w-xl mx-auto">
            Bergabunglah dengan ekosistem parkir terintegrasi. Akses gratis untuk pengendara dan kemudahan integrasi cepat bagi operator parkir swadaya.
        </p>

        {{-- Direct CTA Buttons with Hover Scale & Glow --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
            <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 font-bold text-dark bg-accent rounded-xl hover:bg-accent-dark hover:scale-[1.03] hover:shadow-2xl hover:shadow-accent/40 transition-all duration-200 active:scale-95 text-sm">
                <span>Daftar Pengendara Gratis</span>
                <span class="ml-1.5 transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Parkirin,%20saya%20tertarik%20mendaftarkan%20lahan%20parkir%20mitra" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 font-semibold text-white border-2 border-white/40 rounded-xl hover:bg-white/15 hover:border-white hover:scale-[1.03] transition-all duration-200 active:scale-95 text-sm">
                Daftarkan Lahan Mitra
            </a>
        </div>

        {{-- Contact Details with Hover Transitions --}}
        <div class="mt-12 pt-8 border-t border-white/15 flex flex-wrap justify-center gap-6 sm:gap-10 text-white/80 text-sm">
            <a href="mailto:info@parkirin.id" class="group flex items-center gap-2 hover:text-accent transition-colors duration-200">
                <svg class="w-4 h-4 text-accent transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="group-hover:underline">info@parkirin.id</span>
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Parkirin" target="_blank" class="group flex items-center gap-2 hover:text-accent transition-colors duration-200">
                <svg class="w-4 h-4 text-accent transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span class="group-hover:underline">+62 812-3456-7890</span>
            </a>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Jakarta, Indonesia</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    8. FOOTER (Charcoal Dark + Official White Logo + Hover Links)
═══════════════════════════════════════════ --}}
<footer class="bg-dark text-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            
            {{-- Brand Column --}}
            <div>
                <a href="{{ route('landing') }}" class="inline-flex items-center mb-5 group transition-transform duration-200 hover:scale-[1.02]" aria-label="Beranda Parkirin">
                    <img src="{{ asset('assets/logo/white/Teks.svg') }}" alt="Parkirin" class="h-8 sm:h-9 w-auto object-contain">
                </a>
                <p class="text-sm text-white/60 leading-relaxed font-sans">
                    Sistem parkir cerdas portabel untuk operator parkir swadaya di seluruh Indonesia. Aman, fleksibel, tertib, dan transparan.
                </p>
            </div>

            {{-- Navigasi Halaman with Hover Offset --}}
            <div>
                <h4 class="font-heading font-bold text-white text-sm mb-4 uppercase tracking-wider">Navigasi Halaman</h4>
                <ul class="space-y-2.5 text-sm text-white/60 font-sans">
                    <li><a href="#about" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Tentang Kami</a></li>
                    <li><a href="#fitur" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Fitur Utama</a></li>
                    <li><a href="#cara-kerja" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Alur Operasional</a></li>
                    <li><a href="#testimoni" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Testimoni Pengguna</a></li>
                    <li><a href="#kontak" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Hubungi Kami</a></li>
                </ul>
            </div>

            {{-- Portal Akses with Hover Offset --}}
            <div>
                <h4 class="font-heading font-bold text-white text-sm mb-4 uppercase tracking-wider">Portal Akses</h4>
                <ul class="space-y-2.5 text-sm text-white/60 font-sans">
                    <li><a href="{{ route('login') }}?tab=pengendara" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Masuk Akun Pengendara</a></li>
                    <li><a href="{{ route('login') }}?tab=user" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Portal Staf / Petugas</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Pendaftaran Pengendara</a></li>
                    <li><a href="https://wa.me/6281234567890?text=Halo%20Admin%20Parkirin,%20saya%20ingin%20konsultasi%20mitra%20lahan" target="_blank" class="hover:text-accent hover:translate-x-1 transition-all duration-200 inline-block">Daftar Mitra Lahan</a></li>
                </ul>
            </div>

            {{-- Kontak & Bantuan --}}
            <div>
                <h4 class="font-heading font-bold text-white text-sm mb-4 uppercase tracking-wider">Kontak & Bantuan</h4>
                <ul class="space-y-2.5 text-sm text-white/60 font-sans">
                    <li><a href="mailto:info@parkirin.id" class="hover:text-accent transition-colors">info@parkirin.id</a></li>
                    <li><a href="https://wa.me/6281234567890" target="_blank" class="hover:text-accent transition-colors">+62 812-3456-7890</a></li>
                    <li>Jakarta, Indonesia</li>
                    <li class="text-xs text-white/40 pt-1">Layanan Operasional 24/7</li>
                </ul>
            </div>

        </div>

        <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/40 font-sans">
            <p>&copy; {{ date('Y') }} Parkirin. Hak Cipta Dilindungi Undang-Undang.</p>
            <p>Dirancang untuk kemajuan operator parkir swadaya Indonesia.</p>
        </div>
    </div>
</footer>

</div>

@push('scripts')
<script>
    // 1. Relational Toggle Tab Navigation Logic (Sliding Active Pill)
    let currentActiveTab = 'about';

    function handleNavTabClick(tabId, el) {
        currentActiveTab = tabId;
        moveNavIndicator(el);
    }

    function moveNavIndicator(targetEl) {
        const indicator = document.getElementById('nav-active-pill');
        const container = document.getElementById('nav-toggle-container');
        if (!indicator || !container || !targetEl) return;

        const containerRect = container.getBoundingClientRect();
        const targetRect = targetEl.getBoundingClientRect();

        const offsetLeft = targetRect.left - containerRect.left;
        indicator.style.left = offsetLeft + 'px';
        indicator.style.width = targetRect.width + 'px';
        indicator.style.opacity = '1';

        // Update active text colors
        document.querySelectorAll('.nav-tab-btn').forEach(btn => {
            btn.classList.remove('text-white');
            btn.classList.add('text-dark/75');
        });
        targetEl.classList.remove('text-dark/75');
        targetEl.classList.add('text-white');
    }

    // Mobile Drawer Navigation Toggle
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');

        if (!menu || !iconOpen || !iconClose) return;

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

    // Dynamic 3D Container & Extreme Z-Axis Rotation for Floating Chips
    document.addEventListener('DOMContentLoaded', function() {
        // Init Nav Indicator on first item or matching section
        const firstTab = document.querySelector('.nav-tab-btn[data-tab="about"]');
        if (firstTab) {
            setTimeout(() => {
                moveNavIndicator(firstTab);
            }, 100);
        }

        // Scroll Spy for Navbar Toggle Tab
        const sections = ['about', 'fitur', 'cara-kerja', 'testimoni', 'kontak'];
        window.addEventListener('scroll', function() {
            const scrollPos = window.scrollY + 120;
            for (let i = sections.length - 1; i >= 0; i--) {
                const sectionEl = document.getElementById(sections[i]);
                if (sectionEl && sectionEl.offsetTop <= scrollPos) {
                    if (currentActiveTab !== sections[i]) {
                        currentActiveTab = sections[i];
                        const activeBtn = document.querySelector(`.nav-tab-btn[data-tab="${sections[i]}"]`);
                        if (activeBtn) moveNavIndicator(activeBtn);
                    }
                    break;
                }
            }
        }, { passive: true });

        // Initial Page Load Reveal for Hero Elements
        const heroReveals = document.querySelectorAll('.reveal-hero');
        setTimeout(() => {
            heroReveals.forEach(el => {
                el.classList.remove('opacity-0', 'translate-y-4', 'translate-y-6');
                el.classList.add('opacity-100', 'translate-y-0');
            });
        }, 80);

        // IntersectionObserver for Smooth Scroll Reveal across all sections
        const revealElements = document.querySelectorAll('.reveal-on-scroll');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-8');
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -30px 0px'
            });

            revealElements.forEach(el => observer.observe(el));
        } else {
            revealElements.forEach(el => {
                el.classList.remove('opacity-0', 'translate-y-8');
                el.classList.add('opacity-100', 'translate-y-0');
            });
        }

        // 3D Container Scroll and Floating Chips Z-Rotation
        const scrollArea = document.getElementById('container-scroll-area');
        const scrollCard = document.getElementById('container-scroll-card');
        const chip1 = document.getElementById('hero-chip-1');
        const chip2 = document.getElementById('hero-chip-2');
        const chip3 = document.getElementById('hero-chip-3');
        const chipM1 = document.getElementById('hero-chip-m1');
        const chipM2 = document.getElementById('hero-chip-m2');

        if (!scrollArea || !scrollCard) return;

        let ticking = false;

        function update3DTransform() {
            const rect = scrollArea.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            const total = rect.height;
            const current = windowHeight - rect.top;
            const progress = Math.min(Math.max(current / (windowHeight + total * 0.45), 0), 1);

            const isMobile = window.innerWidth < 768;

            // Central Card 3D Perspective Tilt (rotateX)
            const maxAngleX = isMobile ? 12 : 22;
            const minScale = isMobile ? 0.95 : 0.92;
            const rotateX = (maxAngleX * (1 - progress)).toFixed(2);
            const scale = (minScale + (1 - minScale) * progress).toFixed(3);
            scrollCard.style.transform = `rotateX(${rotateX}deg) scale(${scale})`;

            // Extreme Z-Axis Rotation for Floating Chips (Never clipped!)
            if (!isMobile) {
                // Chip 1 (Top Left): Swing dari -26deg ke +6deg
                if (chip1) {
                    const rotZ1 = (-26 + progress * 32).toFixed(1);
                    const transY1 = (progress * -30).toFixed(1);
                    chip1.style.transform = `rotateZ(${rotZ1}deg) translateY(${transY1}px)`;
                }

                // Chip 2 (Top Right): Swing dari +26deg ke -6deg
                if (chip2) {
                    const rotZ2 = (26 - progress * 32).toFixed(1);
                    const transY2 = (progress * 30).toFixed(1);
                    chip2.style.transform = `rotateZ(${rotZ2}deg) translateY(${transY2}px)`;
                }

                // Chip 3 (Bottom Left): Swing dari -18deg ke +8deg
                if (chip3) {
                    const rotZ3 = (-18 + progress * 26).toFixed(1);
                    const transY3 = (progress * -15).toFixed(1);
                    chip3.style.transform = `rotateZ(${rotZ3}deg) translateY(${transY3}px)`;
                }
            } else {
                // Mobile Chips: Rotasi sumbu Z menuju 0deg
                if (chipM1) {
                    const rotZM1 = (-14 * (1 - progress)).toFixed(1);
                    chipM1.style.transform = `rotateZ(${rotZM1}deg)`;
                }
                if (chipM2) {
                    const rotZM2 = (14 * (1 - progress)).toFixed(1);
                    chipM2.style.transform = `rotateZ(${rotZM2}deg)`;
                }
            }

            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(update3DTransform);
                ticking = true;
            }
        }, { passive: true });

        window.addEventListener('resize', function() {
            if (!ticking) {
                window.requestAnimationFrame(update3DTransform);
                ticking = true;
            }
            const activeBtn = document.querySelector(`.nav-tab-btn[data-tab="${currentActiveTab}"]`);
            if (activeBtn) moveNavIndicator(activeBtn);
        });

        // Trigger on load
        update3DTransform();
    });
</script>
@endpush
@endsection
