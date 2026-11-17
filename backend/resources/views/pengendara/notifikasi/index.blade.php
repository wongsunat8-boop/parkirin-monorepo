@extends('layouts.pengendara')

@section('title', 'Notifikasi — Parkirin')
@section('page-title', 'Pusat Notifikasi')
@section('page-desc', 'Pantau pengumuman sistem resmi, konfirmasi OTP WhatsApp, reservasi booking parkir, dan persetujuan klaim kepemilikan kendaraan.')

@section('pengendara-content')
{{-- Page Header --}}
<div class="page-h">
  <div>
    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
      <a href="{{ route('pengendara.dashboard') }}" class="hover:text-[#0D63A5] transition-colors">Pengendara</a>
      <span>/</span>
      <span class="text-slate-800 font-semibold">Notifikasi</span>
    </div>
    <h1 class="page-title">Pusat Notifikasi</h1>
    <p class="page-desc">Informasi sistem, status OTP, reservasi booking parkir, dan pengajuan klaim kendaraan Anda.</p>
  </div>
</div>

{{-- KPI Quick Metric Cards (Bento Grid) --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 mb-6">
  {{-- Card 1: Total Notifikasi Umum --}}
  <div class="bento-card relative overflow-hidden p-4 sm:p-5">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Notifikasi Umum</span>
      <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0D63A5] flex items-center justify-center">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
      </span>
    </div>
    <div class="font-heading font-extrabold text-2xl sm:text-3xl text-[#212121]">{{ $counts['total_umum'] ?? 0 }}</div>
    <p class="text-xs text-slate-500 mt-1">Pembaruan sistem, OTP & booking</p>
  </div>

  {{-- Card 2: Booking Parkir --}}
  <div class="bento-card relative overflow-hidden p-4 sm:p-5">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Reservasi & Tiket</span>
      <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </span>
    </div>
    <div class="font-heading font-extrabold text-2xl sm:text-3xl text-[#212121]">{{ $counts['booking'] ?? 0 }}</div>
    <p class="text-xs text-slate-500 mt-1">Aktivitas booking & transaksi</p>
  </div>

  {{-- Card 3: Keamanan & OTP --}}
  <div class="bento-card relative overflow-hidden p-4 sm:p-5">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Keamanan & OTP</span>
      <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </span>
    </div>
    <div class="font-heading font-extrabold text-2xl sm:text-3xl text-[#212121]">{{ $counts['keamanan'] ?? 0 }}</div>
    <p class="text-xs text-slate-500 mt-1">Status nomor WhatsApp & reputasi</p>
  </div>

  {{-- Card 4: Klaim Pending Masuk --}}
  <div class="bento-card relative overflow-hidden p-4 sm:p-5">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Klaim Menunggu</span>
      <span class="w-8 h-8 rounded-lg {{ ($counts['klaim_pending'] ?? 0) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </span>
    </div>
    <div class="font-heading font-extrabold text-2xl sm:text-3xl {{ ($counts['klaim_pending'] ?? 0) > 0 ? 'text-rose-600' : 'text-[#212121]' }}">
      {{ $counts['klaim_pending'] ?? 0 }}
    </div>
    <p class="text-xs text-slate-500 mt-1">Klaim kendaraan butuh aksi Anda</p>
  </div>
</div>

{{-- SECTION 1: NOTIFIKASI UMUM (SISTEM, BOOKING & OTP) DENGAN FILTER TAB --}}
<section class="ui-card mb-8" x-data="{ activeCategory: 'semua' }">
  <div class="ui-card-h flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <div class="ui-card-t flex items-center gap-2">
        <span>Pemberitahuan Umum & Aktivitas</span>
        <span class="ui-badge ui-badge-info text-[11px]">{{ $counts['total_umum'] ?? 0 }} Total</span>
      </div>
      <div class="ui-card-d">Pembaruan sistem Parkirin, status verifikasi OTP, dan riwayat reservasi booking parkir.</div>
    </div>

    {{-- Interactive Category Filter Tabs --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
      <button 
        type="button" 
        @click="activeCategory = 'semua'" 
        :class="activeCategory === 'semua' ? 'bg-[#0D63A5] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
      >
        <span>Semua</span>
        <span class="text-[10.5px] px-1.5 py-0.5 rounded-full" :class="activeCategory === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['total_umum'] ?? 0 }}</span>
      </button>

      <button 
        type="button" 
        @click="activeCategory = 'sistem'" 
        :class="activeCategory === 'sistem' ? 'bg-[#0D63A5] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
      >
        <span>Sistem</span>
        <span class="text-[10.5px] px-1.5 py-0.5 rounded-full" :class="activeCategory === 'sistem' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['sistem'] ?? 0 }}</span>
      </button>

      <button 
        type="button" 
        @click="activeCategory = 'booking'" 
        :class="activeCategory === 'booking' ? 'bg-[#0D63A5] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
      >
        <span>Booking Parkir</span>
        <span class="text-[10.5px] px-1.5 py-0.5 rounded-full" :class="activeCategory === 'booking' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['booking'] ?? 0 }}</span>
      </button>

      <button 
        type="button" 
        @click="activeCategory = 'keamanan'" 
        :class="activeCategory === 'keamanan' ? 'bg-[#0D63A5] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
      >
        <span>Keamanan & OTP</span>
        <span class="text-[10.5px] px-1.5 py-0.5 rounded-full" :class="activeCategory === 'keamanan' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['keamanan'] ?? 0 }}</span>
      </button>
    </div>
  </div>

  <div class="ui-card-b space-y-3">
    @forelse($notifikasiUmum as $notif)
      <article 
        x-show="activeCategory === 'semua' || activeCategory === '{{ $notif->kategori }}'" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 transform scale-[0.98]"
        x-transition:enter-end="opacity-100 transform scale-100"
        class="rounded-xl border border-slate-200 p-4 bg-white hover:border-slate-300 transition-all flex flex-col sm:flex-row items-start gap-3.5"
      >
        {{-- Icon Container by Category --}}
        <div class="shrink-0 mt-0.5">
          @if($notif->kategori === 'sistem')
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0D63A5] border border-blue-100 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          @elseif($notif->kategori === 'booking')
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          @else
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
          @endif
        </div>

        {{-- Content Area --}}
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <h3 class="font-heading font-bold text-sm text-[#212121]">{{ $notif->judul }}</h3>

            {{-- Category Pill --}}
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full 
              {{ $notif->kategori === 'sistem' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
              {{ $notif->kategori === 'booking' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
              {{ $notif->kategori === 'keamanan' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}"
            >
              {{ ucfirst($notif->kategori) }}
            </span>

            {{-- Status Pill --}}
            @if(!empty($notif->status_label))
              @php
                $statusBadgeClass = match($notif->status_color) {
                  'emerald' => 'ui-badge-success',
                  'amber'   => 'ui-badge-warning',
                  'rose'    => 'ui-badge-danger',
                  'blue'    => 'ui-badge-info',
                  default   => 'ui-badge-muted',
                };
              @endphp
              <span class="ui-badge {{ $statusBadgeClass }} text-[10.5px] py-0.5 px-2">{{ $notif->status_label }}</span>
            @endif

            <span class="text-[11.5px] text-slate-400 ml-auto" title="{{ $notif->waktu->format('d M Y, H:i') }}">
              {{ $notif->waktu->diffForHumans() }}
            </span>
          </div>

          <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed mb-2.5">
            {{ $notif->pesan }}
          </p>

          {{-- Optional Quick Action Button --}}
          @if(!empty($notif->action_url) && !empty($notif->action_label))
            <div>
              <a href="{{ $notif->action_url }}" class="btn-action-edit inline-flex items-center gap-1.5 text-xs py-1.5 px-3">
                <span>{{ $notif->action_label }}</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </a>
            </div>
          @endif
        </div>
      </article>
    @empty
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-sm text-slate-500">
        Belum ada notifikasi umum untuk akun Anda.
      </div>
    @endforelse
  </div>
</section>

{{-- SECTION 2: KLAIM KENDARAAN PRIBADI (2 DEDICATED CARDS) --}}
<div class="mb-4">
  <h2 class="font-heading font-extrabold text-lg text-[#212121]">Klaim Kendaraan Pribadi</h2>
  <p class="text-xs text-slate-500">Persetujuan pemindahan kepemilikan kendaraan dan riwayat pengajuan klaim Anda.</p>
</div>

<div class="grid gap-4 lg:grid-cols-2">
  {{-- Card A: Klaim Kendaraan Masuk --}}
  <section class="ui-card">
    <div class="ui-card-h flex items-center justify-between">
      <div>
        <div class="ui-card-t flex items-center gap-2">
          <span>Klaim Kendaraan Masuk</span>
          @if(($counts['klaim_pending'] ?? 0) > 0)
            <span class="ui-badge ui-badge-danger text-[11px]">{{ $counts['klaim_pending'] }} Perlu Aksi</span>
          @endif
        </div>
        <div class="ui-card-d">Pengajuan klaim dari orang lain yang menargetkan kendaraan di akun Anda.</div>
      </div>
    </div>

    <div class="ui-card-b space-y-3">
      @forelse($masuk as $k)
        @php
          $badge = match($k->status_klaim) { 
            'Pending'   => 'ui-badge-warning', 
            'Disetujui' => 'ui-badge-success', 
            'Ditolak'   => 'ui-badge-danger', 
            default     => 'ui-badge-muted' 
          };
        @endphp
        <article class="rounded-xl border border-slate-200 p-3.5 bg-white hover:border-slate-300 transition-all">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="badge-plat font-bold px-2.5 py-1 rounded bg-slate-800 text-white text-xs font-mono tracking-wider">
                  {{ $k->kendaraan->plat_nomor ?? '-' }}
                </span>
                <span class="ui-badge {{ $badge }} text-xs">{{ $k->status_klaim }}</span>
              </div>
              <div class="text-sm text-slate-700 mt-2">
                Diajukan oleh: <span class="font-semibold text-[#212121]">{{ $k->pengaju->nama_lengkap ?? '-' }}</span>
              </div>
              <div class="text-[11.5px] text-slate-500 mt-0.5">
                {{ $k->pengaju->email ?? '' }} • {{ $k->tanggal_pengajuan ? $k->tanggal_pengajuan->format('d M Y, H:i') : '-' }}
              </div>
              <div class="text-[11.5px] text-slate-500 mt-1">
                Tipe Kendaraan: <span class="font-medium text-slate-700">{{ $k->kendaraan->jenis_kendaraan ?? '-' }}</span> ({{ $k->kendaraan->warna ?? '-' }})
              </div>
            </div>
          </div>

          {{-- Action buttons for pending claim --}}
          @if($k->status_klaim === 'Pending')
            <div class="flex items-center gap-2 flex-wrap mt-3 pt-3 border-t border-slate-100">
              <form method="POST" action="{{ route('pengendara.notifikasi.klaim.setujui', $k) }}" class="m-0" onsubmit="return confirm('Setujui klaim ini? Kepemilikan kendaraan akan dipindahkan otomatis ke pengaju.');">
                @csrf
                <button type="submit" class="btn-action-success">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Setujui Pemindahan</span>
                </button>
              </form>

              <form method="POST" action="{{ route('pengendara.notifikasi.klaim.tolak', $k) }}" class="m-0" onsubmit="return confirm('Tolak klaim kendaraan ini?');">
                @csrf
                <button type="submit" class="btn-action-danger">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span>Tolak Klaim</span>
                </button>
              </form>
            </div>
          @endif
        </article>
      @empty
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-sm text-slate-500">
          Tidak ada pengajuan klaim kendaraan masuk saat ini.
        </div>
      @endforelse
    </div>
  </section>

  {{-- Card B: Riwayat Klaim yang Diajukan --}}
  <section class="ui-card">
    <div class="ui-card-h">
      <div class="ui-card-t">Riwayat Klaim yang Diajukan</div>
      <div class="ui-card-d">Status pengajuan klaim kepemilikan kendaraan yang telah Anda kirimkan.</div>
    </div>

    <div class="ui-card-b space-y-3">
      @forelse($diajukan as $k)
        @php
          $badge = match($k->status_klaim) { 
            'Pending'   => 'ui-badge-warning', 
            'Disetujui' => 'ui-badge-success', 
            'Ditolak'   => 'ui-badge-danger', 
            default     => 'ui-badge-muted' 
          };
        @endphp
        <article class="rounded-xl border border-slate-200 p-3.5 bg-white hover:border-slate-300 transition-all">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="badge-plat font-bold px-2.5 py-1 rounded bg-slate-800 text-white text-xs font-mono tracking-wider">
                  {{ $k->kendaraan->plat_nomor ?? '-' }}
                </span>
                <span class="ui-badge {{ $badge }} text-xs">{{ $k->status_klaim }}</span>
              </div>
              <div class="text-sm text-slate-700 mt-2">
                Pemilik Sebelumnya: <span class="font-semibold text-[#212121]">{{ $k->pengendaraTarget->nama_lengkap ?? '-' }}</span>
              </div>
              <div class="text-[11.5px] text-slate-500 mt-0.5">
                Diajukan pada: {{ $k->tanggal_pengajuan ? $k->tanggal_pengajuan->format('d M Y, H:i') : '-' }}
              </div>
              @if($k->status_klaim === 'Disetujui')
                <div class="text-xs text-emerald-700 mt-2 font-semibold flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Klaim disetujui! Kendaraan telah resmi berpindah ke akun Anda.</span>
                </div>
              @elseif($k->status_klaim === 'Ditolak')
                <div class="text-xs text-rose-700 mt-2 flex items-center gap-1.5 bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200">
                  <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span>Klaim ditolak oleh pemilik saat ini.</span>
                </div>
              @else
                <div class="text-xs text-amber-700 mt-2 flex items-center gap-1.5 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200">
                  <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Menunggu konfirmasi atau verifikasi dari pemilik kendaraan.</span>
                </div>
              @endif
            </div>
          </div>
        </article>
      @empty
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-sm text-slate-500">
          Belum ada riwayat pengajuan klaim kendaraan.
        </div>
      @endforelse
    </div>
  </section>
</div>
@endsection