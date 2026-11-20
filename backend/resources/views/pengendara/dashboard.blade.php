@extends('layouts.pengendara')

@section('title', 'Dashboard Pengendara — Parkirin')
@section('page-title', 'Dashboard')
@section('page-desc', 'Frekuensi parkir, status tiket, member pass, dan kelola kendaraan pribadi Anda.')

@section('pengendara-content')
<style>
  .bento-grid { display:grid; grid-template-columns: 1fr; gap: 16px; }
  @media (min-width: 1024px) { 
    .bento-grid { grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 18px; } 
    .span-8{grid-column:span 8;} 
    .span-4{grid-column:span 4;} 
    .span-6{grid-column:span 6;} 
    .span-12{grid-column:span 12;} 
  }
  .stack-area { position: relative; min-height: 100px; padding: 4px 0 10px; }
  .stack-card { position: relative; background: #ffffff; border:1px solid rgba(226,232,240,.9); border-radius: 16px; padding: 14px 16px; box-shadow: 0 4px 12px rgba(15,23,42,.03); transition: transform .18s ease, box-shadow .18s ease; margin-bottom: 10px; }
  .stack-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15,23,42,.07); }
  .stack-card::before { content:''; position:absolute; left:0; top:12px; bottom:12px; width:4px; border-radius:999px; background: var(--stack-accent, #0D63A5); }
  .ticket-strip { background: linear-gradient(135deg,#0D63A5 0%,#0A4F84 100%); color:#fff; border-radius:16px; padding:16px; }
  .pass-strip { background: linear-gradient(135deg,#059669 0%,#047857 100%); color:#fff; border-radius:16px; padding:16px; }
  .booking-strip { background: linear-gradient(135deg,#7C3AED 0%,#6D28D9 100%); color:#fff; border-radius:16px; padding:16px; }
  .chart-wrap { position: relative; height: 240px; }
  @media (max-width: 640px){ .chart-wrap { height: 210px; } }
  .modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.45); backdrop-filter: blur(4px); z-index:60; display:flex; align-items:flex-end; justify-content:center; padding:16px; }
  @media (min-width: 640px){ .modal-backdrop{ align-items:center; } }
  .modal-panel { width:100%; max-width:520px; max-height:92vh; overflow:auto; background:#fff; border:1px solid rgba(226,232,240,.9); border-radius:18px; box-shadow:0 20px 50px rgba(15,23,42,.18); }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">Dashboard Pengendara</h1>
    <p class="page-desc">Pantau frekuensi parkir, karcis digital aktif, paket langganan, dan kendaraan pribadi.</p>
  </div>
  <div class="flex items-center gap-2 flex-wrap">
    <a href="{{ route('checkin.scanner') }}" class="ui-btn ui-btn-dark flex items-center gap-2 shadow-sm">
      <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
      <span>Scan Gerbang Masuk</span>
    </a>
    <a href="{{ route('pengendara.katalog.index') }}" class="ui-btn ui-btn-primary">Katalog & Booking</a>
  </div>
</div>

{{-- Quick Action Scanner Gerbang Masuk --}}
<div class="mb-5 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-primary-dark to-primary text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-md border border-slate-800">
  <div class="flex items-center gap-3.5">
    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center shrink-0 border border-white/15">
      <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
    </div>
    <div>
      <div class="flex items-center gap-2">
        <h3 class="font-heading font-black text-base text-white">Sedang di Depan Gerbang Masuk?</h3>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent text-slate-950 font-mono uppercase">Instant In</span>
      </div>
      <p class="text-xs text-white/80 mt-1 max-w-xl leading-relaxed">
        Pindai QR gerbang pos masuk tanpa harus logout. Kendaraan terdaftar Anda akan otomatis dikenali dan tarif member/booking langsung berlaku otomatis!
      </p>
    </div>
  </div>
  <a href="{{ route('checkin.scanner') }}" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-accent hover:bg-accent-dark text-slate-950 font-heading font-black text-xs inline-flex items-center justify-center gap-2 transition shrink-0 shadow-sm cursor-pointer">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <span>Mulai Pindai QR Gerbang</span>
  </a>
</div>

<div class="bento-grid">
  {{-- Frekuensi Parkir (Chart) --}}
  <section class="bento-card {{ ($memberAktif || $bookingAktif) ? 'span-8' : 'span-8' }}" x-data="{ mode: 'mingguan' }">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
      <div>
        <div class="ui-kpi-label">Frekuensi Parkir</div>
        <div class="text-sm text-slate-500 mt-0.5">Total transaksi parkir Anda: <span class="font-bold text-[#212121]">{{ $totalParkir }}</span></div>
      </div>
      <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1 gap-1">
        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold transition" :class="mode==='mingguan' ? 'bg-white text-primary shadow-sm border border-slate-200' : 'text-slate-600 hover:text-slate-900'" @click="mode='mingguan'; window.renderParkirChart && window.renderParkirChart('mingguan')">Mingguan</button>
        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold transition" :class="mode==='bulanan' ? 'bg-white text-primary shadow-sm border border-slate-200' : 'text-slate-600 hover:text-slate-900'" @click="mode='bulanan'; window.renderParkirChart && window.renderParkirChart('bulanan')">Bulanan</button>
      </div>
    </div>
    <div class="chart-wrap">
      <canvas id="parkirFreqChart" aria-label="Grafik frekuensi parkir" role="img"></canvas>
    </div>
    <div class="text-[11.5px] text-slate-500 mt-2">Menampilkan data aktivitas masuk kendaraan Anda berdasarkan periode waktu.</div>
  </section>

  {{-- Status Karcis / Member / Booking --}}
  <div class="span-4 flex flex-col gap-4">
    {{-- Karcis Digital Aktif --}}
    <section class="bento-card" x-data="{ showTicketQrisModal: false }">
      <div class="flex items-start justify-between gap-3 mb-3">
        <div>
          <div class="ui-kpi-label">Karcis Parkir Aktif</div>
          <div class="text-xs text-slate-500 mt-0.5">Kendaraan sedang di dalam lokasi</div>
        </div>
        @if($karcisAktif)
          <span class="ui-badge {{ $karcisAktif->status_pembayaran === 'Sudah Bayar' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $karcisAktif->status_pembayaran }}</span>
        @endif
      </div>

      @if($karcisAktif)
        @php
          $tagihanKarcis = $karcisAktif->biaya_total > 0 ? $karcisAktif->biaya_total : ($karcisAktif->biaya_dasar > 0 ? $karcisAktif->biaya_dasar : 0);
          $qrisActiveImg = $karcisAktif->tempatParkir?->qris_image ? asset($karcisAktif->tempatParkir->qris_image) : asset('assets/qris/default_qris.svg');
        @endphp
        <div class="ticket-strip mb-3">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-[10.5px] uppercase tracking-[.05em] text-white/80 font-bold">Lokasi Parkir</div>
              <div class="font-heading font-extrabold text-sm leading-tight text-white">{{ $karcisAktif->tempatParkir->nama_tempat ?? '-' }}</div>
              <div class="mt-2 flex items-center gap-2 flex-wrap">
                <span class="badge-plat bg-white/20 border-white/30 text-white text-[11px]">{{ $karcisAktif->kendaraan->plat_nomor ?? '-' }}</span>
                <span class="text-[11px] text-white/90">({{ $karcisAktif->kendaraan->jenis_kendaraan ?? '-' }})</span>
              </div>
              <div class="text-[11px] text-white/80 mt-1.5">Masuk: {{ $karcisAktif->waktu_masuk ? \Carbon\Carbon::parse($karcisAktif->waktu_masuk)->format('d M, H:i') : '-' }} WIB</div>
            </div>
            <a href="{{ route('checkin.ticket', $karcisAktif->kode_karcis) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-white text-primary text-xs font-bold shrink-0 shadow-sm hover:bg-slate-100 transition">
              Buka QR
            </a>
          </div>
        </div>
        <div class="flex items-center justify-between gap-2 flex-wrap text-xs">
          <span class="font-mono text-slate-500 font-bold">{{ $karcisAktif->kode_karcis }}</span>
          @if($karcisAktif->status_pembayaran !== 'Sudah Bayar')
            <button type="button" @click="showTicketQrisModal = true" class="btn-action-success text-xs py-1 px-2.5 flex items-center gap-1.5 cursor-pointer">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
              <span>Bayar QRIS</span>
            </button>
          @else
            <span class="ui-badge ui-badge-success text-[10.5px]">Lunas</span>
          @endif
        </div>

        {{-- Modal QRIS Karcis Aktif --}}
        <div x-cloak x-show="showTicketQrisModal" class="modal-backdrop" @click.self="showTicketQrisModal=false">
          <div class="modal-panel" @click.stop>
            <div class="ui-card-h flex items-center justify-between gap-3">
              <div>
                <div class="ui-card-t">Pembayaran Parkir QRIS Resmi</div>
                <div class="ui-card-d">{{ $karcisAktif->tempatParkir->nama_tempat ?? 'Parkirin' }} &bull; Karcis {{ $karcisAktif->kode_karcis }}</div>
              </div>
              <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showTicketQrisModal=false">Tutup</button>
            </div>

            <div class="ui-card-b space-y-4">
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 grid grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-slate-500 block">Kendaraan:</span>
                  <span class="font-mono font-bold text-dark text-sm">{{ $karcisAktif->kendaraan->plat_nomor ?? '-' }}</span>
                  <span class="text-slate-400 block text-[11px]">({{ $karcisAktif->kendaraan->jenis_kendaraan ?? '-' }})</span>
                </div>
                <div>
                  <span class="text-slate-500 block">Waktu Masuk:</span>
                  <span class="font-semibold text-dark">{{ $karcisAktif->waktu_masuk ? \Carbon\Carbon::parse($karcisAktif->waktu_masuk)->format('d/m/Y H:i') : '-' }} WIB</span>
                  <span class="text-slate-500 block text-[11px]">Durasi: {{ $karcisAktif->durasi_jam }} Jam</span>
                </div>
              </div>

              <div class="p-4 rounded-2xl bg-gradient-to-b from-blue-50/60 to-white border border-blue-200/80 text-center space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Total Tagihan Parkir</div>
                <div class="text-3xl font-heading font-black text-primary">Rp {{ number_format((float)$tagihanKarcis, 0, ',', '.') }}</div>

                <div class="pt-2">
                  <img src="{{ $qrisActiveImg }}" alt="QRIS Resmi Tenant" class="w-48 h-48 mx-auto rounded-xl border border-slate-200 bg-white p-2 shadow-sm object-contain">
                </div>

                <div class="pt-2 text-xs text-slate-600 max-w-sm mx-auto leading-relaxed">
                  Pindai QRIS resmi pengelola di atas menggunakan mobile banking atau dompet digital (GoPay, OVO, Dana, BCA, dll).
                </div>
              </div>

              <form method="POST" action="{{ route('pengendara.transaksi.bayar', $karcisAktif) }}" class="pt-2">
                @csrf
                <div class="flex items-center justify-end gap-2">
                  <button type="button" class="ui-btn ui-btn-secondary" @click="showTicketQrisModal=false">Batal</button>
                  <button type="submit" class="ui-btn ui-btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Konfirmasi Sudah Bayar QRIS</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @else
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center">
          <div class="text-xs font-semibold text-slate-600">Tidak ada kendaraan parkir aktif</div>
          <div class="text-[11px] text-slate-400 mt-0.5">Karcis akan tampil saat melakukan Self Check-In di gerbang.</div>
        </div>
      @endif
    </section>

    {{-- Member Pass Aktif (Jika Ada) --}}
    @if($memberAktif)
    <section class="bento-card">
      <div class="flex items-start justify-between gap-2 mb-2">
        <div class="ui-kpi-label">Member Pass Aktif</div>
        <span class="ui-badge ui-badge-success text-[10px]">Aktif</span>
      </div>
      <div class="pass-strip">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="text-[10px] uppercase font-bold text-white/80 tracking-wider">Fast-Track Pass</div>
            <div class="font-heading font-black text-sm text-white">{{ $memberAktif->paket->nama_paket ?? 'Paket Member' }}</div>
            <div class="text-xs text-white/90 mt-0.5">{{ $memberAktif->tempatParkir->nama_tempat ?? '-' }}</div>
            <div class="mt-2 flex items-center gap-2 flex-wrap">
              <span class="badge-plat bg-white/20 border-white/30 text-white text-[10.5px]">{{ $memberAktif->kendaraan->plat_nomor ?? '-' }}</span>
              <span class="text-[11px] text-white/80">
                @if($memberAktif->paket->batas_masuk_harian)
                  Maks {{ $memberAktif->paket->batas_masuk_harian }}x / hari
                @else
                  Akses Unlimited
                @endif
              </span>
            </div>
          </div>
          <div class="text-right">
            <span class="inline-block px-2 py-0.5 rounded bg-white/20 font-mono text-[10px] text-white font-bold">{{ $memberAktif->kode_member }}</span>
          </div>
        </div>
      </div>
    </section>
    @endif

    {{-- Booking Inap (Jika Ada) --}}
    @if($bookingAktif)
    <section class="bento-card">
      <div class="flex items-start justify-between gap-2 mb-2">
        <div class="ui-kpi-label">Booking Inap</div>
        <span class="ui-badge ui-badge-info text-[10px]">{{ $bookingAktif->status }}</span>
      </div>
      <div class="booking-strip">
        <div class="text-[10px] uppercase font-bold text-white/80 tracking-wider">Slot Terjadwal</div>
        <div class="font-heading font-black text-sm text-white">{{ $bookingAktif->tempatParkir->nama_tempat ?? '-' }}</div>
        <div class="mt-2 text-xs text-white/90">
          <div>Mulai: {{ \Carbon\Carbon::parse($bookingAktif->waktu_mulai_inap)->format('d M Y, H:i') }}</div>
          <div>Keluar: {{ \Carbon\Carbon::parse($bookingAktif->waktu_rencana_keluar)->format('d M Y, H:i') }}</div>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="badge-plat bg-white/20 border-white/30 text-white text-[10.5px]">{{ $bookingAktif->kendaraan->plat_nomor ?? '-' }}</span>
          <span class="font-mono text-[10px] text-white/90 font-bold">{{ $bookingAktif->kode_booking }}</span>
        </div>
      </div>
    </section>
    @endif
  </div>

  {{-- Kendaraan Pribadi (Auto-Reassign Support) --}}
  <section class="bento-card span-12" x-data="kendaraanWidget()" @keydown.escape.window="closeAll()">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
      <div>
        <div class="ui-kpi-label">Kendaraan Pribadi</div>
        <div class="text-sm text-slate-500 mt-0.5">Kelola kendaraan Anda. Dilengkapi fitur <strong>Auto-Reassign</strong> otomatis jika plat didaftarkan ulang & bebas kontrak aktif.</div>
      </div>
      <button type="button" class="ui-btn ui-btn-primary ui-btn-sm" @click="openAdd()">+ Tambah Kendaraan</button>
    </div>

    @if($kendaraan->isEmpty())
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
        <div class="text-sm font-semibold text-slate-700">Belum ada kendaraan terdaftar</div>
        <div class="text-xs text-slate-500 mt-1">Daftarkan plat nomor kendaraan untuk memudahkan check-in gerbang dan langganan member.</div>
      </div>
    @else
      <div class="stack-area">
        @foreach($kendaraan as $i => $k)
          @php $locked = $k->transaksi_count > 0; $accents = ['#0D63A5','#0891B2','#7C3AED','#059669','#D97706']; @endphp
          <article class="stack-card" style="--stack-accent: {{ $accents[$i % count($accents)] }};">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="badge-plat font-bold text-sm">{{ $k->plat_nomor }}</span>
                  <span class="ui-badge ui-badge-info">{{ $k->jenis_kendaraan }}</span>
                  <span class="ui-badge ui-badge-muted">{{ $k->warna }}</span>
                  @if($locked)
                    <span class="ui-badge ui-badge-warning" title="Plat & jenis dikunci karena memiliki riwayat transaksi">Immutable History</span>
                  @endif
                </div>
                <div class="text-[11.5px] text-slate-500 mt-1.5">
                  {{ $locked ? 'Plat nomor & jenis dikunci karena memiliki riwayat transaksi parkir. Warna kendaraan tetap dapat diperbarui.' : 'Kendaraan belum memiliki riwayat. Plat, jenis, dan warna dapat disesuaikan.' }}
                </div>
              </div>
              <div class="flex items-center gap-2 flex-wrap shrink-0">
                <button type="button" class="btn-action-edit" @click="openEdit({{ Js::from(['id'=>$k->id_kendaraan,'plat_nomor'=>$k->plat_nomor,'jenis_kendaraan'=>$k->jenis_kendaraan,'warna'=>$k->warna,'locked'=>(bool)$locked]) }})">Edit</button>
                <form method="POST" action="{{ route('pengendara.kendaraan.unlink', $k) }}" onsubmit="return confirm('{{ $locked ? 'Lepas kendaraan ini dari akun Anda? Riwayat transaksi tetap tersimpan.' : 'Hapus kendaraan ini dari akun Anda?' }}');" class="m-0">
                  @csrf
                  <button type="submit" class="btn-action-danger">Unlink</button>
                </form>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif

    {{-- Modal Tambah Kendaraan --}}
    <div x-cloak x-show="showAdd" class="modal-backdrop" @click.self="showAdd=false">
      <div class="modal-panel" @click.stop>
        <div class="ui-card-h flex items-center justify-between gap-3">
          <div><div class="ui-card-t">Tambah Kendaraan</div><div class="ui-card-d">Mendukung otomatisasi Auto-Reassign.</div></div>
          <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showAdd=false">Tutup</button>
        </div>
        <form method="POST" action="{{ route('pengendara.kendaraan.store') }}" class="ui-card-b space-y-3">
          @csrf
          <div>
            <label class="ui-label">Plat Nomor (Format Standar Indonesia)</label>
            <input name="plat_nomor" class="ui-input uppercase font-mono font-bold" placeholder="Contoh: B 1234 XYZ" required maxlength="12"
                   oninput="this.value = (function(v){ let c = v.toUpperCase().replace(/[^A-Z0-9]/g,''); if(!c) return ''; let w = c.match(/^[A-Z]{1,2}/); if(!w) return c.slice(0,10); let r = c.slice(w[0].length); if(!r) return w[0]; let n = r.match(/^\d{1,4}/); if(!n) return w[0]; let s = r.slice(n[0].length).match(/^[A-Z]{1,3}/); return w[0]+' '+n[0]+(s?' '+s[0]:''); })(this.value)">
            <p class="text-[11px] text-slate-400 mt-1">Jika plat ini pernah didaftarkan oleh akun lain yang sudah tidak aktif berkontrak, kepemilikan akan dialihkan otomatis ke akun Anda.</p>
          </div>
          <div class="grid sm:grid-cols-2 gap-3">
            <div>
              <label class="ui-label">Jenis Kendaraan</label>
              <select name="jenis_kendaraan" class="ui-select" required>
                <option value="Motor">Motor</option>
                <option value="Mobil">Mobil</option>
                <option value="Sepeda">Sepeda</option>
              </select>
            </div>
            <div>
              <label class="ui-label">Warna</label>
              <input name="warna" class="ui-input" placeholder="Contoh: Hitam" required>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" class="ui-btn ui-btn-secondary" @click="showAdd=false">Batal</button>
            <button type="submit" class="ui-btn ui-btn-primary">Simpan Kendaraan</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Modal Edit Kendaraan --}}
    <div x-cloak x-show="showEdit" class="modal-backdrop" @click.self="showEdit=false">
      <div class="modal-panel" @click.stop>
        <div class="ui-card-h flex items-center justify-between gap-3">
          <div><div class="ui-card-t">Edit Kendaraan</div><div class="ui-card-d" x-text="edit.locked ? 'Immutable History: hanya warna yang dapat diubah.' : 'Perbarui data kendaraan.'"></div></div>
          <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showEdit=false">Tutup</button>
        </div>
        <form method="POST" :action="editAction()" class="ui-card-b space-y-3">
          @csrf @method('PATCH')
          <div>
            <label class="ui-label">Plat Nomor</label>
            <input name="plat_nomor" class="ui-input uppercase font-mono font-bold" x-model="edit.plat_nomor" :disabled="edit.locked" :required="!edit.locked" maxlength="12">
          </div>
          <div class="grid sm:grid-cols-2 gap-3">
            <div>
              <label class="ui-label">Jenis Kendaraan</label>
              <select name="jenis_kendaraan" class="ui-select" x-model="edit.jenis_kendaraan" :disabled="edit.locked">
                <option value="Motor">Motor</option>
                <option value="Mobil">Mobil</option>
                <option value="Sepeda">Sepeda</option>
              </select>
            </div>
            <div>
              <label class="ui-label">Warna</label>
              <input name="warna" class="ui-input" x-model="edit.warna" required>
            </div>
          </div>
          <template x-if="edit.locked">
            <div class="ui-alert ui-alert-warning text-xs">Kendaraan ini memiliki riwayat transaksi parkir. Plat nomor dan jenis dikunci permanen demi integritas audit (Immutable History).</div>
          </template>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" class="ui-btn ui-btn-secondary" @click="showEdit=false">Batal</button>
            <button type="submit" class="ui-btn ui-btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const data = {
      mingguan: { labels: @json($weeklyLabels), counts: @json($weeklyCounts) },
      bulanan: { labels: @json($monthlyLabels), counts: @json($monthlyCounts) }
    };
    let chart;
    function render(mode){
      const ctx = document.getElementById('parkirFreqChart');
      if (!ctx || typeof Chart === 'undefined') return;
      const d = data[mode] || data.mingguan;
      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: d.labels,
          datasets: [{
            label: 'Transaksi Parkir',
            data: d.counts,
            backgroundColor: 'rgba(13, 99, 165, 0.18)',
            borderColor: '#0D63A5',
            borderWidth: 2,
            borderRadius: 8,
            maxBarThickness: 42
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,0.18)' } },
            x: { grid: { display: false } }
          }
        }
      });
    }
    window.renderParkirChart = render;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ render('mingguan'); });
    else render('mingguan');
  })();

  function kendaraanWidget(){
    return {
      showAdd:false, showEdit:false,
      edit:{id:null, plat_nomor:'', jenis_kendaraan:'Motor', warna:'', locked:false},
      openAdd(){ this.closeAll(); this.showAdd=true; },
      openEdit(k){ this.closeAll(); this.edit = Object.assign({}, k); this.showEdit=true; },
      closeAll(){ this.showAdd=false; this.showEdit=false; },
      editAction(){ return `{{ url('/pengendara/kendaraan') }}/${this.edit.id}`; }
    }
  }
</script>
@endpush
