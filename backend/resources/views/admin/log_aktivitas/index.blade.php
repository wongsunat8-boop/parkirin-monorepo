@extends('layouts.staff')
@section('title','Log Aktivitas Parkir')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <h1 class="page-title">Log Aktivitas Parkir</h1>
    <p class="text-sm text-slate-500 mb-4">Catatan riwayat aktivitas pengguna sistem dan transaksi petugas di lokasi ini.</p>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="ui-card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 font-semibold">Log Aktivitas (Sistem/User)</div>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th>Waktu</th><th>User</th><th>Aktivitas</th></tr></thead>
                    <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-xs text-slate-700 font-mono font-medium">{{ $log->waktu_aktivitas ? \Carbon\Carbon::parse($log->waktu_aktivitas)->format('d/m/Y H:i:s') : '-' }}</td>
                            <td><div class="font-medium">{{ $log->user->nama_lengkap ?? '-' }}</div><div class="ui-label">{{ $log->user->role ?? '' }}</div></td>
                            <td>{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada log.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-gray-100 flex items-center justify-between text-sm">
                @if($logs->previousPageUrl())<a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">&larr; Sebelumnya</a>@else<span></span>@endif
                @if($logs->nextPageUrl())<a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">Berikutnya &rarr;</a>@endif
            </div>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 font-semibold">Aktivitas Transaksi</div>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th>Waktu</th><th>Plat</th><th>Status</th><th>Petugas</th></tr></thead>
                    <tbody>
                    @forelse($transaksis as $t)
                        <tr>
                            <td class="whitespace-nowrap text-xs text-gray-600">{{ optional($t->status==='keluar' && $t->waktu_keluar ? $t->waktu_keluar : $t->waktu_masuk)->format('d/m/Y H:i') }}</td>
                            <td><span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span></td>
                            <td><span class="text-xs px-2 py-0.5 rounded-full {{ $t->status==='masuk'?'bg-warning-bg text-warning':'bg-success-bg text-success' }}">{{ $t->status }}</span></td>
                            <td>{{ $t->petugas->nama_lengkap ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-gray-100 flex items-center justify-between text-sm">
                @if($transaksis->previousPageUrl())<a href="{{ $transaksis->previousPageUrl() }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">&larr; Sebelumnya</a>@else<span></span>@endif
                @if($transaksis->nextPageUrl())<a href="{{ $transaksis->nextPageUrl() }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">Berikutnya &rarr;</a>@endif
            </div>
        </div>
    </div>
@endsection
