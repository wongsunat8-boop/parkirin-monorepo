@extends('layouts.pengendara')

@section('title', 'Katalog Parkir — Parkirin')
@section('page-title', 'Katalog Parkir')
@section('page-desc', 'Pilih lokasi parkir untuk melihat tarif, membeli paket member, atau booking inap secara instan.')

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
    <h1 class="page-title">Katalog Tempat Parkir</h1>
    <p class="page-desc">Informasi lokasi parkir resmi, ketersediaan kuota real-time, paket member bulanan, dan booking slot inap.</p>
  </div>
</div>

{{-- Banner Info Web Auto-Lunas Instan --}}
<div class="ui-alert ui-alert-info mb-4 flex items-center justify-between gap-3 flex-wrap bg-blue-50/70 border-blue-200 text-blue-900">
  <div class="flex items-center gap-2.5">
    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
      <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    </div>
    <div class="text-xs">
      <strong class="font-bold text-primary">Fitur Auto-Lunas Instan:</strong> Pembelian Member dan Booking Inap via upload bukti QRIS langsung berstatus Lunas tanpa menunggu antrean gerbang.
    </div>
  </div>
</div>

<div class="catalog-grid">
  @forelse($tempat as $tp)
    @php
      $sisa = max(0, (int)$tp->kapasitas - (int)$tp->terisi);
      $penuh = $sisa <= 0;
      $persen = $tp->kapasitas > 0 ? min(100, round(($tp->terisi / $tp->kapasitas) * 100)) : 100;
    @endphp
    <article class="bento-card flex flex-col h-full hover:border-primary/40 transition">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="font-heading font-extrabold text-lg text-[#212121] leading-tight">{{ $tp->nama_tempat }}</div>
          <div class="flex items-center gap-1.5 flex-wrap mt-2">
            <span class="ui-badge ui-badge-success text-[10.5px]">Buka</span>
            @if($tp->sediakan_member)
              <span class="ui-badge ui-badge-info text-[10.5px]">Member Aktif</span>
            @endif
            @if($tp->sediakan_booking_inap)
              <span class="ui-badge ui-badge-muted text-[10.5px]">Booking Inap</span>
            @endif
          </div>
        </div>
        <div class="text-right shrink-0">
          <div class="text-[10.5px] font-bold uppercase tracking-[.05em] text-slate-400">Sisa Kuota</div>
          <div class="ui-kpi text-xl sm:text-2xl font-black {{ $penuh ? 'text-rose-600' : 'text-primary' }}">{{ $sisa }}</div>
        </div>
      </div>

      <div class="mt-3">
        <div class="quota-bar {{ $persen >= 85 ? 'is-high' : '' }}"><span style="width: {{ $persen }}%"></span></div>
        <div class="text-[11.5px] text-slate-500 mt-1 flex justify-between">
          <span>Kapasitas: {{ $tp->kapasitas }} slot</span>
          <span>Terisi: {{ $tp->terisi }} ({{ $persen }}%)</span>
        </div>
      </div>

      <div class="mt-3.5 space-y-1 text-xs text-slate-600">
        <div>Tarif Kendaraan: <span class="font-bold text-[#212121]">{{ $tp->tarif->count() }} jenis</span></div>
        <div>Paket Langganan: <span class="font-bold text-primary">{{ $tp->paketLangganans->count() }} paket aktif</span></div>
        @if($tp->kuota_inap > 0)
          <div>Kuota Inap: <span class="font-bold text-slate-800">{{ $tp->kuota_inap }} slot</span></div>
        @endif
      </div>

      <div class="mt-auto pt-4">
        <a href="{{ route('pengendara.katalog.show', $tp) }}" class="ui-btn ui-btn-primary w-full text-xs py-2.5">
          Pilih & Buka Layanan Parkir
        </a>
      </div>
    </article>
  @empty
    <div class="bento-card" style="grid-column:1/-1;">
      <div class="text-sm text-slate-600">Belum ada tempat parkir aktif yang tersedia saat ini.</div>
    </div>
  @endforelse
</div>
@endsection
