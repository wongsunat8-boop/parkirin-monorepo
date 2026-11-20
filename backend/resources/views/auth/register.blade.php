@extends('layouts.app')
@section('title', 'Daftar Pengendara — Parkirin')

@section('content')
<div class="min-h-screen flex bg-slate-50/60 font-sans text-slate-900">
    
    {{-- Left Panel: Form (shadcn/ui style card) --}}
    <div class="flex-1 flex flex-col justify-center px-4 py-8 sm:px-8 lg:px-14 xl:px-20 overflow-y-auto">
        <div class="w-full max-w-md mx-auto my-auto py-4">
            
            {{-- Navigation Back to Landing Page --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-primary transition-colors bg-white border border-slate-200/90 px-3 py-1.5 rounded-lg shadow-xs hover:border-slate-300">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Beranda</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold text-primary bg-primary/10 border border-primary/15 font-mono">
                    Registrasi Akun
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
                <h1 class="font-heading text-2xl sm:text-3xl font-black text-slate-950 tracking-tight">Buat Akun Pengendara</h1>
                <p class="mt-1.5 text-sm text-slate-500 font-sans leading-relaxed">
                    Daftar untuk menikmati kemudahan Self Check-In, booking slot, dan karcis QR digital instan.
                </p>
            </div>

            {{-- Error Callout Alert --}}
            @if(isset($errors) && $errors->any())
            <div class="mb-5 p-3.5 bg-rose-50/90 border border-rose-200 rounded-xl space-y-1 shadow-xs">
                <div class="flex items-center gap-2 text-rose-800 font-bold text-xs uppercase tracking-wide">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Terjadi Kesalahan Pengisian</span>
                </div>
                @foreach($errors->all() as $error)
                <p class="text-xs sm:text-sm text-rose-700 font-medium pl-6 leading-snug">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            {{-- ═══════════════════════════════════════════
                REGISTER FORM
            ═══════════════════════════════════════════ --}}
            <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                @csrf
                
                {{-- Nama Lengkap --}}
                <div>
                    <label for="nama_lengkap" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required autofocus
                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                            placeholder="Contoh: Budi Santoso">
                    </div>
                </div>

                {{-- Email Aktif --}}
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Email Aktif
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                            placeholder="nama@email.com">
                    </div>
                </div>

                {{-- Username & No Telepon Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-mono text-xs">
                                @
                            </div>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required
                                class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                                placeholder="budisantoso">
                        </div>
                    </div>
                    <div>
                        <label for="no_telepon" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            No. Telepon <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <input type="tel" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}"
                                class="flex h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                                placeholder="08123456789">
                        </div>
                    </div>
                </div>

                {{-- Password & Confirm Password Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" x-data="{ showPass: false, showConfirm: false }">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                                class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 pr-10 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                                placeholder="Min. 8 karakter">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Konfirmasi Sandi
                        </label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                                class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 pr-10 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary shadow-xs transition-all" 
                                placeholder="Ulangi sandi">
                            <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" 
                    class="inline-flex items-center justify-center rounded-xl text-sm font-bold h-11 px-4 py-2 bg-primary text-white hover:bg-primary-dark shadow-sm hover:shadow transition-all duration-150 active:scale-[0.99] w-full gap-2 mt-2">
                    <span>Daftar Sekarang</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            {{-- Divider --}}
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-xs uppercase"><span class="bg-slate-50/90 px-3 text-slate-400 font-semibold tracking-wider">atau daftar dengan</span></div>
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
                <span>Daftar dengan Google</span>
            </a>

            {{-- Footer Switch Link --}}
            <p class="mt-8 text-center text-xs sm:text-sm text-slate-500 font-medium">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="text-primary font-bold hover:underline ml-1">Masuk sekarang</a>
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
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <span>Kemudahan untuk Pengendara Modern</span>
            </div>

            <div class="space-y-2.5">
                <h2 class="font-heading text-3xl xl:text-4xl font-black text-white tracking-tight leading-tight">
                    Parkir Nyaman Tanpa Khawatir Kehabisan Tempat
                </h2>
                <p class="text-sm xl:text-base text-white/75 font-sans leading-relaxed">
                    Akses karcis digital QR code di ponsel, pantau sisa slot tempat parkir secara real-time, dan simpan riwayat pembayaran secara transparan.
                </p>
            </div>

            {{-- Feature Pillars in Clean Cards --}}
            <div class="grid grid-cols-2 gap-3.5 pt-2">
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <p class="font-heading font-bold text-sm text-accent">Karcis QR Digital</p>
                    <p class="text-xs text-white/60 mt-1">Bebas kertas hilang & terhindar dari denda tiket fisik.</p>
                </div>
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <p class="font-heading font-bold text-sm text-accent">Pemesanan Slot</p>
                    <p class="text-xs text-white/60 mt-1">Cek kuota langsung & pesan tempat sebelum berangkat.</p>
                </div>
            </div>

            {{-- Trust Guarantee Note --}}
            <div class="flex items-center gap-3 pt-2 text-xs text-white/60">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Data kendaraan dan transaksi terenkripsi aman di cloud Parkirin.</span>
            </div>

        </div>
    </div>

</div>
@endsection
