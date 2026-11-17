@extends('layouts.pengendara')

@section('title', 'Riwayat Parkir — Parkirin')
@section('page-title', 'Riwayat Parkir')
@section('page-desc', 'Riwayat transaksi parkir dengan filter status, simulasi bayar, dan struk digital.')

@section('pengendara-content')
<div class="page-h">
  <div>
    <h1 class="page-title">Riwayat Parkir</h1>
    <p class="page-desc">Data ditampilkan dengan Cursor/Keyset Pagination untuk performa daftar transaksi.</p>
  </div>
</div>

<form method="GET" action="{{ route('pengendara.riwayat.index') }}" class="bento-card mb-4">
  <div class="grid sm:grid-cols-[1fr,1fr,auto] gap-3 items-end">
    <div>
      <label class="ui-label">Status Parkir</label>
      <select name="status" class="ui-select">
        <option value="">Semua</option>
        <option value="masuk" @selected(request('status')==='masuk')>Masuk</option>
        <option value="keluar" @selected(request('status')==='keluar')>Keluar</option>
      </select>
    </div>
    <div>
      <label class="ui-label">Status Pembayaran</label>
      <select name="status_pembayaran" class="ui-select">
        <option value="">Semua</option>
        <option value="Belum Bayar" @selected(request('status_pembayaran')==='Belum Bayar')>Belum Bayar</option>
        <option value="Sudah Bayar" @selected(request('status_pembayaran')==='Sudah Bayar')>Sudah Bayar</option>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="ui-btn ui-btn-primary">Filter</button>
      <a href="{{ route('pengendara.riwayat.index') }}" class="ui-btn ui-btn-secondary">Reset</a>
    </div>
  </div>
</form>

<div class="ui-card">
  <div class="ui-table-wrap">
    <table class="ui-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Lokasi & Kendaraan</th>
          <th>Waktu</th>
          <th>Biaya</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($riwayat as $t)
          <tr>
            <td><span class="font-mono text-xs font-semibold">#{{ $t->id_transaksi }}</span></td>
            <td>
              <div class="font-semibold text-[13.5px] text-[#212121]">{{ $t->tempatParkir->nama_tempat ?? '-' }}</div>
              <div class="flex items-center gap-2 mt-1 flex-wrap">
                <span class="badge-plat" style="font-size:11px; padding:2px 8px;">{{ $t->kendaraan->plat_nomor ?? '-' }}</span>
                <span class="text-[11.5px] text-slate-500">{{ $t->kendaraan->jenis_kendaraan ?? '' }}</span>
              </div>
            </td>
            <td class="text-[12.5px] text-slate-600 whitespace-nowrap">
              <div>Masuk: {{ $t->waktu_masuk?->format('d/m/Y H:i') ?? '-' }}</div>
              <div>Keluar: {{ $t->waktu_keluar?->format('d/m/Y H:i') ?? '-' }}</div>
              @if($t->durasi_jam)<div class="text-slate-500">Durasi: {{ $t->durasi_jam }} jam</div>@endif
            </td>
            <td class="font-semibold whitespace-nowrap">
              @if(!is_null($t->biaya_total)) Rp {{ number_format((float)$t->biaya_total,0,',','.') }} @else <span class="text-slate-400 font-normal">-</span> @endif
            </td>
            <td>
              <div class="flex flex-col gap-1 items-start">
                <span class="ui-badge {{ $t->status === 'masuk' ? 'ui-badge-info' : 'ui-badge-muted' }}">{{ ucfirst($t->status) }}</span>
                <span class="ui-badge {{ $t->status_pembayaran === 'Sudah Bayar' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $t->status_pembayaran }}</span>
              </div>
            </td>
            <td>
              <div class="flex items-center gap-2 flex-wrap">
                @if($t->status_pembayaran !== 'Sudah Bayar')
                  <form method="POST" action="{{ route('pengendara.riwayat.bayar', $t) }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn-action-success" title="Simulasi pembayaran Cashless/QRIS">Bayar QRIS</button>
                  </form>
                @endif
                <a href="{{ route('pengendara.riwayat.struk', $t) }}" target="_blank" class="btn-action-edit">Struk</a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-slate-500 py-8">Belum ada riwayat parkir.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($riwayat->hasPages() || $riwayat->previousPageUrl())
    <div class="p-4 border-t border-slate-100 flex items-center justify-between gap-3 flex-wrap">
      <div class="text-xs text-slate-500">Cursor pagination (keyset) — halaman transaksi berikut/sebelumnya.</div>
      <div class="flex items-center gap-2">
        @if($riwayat->previousPageUrl())<a href="{{ $riwayat->previousPageUrl() }}" class="ui-btn ui-btn-secondary ui-btn-sm">Sebelumnya</a>@endif
        @if($riwayat->nextPageUrl())<a href="{{ $riwayat->nextPageUrl() }}" class="ui-btn ui-btn-secondary ui-btn-sm">Berikutnya</a>@endif
      </div>
    </div>
  @endif
</div>
@endsection
