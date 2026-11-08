@extends('layouts.staff')
@section('title', ($petugas->exists?'Edit':'Tambah').' Petugas')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <h1 class="page-title mb-4">{{ $petugas->exists?'Edit':'Tambah' }} Petugas</h1>
    <form method="POST" action="{{ $petugas->exists ? route('admin.petugas.update',$petugas) : route('admin.petugas.store') }}" class="ui-card ui-card-b max-w-2xl space-y-4">
        @csrf @if($petugas->exists) @method('PUT') @endif
        <div><label class="ui-label">Nama Lengkap</label><input name="nama_lengkap" value="{{ old('nama_lengkap',$petugas->nama_lengkap) }}" required class="ui-input"></div>
        <div><label class="ui-label">Username</label><input name="username" value="{{ old('username',$petugas->username) }}" required class="ui-input"></div>
        <div><label class="ui-label">Password {{ $petugas->exists?'(kosongkan jika tidak diubah)':'' }}</label><input type="password" name="password" {{ $petugas->exists?'':'required' }} class="ui-input"></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif',$petugas->status_aktif ?? 1))> Aktif</label>
        <div class="flex gap-2"><button class="ui-btn ui-btn-primary">Simpan</button><a href="{{ route('admin.petugas.index') }}" class="ui-btn ui-btn-secondary">Batal</a></div>
    </form>
@endsection
