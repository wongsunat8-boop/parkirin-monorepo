@extends('layouts.app')

@section('title', 'Parkirin — Sistem Parkir Portabel Berbasis Web untuk Lahan Swadaya Off-Street')

@section('content')

{{-- ═══════════════════════════════════════════
    1. HEADER (White Chrome — Broadcom Enterprise Architecture)
═══════════════════════════════════════════ --}}
<header class="sticky top-0 inset-x-0 z-50 bg-white border-b border-[#E5E7EB] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            {{-- Official Parkirin Logo (Primary / Blue Variant on White Chrome) --}}
            <a href="{{ route('landing') }}" class="flex items-center shrink-0 py-1 transition-transform duration-200 hover:scale-[1.01]" aria-label="Beranda Parkirin">
                <img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" class="h-8 sm:h-9 w-auto object-contain">
            </a>

            {{-- Desktop Clean Text Navigation (Flat Editorial Links) --}}
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-[#494A5E]">
                <a href="#about" class="hover:text-[#0D63A5] transition-colors py-2">Tentang</a>
                <a href="#fitur" class="hover:text-[#0D63A5] transition-colors py-2">Fitur</a>
                <a href="#cara-kerja" class="hover:text-[#0D63A5] transition-colors py-2">Cara Kerja</a>
                <a href="#keunggulan" class="hover:text-[#0D63A5] transition-colors py-2">Keunggulan</a>
                <a href="#testimoni" class="hover:text-[#0D63A5] transition-colors py-2">Testimoni</a>
            </nav>

            {{-- Desktop Auth Actions (Sharp Rectangular Buttons: rounded-none, min-h-[47px]) --}}
            <div class="hidden sm:flex items-center gap-3">
                @if(auth('pengendara')->check())
                    <a href="{{ route('pengendara.dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 min-h-[47px] text-sm font-bold text-[#212121] bg-[#FFD717] hover:bg-[#E6C200] rounded-none transition-all duration-200 shadow-none active:scale-[0.98]">
                        <svg class="w-4 h-4 text-[#212121]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Dashboard Pengendara</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 min-h-[47px] text-sm font-semibold text-[#DC2626] hover:bg-[#FEE2E2] rounded-none transition-colors duration-200">
                            Keluar
                        </button>
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
                    <a href="{{ $dashRoute }}" class="inline-flex items-center justify-center gap-2 px-5 min-h-[47px] text-sm font-bold text-white bg-[#0D63A5] hover:bg-[#083D68] rounded-none transition-all duration-200 shadow-none active:scale-[0.98]">
                        <span>Portal {{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                        <span>&rarr;</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 min-h-[47px] text-sm font-semibold text-[#DC2626] hover:bg-[#FEE2E2] rounded-none transition-colors duration-200">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 min-h-[47px] text-sm font-bold text-[#0D63A5] border border-[#0D63A5] bg-transparent hover:bg-[#0D63A5] hover:text-white rounded-none transition-all duration-200 active:scale-[0.98]">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 min-h-[47px] text-sm font-bold text-white bg-[#0D63A5] hover:bg-[#083D68] border border-[#0D63A5] rounded-none transition-all duration-200 shadow-none active:scale-[0.98]">
                        Daftar
                    </a>
                @endif
            </div>

            {{-- Mobile Hamburger Button --}}
            <div class="flex items-center md:hidden">
                <button type="button" onclick="toggleMobileMenu()" class="p-2 text-[#212121] hover:bg-[#F9F9F9] border border-[#E5E7EB] rounded-none transition-colors focus:outline-none" aria-label="Buka Menu Navigasi">
                    <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu Dropdown (Sharp Rectangular) --}}
    <div id="mobile-menu" class="hidden md:hidden border-t border-[#E5E7EB] bg-white px-5 pt-4 pb-6 space-y-3">
        <a href="#about" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-[#494A5E] hover:text-[#0D63A5] transition-colors">Tentang</a>
        <a href="#fitur" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-[#494A5E] hover:text-[#0D63A5] transition-colors">Fitur</a>
        <a href="#cara-kerja" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-[#494A5E] hover:text-[#0D63A5] transition-colors">Cara Kerja</a>
        <a href="#keunggulan" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-[#494A5E] hover:text-[#0D63A5] transition-colors">Keunggulan</a>
        <a href="#testimoni" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-[#494A5E] hover:text-[#0D63A5] transition-colors">Testimoni</a>
        <div class="pt-4 border-t border-[#E5E7EB] flex flex-col gap-2.5">
            @if(auth('pengendara')->check())
                <a href="{{ route('pengendara.dashboard') }}" class="w-full text-center min-h-[48px] py-3 text-sm font-bold text-[#212121] bg-[#FFD717] rounded-none hover:bg-[#E6C200] transition-colors flex items-center justify-center">Buka Dashboard Pengendara</a>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full text-center py-2 text-xs font-semibold text-[#DC2626]">Keluar Akun</button>
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
                <a href="{{ $dashRouteMobile }}" class="w-full text-center min-h-[48px] py-3 text-sm font-bold text-white bg-[#0D63A5] rounded-none hover:bg-[#083D68] transition-colors flex items-center justify-center">Buka Portal {{ ucfirst(str_replace('_', ' ', $roleMobile)) }}</a>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full text-center py-2 text-xs font-semibold text-[#DC2626]">Keluar Akun</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full text-center min-h-[47px] py-2.5 text-sm font-bold text-[#0D63A5] border border-[#0D63A5] rounded-none hover:bg-[#0D63A5] hover:text-white transition-colors flex items-center justify-center">Masuk</a>
                <a href="{{ route('register') }}" class="w-full text-center min-h-[47px] py-2.5 text-sm font-bold text-white bg-[#0D63A5] rounded-none hover:bg-[#083D68] transition-colors flex items-center justify-center">Daftar Akun Baru</a>
            @endif
        </div>
    </div>
</header>

<div>

{{-- ═══════════════════════════════════════════
    2. HERO SECTION (Foto Pelayanan Parkir Swadaya Latar Belakang + Teks & CTA Saja)
═══════════════════════════════════════════ --}}
<section class="relative bg-[#212121] text-white overflow-hidden py-24 sm:py-32 lg:py-36 border-b border-[#333333]">
    {{-- Background Photo: Pelayanan Parkir Swadaya Off-Street (Bebas Royalti Unsplash) --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="https://images.unsplash.com/photo-1716253871008-3ca31dbe93a0?auto=format&fit=crop&w=1920&q=80" 
             alt="Pelayanan Parkir di Tempat Parkir Swadaya" 
             class="w-full h-full object-cover object-center filter brightness-[0.28] contrast-[1.18]">
        {{-- High-Contrast Dark Editorial Gradient Overlays --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#212121]/80 via-[#212121]/64 to-[#212121]/50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#212121] via-transparent to-[#212121]/60"></div>
    </div>

    {{-- Content: Teks dan Tombol CTA Saja --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-6 text-left">
            {{-- Category Eyebrow --}}
            <div class="reveal-hero opacity-0 translate-y-4 transition-all duration-700 ease-out inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 border border-white/20 text-xs font-bold tracking-wider uppercase text-[#FFD717] font-sans rounded-none">
                <span class="w-2 h-2 bg-[#FFD717]"></span>
                <span>Sistem Parkir Portabel Berbasis Web &bull; Lahan Swadaya Off-Street</span>
            </div>

            {{-- Hero Headline --}}
            <h1 class="reveal-hero opacity-0 translate-y-4 transition-all duration-700 ease-out delay-100 font-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight">
                Kelola Parkir Lebih Cerdas: Sistem Parkir Portabel Berbasis Web untuk Lahan Swadaya Off-Street
            </h1>

            {{-- Subcopy --}}
            <p class="reveal-hero opacity-0 translate-y-4 transition-all duration-700 ease-out delay-200 font-sans text-base sm:text-lg text-[#E5E7EB] leading-relaxed max-w-2xl font-normal">
                Kelola operasional parkir mandiri tanpa beli alat mahal, tanpa kertas karcis, dan 100% data privat. Pengendara cukup scan QR poster gerbang untuk check-in mandiri, dan petugas memvalidasi karcis langsung dari browser HP.
            </p>

            {{-- Key Checkpoints --}}
            <div class="reveal-hero opacity-0 translate-y-4 transition-all duration-700 ease-out delay-300 grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs font-semibold text-white/95">
                <div class="flex items-center gap-2.5 p-3 bg-[#212121]/80 border border-white/20 rounded-none">
                    <svg class="w-4 h-4 text-[#FFD717] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Modal Rp0 Tanpa Alat</span>
                </div>
                <div class="flex items-center gap-2.5 p-3 bg-[#212121]/80 border border-white/20 rounded-none">
                    <svg class="w-4 h-4 text-[#FFD717] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Self Check-In &lt; 5 Detik</span>
                </div>
                <div class="flex items-center gap-2.5 p-3 bg-[#212121]/80 border border-white/20 rounded-none">
                    <svg class="w-4 h-4 text-[#FFD717] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Auto-Lunas & Data Privat</span>
                </div>
            </div>

            {{-- 2 Rectangular Sharp CTAs (rounded-none, min-h-[52px]) --}}
            <div class="reveal-hero opacity-0 translate-y-4 transition-all duration-700 ease-out delay-400 pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                {{-- Primary CTA: Scan QR Check-In (Guest) -> /scan --}}
                <a href="{{ route('checkin.scanner') }}" class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center gap-3 px-8 py-3.5 bg-[#FFD717] hover:bg-[#E6C200] text-[#212121] font-heading font-extrabold text-base rounded-none transition-all duration-200 border border-[#FFD717] shadow-none active:scale-[0.98]">
                    <svg class="w-5 h-5 text-[#212121] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                        <path d="M7 7h.01"></path>
                        <path d="M17 7h.01"></path>
                        <path d="M7 17h.01"></path>
                        <path d="M17 17h.01"></path>
                    </svg>
                    <span>Scan QR Check-In (Guest)</span>
                </a>

                {{-- Secondary CTA: Masuk / Daftar Mitra -> /login --}}
                <a href="{{ route('login') }}" class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center px-7 py-3.5 border border-white text-white hover:bg-white hover:text-[#212121] font-sans font-bold text-sm rounded-none transition-all duration-200 shadow-none active:scale-[0.98]">
                    <span>Masuk / Daftar Mitra</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    3. ABOUT US (#FFFFFF Band — 4 Brand Pillars)
═══════════════════════════════════════════ --}}
<section id="about" class="scroll-mt-20 py-16 lg:py-20 bg-white border-b border-[#E5E7EB]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header with Scroll Reveal --}}
        <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold tracking-wider uppercase text-[#0D63A5] font-sans">Tentang Parkirin</span>
            <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#212121] tracking-tight mt-1">
                Tata Kelola Parkir Mandiri yang Tertib, Cepat, dan Transparan
            </h2>
            <p class="mt-3 text-base text-[#494A5E] font-sans leading-relaxed">
                Parkirin hadir untuk membantu pengelola lahan parkir swadaya—seperti penitipan stasiun, ruko, pasar, dan kafe—mengelola operasional harian secara mandiri tanpa ketergantungan perangkat keras mahal.
            </p>
        </div>

        {{-- Grid 4 Flat Cards: 4 Brand Pillars (Aman, Fleksibel, Profesional, Terpercaya) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Pilar 1: Aman --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Aman</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Catatan kendaraan keluar-masuk tersimpan otomatis di server cloud. Menghilangkan potensi selisih uang setoran kasir di akhir shift dan mencegah kecurangan tiket.
                    </p>
                </div>
            </div>

            {{-- Pilar 2: Fleksibel --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-100">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Fleksibel</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Dapat langsung dibuka dari browser HP apa pun tanpa instalasi aplikasi. Pengendara check-in mandiri dan petugas memindai karcis keluar hanya dengan browser ponsel.
                    </p>
                </div>
            </div>

            {{-- Pilar 3: Profesional --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-150">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Profesional</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Tarif per jam, tarif flat harian, hingga tarif menginap terhitung otomatis secara presisi. Transparansi rincian karcis digital membuat pengendara merasa nyaman tanpa salah hitung.
                    </p>
                </div>
            </div>

            {{-- Pilar 4: Terpercaya --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-200">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Terpercaya</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Arsitektur multi-tenant mandiri menjaga seluruh data transaksi dan keuangan 100% milik pengelola lahan, tanpa dibagikan atau diintegrasikan ke retribusi pihak ketiga.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    4. FITUR UTAMA (#F9F9F9 Band — 6 Enterprise Cards)
═══════════════════════════════════════════ --}}
<section id="fitur" class="scroll-mt-20 py-16 lg:py-20 bg-[#F9F9F9] border-b border-[#E5E7EB]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header with Scroll Reveal --}}
        <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold tracking-wider uppercase text-[#0D63A5] font-sans">Fitur Enterprise</span>
            <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#212121] tracking-tight mt-1">
                Solusi Lengkap Pengelolaan Parkir Berbasis Web
            </h2>
            <span class="sr-only">Semua Kebutuhan Parkir dalam Satu Aplikasi</span>
            <p class="mt-3 text-base text-[#494A5E] font-sans leading-relaxed">
                Enam kapabilitas inti yang dirancang untuk efisiensi operator lahan swadaya dan kenyamanan pengendara.
            </p>
        </div>

        {{-- 3-Column Grid of 6 Flat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {{-- Feature 1: Self-Service QR Check-In --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                            <path d="M7 7h.01"></path>
                            <path d="M17 7h.01"></path>
                            <path d="M7 17h.01"></path>
                            <path d="M17 17h.01"></path>
                        </svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Self-Service QR Check-In</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Pengendara memindai poster QR yang ditempel di tiang gerbang masuk dengan kamera bawaan ponsel, memasukkan nomor plat, dan langsung menerima karcis digital dalam hitungan detik tanpa perlu mengunduh aplikasi atau login akun.
                    </p>
                </div>
            </div>

            {{-- Feature 2: Karcis Digital & HTML5 Web Scanner --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-75">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Karcis Digital & Web Scanner</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Menggantikan karcis kertas konvensional. Petugas jaga cukup membuka web scanner kamera di browser ponsel untuk memvalidasi karcis saat kendaraan keluar, menghilangkan biaya pembelian kertas struk dan mesin printer.
                    </p>
                </div>
            </div>

            {{-- Feature 3: Auto-Lunas Instan & Post-Audit QRIS --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-100">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Auto-Lunas Instan & Post-Audit</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Pengendara dapat mengunggah bukti pembayaran QRIS untuk langsung membuka akses keluar gerbang secara instan. Status tercatat auto-lunas, sementara Admin pengelola dapat memeriksa kesesuaian mutasi rekening sewaktu-waktu.
                    </p>
                </div>
            </div>

            {{-- Feature 4: Member Multi-Tier & Booking Inap >= 2 Hari --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-150">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Member Multi-Tier & Booking Inap</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Kelola kuota langganan member harian, mingguan, hingga bulanan dengan skema tarif khusus. Memfasilitasi reservasi parkir inap minimal 2 hari bagi komuter stasiun dengan jaminan ketersediaan slot kendaraan.
                    </p>
                </div>
            </div>

            {{-- Feature 5: Analitik Pendapatan YoY & Anti-Fraud Shift --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-200">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Analitik YoY & Anti-Fraud Shift</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Pantau tren rasio pertumbuhan pendapatan Year-over-Year (YoY), jam-jam puncak kepadatan lahan, serta audit rekapan setoran kasir per shift petugas untuk memastikan tidak ada celah kebocoran pendapatan.
                    </p>
                </div>
            </div>

            {{-- Feature 6: Isolasi Data Multi-Tenant 100% Privat --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-250">
                <div>
                    <div class="w-10 h-10 bg-[#0D63A5]/10 border border-[#0D63A5]/20 rounded-none flex items-center justify-center mb-4 text-[#0D63A5]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-[#212121]">Isolasi Data Multi-Tenant Privat</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Setiap lokasi lahan memiliki basis data yang terisolasi mandiri. Seluruh data keuangan, tarif, dan log kendaraan adalah milik penuh pengelola lahan swadaya—bukan untuk integrasi retribusi pihak ketiga.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    5. CARA KERJA (#FFFFFF Band — 4 Langkah Tajam)
═══════════════════════════════════════════ --}}
<section id="cara-kerja" class="scroll-mt-20 py-16 lg:py-20 bg-white border-b border-[#E5E7EB]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header with Scroll Reveal --}}
        <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold tracking-wider uppercase text-[#0D63A5] font-sans">Alur Operasional</span>
            <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#212121] tracking-tight mt-1">
                Empat Langkah Mudah di Lapangan
            </h2>
            <p class="mt-3 text-base text-[#494A5E] font-sans leading-relaxed">
                Alur kerja terpadu dari gerbang masuk hingga keluar tanpa antrean panjang dan tanpa instalasi aplikasi.
            </p>
        </div>

        {{-- 4 Step Cards with Sharp Square Badges 01-04 in #0D63A5 --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Step 1 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-10 h-10 bg-[#0D63A5] text-white font-heading font-black text-lg flex items-center justify-center rounded-none shadow-none">
                            01
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0D63A5] font-sans">Tahap 1</span>
                    </div>
                    <h3 class="font-heading font-bold text-base sm:text-lg text-[#212121]">Self Check-In Masuk: Scan Poster QR Gerbang</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Pengendara tiba di gerbang masuk dan memindai poster QR yang terpasang di tiang pos langsung menggunakan kamera ponsel tanpa perlu memasang aplikasi.
                    </p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-100">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-10 h-10 bg-[#0D63A5] text-white font-heading font-black text-lg flex items-center justify-center rounded-none shadow-none">
                            02
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0D63A5] font-sans">Tahap 2</span>
                    </div>
                    <h3 class="font-heading font-bold text-base sm:text-lg text-[#212121]">Input Plat & Karcis Digital</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Halaman web check-in terbuka otomatis. Pengendara memasukkan nomor plat kendaraan, memilih jenis kendaraan, dan karcis digital QR instan langsung terbit di layar HP.
                    </p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-150">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-10 h-10 bg-[#0D63A5] text-white font-heading font-black text-lg flex items-center justify-center rounded-none shadow-none">
                            03
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0D63A5] font-sans">Tahap 3</span>
                    </div>
                    <h3 class="font-heading font-bold text-base sm:text-lg text-[#212121]">Parkir Aman & Terdata</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Kendaraan parkir di area lahan dengan tertib. Jam masuk dan durasi terhitung akurat secara real-time di server cloud pengelola tanpa risiko tiket fisik tercecer.
                    </p>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5]/50 transition-all duration-300 delay-200">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-10 h-10 bg-[#0D63A5] text-white font-heading font-black text-lg flex items-center justify-center rounded-none shadow-none">
                            04
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0D63A5] font-sans">Tahap 4</span>
                    </div>
                    <h3 class="font-heading font-bold text-base sm:text-lg text-[#212121]">Scan Keluar & Auto-Lunas</h3>
                    <p class="mt-2 text-sm text-[#494A5E] font-sans leading-relaxed">
                        Di pos keluar, petugas memindai QR karcis lewat browser ponsel. Total tarif dihitung otomatis oleh sistem, pengendara membayar via QRIS atau tunai, dan palang gerbang terbuka.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    6. KEUNGGULAN (#F9F9F9 Band — 2-Column Editorial & Comparison)
═══════════════════════════════════════════ --}}
<section id="keunggulan" class="scroll-mt-20 py-16 lg:py-20 bg-[#F9F9F9] border-b border-[#E5E7EB]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-10 items-center">
            
            {{-- Left Column (60%): Editorial Pointers --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out lg:col-span-6 space-y-6">
                <div>
                    <span class="text-xs font-bold tracking-wider uppercase text-[#0D63A5] font-sans">Keunggulan Kompetitif</span>
                    <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#212121] tracking-tight mt-1">
                        Efisiensi Nyata untuk Lahan Swadaya Off-Street
                    </h2>
                    <p class="mt-3 text-base text-[#494A5E] font-sans leading-relaxed">
                        Parkirin mengatasi kendala utama pengelola lahan parkir mandiri: keterbatasan anggaran perangkat keras dan kerentanan audit manual. Kami menghadirkan solusi enterprise yang ringan dan dapat langsung diterapkan.
                    </p>
                </div>

                {{-- 3 Key Pointers --}}
                <div class="space-y-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-none hover:border-[#0D63A5]/50 transition-all duration-200">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 bg-[#0D63A5] text-white flex items-center justify-center shrink-0 rounded-none font-bold text-sm">
                                &check;
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-base text-[#212121]">Modal Rp0 Tanpa Beli Alat Baru</h3>
                                <p class="mt-1 text-sm text-[#494A5E] font-sans leading-relaxed">
                                    Pengelola tidak perlu mengeluarkan belasan juta rupiah untuk mesin tiket otomatis, barrier gate khusus, atau printer kasir. Sistem berjalan penuh pada smartphone yang sudah ada.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-none hover:border-[#0D63A5]/50 transition-all duration-200">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 bg-[#0D63A5] text-white flex items-center justify-center shrink-0 rounded-none font-bold text-sm">
                                &check;
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-base text-[#212121]">Khusus Lahan Swadaya Off-Street</h3>
                                <p class="mt-1 text-sm text-[#494A5E] font-sans leading-relaxed">
                                    Dirancang spesifik untuk penitipan stasiun, area parkir ruko, restoran, dan pasar mandiri tanpa kerumitan birokrasi retribusi pihak ketiga.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-none hover:border-[#0D63A5]/50 transition-all duration-200">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 bg-[#0D63A5] text-white flex items-center justify-center shrink-0 rounded-none font-bold text-sm">
                                &check;
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-base text-[#212121]">Anti Pass-Back & SOP Karcis Hilang</h3>
                                <p class="mt-1 text-sm text-[#494A5E] font-sans leading-relaxed">
                                    Karcis digital tidak bisa digunakan ganda (anti pass-back). Jika pengunjung tidak sengaja menutup halaman tiket, petugas dapat melacak durasi masuk berdasarkan nomor plat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (40%): Clean Flat Comparison Table --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out lg:col-span-6 delay-150">
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-7 shadow-none hover:border-[#0D63A5]/40 transition-all duration-300">
                    <div class="pb-4 border-b border-[#E5E7EB]">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0D63A5] font-sans">Komparasi Langsung</span>
                        <h3 class="font-heading font-bold text-lg text-[#212121]">Perbandingan Metode Operasional</h3>
                        <p class="text-xs text-[#494A5E] mt-0.5">Sistem Konvensional Kertas vs Solusi Parkirin</p>
                    </div>

                    <div class="mt-5 space-y-4 text-xs font-sans">
                        {{-- Row 1 --}}
                        <div class="p-3.5 bg-[#F9F9F9] border border-[#E5E7EB] rounded-none">
                            <div class="flex items-center justify-between pb-1.5 border-b border-[#E5E7EB]/80">
                                <span class="font-bold text-[#212121] uppercase tracking-wide">Investasi Alat Awal</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div class="text-[#DC2626]">
                                    <span class="block font-semibold">Konvensional:</span>
                                    <span>Mesin tiket & printer (Jutaan Rupiah)</span>
                                </div>
                                <div class="text-emerald-700 font-bold border-l border-[#E5E7EB] pl-2">
                                    <span class="block font-bold text-[#0D63A5]">Parkirin:</span>
                                    <span>Rp0 (Cukup Smartphone & Poster QR)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Row 2 --}}
                        <div class="p-3.5 bg-[#F9F9F9] border border-[#E5E7EB] rounded-none">
                            <div class="flex items-center justify-between pb-1.5 border-b border-[#E5E7EB]/80">
                                <span class="font-bold text-[#212121] uppercase tracking-wide">Konsumsi Media Karcis</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div class="text-[#DC2626]">
                                    <span class="block font-semibold">Konvensional:</span>
                                    <span>Ratusan roll kertas karcis / bulan</span>
                                </div>
                                <div class="text-emerald-700 font-bold border-l border-[#E5E7EB] pl-2">
                                    <span class="block font-bold text-[#0D63A5]">Parkirin:</span>
                                    <span>100% Karcis Digital di Browser HP</span>
                                </div>
                            </div>
                        </div>

                        {{-- Row 3 --}}
                        <div class="p-3.5 bg-[#F9F9F9] border border-[#E5E7EB] rounded-none">
                            <div class="flex items-center justify-between pb-1.5 border-b border-[#E5E7EB]/80">
                                <span class="font-bold text-[#212121] uppercase tracking-wide">Penanganan Karcis Hilang</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div class="text-[#DC2626]">
                                    <span class="block font-semibold">Konvensional:</span>
                                    <span>Denda sepihak & rawan sengketa</span>
                                </div>
                                <div class="text-emerald-700 font-bold border-l border-[#E5E7EB] pl-2">
                                    <span class="block font-bold text-[#0D63A5]">Parkirin:</span>
                                    <span>Cek Riwayat Plat Cepat di Database Web</span>
                                </div>
                            </div>
                        </div>

                        {{-- Row 4 --}}
                        <div class="p-3.5 bg-[#F9F9F9] border border-[#E5E7EB] rounded-none">
                            <div class="flex items-center justify-between pb-1.5 border-b border-[#E5E7EB]/80">
                                <span class="font-bold text-[#212121] uppercase tracking-wide">Audit Setoran Kasir Shift</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div class="text-[#DC2626]">
                                    <span class="block font-semibold">Konvensional:</span>
                                    <span>Rekap manual kasir rawan selisih</span>
                                </div>
                                <div class="text-emerald-700 font-bold border-l border-[#E5E7EB] pl-2">
                                    <span class="block font-bold text-[#0D63A5]">Parkirin:</span>
                                    <span>Audit Cloud Real-Time Anti-Bocor</span>
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
    7. TESTIMONI (#FFFFFF Band — 3 Authentic Editorial Cards)
═══════════════════════════════════════════ --}}
<section id="testimoni" class="scroll-mt-20 py-16 lg:py-20 bg-white border-b border-[#E5E7EB]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header with Scroll Reveal --}}
        <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold tracking-wider uppercase text-[#0D63A5] font-sans">Testimoni Pengguna</span>
            <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#212121] tracking-tight mt-1">
                Pengalaman Nyata Pengelola Lahan & Pengendara
            </h2>
            <p class="mt-3 text-base text-[#494A5E] font-sans leading-relaxed">
                Cerita langsung dari mereka yang menggunakan Parkirin dalam operasional harian di lapangan.
            </p>
        </div>

        {{-- 3 Editorial Cards with Left Accent Bar (border-l-4 border-l-[#0D63A5]) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Testimoni 1 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] border-l-4 border-l-[#0D63A5] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5] transition-all duration-300">
                <div>
                    <div class="text-[#0D63A5] mb-3">
                        <svg class="w-8 h-8 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    </div>
                    <p class="text-sm text-[#494A5E] font-sans leading-relaxed">
                        "Tiap pagi ratusan motor komuter buru-buru ngejar KRL. Kalau pakai karcis kertas konvensional, antrean di gerbang masuk bikin macet sampai jalan raya. Sejak pasang poster QR Parkirin, pengendara tinggal scan dari motor dan langsung masuk dalam 5 detik. Rekap setoran kasir tiap ganti shift sore juga klop tanpa selisih uang receh."
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-[#E5E7EB]">
                    <h4 class="font-heading font-bold text-sm text-[#212121]">Budi Santoso</h4>
                    <p class="text-xs text-[#0D63A5] font-semibold mt-0.5">Pengelola Penitipan Motor Stasiun (Bojong Gede)</p>
                </div>
            </div>

            {{-- Testimoni 2 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] border-l-4 border-l-[#0D63A5] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5] transition-all duration-300 delay-100">
                <div>
                    <div class="text-[#0D63A5] mb-3">
                        <svg class="w-8 h-8 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    </div>
                    <p class="text-sm text-[#494A5E] font-sans leading-relaxed">
                        "Paling malas kalau karcis kertas basah pas hujan atau keselip di jaket lalu kena denda karcis hilang. Dengan Parkirin, karcis langsung ada di browser HP. Pas mau keluar tinggal perlihatkan QR ke petugas. Jauh lebih tenang dan praktis."
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-[#E5E7EB]">
                    <h4 class="font-heading font-bold text-sm text-[#212121]">Dimas Prasetyo</h4>
                    <p class="text-xs text-[#0D63A5] font-semibold mt-0.5">Pengendara Komuter KRL Harian</p>
                </div>
            </div>

            {{-- Testimoni 3 --}}
            <div class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-white border border-[#E5E7EB] border-l-4 border-l-[#0D63A5] rounded-lg p-6 sm:p-7 shadow-none flex flex-col justify-between hover:-translate-y-1 hover:border-[#0D63A5] transition-all duration-300 delay-150">
                <div>
                    <div class="text-[#0D63A5] mb-3">
                        <svg class="w-8 h-8 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    </div>
                    <p class="text-sm text-[#494A5E] font-sans leading-relaxed">
                        "Dulu sempat survei vendor palang otomatis, biayanya ditawari sampai puluhan juta dan perawatannya mahal. Untuk lahan swadaya jelas tidak masuk hitungannya. Parkirin jadi jalan keluar terbaik: cukup modal HP petugas dan poster QR di tiang. Tarif kendaraan yang menginap berhari-hari juga otomatis terhitung transparan."
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-[#E5E7EB]">
                    <h4 class="font-heading font-bold text-sm text-[#212121]">Hj. Ratna Wulandari</h4>
                    <p class="text-xs text-[#0D63A5] font-semibold mt-0.5">Pengelola Lahan Parkir Ruko & Area Kuliner</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
    8. CTA BAND (#0D63A5) & FOOTER (#212121)
═══════════════════════════════════════════ --}}
{{-- Full-Width CTA Band (#0D63A5) --}}
<section class="reveal-on-scroll opacity-0 translate-y-6 transition-all duration-700 ease-out bg-[#0D63A5] text-white py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
            Siap Memodernisasi Lahan Parkir Anda Hari Ini?
        </h2>
        <p class="mt-4 text-base sm:text-lg text-white/90 font-sans max-w-2xl mx-auto leading-relaxed">
            Tingkatkan efisiensi lahan swadaya Anda dengan sistem parkir web portabel tanpa investasi alat baru. Coba check-in mandiri sekarang atau hubungi tim kami untuk konsultasi kemitraan.
        </p>

        {{-- CTA Actions: White Outlined + Yellow Button (rounded-none, min-h-[50px]) --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            {{-- Yellow Primary Button: Self Check-In --}}
            <a href="{{ route('checkin.scanner') }}" class="w-full sm:w-auto min-h-[50px] inline-flex items-center justify-center gap-2.5 px-8 py-3.5 bg-[#FFD717] hover:bg-[#E6C200] text-[#212121] font-heading font-bold text-sm rounded-none transition-all duration-200 border border-[#FFD717] shadow-none active:scale-[0.98]">
                <svg class="w-4 h-4 text-[#212121]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Coba Self Check-In (Guest)</span>
            </a>

            {{-- White Outlined Button: Masuk / Daftar Mitra --}}
            <a href="{{ route('login') }}" class="w-full sm:w-auto min-h-[50px] inline-flex items-center justify-center px-8 py-3.5 border-2 border-white text-white hover:bg-white hover:text-[#0D63A5] font-sans font-bold text-sm rounded-none transition-all duration-200 shadow-none active:scale-[0.98]">
                <span>Masuk / Daftar Mitra</span>
            </a>
        </div>
    </div>
</section>

{{-- Enterprise Dark Footer (#212121) --}}
<footer class="bg-[#212121] text-white py-14 border-t border-[#333333]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            
            {{-- Brand Column (White Logo Variant) --}}
            <div>
                <a href="{{ route('landing') }}" class="inline-block mb-4 transition-transform duration-200 hover:scale-[1.01]" aria-label="Beranda Parkirin">
                    <img src="{{ asset('assets/logo/white/Teks.svg') }}" alt="Parkirin" class="h-8 w-auto object-contain">
                </a>
                <p class="text-xs sm:text-sm text-[#9CA3AF] leading-relaxed font-sans">
                    Sistem parkir portabel berbasis web untuk pengelola lahan swadaya off-street di seluruh Indonesia. Aman, fleksibel, profesional, dan terpercaya.
                </p>
            </div>

            {{-- Navigasi Halaman --}}
            <div>
                <h4 class="font-heading font-bold text-white text-xs uppercase tracking-wider mb-4">Navigasi Halaman</h4>
                <ul class="space-y-2.5 text-xs sm:text-sm text-[#9CA3AF] font-sans">
                    <li><a href="#about" class="hover:text-white transition-colors duration-200">Tentang Kami</a></li>
                    <li><a href="#fitur" class="hover:text-white transition-colors duration-200">Fitur Enterprise</a></li>
                    <li><a href="#cara-kerja" class="hover:text-white transition-colors duration-200">Alur Operasional</a></li>
                    <li><a href="#keunggulan" class="hover:text-white transition-colors duration-200">Keunggulan Lahan</a></li>
                    <li><a href="#testimoni" class="hover:text-white transition-colors duration-200">Testimoni Pengguna</a></li>
                </ul>
            </div>

            {{-- Portal Akses --}}
            <div>
                <h4 class="font-heading font-bold text-white text-xs uppercase tracking-wider mb-4">Portal Akses</h4>
                <ul class="space-y-2.5 text-xs sm:text-sm text-[#9CA3AF] font-sans">
                    <li><a href="{{ route('login') }}?tab=pengendara" class="hover:text-white transition-colors duration-200">Masuk Akun Pengendara</a></li>
                    <li><a href="{{ route('login') }}?tab=user" class="hover:text-white transition-colors duration-200">Portal Staf / Petugas</a></li>
                    <li><a href="{{ route('checkin.scanner') }}" class="hover:text-white transition-colors duration-200">Self Check-In Guest</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition-colors duration-200">Pendaftaran Akun Baru</a></li>
                </ul>
            </div>

            {{-- Kontak & Bantuan --}}
            <div>
                <h4 class="font-heading font-bold text-white text-xs uppercase tracking-wider mb-4">Kontak & Informasi</h4>
                <ul class="space-y-2.5 text-xs sm:text-sm text-[#9CA3AF] font-sans">
                    <li><a href="mailto:info@parkirin.id" class="hover:text-white transition-colors duration-200">info@parkirin.id</a></li>
                    <li><a href="https://wa.me/6281234567890" target="_blank" class="hover:text-white transition-colors duration-200">+62 812-3456-7890</a></li>
                    <li>Jakarta, Indonesia</li>
                    <li class="text-[11px] text-[#6B7280] pt-1">Layanan Operasional Web 24/7</li>
                </ul>
            </div>

        </div>

        {{-- Bottom Copyright Notice --}}
        <div class="border-t border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-[#9CA3AF] gap-4 font-sans">
            <p>&copy; {{ date('Y') }} Parkirin. Hak Cipta Dilindungi Undang-Undang.</p>
            <p>Sistem Parkir Portabel Berbasis Web &bull; Khusus Pengelola Swadaya Off-Street.</p>
        </div>
    </div>
</footer>

</div>

@push('scripts')
<script>
    // Responsive Mobile Menu Drawer Toggle
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

    // Lightweight Company Profile Animations (Hero Stagger & Scroll Reveal)
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Initial Hero Stagger Reveal
        const heroElements = document.querySelectorAll('.reveal-hero');
        heroElements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.remove('opacity-0', 'translate-y-4');
                el.classList.add('opacity-100', 'translate-y-0');
            }, 60 + index * 90);
        });

        // 2. Simple Scroll Reveal Observer for Sections & Cards
        const scrollElements = document.querySelectorAll('.reveal-on-scroll');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-6');
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -30px 0px'
            });

            scrollElements.forEach(el => observer.observe(el));
        } else {
            // Fallback for older browsers
            scrollElements.forEach(el => {
                el.classList.remove('opacity-0', 'translate-y-6');
                el.classList.add('opacity-100', 'translate-y-0');
            });
        }
    });
</script>
@endpush
@endsection