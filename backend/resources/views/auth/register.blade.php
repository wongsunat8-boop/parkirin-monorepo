@extends('layouts.app')
@section('title', 'Daftar Pengendara — Parkirin')

@section('content')
<div class="min-h-screen flex">
    {{-- Left Panel: Illustration --}}
    <div class="hidden lg:flex flex-1 bg-gradient-to-br from-primary via-primary-dark to-primary items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full bg-accent"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-white"></div>
        </div>
        <div class="relative text-center text-white max-w-md">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center mx-auto mb-6 border border-white/20 shadow-lg">
                <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h2 class="font-heading text-3xl font-extrabold">Bergabung dengan Parkirin</h2>
            <p class="mt-3 text-white/80 leading-relaxed text-sm">Daftarkan diri Anda sebagai pengendara dan nikmati kemudahan booking slot parkir, karcis digital QR code, serta riwayat parkir yang transparan.</p>
            <div class="mt-8 grid grid-cols-2 gap-3 text-left">
                <div class="p-3 bg-white/10 backdrop-blur-sm rounded-xl border border-white/15">
                    <p class="font-heading font-bold text-sm text-accent">Karcis Digital</p>
                    <p class="text-xs text-white/70 mt-0.5">Bebas hilang & praktis</p>
                </div>
                <div class="p-3 bg-white/10 backdrop-blur-sm rounded-xl border border-white/15">
                    <p class="font-heading font-bold text-sm text-accent">Booking Slot</p>
                    <p class="text-xs text-white/70 mt-0.5">Grace period 10 menit</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel: Form --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 overflow-y-auto">
        <div class="w-full max-w-md my-auto">
            {{-- Navigasi Back to Landing Page --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-bold text-dark/60 hover:text-primary transition group bg-white border border-slate-200/80 px-3 py-1.5 rounded-lg shadow-sm">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1 text-primary" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Beranda
                </a>
                <span class="text-xs font-bold text-primary bg-primary/10 px-2.5 py-1 rounded-md">Pendaftaran Pengendara</span>
            </div>

            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 mb-6">
                <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-md shadow-primary/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
                </div>
                <span class="font-heading font-black text-2xl text-dark tracking-tight">Park<span class="text-primary">irin</span></span>
            </a>

            <h1 class="font-heading text-2xl sm:text-3xl font-bold text-dark">Buat Akun Pengendara</h1>
            <p class="mt-1.5 text-sm text-dark/60">Lengkapi data diri Anda untuk memulai</p>

            @if(isset($errors) && $errors->any())
            <div class="mt-4 p-3.5 bg-danger-bg border border-danger/20 rounded-xl space-y-1">
                @foreach($errors->all() as $error)
                <p class="text-sm text-danger font-medium">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-3.5">
                @csrf
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-dark/70 mb-1">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required autofocus
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Contoh: Budi Santoso">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-dark/70 mb-1">Email Aktif</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="nama@email.com">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="username" class="block text-sm font-medium text-dark/70 mb-1">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="budisantoso">
                    </div>
                    <div>
                        <label for="no_telepon" class="block text-sm font-medium text-dark/70 mb-1">No. Telp <span class="text-dark/40">(opsional)</span></label>
                        <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="08123456789">
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-dark/70 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-dark/70 mb-1">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Ulangi password">
                </div>

                <button type="submit" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 mt-2">Daftar Sekarang</button>
            </form>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-xs uppercase tracking-wider"><span class="bg-secondary px-3 text-dark/40 font-medium">atau daftar dengan</span></div>
            </div>

            <a href="{{ route('auth.google') }}" class="w-full inline-flex items-center justify-center gap-3 py-2.5 border border-slate-200 bg-white rounded-xl text-sm font-semibold text-dark hover:bg-slate-50 transition shadow-sm">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Google
            </a>

            <p class="mt-6 text-center text-sm text-dark/60">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Masuk</a>
            </p>
        </div>
    </div>
</div>
@endsection
