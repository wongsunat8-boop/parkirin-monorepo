@extends('layouts.pengendara')

@section('title', 'Profil — Parkirin')
@section('page-title', 'Profil Pengendara')
@section('page-desc', 'Perbarui informasi akun, nomor telepon, dan keamanan password Anda.')

@section('pengendara-content')
@php $u = auth('pengendara')->user(); @endphp
<style>
  .profil-grid { display:grid; grid-template-columns:1fr; gap:16px; }
  @media (min-width: 1024px){ .profil-grid{ grid-template-columns: repeat(2,minmax(0,1fr)); } }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">Profil Pengendara</h1>
    <p class="page-desc">Kelola identitas akun, kontak (opsional), dan keamanan kata sandi Anda.</p>
  </div>
</div>

<div class="profil-grid">
  {{-- Form Edit Profil & Kontak --}}
  <section class="ui-card">
    <div class="ui-card-h">
      <div class="ui-card-t">Informasi Akun</div>
      <div class="ui-card-d">Perbarui nama, email, username, dan kontak.</div>
    </div>
    <form method="POST" action="{{ route('pengendara.profil.update') }}" class="ui-card-b space-y-3">
      @csrf @method('PATCH')
      
      <div>
        <label class="ui-label">Nama Lengkap</label>
        <input name="nama_lengkap" class="ui-input" value="{{ old('nama_lengkap', $u->nama_lengkap) }}" required maxlength="50">
      </div>

      <div>
        <label class="ui-label">Email</label>
        <input type="email" name="email" class="ui-input" value="{{ old('email', $u->email) }}" required maxlength="100">
      </div>

      <div>
        <label class="ui-label">Username</label>
        <input name="username" class="ui-input" value="{{ old('username', $u->username) }}" placeholder="Contoh: pengendara123" maxlength="50">
        <div class="text-[11px] text-slate-400 mt-1">Digunakan untuk login alternatif selain email.</div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="ui-label mb-0">Nomor Telepon <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
          <span class="text-[11px] text-slate-400">Tanpa OTP</span>
        </div>
        <input name="no_telepon" type="tel" class="ui-input" value="{{ old('no_telepon', $u->no_telepon) }}" placeholder="Contoh: 081234567890" maxlength="20">
        <div class="text-[11.5px] text-slate-500 mt-1">Nomor telepon bersifat opsional untuk keperluan informasi kontak langsung.</div>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" class="ui-btn ui-btn-primary">Simpan Profil</button>
      </div>
    </form>
  </section>

  {{-- Form Edit Password --}}
  <section class="ui-card">
    <div class="ui-card-h">
      <div class="ui-card-t">Keamanan Kata Sandi</div>
      <div class="ui-card-d">Ubah password akun secara mandiri.</div>
    </div>
    <form method="POST" action="{{ route('pengendara.profil.password') }}" class="ui-card-b space-y-3">
      @csrf @method('PATCH')

      <div>
        <label class="ui-label">Password Lama</label>
        <input type="password" name="password_lama" class="ui-input" autocomplete="current-password" placeholder="Masukkan password saat ini" required>
      </div>

      <div>
        <label class="ui-label">Password Baru</label>
        <input type="password" name="password" class="ui-input" autocomplete="new-password" minlength="8" placeholder="Minimal 8 karakter" required>
      </div>

      <div>
        <label class="ui-label">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" class="ui-input" autocomplete="new-password" minlength="8" placeholder="Ulangi password baru" required>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" class="ui-btn ui-btn-primary">Perbarui Password</button>
      </div>
    </form>
  </section>
</div>
@endsection
