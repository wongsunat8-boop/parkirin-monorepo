@extends('layouts.staff')
@section('title','Kelola Kendaraan')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <h1 class="page-title">Kelola Kendaraan</h1>
    <p class="text-sm text-gray-500 mb-4">Input pelanggan offline dan ajukan banned bila diperlukan.</p>

    <div class="ui-card ui-card-b mb-4">
        <h2 class="font-semibold mb-3">Input Kendaraan Pelanggan Offline</h2>
        <form method="POST" action="{{ route('admin.kendaraan.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Plat Nomor</label><input name="plat_nomor" value="{{ old('plat_nomor') }}" required class="ui-input uppercase" placeholder="B 1234 XYZ"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Jenis</label><select name="jenis_kendaraan" required class="ui-input">@foreach(['Motor','Mobil','Sepeda'] as $j)<option value="{{ $j }}" @selected(old('jenis_kendaraan')===$j)>{{ $j }}</option>@endforeach</select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Warna</label><input name="warna" value="{{ old('warna') }}" required class="ui-input" placeholder="Hitam"></div>
            <button class="ui-btn ui-btn-primary text-sm">Tambah</button>
        </form>
    </div>

    <form method="GET" class="ui-card ui-card-b mb-4 flex flex-wrap gap-2 items-end">
        <input name="q" value="{{ request('q') }}" class="ui-input" placeholder="Cari plat nomor">
        <select name="jenis" class="ui-input"><option value="">Semua jenis</option>@foreach(['Motor','Mobil','Sepeda'] as $j)<option value="{{ $j }}" @selected(request('jenis')===$j)>{{ $j }}</option>@endforeach</select>
        <button class="ui-btn ui-btn-dark ui-btn-sm">Filter</button>
    </form>

    <div class="ui-card overflow-hidden">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr><th>Plat</th><th>Jenis/Warna</th><th>Pemilik</th><th>Frekuensi (tenant)</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($kendaraans as $k)
                    <tr>
                        <td><span class="badge-plat">{{ $k->plat_nomor }}</span></td>
                        <td>{{ $k->jenis_kendaraan }} / {{ $k->warna }}</td>
                        <td>{{ $k->pengendara->nama_lengkap ?? 'Offline/Belum klaim' }}</td>
                        <td>{{ $k->transaksi_count }}</td>
                        <td>
                            <details class="inline">
                                <summary class="cursor-pointer text-danger hover:underline list-none">Ajukan Banned</summary>
                                <form method="POST" action="{{ route('admin.kendaraan.pengajuan-banned',$k) }}" class="mt-2 flex gap-2 items-center">@csrf
                                    <input name="alasan" required maxlength="100" class="ui-input" placeholder="Alasan (maks 100)">
                                    <button class="bg-danger text-white text-xs px-3 py-1.5 rounded-lg">Kirim</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada data kendaraan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-gray-100">{{ $kendaraans->links() }}</div>
    </div>
@endsection
