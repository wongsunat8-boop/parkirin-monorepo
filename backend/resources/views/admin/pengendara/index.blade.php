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
                            <details class="inline"><summary class="cursor-pointer text-danger hover:underline list-none">Ajukan Banned</summary>
                                <form method="POST" action="{{ route('admin.pengendara.pengajuan-banned',$p) }}" class="mt-2 flex gap-2 items-center">@csrf
                                    <input name="alasan" required maxlength="100" class="ui-input" placeholder="Alasan">
                                    <button class="bg-danger text-white text-xs px-3 py-1.5 rounded-lg">Kirim</button>
                                </form>
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
