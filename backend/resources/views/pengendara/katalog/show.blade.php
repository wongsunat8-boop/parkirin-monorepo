@extends('layouts.pengendara')

@section('title', ($tempat->nama_tempat ?? 'Detail Katalog') . ' — Parkirin')
@section('page-title', 'Detail Katalog Parkir')
@section('page-desc', 'Informasi detail tempat parkir, kuota, dan skema tarif.')

@section('pengendara-content')
@php
  $sisa = max(0, (int)$tempat->kapasitas - (int)$tempat->terisi);
  $penuh = $sisa <= 0;
  $persen = $tempat->kapasitas > 0 ? min(100, round(($tempat->terisi / $tempat->kapasitas) * 100)) : 100;
@endphp
<style>
  .detail-grid { display:grid; grid-template-columns:1fr; gap:16px; }
  @media (min-width: 1024px){ .detail-grid{ grid-template-columns: repeat(12,minmax(0,1fr)); } .d-span-5{grid-column:span 5;} .d-span-7{grid-column:span 7;} }
  .quota-bar { height:10px; border-radius:999px; background:#e2e8f0; overflow:hidden; }
  .quota-bar > span { display:block; height:100%; border-radius:999px; background:linear-gradient(90deg,#0D63A5,#1A7DC8); }
  .quota-bar.is-high > span { background:linear-gradient(90deg,#D97706,#DC2626); }
  .tarif-card { border:1px solid rgba(226,232,240,.9); border-radius:14px; padding:14px; background:#fff; }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">Detail Tempat Parkir</h1>
    <p class="page-desc">Informasi ditampilkan untuk referensi pengendara. Booking parkir dilakukan melalui aplikasi mobile Parkirin.</p>
  </div>
  <a href="{{ route('pengendara.katalog.index') }}" class="ui-btn ui-btn-secondary">Kembali ke Katalog</a>
</div>

<div class="detail-grid">
  <section class="bento-card d-span-5">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="font-heading font-extrabold text-xl text-[#212121] leading-tight">{{ $tempat->nama_tempat }}</div>
        <div class="flex items-center gap-2 flex-wrap mt-2">
          <span class="ui-badge {{ $tempat->status_aktif ? 'ui-badge-success' : 'ui-badge-muted' }}">{{ $tempat->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
          @if($penuh)<span class="ui-badge ui-badge-danger">Kuota Penuh</span>@endif
        </div>
      </div>
    </div>

    <div class="mt-5 grid grid-cols-3 gap-3">
      <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
        <div class="ui-kpi-label">Kapasitas</div>
        <div class="ui-kpi" style="font-size:22px;">{{ $tempat->kapasitas }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
        <div class="ui-kpi-label">Terisi</div>
        <div class="ui-kpi" style="font-size:22px;">{{ $tempat->terisi }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
        <div class="ui-kpi-label">Sisa</div>
        <div class="ui-kpi" style="font-size:22px; color: {{ $penuh ? '#DC2626' : '#0D63A5' }};">{{ $sisa }}</div>
      </div>
    </div>

    <div class="mt-4">
      <div class="quota-bar {{ $persen >= 85 ? 'is-high' : '' }}"><span style="width: {{ $persen }}%"></span></div>
      <div class="text-[12px] text-slate-500 mt-1.5">Tingkat keterisian: {{ $persen }}% (data real-time berdasarkan kuota saat ini)</div>
    </div>

    <div class="mt-5 ui-alert ui-alert-info text-xs" style="background:#eff6ff; border-color:#bfdbfe; color:#1e3a8a;">
      Booking parkir tidak tersedia di website. Gunakan <strong>aplikasi mobile Parkirin</strong> untuk melakukan pemesanan slot parkir.
    </div>
  </section>

  <section class="bento-card d-span-7">
    <div class="ui-kpi-label mb-1">Skema Tarif Aktif</div>
    <div class="text-sm text-slate-500 mb-4">Rincian tarif per jenis kendaraan di lokasi ini.</div>

    @if($tempat->tarif->isEmpty())
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-5 text-sm text-slate-500">Belum ada tarif aktif yang terdaftar untuk lokasi ini.</div>
    @else
      <div class="grid sm:grid-cols-2 gap-3">
        @foreach($tempat->tarif as $tarif)
          <article class="tarif-card">
            <div class="flex items-center justify-between gap-2 mb-2">
              <span class="font-heading font-extrabold text-base text-[#212121]">{{ $tarif->jenis_kendaraan }}</span>
              <span class="ui-badge ui-badge-info">{{ $tarif->tipe_tarif }}</span>
            </div>
            <div class="text-sm text-slate-700 leading-relaxed">
              @if($tarif->tipe_tarif === 'Per Jam')
                <div><span class="text-slate-500">Tarif:</span> <span class="font-semibold">Rp {{ number_format((float)$tarif->tarif_per_jam,0,',','.') }}</span> / jam</div>
              @elseif($tarif->tipe_tarif === 'Flat Harian')
                <div><span class="text-slate-500">Tarif Flat:</span> <span class="font-semibold">Rp {{ number_format((float)$tarif->tarif_per_jam,0,',','.') }}</span> / hari</div>
              @else
                <div><span class="text-slate-500">Jam Pertama:</span> <span class="font-semibold">Rp {{ number_format((float)$tarif->tarif_jam_pertama,0,',','.') }}</span></div>
                <div><span class="text-slate-500">Jam Berikutnya:</span> <span class="font-semibold">Rp {{ number_format((float)$tarif->tarif_berikutnya,0,',','.') }}</span> / jam</div>
              @endif
              <div class="mt-1.5"><span class="text-slate-500">Tarif Inap:</span> <span class="font-semibold">{{ (float)$tarif->tarif_inap > 0 ? 'Rp '.number_format((float)$tarif->tarif_inap,0,',','.').' / malam' : 'Tidak ada' }}</span></div>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </section>
</div>
@endsection
