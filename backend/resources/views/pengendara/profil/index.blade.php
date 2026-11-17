@extends('layouts.pengendara')

@section('title', 'Profil — Parkirin')
@section('page-title', 'Profil Page')
@section('page-desc', 'Perbarui profil, password, dan nomor telepon dengan verifikasi OTP.')

@section('pengendara-content')
@php $u = auth('pengendara')->user(); @endphp
<style>
  .profil-grid { display:grid; grid-template-columns:1fr; gap:16px; }
  @media (min-width: 1024px){ .profil-grid{ grid-template-columns: repeat(2,minmax(0,1fr)); } .profil-span-2{ grid-column: span 2; } }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">Profil Pengendara</h1>
    <p class="page-desc">Kelola informasi akun, keamanan password, dan verifikasi nomor telepon.</p>
  </div>
</div>

<div class="profil-grid" x-data="{ otpModal: {{ session('profil_otp_phone') ? 'true' : 'false' }} }">
  <section class="ui-card">
    <div class="ui-card-h">
      <div class="ui-card-t">Edit Profil</div>
      <div class="ui-card-d">Perbarui nama, email, dan username.</div>
    </div>
    <form method="POST" action="{{ route('pengendara.profil.update') }}" class="ui-card-b space-y-3">
      @csrf @method('PATCH')
      <div><label class="ui-label">Nama Lengkap</label><input name="nama_lengkap" class="ui-input" value="{{ old('nama_lengkap', $u->nama_lengkap) }}" required></div>
      <div><label class="ui-label">Email</label><input type="email" name="email" class="ui-input" value="{{ old('email', $u->email) }}" required></div>
      <div><label class="ui-label">Username</label><input name="username" class="ui-input" value="{{ old('username', $u->username) }}" placeholder="Opsional"></div>
      <div class="flex justify-end"><button type="submit" class="ui-btn ui-btn-primary">Simpan Profil</button></div>
    </form>
  </section>

  <section class="ui-card">
    <div class="ui-card-h">
      <div class="ui-card-t">Edit Password</div>
      <div class="ui-card-d">Validasi password lama diperlukan sebelum mengganti password.</div>
    </div>
    <form method="POST" action="{{ route('pengendara.profil.password') }}" class="ui-card-b space-y-3">
      @csrf @method('PATCH')
      <div><label class="ui-label">Password Lama</label><input type="password" name="password_lama" class="ui-input" autocomplete="current-password" required></div>
      <div><label class="ui-label">Password Baru</label><input type="password" name="password" class="ui-input" autocomplete="new-password" minlength="8" required></div>
      <div><label class="ui-label">Konfirmasi Password Baru</label><input type="password" name="password_confirmation" class="ui-input" autocomplete="new-password" minlength="8" required></div>
      <div class="flex justify-end"><button type="submit" class="ui-btn ui-btn-primary">Perbarui Password</button></div>
    </form>
  </section>

  <section class="ui-card profil-span-2">
    <div class="ui-card-h flex items-start justify-between gap-3 flex-wrap">
      <div>
        <div class="ui-card-t">Edit No. Telepon</div>
        <div class="ui-card-d">Nomor telepon tidak unik di database (mendukung daur ulang nomor operator). Perubahan diverifikasi dengan OTP yang dikirim ke nomor tujuan.</div>
      </div>
      <span class="ui-badge ui-badge-info">Nomor saat ini: {{ $u->no_telepon ?: 'Belum diisi' }}</span>
    </div>
    <div class="ui-card-b">
      <div class="grid lg:grid-cols-2 gap-4">
        <form method="POST" action="{{ route('pengendara.profil.telepon.otp') }}" class="space-y-3 rounded-xl border border-slate-200 p-4 bg-slate-50/40">
          @csrf
          <div>
            <label class="ui-label">Nomor Telepon Baru</label>
            <input name="no_telepon" type="tel" class="ui-input" value="{{ old('no_telepon', session('profil_otp_phone', $u->no_telepon)) }}" placeholder="Contoh: 081234567890" inputmode="numeric" pattern="^08[0-9]{8,11}$" minlength="10" maxlength="13" title="Awalan 08, panjang 10-13 digit" required>
            <div class="text-[11.5px] text-slate-500 mt-1.5">Format wajib: awalan <strong>08</strong>, total <strong>10–13 digit</strong> (contoh: 081234567890).</div>
          </div>
          <div class="ui-alert ui-alert-warning text-xs">Kode OTP 6 digit akan dikirim ke nomor yang Anda masukkan dan berlaku 5 menit.</div>
          <button type="submit" class="ui-btn ui-btn-secondary w-full sm:w-auto">Kirim OTP</button>
        </form>

        <form method="POST" action="{{ route('pengendara.profil.telepon') }}" class="space-y-3 rounded-xl border border-slate-200 p-4 bg-white">
          @csrf @method('PATCH')
          <div>
            <label class="ui-label">Nomor Telepon (sesuai OTP)</label>
            <input name="no_telepon" type="tel" class="ui-input" value="{{ old('no_telepon', session('profil_otp_phone', $u->no_telepon)) }}" placeholder="Nomor yang menerima OTP" inputmode="numeric" pattern="^08[0-9]{8,11}$" minlength="10" maxlength="13" required>
          </div>
          <div>
            <label class="ui-label">Kode OTP</label>
            <input name="kode_otp" class="ui-input font-mono tracking-[0.2em]" maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="6 digit" required>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <button type="submit" class="ui-btn ui-btn-primary">Verifikasi & Simpan Nomor</button>
            <button type="button" class="ui-btn ui-btn-ghost" @click="otpModal=true">Bantuan OTP</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <div x-cloak x-show="otpModal" class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4" @keydown.escape.window="otpModal=false">
    <div class="absolute inset-0 bg-slate-900/45 backdrop-blur-sm" @click="otpModal=false"></div>
    <div class="relative w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden">
      <div class="ui-card-h flex items-center justify-between gap-3">
        <div><div class="ui-card-t">Bantuan Verifikasi OTP</div><div class="ui-card-d">Alur verifikasi nomor telepon pengendara.</div></div>
        <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="otpModal=false">Tutup</button>
      </div>
      <div class="ui-card-b space-y-3 text-sm text-slate-700">
        <ol class="list-decimal pl-5 space-y-1.5">
          <li>Masukkan nomor telepon baru berawalan <strong>08</strong> (10–13 digit), lalu klik <strong>Kirim OTP</strong>.</li>
          <li>Kode OTP 6 digit akan dikirim ke nomor tujuan dan berlaku selama 5 menit.</li>
          <li>Masukkan kode OTP dan nomor yang sama, lalu klik <strong>Verifikasi & Simpan Nomor</strong>.</li>
        </ol>
        @if(session('profil_otp_phone'))
          <div class="ui-alert ui-alert-warning text-xs">OTP aktif dikirim ke <span class="font-mono font-semibold">{{ session('profil_otp_phone') }}</span>. Periksa pesan masuk pada nomor tersebut.</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
