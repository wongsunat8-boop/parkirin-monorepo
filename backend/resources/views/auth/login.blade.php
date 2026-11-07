@extends('layouts.app')
@section('title', 'Masuk — Parkirin')

@section('content')
<div class="min-h-screen flex">
    {{-- Left Panel: Form --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            {{-- Navigasi Back to Landing Page --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-bold text-dark/60 hover:text-primary transition group bg-white border border-slate-200/80 px-3 py-1.5 rounded-lg shadow-sm">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1 text-primary" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Beranda
                </a>
                <span class="text-xs font-bold text-primary bg-primary/10 px-2.5 py-1 rounded-md">Portal Parkirin</span>
            </div>

            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 mb-6">
                <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-md shadow-primary/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
                </div>
                <span class="font-heading font-black text-2xl text-dark tracking-tight">Park<span class="text-primary">irin</span></span>
            </a>

            <h1 class="font-heading text-2xl sm:text-3xl font-bold text-dark">Selamat Datang Kembali</h1>
            <p class="mt-1.5 text-sm text-dark/60">Masuk ke sistem pengelolaan dan pemesanan parkir</p>

            {{-- Tab selector --}}
            <div class="mt-6 flex bg-slate-100 rounded-xl p-1 border border-slate-200">
                <button type="button" id="tab-pengendara" onclick="switchTab('pengendara')" class="flex-1 py-2.5 text-sm font-semibold rounded-lg transition tab-active">Pengendara</button>
                <button type="button" id="tab-user" onclick="switchTab('user')" class="flex-1 py-2.5 text-sm font-semibold rounded-lg transition text-dark/60">Staf / Manajemen</button>
            </div>

            @if(isset($errors) && $errors->any())
            <div class="mt-4 p-3.5 bg-danger-bg border border-danger/20 rounded-xl">
                <p class="text-sm text-danger font-medium">{{ $errors->first() }}</p>
            </div>
            @endif

            {{-- Pengendara Login Form (Default) --}}
            <form id="form-pengendara" method="POST" action="{{ route('login.pengendara') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-dark/70 mb-1.5">Email atau Username</label>
                    <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="email@contoh.com atau username">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password-pengendara" class="text-sm font-medium text-dark/70">Password</label>
                    </div>
                    <input type="password" id="password-pengendara" name="password" required
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Masukkan password">
                </div>
                <button type="submit" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20">Masuk sebagai Pengendara</button>

                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                    <div class="relative flex justify-center text-xs uppercase tracking-wider"><span class="bg-secondary px-3 text-dark/40 font-medium">atau masuk dengan</span></div>
                </div>

                <a href="{{ route('auth.google') }}" class="w-full inline-flex items-center justify-center gap-3 py-3 border border-slate-200 bg-white rounded-xl text-sm font-semibold text-dark hover:bg-slate-50 transition shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    Google
                </a>
            </form>

            {{-- User (Staff) Login Form --}}
            <form id="form-user" method="POST" action="{{ route('login.user') }}" class="mt-6 space-y-4 hidden">
                @csrf
                <div>
                    <label for="username" class="block text-sm font-medium text-dark/70 mb-1.5">Username Petugas / Admin / Owner</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Masukkan username staf">
                </div>
                <div>
                    <label for="password-user" class="block text-sm font-medium text-dark/70 mb-1.5">Password</label>
                    <input type="password" id="password-user" name="password" required
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Masukkan password staf">
                </div>
                <button type="submit" class="w-full py-3 bg-dark text-white font-semibold rounded-xl hover:bg-black transition shadow-md">Masuk Portal Staf</button>
            </form>

            <p class="mt-8 text-center text-sm text-dark/60">
                Belum punya akun pengendara? <a href="{{ route('register') }}" class="text-primary font-semibold hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>

    {{-- Right Panel: Visual Hero Card --}}
    <div class="hidden lg:flex flex-1 bg-gradient-to-br from-primary via-primary-dark to-primary items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full bg-accent"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-white"></div>
        </div>
        <div class="relative text-center text-white max-w-md">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center mx-auto mb-6 border border-white/20 shadow-lg">
                <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <h2 class="font-heading text-3xl font-extrabold">Parkirin SaaS</h2>
            <p class="mt-3 text-white/80 leading-relaxed text-sm">Sistem manajemen parkir multi-tenant modern dengan isolasi data yang aman, karcis QR digital, dan kontrol penuh untuk operator maupun pengendara.</p>
            <div class="mt-8 inline-flex items-center gap-3 px-4 py-2 rounded-full bg-white/10 border border-white/15 text-xs text-white/90">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                Multi-Tenant Architecture & Anti-IDOR Protection
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
            tabUser.classList.remove('text-dark/60');
            tabPengendara.classList.remove('tab-active');
            tabPengendara.classList.add('text-dark/60');
        } else {
            userForm.classList.add('hidden');
            pengendaraForm.classList.remove('hidden');
            tabPengendara.classList.add('tab-active');
            tabPengendara.classList.remove('text-dark/60');
            tabUser.classList.remove('tab-active');
            tabUser.classList.add('text-dark/60');
        }
    }

    // Auto-switch tab if URL has ?tab=user
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'user' || urlParams.get('tab') === 'staf') {
            switchTab('user');
        }
    });
</script>
@endpush

@push('styles')
<style>
    .tab-active {
        background: #FFFFFF;
        color: #0D63A5;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.08);
    }
</style>
@endpush
@endsection
