@extends('layouts.app')
@section('title', 'Lengkapi Profil — Parkirin')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-secondary p-6">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200 p-8">
        {{-- Navigasi Back to Landing Page --}}
        <div class="mb-5 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-bold text-dark/60 hover:text-primary transition group bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1 text-primary" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </a>
            <span class="text-xs font-bold text-success bg-success-bg px-2.5 py-1 rounded-md">Google Terhubung</span>
        </div>

        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4 border border-primary/20">
                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h1 class="font-heading text-2xl font-bold text-dark">Halo, {{ $pengendara->nama_lengkap }}!</h1>
            <p class="mt-2 text-sm text-dark/60">Satu langkah lagi — pilih username unik untuk akun Parkirin Anda.</p>
        </div>

        @if(isset($errors) && $errors->any())
        <div class="mb-5 p-3.5 bg-danger-bg border border-danger/20 rounded-xl space-y-1">
            @foreach($errors->all() as $error)
            <p class="text-sm text-danger font-medium">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('auth.google.onboarding.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block text-sm font-medium text-dark/70 mb-1.5">Pilih Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm" placeholder="Contoh: pengendara_hebat">
                <p class="mt-1.5 text-xs text-dark/50">Hanya huruf, angka, tanda strip, dan garis bawah.</p>
            </div>
            <button type="submit" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20">Simpan & Masuk Dashboard</button>
        </form>
    </div>
</div>
@endsection
