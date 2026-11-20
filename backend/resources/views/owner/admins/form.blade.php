@extends('layouts.staff')
@section('title', ($admin->exists?'Edit':'Tambah').' Admin')
@section('sidebar')
    <a href="{{ route('owner.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Dashboard</a>
    <a href="{{ route('owner.admins.index') }}" class="block px-3 py-2 rounded-lg bg-primary text-white">Kelola Admin</a>
    <a href="{{ route('owner.transaksi.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Transaksi Parkir</a>
@endsection
@section('staff-content')
    <h1 class="page-title mb-4">{{ $admin->exists?'Edit':'Tambah' }} Admin</h1>
    <form method="POST" action="{{ $admin->exists ? route('owner.admins.update',$admin) : route('owner.admins.store') }}" class="ui-card ui-card-b max-w-2xl space-y-4">
        @csrf @if($admin->exists) @method('PUT') @endif
        <div><label class="ui-label">Nama Lengkap</label><input name="nama_lengkap" value="{{ old('nama_lengkap',$admin->nama_lengkap) }}" required class="ui-input"></div>
        <div><label class="ui-label">Username</label><input name="username" value="{{ old('username',$admin->username) }}" required class="ui-input"></div>
        <div><label class="ui-label">Password {{ $admin->exists?'(kosongkan jika tidak diubah)':'' }}</label><input type="password" name="password" {{ $admin->exists?'':'required' }} class="ui-input"></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif',$admin->status_aktif ?? 1))> Aktif</label>
        <div class="flex gap-2"><button class="ui-btn ui-btn-primary">Simpan</button><a href="{{ route('owner.admins.index') }}" class="ui-btn ui-btn-secondary">Batal</a></div>
    </form>
@endsection
