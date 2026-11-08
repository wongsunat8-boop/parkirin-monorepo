@extends('layouts.staff')
@section('title','Dashboard Petugas')
@section('page-title','Dashboard Petugas')
@section('page-desc', ($tenant->nama_tempat ?? 'Tenant').' — pantau kuota dan kendaraan aktif.')

@section('staff-content')
@php $sisa = max(0, ($tenant->kapasitas ?? 0) - ($tenant->terisi ?? 0)); $kap = (int)($tenant->kapasitas ?? 0); @endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Kuota Terisi</div>
    <div class="ui-kpi mt-2">{{ $tenant->terisi ?? 0 }}<span class="text-lg text-gray-400 font-semibold"> / {{ $kap }}</span></div>
    <div class="ui-kpi-sub {{ $kap > 0 && $sisa <= 3 ? 'text-danger font-medium' : '' }}">Sisa slot: {{ $sisa }}</div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Transaksi Aktif</div>
    <div class="ui-kpi mt-2 text-primary">{{ $transaksiAktif->total() }}</div>
    <div class="ui-kpi-sub">Kendaraan masih di area</div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Status Layanan</div>
    <div class="mt-3 flex items-center gap-2">
      <span class="ui-badge {{ ($tenant->sediakan_booking ?? 0) ? 'ui-badge-success':'ui-badge-muted' }}">Booking {{ ($tenant->sediakan_booking ?? 0) ? 'Aktif':'Nonaktif' }}</span>
      <span class="ui-badge {{ ($tenant->status_aktif ?? 0) ? 'ui-badge-success':'ui-badge-danger' }}">Tenant {{ ($tenant->status_aktif ?? 0) ? 'Aktif':'Nonaktif' }}</span>
    </div>
    <div class="ui-kpi-sub mt-2">Informasi operasional lapangan.</div>
  </div>
</div>

<div class="ui-card">
  <div class="ui-card-h"><div class="ui-card-t">Daftar Transaksi Aktif</div><div class="ui-card-d">Kendaraan yang belum checkout.</div></div>
  <div class="ui-table-wrap">
    <table class="ui-table"><thead><tr><th>Plat</th><th>Jenis</th><th>Pengendara</th><th>Waktu Masuk</th><th>Durasi</th></tr></thead>
    <tbody>
      @forelse($transaksiAktif as $t)
        <tr>
          <td><span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span></td>
          <td>{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</td>
          <td>{{ $t->pengendara->nama_lengkap ?? 'Offline' }}</td>
          <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_masuk)->format('d/m/Y H:i') }}</td>
          <td>{{ optional($t->waktu_masuk)->diffForHumans(null, true) }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-gray-500 py-8">Tidak ada kendaraan aktif saat ini.</td></tr>
      @endforelse
    </tbody></table>
  </div>
  <div class="px-4 py-3 border-t border-gray-100">{{ $transaksiAktif->links() }}</div>
</div>
@endsection
