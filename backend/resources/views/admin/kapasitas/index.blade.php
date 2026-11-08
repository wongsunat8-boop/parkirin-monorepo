@extends('layouts.staff')
@section('title','Kelola Kapasitas')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <h1 class="page-title">Kelola Kapasitas</h1>
    <p class="text-sm text-gray-500 mb-4">{{ $tenant->nama_tempat }}. Tombol Tambah terkunci setelah kapasitas &gt; 0.</p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="ui-card ui-card-b">
            <h2 class="font-semibold mb-3">Kapasitas Awal (Tambah)</h2>
            @if($tenant->kapasitas > 0)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                    Kapasitas sudah diatur ({{ $tenant->kapasitas }}). Penambahan awal terkunci; gunakan form <strong>Edit</strong>.
                </div>
                <button disabled class="mt-3 w-full bg-gray-200 text-gray-500 font-semibold px-4 py-3 rounded-lg cursor-not-allowed">Tambah Terkunci</button>
            @else
                <form method="POST" action="{{ route('admin.kapasitas.store') }}" class="space-y-3">
                    @csrf
                    <div><label class="ui-label">Kapasitas Awal</label><input type="number" min="1" name="kapasitas" value="{{ old('kapasitas') }}" required class="ui-input"></div>
                    <button class="ui-btn ui-btn-primary">Tambah Kapasitas</button>
                </form>
            @endif
        </div>

        <div class="ui-card ui-card-b">
            <h2 class="font-semibold mb-3">Edit Kapasitas & Booking</h2>
            <form method="POST" action="{{ route('admin.kapasitas.update') }}" class="space-y-3 mb-4">
                @csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="ui-label">Kapasitas</label><input type="number" min="0" name="kapasitas" value="{{ old('kapasitas',$tenant->kapasitas) }}" required class="ui-input"></div>
                    <div><label class="ui-label">Terisi</label><input type="number" min="0" name="terisi" value="{{ old('terisi',$tenant->terisi) }}" required class="ui-input"></div>
                </div>
                <button class="ui-btn ui-btn-dark">Simpan Perubahan</button>
            </form>
            <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Sediakan Booking</p>
                    <p class="ui-label">Status: <strong>{{ $tenant->sediakan_booking ? 'Aktif' : 'Nonaktif' }}</strong></p>
                </div>
                <form method="POST" action="{{ route('admin.kapasitas.toggle-booking') }}">@csrf @method('PATCH')
                    <button class="px-4 py-3 rounded-lg text-sm font-semibold {{ $tenant->sediakan_booking ? 'bg-warning-bg text-warning' : 'bg-success-bg text-success' }}">{{ $tenant->sediakan_booking ? 'Matikan' : 'Aktifkan' }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
