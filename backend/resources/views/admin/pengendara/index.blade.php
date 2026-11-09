@extends('layouts.staff')
@section('title','Daftar Pengendara')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <h1 class="page-title">Daftar Pengendara</h1>
    <p class="text-sm text-gray-500 mb-4">Pengendara yang pernah bertransaksi di tenant ini, beserta frekuensi parkir.</p>
    <form method="GET" class="ui-card ui-card-b mb-4 flex gap-2 items-end"><input name="q" value="{{ request('q') }}" class="ui-input" placeholder="Cari nama/username/email"><button class="ui-btn ui-btn-dark ui-btn-sm">Cari</button></form>
    <div class="ui-card overflow-hidden">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr><th>Pengendara</th><th>Kendaraan</th><th>Frekuensi Parkir</th><th>Skor Reputasi</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($pengendaras as $p)
                    <tr>
                        <td><div class="font-medium">{{ $p->nama_lengkap }}</div><div class="ui-label">{{ $p->username ?? $p->email }}</div></td>
                        <td>@foreach($p->kendaraan->take(2) as $k)<span class="badge-plat mr-1 mb-1">{{ $k->plat_nomor }}</span>@endforeach @if($p->kendaraan->count()>2)<span class="ui-label">+{{ $p->kendaraan->count()-2 }}</span>@endif</td>
                        <td class="font-semibold">{{ $p->frekuensi_parkir }}x</td>
                        <td>{{ $p->skor_reputasi }}</td>
                        <td>
                            <details class="group relative inline-block text-left">
                                <summary class="btn-action-danger cursor-pointer list-none select-none">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Ajukan Banned</span>
                                </summary>
                                <div class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-slate-200 p-3 z-20">
                                    <div class="text-xs font-bold text-slate-800 mb-2">Alasan Pengajuan Banned</div>
                                    <form method="POST" action="{{ route('admin.pengendara.pengajuan-banned',$p) }}" class="space-y-2">
                                        @csrf
                                        <input name="alasan" required maxlength="100" class="ui-input text-xs" placeholder="Contoh: Pelanggaran tata tertib / skor rendah">
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
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada pengendara dengan transaksi di tenant ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-gray-100">{{ $pengendaras->links() }}</div>
    </div>
@endsection
