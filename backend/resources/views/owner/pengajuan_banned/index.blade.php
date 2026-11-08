@extends('layouts.staff')
@section('title','Pengajuan Banned')
@section('page-title','Kelola Pengajuan Banned')
@section('page-desc','Tinjau, setujui, tolak, atau cabut pengajuan. Persetujuan mengurangi skor reputasi pengendara (-10).')

@section('staff-content')
    <form method="GET" class="ui-card ui-card-b mb-4 flex flex-wrap gap-3 items-end">
        <div class="min-w-[180px]"><label class="ui-label">Status</label>
            <select name="status" class="ui-select"><option value="">Semua</option>@foreach(['Pending','Disetujui','Ditolak','Dicabut'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select>
        </div>
        <button class="ui-btn ui-btn-dark">Filter</button>
    </form>
    <div class="ui-card overflow-hidden">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr><th>Pengaju</th><th>Target</th><th>Alasan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($pengajuans as $p)
                    <tr>
                        <td>{{ $p->pengaju->nama_lengkap ?? '-' }}</td>
                        <td>
                            @if($p->kendaraan)<span class="badge-plat mr-1">{{ $p->kendaraan->plat_nomor }}</span>@endif
                            <div class="text-gray-700">{{ $p->pengendara->nama_lengkap ?? 'Tanpa akun' }}</div>
                            @if($p->pengendara)<div class="ui-label">Skor: {{ $p->pengendara->skor_reputasi }}</div>@endif
                        </td>
                        <td class="max-w-xs">{{ $p->alasan }}</td>
                        <td class="whitespace-nowrap">{{ optional($p->tanggal_pengajuan)->format('d/m/Y H:i') }}</td>
                        <td><span class="text-xs px-2 py-0.5 rounded-full {{ $p->status_pengajuan==='Pending'?'bg-warning-bg text-warning':($p->status_pengajuan==='Disetujui'?'bg-danger-bg text-danger':($p->status_pengajuan==='Ditolak'?'bg-gray-100 text-gray-600':'bg-blue-50 text-primary')) }}">{{ $p->status_pengajuan }}</span></td>
                        <td class="whitespace-nowrap">
                            @if($p->status_pengajuan === 'Pending')
                                <form method="POST" action="{{ route('owner.pengajuan-banned.approve',$p) }}" class="inline">@csrf @method('PATCH')<button class="ui-badge ui-badge-success hover:underline mr-2" onclick="return confirm('Setujui pengajuan ini? Skor reputasi pengendara akan dikurangi.')">Setujui</button></form>
                                <form method="POST" action="{{ route('owner.pengajuan-banned.reject',$p) }}" class="inline">@csrf @method('PATCH')<button class="text-gray-600 hover:underline">Tolak</button></form>
                            @elseif($p->status_pengajuan === 'Disetujui')
                                <form method="POST" action="{{ route('owner.pengajuan-banned.revoke',$p) }}" class="inline">@csrf @method('PATCH')<button class="ui-link" onclick="return confirm('Cabut status banned ini?')">Cabut</button></form>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada pengajuan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-gray-100">{{ $pengajuans->links() }}</div>
    </div>
@endsection
