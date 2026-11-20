@extends('layouts.staff')
@section('title', ($owner->exists ? 'Edit' : 'Tambah').' Owner')
@section('page-title', ($owner->exists ? 'Edit' : 'Tambah').' Akun Owner')
@section('page-desc', 'Pengaturan akun kredensial pemilik atau pengelola tempat parkir.')

@section('staff-content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-heading font-extrabold text-base text-[#212121]">
                    {{ $owner->exists ? 'Edit Kredensial Owner' : 'Buat Akun Owner Baru' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Akun ini memiliki hak akses penuh ke dashboard analitik tenant.</p>
            </div>
            <a href="{{ route('super-admin.owners.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ $owner->exists ? route('super-admin.owners.update', $owner) : route('super-admin.owners.store') }}" class="p-6 space-y-4">
            @csrf
            @if($owner->exists)
                @method('PUT')
            @endif

            <div>
                <label class="ui-label">Nama Lengkap Owner <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $owner->nama_lengkap) }}" required placeholder="Contoh: Bpk. Bambang Wijaya" class="ui-input">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">Username Login <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" value="{{ old('username', $owner->username) }}" required placeholder="owner_bambang" class="ui-input font-mono">
                </div>
                <div>
                    <label class="ui-label">Password {{ $owner->exists ? '(Kosongkan jika tetap)' : '*' }}</label>
                    <input type="password" name="password" {{ $owner->exists ? '' : 'required' }} placeholder="Min. 8 karakter" class="ui-input font-mono">
                </div>
            </div>

            <div>
                <label class="ui-label">Tempat Parkir / Tenant Terikat <span class="text-rose-500">*</span></label>
                <select name="id_tempat_parkir" required class="ui-select">
                    <option value="">-- Pilih Tenant --</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id_tempat_parkir }}" @selected(old('id_tempat_parkir', $owner->id_tempat_parkir) == $t->id_tempat_parkir)>
                            {{ $t->nama_tempat }} (ID: #{{ $t->id_tempat_parkir }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Owner hanya dapat memantau data di lokasi tempat parkir yang dipilih.</p>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2.5 text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $owner->status_aktif ?? 1)) class="w-4 h-4 text-primary rounded border-slate-300">
                    <span>Akun Aktif (Dapat Login ke Sistem)</span>
                </label>
            </div>

            <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                <button type="submit" class="ui-btn ui-btn-primary text-xs">Simpan Owner</button>
                <a href="{{ route('super-admin.owners.index') }}" class="ui-btn ui-btn-secondary text-xs">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
