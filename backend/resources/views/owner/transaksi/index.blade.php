@extends('layouts.staff')
@section('title','Transaksi Parkir')
@section('page-title','Daftar Transaksi Parkir')
@section('page-desc','Filter, tinjau, cetak, atau ekspor rekap transaksi tenant.')

@section('staff-content')
<div class="page-h">
  <div>
    <h1 class="page-title sm:hidden">Transaksi Parkir</h1>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('owner.transaksi.export', request()->query()) }}" class="ui-btn ui-btn-secondary">Ekspor CSV</a>
    <a href="{{ route('owner.transaksi.print', request()->query()) }}" target="_blank" class="ui-btn ui-btn-primary">Cetak Rekap (A4)</a>
  </div>
</div>

<form method="GET" class="ui-card ui-card-b mb-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 items-end">
    <div><label class="ui-label">Dari Tanggal</label><input type="date" name="dari" value="{{ request('dari') }}" class="ui-input"></div>
    <div><label class="ui-label">Sampai Tanggal</label><input type="date" name="sampai" value="{{ request('sampai') }}" class="ui-input"></div>
    <div><label class="ui-label">Status</label><select name="status" class="ui-select"><option value="">Semua</option><option value="masuk" @selected(request('status')==='masuk')>Masuk</option><option value="keluar" @selected(request('status')==='keluar')>Keluar</option></select></div>
    <div><label class="ui-label">Pembayaran</label><select name="pembayaran" class="ui-select"><option value="">Semua</option><option value="Belum Bayar" @selected(request('pembayaran')==='Belum Bayar')>Belum Bayar</option><option value="Sudah Bayar" @selected(request('pembayaran')==='Sudah Bayar')>Sudah Bayar</option></select></div>
    <div class="xl:col-span-2 flex gap-2"><button class="ui-btn ui-btn-primary">Terapkan Filter</button><a href="{{ route('owner.transaksi.index') }}" class="ui-btn ui-btn-secondary">Reset</a></div>
  </div>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Total Transaksi (Filter)</div><div class="ui-kpi mt-1">{{ number_format($summary['total'],0,',','.') }}</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Pendapatan Sudah Bayar (Filter)</div><div class="ui-kpi mt-1" style="color:#16a34a">Rp {{ number_format($summary['pendapatan'],0,',','.') }}</div></div>
</div>

<div class="ui-card overflow-hidden">
  <div class="ui-table-wrap">
    <table class="ui-table">
      <thead><tr><th>ID</th><th>Plat</th><th>Pengendara</th><th>Masuk</th><th>Keluar</th><th>Biaya</th><th>Pembayaran</th><th>Status</th></tr></thead>
      <tbody>
      @forelse($transaksis as $t)
        <tr>
          <td class="text-gray-500">#{{ $t->id_transaksi }}</td>
          <td><span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span></td>
          <td>{{ $t->pengendara->nama_lengkap ?? 'Offline' }}</td>
          <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_masuk)->format('d/m/Y H:i') }}</td>
          <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_keluar)->format('d/m/Y H:i') ?? '-' }}</td>
          <td class="whitespace-nowrap">{{ $t->biaya_total ? 'Rp '.number_format($t->biaya_total,0,',','.') : '-' }}</td>
          <td><span class="ui-badge {{ $t->status_pembayaran==='Sudah Bayar'?'ui-badge-success':'ui-badge-warning' }}">{{ $t->status_pembayaran }}</span></td>
          <td><span class="ui-badge {{ $t->status==='masuk'?'ui-badge-warning':'ui-badge-success' }}">{{ $t->status }}</span></td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-gray-500 py-10">Tidak ada transaksi untuk filter ini.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="px-4 py-3 border-t border-gray-100">{{ $transaksis->links() }}</div>
</div>
@endsection
