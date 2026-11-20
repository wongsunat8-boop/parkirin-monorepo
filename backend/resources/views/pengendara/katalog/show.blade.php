@extends('layouts.pengendara')

@section('title', ($tempat->nama_tempat ?? 'Detail Katalog') . ' — Parkirin')
@section('page-title', 'Layanan Parkir — ' . $tempat->nama_tempat)
@section('page-desc', 'Skema tarif, pembelian paket langganan member, dan booking parkir inap via QRIS Auto-Lunas.')

@section('pengendara-content')
@php
  $sisaReguler = max(0, (int)$tempat->kapasitas_reguler - (int)$tempat->terisi);
  $persenReguler = $tempat->kapasitas_reguler > 0 ? min(100, round(($tempat->terisi / $tempat->kapasitas_reguler) * 100)) : 100;
  $qrisPath = $tempat->qris_image ? asset($tempat->qris_image) : 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode('QRIS-PARKIRIN-' . $tempat->id_tempat_parkir);
@endphp

<style>
  .detail-grid { display:grid; grid-template-columns:1fr; gap:18px; }
  @media (min-width: 1024px){ .detail-grid{ grid-template-columns: repeat(12,minmax(0,1fr)); } .d-span-4{grid-column:span 4;} .d-span-8{grid-column:span 8;} }
  .quota-bar { height:8px; border-radius:999px; background:#e2e8f0; overflow:hidden; }
  .quota-bar > span { display:block; height:100%; border-radius:999px; background:linear-gradient(90deg,#0D63A5,#1A7DC8); }
  .tarif-card { border:1px solid rgba(226,232,240,.9); border-radius:14px; padding:14px; background:#fff; }
  .modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.45); backdrop-filter: blur(4px); z-index:60; display:flex; align-items:flex-end; justify-content:center; padding:16px; }
  @media (min-width: 640px){ .modal-backdrop{ align-items:center; } }
  .modal-panel { width:100%; max-width:540px; max-height:92vh; overflow:auto; background:#fff; border:1px solid rgba(226,232,240,.9); border-radius:20px; box-shadow:0 20px 50px rgba(15,23,42,.18); }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">{{ $tempat->nama_tempat }}</h1>
    <p class="page-desc">Informasi operasional, tarif resmi, paket langganan member, dan booking inap mandiri.</p>
  </div>
  <a href="{{ route('pengendara.katalog.index') }}" class="ui-btn ui-btn-secondary">Kembali ke Katalog</a>
</div>

{{-- Error & Success Notifications --}}
@if($errors->any())
  <div class="ui-alert ui-alert-danger mb-4">
    <div class="font-bold mb-1">Terdapat kendala pada permintaan Anda:</div>
    <ul class="list-disc pl-5 space-y-0.5 text-xs">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="detail-grid" x-data="katalogOrder({{ Js::from($tempat->tarif->keyBy('jenis_kendaraan')) }})" @keydown.escape.window="closeAll()">
  
  {{-- Sidebar Informasi Kuota --}}
  <div class="d-span-4 flex flex-col gap-4">
    <section class="bento-card">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="font-heading font-extrabold text-lg text-[#212121] leading-tight">{{ $tempat->nama_tempat }}</div>
          <div class="flex items-center gap-1.5 flex-wrap mt-2">
            <span class="ui-badge ui-badge-success text-[10px]">Buka 24 Jam</span>
            @if($tempat->kode_qr_gate)
              <span class="ui-badge ui-badge-muted font-mono text-[10px]">{{ $tempat->kode_qr_gate }}</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Slot Reguler --}}
      <div class="mt-4 pt-3 border-t border-slate-100">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kapasitas Slot Reguler</div>
        <div class="flex items-baseline justify-between mt-1">
          <span class="text-2xl font-black text-primary">{{ $sisaReguler }}</span>
          <span class="text-xs text-slate-500">dari {{ $tempat->kapasitas_reguler }} slot</span>
        </div>
        <div class="quota-bar mt-2"><span style="width: {{ $persenReguler }}%"></span></div>
      </div>

      {{-- Slot Inap --}}
      @if($tempat->sediakan_booking_inap)
      <div class="mt-4 pt-3 border-t border-slate-100">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Slot Menginap (Inap)</div>
        <div class="flex items-baseline justify-between mt-1">
          <span class="text-2xl font-black text-purple-700">{{ $sisaInap }}</span>
          <span class="text-xs text-slate-500">tersedia dari {{ $tempat->kuota_inap }} slot</span>
        </div>
        <div class="text-[11px] text-slate-500 mt-1">
          Terisi / terjadwal: {{ $terisiInap }} kendaraan inap
        </div>
      </div>
      @endif

      {{-- Fast Track Quick Gate Info --}}
      <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 space-y-1">
        <div class="font-bold text-slate-900 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>Fast-Track Gate Support</span>
        </div>
        <p class="text-[11.5px] leading-relaxed">Member Pass & Booking Inap terhubung otomatis ke Gerbang Mandiri (`/in/{{ $tempat->id_tempat_parkir }}`). Cukup masukkan plat nomor Anda di gerbang.</p>
      </div>
    </section>

    {{-- Kartu QRIS Tenant Resmi --}}
    <section class="bento-card text-center">
      <div class="ui-kpi-label mb-2">QRIS Pembayaran Resmi</div>
      <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl inline-block max-w-[200px]">
        <img src="{{ $qrisPath }}" alt="QRIS {{ $tempat->nama_tempat }}" class="w-full h-auto object-contain rounded-lg">
      </div>
      <div class="text-xs font-bold text-slate-800 mt-2">{{ $tempat->nama_tempat }}</div>
      <p class="text-[11px] text-slate-500 mt-0.5">Pindai kode QRIS di atas melalui m-Banking / e-Wallet saat membeli paket atau booking inap.</p>
    </section>
  </div>

  {{-- Area Konten Utama --}}
  <div class="d-span-8 space-y-5">
    
    {{-- 1. Paket Langganan Member --}}
    <section class="bento-card">
      <div class="flex items-start justify-between gap-3 mb-3">
        <div>
          <div class="ui-kpi-label">Paket Langganan Member</div>
          <div class="text-xs text-slate-500 mt-0.5">Akses parkir bulanan tanpa perlu bayar harian di kasir keluar.</div>
        </div>
        @if($tempat->sediakan_member)
          <span class="ui-badge ui-badge-success text-[10.5px]">Tersedia</span>
        @else
          <span class="ui-badge ui-badge-muted text-[10.5px]">Tidak Aktif</span>
        @endif
      </div>

      @if(!$tempat->sediakan_member)
        <div class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500 text-center">
          Layanan langganan member saat ini dinonaktifkan oleh pihak pengelola.
        </div>
      @elseif($tempat->paketLangganans->isEmpty())
        <div class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500 text-center">
          Belum ada paket langganan aktif yang didaftarkan untuk tempat parkir ini.
        </div>
      @else
        <div class="grid sm:grid-cols-2 gap-3">
          @foreach($tempat->paketLangganans as $pkt)
            <div class="border border-slate-200/90 rounded-2xl p-4 bg-white hover:border-primary/40 transition flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between gap-2">
                  <span class="font-heading font-black text-base text-[#212121]">{{ $pkt->nama_paket }}</span>
                  <span class="ui-badge ui-badge-info text-[10px]">{{ $pkt->jenis_kendaraan }}</span>
                </div>
                <div class="mt-2 text-2xl font-black text-primary">
                  Rp {{ number_format($pkt->harga_paket, 0, ',', '.') }}
                  <span class="text-xs font-normal text-slate-500">/ {{ $pkt->durasi_hari }} hari</span>
                </div>
                <div class="mt-2 space-y-1 text-xs text-slate-600">
                  <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Masa Aktif: <strong>{{ $pkt->durasi_hari }} Hari</strong></span>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>
                      Batas Masuk Harian: 
                      @if($pkt->batas_masuk_harian)
                        <strong>{{ $pkt->batas_masuk_harian }}x / hari</strong> (Auto-Fallback)
                      @else
                        <strong>Tak Terbatas (Unlimited)</strong>
                      @endif
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-4 pt-3 border-t border-slate-100">
                <button type="button" 
                        class="ui-btn ui-btn-primary w-full text-xs py-2"
                        @click="openOrderMember({{ Js::from(['id_paket'=>$pkt->id_paket,'nama_paket'=>$pkt->nama_paket,'harga'=>$pkt->harga_paket,'jenis_kendaraan'=>$pkt->jenis_kendaraan,'durasi'=>$pkt->durasi_hari]) }})">
                  Beli Member Pass Ini
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>

    {{-- 2. Booking Slot Inap --}}
    @if($tempat->sediakan_booking_inap)
    <section class="bento-card">
      <div class="flex items-start justify-between gap-3 mb-3">
        <div>
          <div class="ui-kpi-label">Booking Slot Parkir Inap</div>
          <div class="text-xs text-slate-500 mt-0.5">Amankan tempat inap kendaraan Anda sebelum tiba di lokasi.</div>
        </div>
        <span class="ui-badge ui-badge-info text-[10.5px]">Sisa {{ $sisaInap }} Slot</span>
      </div>

      <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="space-y-1 text-xs text-purple-950">
          <div class="font-bold text-sm text-purple-900">Perjalanan Menginap atau Tinggal Kendaraan?</div>
          <p class="leading-relaxed">Pesan slot inap lebih awal. Sistem akan mengunci slot dengan transaksi database aman, memberikan tiket digital terjadwal, dan gerbang otomatis terbuka saat tiba.</p>
        </div>
        <button type="button" 
                class="ui-btn bg-purple-700 hover:bg-purple-800 text-white text-xs py-2.5 px-4 shrink-0 shadow-sm"
                @click="openBookingModal()">
          Booking Inap Sekarang
        </button>
      </div>
    </section>
    @endif

    {{-- 3. Skema Tarif Reguler --}}
    <section class="bento-card">
      <div class="ui-kpi-label mb-1">Skema Tarif Parkir Reguler</div>
      <div class="text-xs text-slate-500 mb-3">Berlaku untuk pengunjung umum (Walk-In Self Check-In).</div>

      @if($tempat->tarif->isEmpty())
        <div class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500">
          Belum ada skema tarif yang tercatat di sistem untuk lokasi ini.
        </div>
      @else
        <div class="grid sm:grid-cols-2 gap-3">
          @foreach($tempat->tarif as $trf)
            <div class="tarif-card">
              <div class="flex items-center justify-between gap-2 mb-2">
                <span class="font-heading font-black text-sm text-slate-900">{{ $trf->jenis_kendaraan }}</span>
                <span class="ui-badge ui-badge-info text-[10px]">{{ $trf->tipe_tarif }}</span>
              </div>
              <div class="space-y-1 text-xs text-slate-600">
                @if($trf->tipe_tarif === 'Flat Harian')
                  <div>Tarif Flat: <strong class="text-slate-900">Rp {{ number_format($trf->tarif_per_jam, 0, ',', '.') }} / hari</strong></div>
                @elseif($trf->tipe_tarif === 'Per Jam')
                  <div>Tarif Per Jam: <strong class="text-slate-900">Rp {{ number_format($trf->tarif_per_jam, 0, ',', '.') }} / jam</strong></div>
                @else
                  <div>Jam Pertama: <strong class="text-slate-900">Rp {{ number_format($trf->tarif_jam_pertama, 0, ',', '.') }}</strong></div>
                  <div>Jam Berikutnya: <strong class="text-slate-900">Rp {{ number_format($trf->tarif_berikutnya, 0, ',', '.') }} / jam</strong></div>
                @endif
                <div>Tarif Inap Harian: <strong class="text-purple-700">{{ $trf->tarif_inap_harian > 0 ? 'Rp '.number_format($trf->tarif_inap_harian, 0, ',', '.').' / malam' : 'Tidak melayani' }}</strong></div>
                <div>Denda Karcis Hilang: <strong class="text-rose-600">Rp {{ number_format($trf->denda_karcis_hilang, 0, ',', '.') }}</strong></div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>

  </div>

  {{-- Modal Pembelian Member Pass --}}
  <div x-cloak x-show="showMemberModal" class="modal-backdrop" @click.self="showMemberModal=false">
    <div class="modal-panel" @click.stop>
      <div class="ui-card-h flex items-center justify-between gap-3">
        <div>
          <div class="ui-card-t">Beli Member Pass (Auto-Lunas Instan)</div>
          <div class="ui-card-d" x-text="memberOrder.nama_paket + ' — Rp ' + formatNumber(memberOrder.harga)"></div>
        </div>
        <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showMemberModal=false">Tutup</button>
      </div>

      <form method="POST" action="{{ route('pengendara.katalog.member.store', $tempat) }}" enctype="multipart/form-data" class="ui-card-b space-y-4">
        @csrf
        <input type="hidden" name="id_paket" :value="memberOrder.id_paket">

        {{-- Pilih Kendaraan --}}
        <div>
          <label class="ui-label">Pilih Kendaraan Anda</label>
          @if($kendaraanUser->isEmpty())
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
              Anda belum mendaftarkan kendaraan. Silakan tambahkan kendaraan di <a href="{{ route('pengendara.dashboard') }}" class="underline font-bold">Dashboard</a> terlebih dahulu.
            </div>
          @else
            <select name="id_kendaraan" class="ui-select" required>
              <option value="">-- Pilih Kendaraan --</option>
              @foreach($kendaraanUser as $ku)
                @php
                  $activeMem = $activeMembersByKendaraan[$ku->id_kendaraan] ?? null;
                @endphp
                <option value="{{ $ku->id_kendaraan }}" {{ $activeMem ? 'disabled' : '' }}>
                  {{ $ku->plat_nomor }} ({{ $ku->jenis_kendaraan }} - {{ $ku->warna }})
                  @if($activeMem)
                    — [Sudah Member Aktif s/d {{ \Carbon\Carbon::parse($activeMem->tanggal_mulai)->addDays($activeMem->paket->durasi_hari)->translatedFormat('d M Y') }}]
                  @endif
                </option>
              @endforeach
            </select>
            <div class="text-[11px] text-slate-400 mt-1">Pastikan jenis kendaraan sesuai paket. Kendaraan dengan member aktif di lokasi ini tidak dapat didaftarkan ulang, namun dapat didaftarkan di cabang tempat parkir lain.</div>
            @if(!empty($activeMembersByKendaraan) && count($activeMembersByKendaraan) > 0)
              <div class="mt-2.5 p-2.5 rounded-xl bg-sky-50 border border-sky-200 text-[11px] text-sky-800 flex items-start gap-2">
                <svg class="w-4 h-4 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kendaraan Anda yang sudah memiliki member aktif di <strong>{{ $tempat->nama_tempat }}</strong> dinonaktifkan dari pilihan untuk mencegah pembelian ganda. Kendaraan tersebut tetap dapat didaftarkan di lokasi parkir Parkirin lainnya.</span>
              </div>
            @endif
          @endif
        </div>

        {{-- Tanggal Mulai --}}
        <div>
          <label class="ui-label">Tanggal Mulai Berlaku</label>
          <input type="date" name="tanggal_mulai" class="ui-input" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
        </div>

        {{-- Info Tagihan & QRIS --}}
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-2">
          <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pembayaran QRIS</div>
          <div class="text-2xl font-black text-primary" x-text="'Rp ' + formatNumber(memberOrder.harga)"></div>
          <div class="pt-1">
            <img src="{{ $qrisPath }}" alt="QRIS Pembayaran" class="w-32 h-32 mx-auto rounded-lg border border-slate-200 bg-white p-1">
          </div>
          <p class="text-[10.5px] text-slate-500">Transfer tepat sesuai nominal tagihan melalui QRIS resmi di atas.</p>
        </div>

        {{-- Upload Bukti Bayar --}}
        <div>
          <label class="ui-label">Unggah Bukti Transfer / Resi QRIS</label>
          <input type="file" name="bukti_bayar" accept="image/*" class="ui-input text-xs" required>
          <div class="text-[11px] text-slate-400 mt-1">Format gambar (JPG, PNG, WebP), maksimal 4MB.</div>
        </div>

        {{-- Banner Auto-Lunas Instan --}}
        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 leading-relaxed">
          <strong class="font-bold">Auto-Lunas Instan:</strong> Setelah resi diunggah, Member Pass langsung berstatus <em>Lunas</em> dan aktif sehingga Anda tidak perlu menunggu verifikasi manual untuk masuk gerbang. Admin akan mencocokkan mutasi via Post-Audit.
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="ui-btn ui-btn-secondary" @click="showMemberModal=false">Batal</button>
          <button type="submit" class="ui-btn ui-btn-primary" @if($kendaraanUser->isEmpty()) disabled @endif>Bayar & Aktifkan Member Pass</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Booking Parkir Inap --}}
  <div x-cloak x-show="showBookingModal" class="modal-backdrop" @click.self="showBookingModal=false">
    <div class="modal-panel" @click.stop>
      <div class="ui-card-h flex items-center justify-between gap-3">
        <div>
          <div class="ui-card-t">Booking Slot Parkir Inap (Auto-Lunas)</div>
          <div class="ui-card-d">{{ $tempat->nama_tempat }} &bull; Sisa Kuota: {{ $sisaInap }} slot</div>
        </div>
        <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showBookingModal=false">Tutup</button>
      </div>

      <form method="POST" action="{{ route('pengendara.katalog.booking.store', $tempat) }}" enctype="multipart/form-data" class="ui-card-b space-y-4">
        @csrf

        {{-- Pilih Kendaraan --}}
        <div>
          <label class="ui-label">Pilih Kendaraan Anda</label>
          @if($kendaraanUser->isEmpty())
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
              Anda belum mendaftarkan kendaraan. Silakan tambahkan kendaraan di <a href="{{ route('pengendara.dashboard') }}" class="underline font-bold">Dashboard</a>.
            </div>
          @else
            <select name="id_kendaraan" class="ui-select" x-model="bookingForm.id_kendaraan" @change="recalcBooking()" required>
              <option value="">-- Pilih Kendaraan --</option>
              @foreach($kendaraanUser as $ku)
                <option value="{{ $ku->id_kendaraan }}" data-jenis="{{ $ku->jenis_kendaraan }}">
                  {{ $ku->plat_nomor }} ({{ $ku->jenis_kendaraan }} - {{ $ku->warna }})
                </option>
              @endforeach
            </select>
          @endif
        </div>

        {{-- Jadwal Rentang Inap --}}
        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="ui-label">Waktu Mulai Inap</label>
            <input type="datetime-local" name="waktu_mulai_inap" class="ui-input" x-model="bookingForm.waktu_mulai" @change="recalcBooking()" required>
          </div>
          <div>
            <label class="ui-label">Waktu Rencana Keluar</label>
            <input type="datetime-local" name="waktu_rencana_keluar" class="ui-input" x-model="bookingForm.waktu_keluar" @change="recalcBooking()" required>
          </div>
        </div>

        {{-- Kalkulasi Estimasi Biaya Inap --}}
        <template x-if="bookingForm.isValid">
          <div class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-200 text-center space-y-2">
            <div class="text-[11px] font-bold text-purple-900 uppercase tracking-wider">
              Estimasi Biaya Inap (<span x-text="bookingForm.nights"></span> Malam Inap)
            </div>
            <div class="text-2xl font-black text-purple-900" x-text="'Rp ' + formatNumber(bookingForm.totalBiaya)"></div>
            <div class="pt-1">
              <img src="{{ $qrisPath }}" alt="QRIS Pembayaran" class="w-32 h-32 mx-auto rounded-lg border border-slate-200 bg-white p-1">
            </div>
            <p class="text-[10.5px] text-purple-800">
              Dihitung dari <span class="font-bold" x-text="bookingForm.nights"></span> kali melewati pukul 00:00. Pindai QRIS resmi di atas dan bayar tepat sesuai total nominal.
            </p>
          </div>
        </template>

        {{-- Peringatan jika kurang dari 2 malam --}}
        <template x-if="!bookingForm.isValid">
          <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1.5">
            <div class="font-bold flex items-center gap-1.5 text-amber-800">
              <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <span>Minimal Pemesanan Inap: 2 Malam</span>
            </div>
            <p class="text-[11.5px] leading-relaxed">
              Jadwal yang dipilih hanya melewati <strong x-text="Math.max(0, bookingForm.nights)"></strong> malam (titik 00:00). Layanan booking inap di Parkirin diperuntukkan bagi pengendara yang menitipkan kendaraan <strong>minimal 2 malam ke atas</strong>.
            </p>
            <p class="text-[11px] text-amber-700">
              Untuk durasi 1 malam atau parkir harian biasa, Anda tidak perlu booking online—cukup datang langsung dan gunakan karcis reguler di gerbang masuk.
            </p>
          </div>
        </template>

        {{-- Upload Bukti Bayar --}}
        <div>
          <label class="ui-label">Unggah Bukti Transfer / Resi QRIS</label>
          <input type="file" name="bukti_bayar" accept="image/*" class="ui-input text-xs" required>
        </div>

        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 leading-relaxed">
          <strong class="font-bold">Optimistic Check-In:</strong> Slot inap langsung diamankan dengan status <em>Lunas & Terjadwal</em>. Saat jadwal tiba, gerbang akan langsung mendeteksi plat nomor Anda.
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="ui-btn ui-btn-secondary" @click="showBookingModal=false">Batal</button>
          <button type="submit" class="ui-btn bg-purple-700 hover:bg-purple-800 text-white disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" :disabled="!bookingForm.isValid || bookingForm.nights < 2 || @js($kendaraanUser->isEmpty() || $sisaInap <= 0)">Kirim & Amankan Slot Inap</button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  function katalogOrder(tarifs){
    return {
      showMemberModal: false,
      showBookingModal: false,
      memberOrder: { id_paket:null, nama_paket:'', harga:0, jenis_kendaraan:'', durasi:30 },
      bookingForm: { id_kendaraan:'', waktu_mulai:'', waktu_keluar:'', nights:2, isValid:true, totalBiaya:0 },
      tarifs: tarifs || {},

      openOrderMember(pkt){
        this.closeAll();
        this.memberOrder = Object.assign({}, pkt);
        this.showMemberModal = true;
      },
      openBookingModal(){
        this.closeAll();
        // default waktu mulai: 1 jam lagi
        const now = new Date();
        now.setHours(now.getHours() + 1, 0, 0, 0);
        // default waktu keluar: minimal 2 malam (2 hari kemudian)
        const twoNightsLater = new Date(now.getTime() + (2 * 24 * 60 * 60 * 1000));
        
        const pad = (n) => String(n).padStart(2, '0');
        const formatLocal = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;

        this.bookingForm.waktu_mulai = formatLocal(now);
        this.bookingForm.waktu_keluar = formatLocal(twoNightsLater);
        this.recalcBooking();
        this.showBookingModal = true;
      },
      recalcBooking(){
        const sel = document.querySelector('select[name="id_kendaraan"]');
        let jenis = 'Motor';
        if (sel && sel.selectedIndex >= 0) {
          const opt = sel.options[sel.selectedIndex];
          jenis = opt.getAttribute('data-jenis') || 'Motor';
        }
        
        const t = this.tarifs[jenis] || this.tarifs['Motor'] || {};
        const tarifInap = parseFloat(t.tarif_inap_harian || 0);

        if (this.bookingForm.waktu_mulai && this.bookingForm.waktu_keluar) {
          const start = new Date(this.bookingForm.waktu_mulai);
          const end = new Date(this.bookingForm.waktu_keluar);
          
          // Hitung berapa kali melewati titik 00:00 (pergantian malam kalender)
          const dStart = new Date(start.getFullYear(), start.getMonth(), start.getDate());
          const dEnd = new Date(end.getFullYear(), end.getMonth(), end.getDate());
          const diffMs = dEnd - dStart;
          const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

          this.bookingForm.nights = diffDays;
          this.bookingForm.isValid = diffDays >= 2;

          if (diffDays >= 2) {
            this.bookingForm.totalBiaya = diffDays * tarifInap;
          } else {
            this.bookingForm.totalBiaya = 0;
          }
        } else {
          this.bookingForm.nights = 0;
          this.bookingForm.isValid = false;
          this.bookingForm.totalBiaya = 0;
        }
      },
      closeAll(){
        this.showMemberModal = false;
        this.showBookingModal = false;
      },
      formatNumber(n){
        return Number(n || 0).toLocaleString('id-ID');
      }
    }
  }
</script>
@endpush
