@extends('layouts.app')
@section('title', 'Masuk — Parkirin')

@section('content')
<div class="min-h-screen flex bg-slate-50/60 font-sans text-slate-900">
    {{-- Left Panel: Form (shadcn/ui style card) --}}
    <div class="flex-1 flex flex-col justify-center px-4 py-8 sm:px-8 lg:px-14 xl:px-20">
        <div class="w-full max-w-md mx-auto">
            
            {{-- Navigation Back to Landing Page --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-primary transition-colors bg-white border border-slate-200/90 px-3 py-1.5 rounded-lg shadow-xs hover:border-slate-300">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Beranda</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold text-primary bg-primary/10 border border-primary/15 font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                    Portal Parkirin
                </span>
            </div>

            {{-- Brand Logo --}}
            <div class="mb-5">
                <a href="{{ route('landing') }}" class="inline-flex items-center group focus:outline-none" aria-label="Beranda Parkirin">
                    <img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" class="h-9 w-auto object-contain">
                </a>
            </div>

            {{-- Card Header --}}
            <div class="mb-6">
                <h1 class="font-heading text-2xl sm:text-3xl font-black text-slate-950 tracking-tight">Selamat Datang Kembali</h1>
                <p class="mt-1.5 text-sm text-slate-500 font-sans leading-relaxed">
                    Masuk untuk mengelola operasional lahan atau mengakses karcis parkir Anda.
                </p>
            </div>

            {{-- shadcn/ui Segmented Tabs Trigger --}}
            <div class="p-1 bg-slate-100/90 rounded-xl border border-slate-200/90 flex items-center gap-1 shadow-inner">
                <button type="button" id="tab-pengendara" onclick="switchTab('pengendara')" 
                    class="flex-1 py-2 px-3 text-xs sm:text-sm font-bold rounded-lg transition-all duration-200 text-center tab-active flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pengendara</span>
                </button>
                <button type="button" id="tab-user" onclick="switchTab('user')" 
                    class="flex-1 py-2 px-3 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 text-center text-slate-500 hover:text-slate-900 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Staf / Manajemen</span>
                </button>
            </div>

            {{-- Error Callout Alert --}}
            @if(isset($errors) && $errors->any())
            <div class="mt-5 p-3.5 bg-rose-50/90 border border-rose-200 rounded-xl flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <p class="text-xs font-bold text-rose-800 uppercase tracking-wide">Pemberitahuan</p>
                    <p class="text-xs sm:text-sm text-rose-700 mt-0.5 font-medium leading-snug">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════
                PENGENDARA LOGIN FORM (Default)
            ═══════════════════════════════════════════ --}}
            <form id="form-pengendara" method="POST" action="{{ route('login.pengendara') }}" class="mt-6 space-y-4" onsubmit="handleLoginSubmit(this)">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Email atau Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                            placeholder="nama@email.com atau username">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password-pengendara" class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password-pengendara" name="password" required
                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-10 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                            placeholder="Masukkan kata sandi">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                    class="inline-flex items-center justify-center rounded-xl text-sm font-bold h-11 px-4 py-2 bg-primary text-white hover:bg-primary-dark shadow-sm hover:shadow transition-all duration-150 active:scale-[0.99] w-full gap-2 mt-1">
                    <span>Masuk sebagai Pengendara</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-slate-50/90 px-3 text-slate-400 font-semibold tracking-wider">atau masuk dengan</span></div>
                </div>

                {{-- Google OAuth Button --}}
                <a href="{{ route('auth.google') }}" 
                    class="inline-flex items-center justify-center gap-3 rounded-xl text-sm font-semibold h-11 px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-800 shadow-xs transition-all active:scale-[0.99] w-full">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Lanjutkan dengan Google</span>
                </a>
            </form>

            {{-- ═══════════════════════════════════════════
                STAF / MANAJEMEN LOGIN FORM
            ═══════════════════════════════════════════ --}}
            <form id="form-user" method="POST" action="{{ route('login.user') }}" class="mt-6 space-y-4 hidden" onsubmit="handleLoginSubmit(this)">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username Akun Staf
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required
                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                            placeholder="Petugas / Admin / Owner / Super Admin">
                    </div>
                </div>

                <div>
                    <label for="password-user" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Staf
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password-user" name="password" required
                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-10 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                            placeholder="Masukkan kata sandi akun staf">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                    class="inline-flex items-center justify-center rounded-xl text-sm font-bold h-11 px-4 py-2 bg-slate-900 text-white hover:bg-black shadow-sm hover:shadow transition-all duration-150 active:scale-[0.99] w-full gap-2 mt-1">
                    <span>Masuk Portal Staf</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            {{-- Footer Switch Link --}}
            <p class="mt-8 text-center text-xs sm:text-sm text-slate-500 font-medium">
                Belum punya akun pengendara? 
                <a href="{{ route('register') }}" class="text-primary font-bold hover:underline ml-1">Daftar sekarang</a>
            </p>

        </div>
    </div>

    {{-- Right Panel: Visual Hero Showcase (Enterprise shadcn style) --}}
    <div class="hidden lg:flex flex-1 bg-gradient-to-br from-slate-900 via-primary-dark to-slate-950 items-center justify-center p-12 relative overflow-hidden border-l border-slate-800">
        {{-- Subtle background geometric accents --}}
        <div class="absolute inset-0 opacity-15 pointer-events-none">
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-accent blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-primary-light blur-3xl"></div>
        </div>

        <div class="relative text-white max-w-lg space-y-6">
            
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-accent backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Sistem Pengelola Parkir Digital</span>
            </div>

            <div class="space-y-2.5">
                <h2 class="font-heading text-3xl xl:text-4xl font-black text-white tracking-tight leading-tight">
                    Satu Platform untuk Seluruh Kebutuhan Parkir
                </h2>
                <p class="text-sm xl:text-base text-white/75 font-sans leading-relaxed">
                    Sistem digital dengan pemisahan data tiap lokasi parkir yang aman, scan otomatis plat nomor kendaraan, dan tiket QR mandiri tanpa perlu antre.
                </p>
            </div>

            {{-- Feature Pillars in Clean Cards --}}
            <div class="grid grid-cols-2 gap-3.5 pt-2">
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div class="w-8 h-8 rounded-lg bg-accent/20 text-accent flex items-center justify-center mb-2 font-bold text-xs">
                        QR
                    </div>
                    <p class="font-heading font-bold text-sm text-white">Masuk Mandiri & Tiket QR</p>
                    <p class="text-xs text-white/60 mt-0.5">Pengendara & member langsung terdata di gerbang.</p>
                </div>
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="font-heading font-bold text-sm text-white">Pembayaran Cepat & Otomatis</p>
                    <p class="text-xs text-white/60 mt-0.5">Palang terbuka langsung tanpa selisih uang kasir.</p>
                </div>
            </div>

            {{-- Security / Compliance Note --}}
            <div class="flex items-center gap-3 pt-2 text-xs text-white/60">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>5 Tingkatan Hak Akses: Super Admin, Pemilik Lahan, Admin, Petugas, dan Pengendara.</span>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tab) {
        const userForm = document.getElementById('form-user');
        const pengendaraForm = document.getElementById('form-pengendara');
        const tabUser = document.getElementById('tab-user');
        const tabPengendara = document.getElementById('tab-pengendara');

        if (tab === 'user') {
            userForm.classList.remove('hidden');
            pengendaraForm.classList.add('hidden');
            tabUser.classList.add('tab-active');
            tabUser.classList.remove('text-slate-500');
            tabPengendara.classList.remove('tab-active');
            tabPengendara.classList.add('text-slate-500');
        } else {
            userForm.classList.add('hidden');
            pengendaraForm.classList.remove('hidden');
            tabPengendara.classList.add('tab-active');
            tabPengendara.classList.remove('text-slate-500');
            tabUser.classList.remove('tab-active');
            tabUser.classList.add('text-slate-500');
        }
    }

    // Auto-switch tab if URL has ?tab=user or ?tab=staf
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'user' || urlParams.get('tab') === 'staf') {
            switchTab('user');
        }
    });

    // Mencegah double submit cepat yang memicu 419 Page Expired
    function handleLoginSubmit(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            const span = btn.querySelector('span');
            if (span) {
                span.textContent = 'Memproses...';
            }
        }
    }
</script>
@endpush

@push('styles')
<style>
    .tab-active {
        background-color: #FFFFFF !important;
        color: #0F172A !important;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.06);
    }
</style>
@endpush
@endsection
