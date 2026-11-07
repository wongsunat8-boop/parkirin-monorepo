@extends('layouts.app')
@section('title', 'Konfigurasi Google OAuth — Parkirin')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-secondary">
    <div class="w-full max-w-xl bg-white rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-10">
        {{-- Icon & Header --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-warning-bg border border-warning/30 flex items-center justify-center text-warning">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h1 class="font-heading text-xl sm:text-2xl font-bold text-dark">Google Client ID Belum Diatur</h1>
                <p class="text-xs sm:text-sm text-dark/60">Kredensial OAuth 2.0 belum diisi pada file <code class="px-1.5 py-0.5 bg-slate-100 rounded text-primary font-mono font-semibold">.env</code></p>
            </div>
        </div>

        {{-- Penjelasan Singkat --}}
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm text-dark/70 space-y-2 mb-6">
            <p>Error <strong class="text-danger font-semibold">"Missing required parameter: client_id"</strong> terjadi karena variabel <code class="font-mono text-dark font-semibold">GOOGLE_CLIENT_ID</code> di backend masih kosong.</p>
            <p>Untuk kemudahan pengujian fitur <strong>Onboarding Username Pengendara</strong> di lingkungan lokal/pengembangan, Anda dapat langsung menggunakan <strong>Simulasi Akun Google (Dev Mode)</strong> di bawah ini.</p>
        </div>

        {{-- Opsi 1: Simulasi Cepat (Dev Mode) --}}
        <div class="p-5 bg-primary/5 border border-primary/20 rounded-2xl mb-6">
            <h2 class="font-heading font-bold text-sm text-primary mb-3 flex items-center gap-2">
                <span>🚀</span> Opsi 1: Lanjutkan via Simulasi Cepat (Dev Mode)
            </h2>
            <form method="POST" action="{{ route('auth.google') }}" class="space-y-3">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-semibold text-dark/70 mb-1">Nama Pengendara Google</label>
                    <input type="text" id="name" name="name" value="Ghaniyy Albar" required
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                </div>
                <div>
                    <label for="email" class="block text-xs font-semibold text-dark/70 mb-1">Email Google</label>
                    <input type="email" id="email" name="email" value="ghaniyyalbar@gmail.com" required
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                </div>
                <button type="submit" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 text-xs sm:text-sm">
                    Simulasikan Login Google & Lanjut ke Onboarding &rarr;
                </button>
            </form>
        </div>

        {{-- Opsi 2: Konfigurasi Riil --}}
        <div class="border-t border-slate-200 pt-5">
            <h2 class="font-heading font-bold text-sm text-dark mb-2 flex items-center gap-2">
                <span>⚙️</span> Opsi 2: Cara Mengaktifkan Google OAuth Asli di .env
            </h2>
            <ol class="list-decimal list-inside text-xs text-dark/70 space-y-1.5 leading-relaxed">
                <li>Buka <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-primary font-semibold hover:underline">Google Cloud Console &rarr; Credentials</a>.</li>
                <li>Buat <strong>OAuth 2.0 Client ID</strong> (Application type: <em>Web application</em>).</li>
                <li>Tambahkan Authorized Redirect URI: <code class="bg-slate-100 px-1 py-0.5 rounded text-dark font-mono font-semibold">{{ url('/auth/google/callback') }}</code></li>
                <li>Buka file <code class="bg-slate-100 px-1 py-0.5 rounded text-dark font-mono font-semibold">backend/.env</code>, lalu isi nilai:
                    <pre class="mt-2 p-2.5 bg-dark text-slate-200 rounded-xl font-mono text-[11px] overflow-x-auto">GOOGLE_CLIENT_ID=isi_client_id_anda.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=isi_client_secret_anda
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"</pre>
                </li>
            </ol>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-dark/60 hover:text-primary transition">&larr; Kembali ke Halaman Login</a>
        </div>
    </div>
</div>
@endsection
