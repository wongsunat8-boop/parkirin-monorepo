@extends('layouts.pengendara')

@section('title', 'Katalog Parkir — Parkirin')
@section('page-title', 'Katalog Parkir')
@section('page-desc', 'Daftar tempat parkir aktif. Pilih lokasi untuk melihat detail informasi.')

@section('pengendara-content')
<style>
  .catalog-grid { display:grid; grid-template-columns: 1fr; gap:16px; }
  @media (min-width: 640px){ .catalog-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
  .quota-bar { height:8px; border-radius:999px; background:#e2e8f0; overflow:hidden; }
  .quota-bar > span { display:block; height:100%; border-radius:999px; background:linear-gradient(90deg,#0D63A5,#1A7DC8); }
  .quota-bar.is-high > span { background:linear-gradient(90deg,#D97706,#DC2626); }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">Katalog Parkir</h1>
    <p class="page-desc">Lihat informasi tempat parkir aktif dan sisa kuota real-time. Untuk melihat detail lengkap, buka halaman detail tempat parkir.</p>
  </div>
</div>

<div class="ui-alert ui-alert-info mb-4" style="background:#eff6ff; border-color:#bfdbfe; color:#1e3a8a;">
  <span class="text-xs font-semibold">Informasi:</span>
  <span class="text-xs">Fitur booking parkir melalui website tidak tersedia. Pemesanan/booking dilakukan melalui aplikasi mobile Parkirin.</span>
</div>

<div class="catalog-grid">
  @forelse($tempat as $tp)
    @php
      $sisa = max(0, (int)$tp->kapasitas - (int)$tp->terisi);
      $penuh = $sisa <= 0;
      $persen = $tp->kapasitas > 0 ? min(100, round(($tp->terisi / $tp->kapasitas) * 100)) : 100;
    @endphp
    <article class="bento-card flex flex-col h-full">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="font-heading font-extrabold text-lg text-[#212121] leading-tight">{{ $tp->nama_tempat }}</div>
          <div class="flex items-center gap-2 flex-wrap mt-2">
            <span class="ui-badge ui-badge-success">Aktif</span>
            @if($penuh)<span class="ui-badge ui-badge-danger">Kuota Penuh</span>@else<span class="ui-badge ui-badge-muted">{{ $tp->terisi }}/{{ $tp->kapasitas }} terisi</span>@endif
          </div>
        </div>
        <div class="text-right shrink-0">
          <div class="text-[11px] font-semibold uppercase tracking-[.05em] text-slate-500">Sisa Kuota</div>
          <div class="ui-kpi" style="font-size:24px;">{{ $sisa }}</div>
        </div>
      </div>

      <div class="mt-3">
        <div class="quota-bar {{ $persen >= 85 ? 'is-high' : '' }}"><span style="width: {{ $persen }}%"></span></div>
        <div class="text-[11.5px] text-slate-500 mt-1">Kapasitas {{ $tp->kapasitas }} • Terisi {{ $tp->terisi }} ({{ $persen }}%)</div>
      </div>

      <div class="mt-3 text-[12.5px] text-slate-600">
        Tarif aktif: <span class="font-semibold text-[#212121]">{{ $tp->tarif->count() }}</span> jenis kendaraan
      </div>

      <div class="mt-auto pt-4">
        <a href="{{ route('pengendara.katalog.show', $tp) }}" class="ui-btn ui-btn-primary w-full">Detail Katalog Parkir</a>
      </div>
    </article>
  @empty
    <div class="bento-card" style="grid-column:1/-1;">
      <div class="text-sm text-slate-600">Belum ada tempat parkir aktif yang tersedia.</div>
    </div>
  @endforelse
</div>
@endsection
