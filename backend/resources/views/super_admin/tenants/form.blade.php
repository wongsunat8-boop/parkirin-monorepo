@extends('layouts.staff')
@section('title', ($tenant->exists ? 'Edit' : 'Tambah').' Tenant')
@section('sidebar')
    <a href="{{ route('super-admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Dashboard</a>
    <a href="{{ route('super-admin.tenants.index') }}" class="block px-3 py-2 rounded-lg bg-primary text-white">Kelola Tenant</a>
    <a href="{{ route('super-admin.owners.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Kelola Owner</a>
@endsection
@section('staff-content')
    <h1 class="page-title mb-4">{{ $tenant->exists ? 'Edit' : 'Tambah' }} Tenant</h1>
    <form method="POST" action="{{ $tenant->exists ? route('super-admin.tenants.update',$tenant) : route('super-admin.tenants.store') }}" class="ui-card ui-card-b max-w-2xl space-y-4">
        @csrf
        @if($tenant->exists) @method('PUT') @endif
        <div>
            <label class="ui-label">Nama Tempat Parkir</label>
            <input name="nama_tempat" value="{{ old('nama_tempat',$tenant->nama_tempat) }}" required class="ui-input">
        </div>
        @unless($tenant->exists)
        <div>
            <label class="ui-label">Kapasitas Awal</label>
            <input type="number" min="0" name="kapasitas" value="{{ old('kapasitas',0) }}" class="ui-input">
        </div>
        @endunless
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="sediakan_booking" value="1" @checked(old('sediakan_booking',$tenant->sediakan_booking ?? 1))> Sediakan Booking</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif',$tenant->status_aktif ?? 1))> Aktif</label>
        <div class="flex gap-2 pt-2">
            <button class="ui-btn ui-btn-primary">Simpan</button>
            <a href="{{ route('super-admin.tenants.index') }}" class="ui-btn ui-btn-secondary">Batal</a>
        </div>
    </form>
@endsection
