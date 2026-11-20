@extends('layouts.pengendara')

@section('title', 'Riwayat Parkir — Parkirin')
@section('page-title', 'Riwayat Parkir')
@section('page-desc', 'Riwayat transaksi parkir dengan filter status, pembayaran QRIS resmi, dan struk digital.')

@section('pengendara-content')
<div x-data="{ 
    showQrisModal: false, 
    activeTransaksi: { 
        id: '', 
        action_url: '', 
        kode_karcis: '', 
        tempat_nama: '', 
        plat_nomor: '', 
        jenis_kendaraan: '', 
        waktu_masuk: '', 
        durasi_jam: 1, 
        total_biaya: 'Rp 0', 
        qris_url: '' 
    }, 
    openQrisModal(data) { 
        this.activeTransaksi = data; 
        this.showQrisModal = true; 
    } 
}">

<div class="page-h">
  <div>
    <h1 class="page-title">Riwayat Parkir</h1>
    <p class="page-desc">Daftar lengkap riwayat parkir dan pembayaran kendaraan Anda.</p>
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
          @php
            $biayaHitung = !is_null($t->biaya_total) && $t->biaya_total > 0 ? $t->biaya_total : ($t->biaya_dasar > 0 ? $t->biaya_dasar : 0);
            $qrisImg = $t->tempatParkir?->qris_image ? asset($t->tempatParkir->qris_image) : asset('assets/qris/default_qris.svg');
          @endphp
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
                  <button type="button" 
                    @click="openQrisModal({{ Js::from([
                      'id' => $t->id_transaksi,
                      'action_url' => route('pengendara.riwayat.bayar', $t),
                      'kode_karcis' => $t->kode_karcis,
                      'tempat_nama' => $t->tempatParkir->nama_tempat ?? 'Parkirin',
                      'plat_nomor' => $t->kendaraan->plat_nomor ?? '-',
                      'jenis_kendaraan' => $t->kendaraan->jenis_kendaraan ?? '-',
                      'waktu_masuk' => $t->waktu_masuk ? $t->waktu_masuk->format('d/m/Y H:i') . ' WIB' : '-',
                      'durasi_jam' => $t->durasi_jam,
                      'total_biaya' => 'Rp ' . number_format((float)$biayaHitung, 0, ',', '.'),
                      'qris_url' => $qrisImg,
                    ]) }})"
                    class="btn-action-success flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Bayar QRIS</span>
                  </button>
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
      <div class="text-xs text-slate-500">Menampilkan riwayat transaksi parkir. Gunakan tombol untuk melihat halaman lain.</div>
      <div class="flex items-center gap-2">
        @if($riwayat->previousPageUrl())<a href="{{ $riwayat->previousPageUrl() }}" class="ui-btn ui-btn-secondary ui-btn-sm">Sebelumnya</a>@endif
        @if($riwayat->nextPageUrl())<a href="{{ $riwayat->nextPageUrl() }}" class="ui-btn ui-btn-secondary ui-btn-sm">Berikutnya</a>@endif
      </div>
    </div>
  @endif
</div>

{{-- Modal Pembayaran QRIS Resmi Tenant --}}
<div x-cloak x-show="showQrisModal" class="modal-backdrop" @click.self="showQrisModal=false">
  <div class="modal-panel" @click.stop>
    <div class="ui-card-h flex items-center justify-between gap-3">
      <div>
        <div class="ui-card-t">Pembayaran Parkir QRIS Resmi</div>
        <div class="ui-card-d" x-text="activeTransaksi.tempat_nama + ' • Karcis ' + activeTransaksi.kode_karcis"></div>
      </div>
      <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showQrisModal=false">Tutup</button>
    </div>

    <div class="ui-card-b space-y-4">
      {{-- Ringkasan Kendaraan & Waktu --}}
      <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 grid grid-cols-2 gap-3 text-xs">
        <div>
          <span class="text-slate-500 block">Kendaraan:</span>
          <span class="font-mono font-bold text-dark text-sm" x-text="activeTransaksi.plat_nomor"></span>
          <span class="text-slate-400 block text-[11px]" x-text="'(' + activeTransaksi.jenis_kendaraan + ')'"></span>
        </div>
        <div>
          <span class="text-slate-500 block">Waktu Masuk:</span>
          <span class="font-semibold text-dark" x-text="activeTransaksi.waktu_masuk"></span>
          <span class="text-slate-500 block text-[11px]" x-text="'Durasi: ' + activeTransaksi.durasi_jam + ' Jam'"></span>
        </div>
      </div>

      {{-- Total Tagihan & Gambar QRIS --}}
      <div class="p-4 rounded-2xl bg-gradient-to-b from-blue-50/60 to-white border border-blue-200/80 text-center space-y-2">
        <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Total Tagihan Parkir</div>
        <div class="text-3xl font-heading font-black text-primary" x-text="activeTransaksi.total_biaya"></div>

        <div class="pt-2">
          <img :src="activeTransaksi.qris_url" alt="QRIS Resmi Tenant" class="w-48 h-48 mx-auto rounded-xl border border-slate-200 bg-white p-2 shadow-sm object-contain">
        </div>

        <div class="pt-2 text-xs text-slate-600 max-w-sm mx-auto leading-relaxed">
          Pindai QRIS resmi pengelola di atas menggunakan mobile banking (BCA, Mandiri, BRI, BNI) atau dompet digital (GoPay, OVO, Dana, ShopeePay).
        </div>
      </div>

      {{-- Form Konfirmasi Pelunasan --}}
      <form :action="activeTransaksi.action_url" method="POST" class="pt-2">
        @csrf
        <div class="flex items-center justify-end gap-2">
          <button type="button" class="ui-btn ui-btn-secondary" @click="showQrisModal=false">Batal</button>
          <button type="submit" class="ui-btn ui-btn-primary flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Konfirmasi Sudah Bayar QRIS</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

</div>
@endsection
