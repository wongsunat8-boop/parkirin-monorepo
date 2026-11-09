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
                            <details class="group relative inline-block text-left">
                                <summary class="btn-action-danger cursor-pointer list-none select-none">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Ajukan Banned</span>
                                </summary>
                                <div class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-slate-200 p-3 z-20">
                                    <div class="text-xs font-bold text-slate-800 mb-2">Alasan Pengajuan Banned</div>
                                    <form method="POST" action="{{ route('admin.kendaraan.pengajuan-banned',$k) }}" class="space-y-2">
                                        @csrf
                                        <input name="alasan" required maxlength="100" class="ui-input text-xs" placeholder="Contoh: Parkir liar / tarif mangkir">
                                        <div class="flex justify-end gap-1.5">
                                            <button type="submit" class="btn-action-danger">
                                                <span>Kirim ke Owner</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
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
