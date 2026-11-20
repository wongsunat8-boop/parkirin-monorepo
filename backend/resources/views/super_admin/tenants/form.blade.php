@extends('layouts.staff')
@section('title', ($tenant->exists ? 'Edit' : 'Tambah').' Lokasi Parkir')
@section('page-title', ($tenant->exists ? 'Edit' : 'Tambah').' Lokasi Parkir')
@section('page-desc', 'Master data lokasi dan identitas pengelola tempat parkir.')

@section('staff-content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-heading font-extrabold text-base text-[#212121]">
                    {{ $tenant->exists ? 'Edit Data Lokasi Parkir' : 'Tambah Lokasi Parkir Baru' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Lengkapi profil tempat parkir swadaya / mitra.</p>
            </div>
            <a href="{{ route('super-admin.tenants.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ $tenant->exists ? route('super-admin.tenants.update', $tenant) : route('super-admin.tenants.store') }}" class="p-6 space-y-4">
            @csrf
            @if($tenant->exists)
                @method('PUT')
            @endif

            <div>
                <label class="ui-label">Nama Tempat Parkir <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_tempat" value="{{ old('nama_tempat', $tenant->nama_tempat) }}" required placeholder="Contoh: Parkiran Stasiun Sudimara" class="ui-input">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">Kapasitas Reguler (Slot)</label>
                    <input type="number" min="0" name="kapasitas" value="{{ old('kapasitas', $tenant->kapasitas_reguler ?? 0) }}" class="ui-input font-bold text-slate-800">
                </div>
                <div>
                    <label class="ui-label">Kuota Inap (Slot)</label>
                    <input type="number" min="0" name="kuota_inap" value="{{ old('kuota_inap', $tenant->kuota_inap ?? 0) }}" class="ui-input font-bold text-slate-800">
                </div>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-100">
                <label class="flex items-center gap-2.5 text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <input type="checkbox" name="sediakan_booking" value="1" @checked(old('sediakan_booking', $tenant->sediakan_booking_inap ?? 1)) class="w-4 h-4 text-primary rounded border-slate-300">
                    <span>Sediakan Layanan Booking Parkir Inap</span>
                </label>
                <label class="flex items-center gap-2.5 text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <input type="checkbox" name="sediakan_member" value="1" @checked(old('sediakan_member', $tenant->sediakan_member ?? 1)) class="w-4 h-4 text-primary rounded border-slate-300">
                    <span>Sediakan Layanan Pembelian Member Pass</span>
                </label>
                <label class="flex items-center gap-2.5 text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $tenant->status_aktif ?? 1)) class="w-4 h-4 text-primary rounded border-slate-300">
                    <span>Status Tenant Aktif (Siap Operasional)</span>
                </label>
            </div>

            <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                <button type="submit" class="ui-btn ui-btn-primary text-xs">Simpan Tenant</button>
                <a href="{{ route('super-admin.tenants.index') }}" class="ui-btn ui-btn-secondary text-xs">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
