@extends('layouts.staff')
@section('title','Transaksi Parkir')
@section('page-title','Daftar Transaksi Parkir')
@section('page-desc','Filter riwayat, tinjau, cetak lembar standar A4, atau unduh ekspor CSV transaksi.')

@section('staff-content')
<div class="page-h">
  <div>
    <h1 class="page-title sm:hidden">Transaksi Parkir</h1>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('owner.transaksi.export', request()->query()) }}" class="ui-btn ui-btn-secondary inline-flex items-center gap-1.5" title="Unduh CSV 3 Bagian (Perbandingan Tahunan, 7 Hari, dan Hasil Filter)">
      <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      <span>Ekspor CSV</span>
    </a>
    <a href="{{ route('owner.transaksi.print', request()->query()) }}" target="_blank" class="ui-btn ui-btn-primary inline-flex items-center gap-1.5" title="Buka pratinjau cetak standar kertas A4 atau simpan langsung ke file PDF">
      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      <span>Cetak / Simpan PDF (A4)</span>
    </a>
  </div>
</div>

<form method="GET" class="ui-card ui-card-b mb-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 items-end">
    <div>
      <label class="ui-label">Dari Tanggal</label>
      <input type="date" name="dari" value="{{ request('dari') }}" class="ui-input">
    </div>
    <div>
      <label class="ui-label">Sampai Tanggal</label>
      <input type="date" name="sampai" value="{{ request('sampai') }}" class="ui-input">
    </div>
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
      <select name="pembayaran" class="ui-select">
        <option value="">Semua</option>
        <option value="Sudah Bayar" @selected(request('pembayaran')==='Sudah Bayar')>Sudah Bayar (Lunas)</option>
        <option value="Belum Bayar" @selected(request('pembayaran')==='Belum Bayar')>Belum Bayar</option>
      </select>
    </div>
    <div class="xl:col-span-2 flex gap-2">
      <button class="ui-btn ui-btn-primary flex-1">Terapkan Filter</button>
      <a href="{{ route('owner.transaksi.index') }}" class="ui-btn ui-btn-secondary">Reset</a>
    </div>
  </div>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Total Transaksi (Filter)</div>
    <div class="ui-kpi mt-1">{{ number_format($summary['total'], 0, ',', '.') }}</div>
    <div class="text-[11px] text-gray-500 mt-1">
      Karcis: {{ number_format($summary['karcis_total'] ?? 0) }} &bull; Member: {{ number_format($summary['member_total'] ?? 0) }} &bull; Booking: {{ number_format($summary['booking_total'] ?? 0) }}
    </div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Pendapatan Lunas (Filter)</div>
    <div class="ui-kpi mt-1" style="color:#16a34a">Rp {{ number_format($summary['pendapatan'], 0, ',', '.') }}</div>
    <div class="text-[11px] text-gray-500 mt-1">
      Tunai: Rp {{ number_format($summary['tunai'] ?? 0, 0, ',', '.') }} &bull; Non-Tunai: Rp {{ number_format($summary['total_non_tunai'] ?? 0, 0, ',', '.') }}
    </div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Penerimaan Non-Tunai (Filter)</div>
    <div class="ui-kpi mt-1 text-primary">Rp {{ number_format($summary['total_non_tunai'] ?? 0, 0, ',', '.') }}</div>
    <div class="flex flex-wrap gap-1 mt-1 text-[10px]">
      <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold" title="QRIS di Pos Kasir Keluar">Pos: Rp {{ number_format($summary['non_tunai_karcis'] ?? 0, 0, ',', '.') }}</span>
      <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold" title="Member Pass Lunas">Member: Rp {{ number_format($summary['non_tunai_member'] ?? 0, 0, ',', '.') }}</span>
      <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold" title="Booking Inap Lunas">Inap: Rp {{ number_format($summary['non_tunai_booking'] ?? 0, 0, ',', '.') }}</span>
    </div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Belum Bayar (Filter)</div>
    <div class="ui-kpi mt-1" style="color:#d97706">Rp {{ number_format($summary['belum_bayar'] ?? 0, 0, ',', '.') }}</div>
    <div class="text-[11px] text-gray-500 mt-1">Tagihan parkir gerbang tertunda</div>
  </div>
</div>

{{-- Navigation Tabs: Karcis Gerbang | Langganan Member | Booking Inap --}}
<div class="flex items-center gap-2 border-b border-gray-200 mb-4 pb-2 overflow-x-auto">
  <a href="{{ route('owner.transaksi.index', array_merge(request()->query(), ['tab' => 'karcis'])) }}"
     class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ ($tab ?? 'karcis') === 'karcis' ? 'bg-primary text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
    <span>Karcis Gerbang (Pos)</span>
    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'karcis') === 'karcis' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700' }}">
      {{ number_format($summary['karcis_total'] ?? 0) }}
    </span>
  </a>
  <a href="{{ route('owner.transaksi.index', array_merge(request()->query(), ['tab' => 'member'])) }}"
     class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ ($tab ?? 'karcis') === 'member' ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
    <span>Langganan Member (Non-Tunai)</span>
    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'karcis') === 'member' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
      {{ number_format($summary['member_total'] ?? 0) }}
    </span>
  </a>
  <a href="{{ route('owner.transaksi.index', array_merge(request()->query(), ['tab' => 'booking'])) }}"
     class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ ($tab ?? 'karcis') === 'booking' ? 'bg-purple-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
    <span>Booking Inap (Non-Tunai)</span>
    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'karcis') === 'booking' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800' }}">
      {{ number_format($summary['booking_total'] ?? 0) }}
    </span>
  </a>
</div>

@if(($tab ?? 'karcis') === 'member')
  {{-- Table Langganan Member --}}
  <div class="ui-card overflow-hidden">
    <div class="p-3 bg-slate-50 border-b border-gray-100 flex items-center justify-between">
      <div class="text-xs font-bold text-gray-700 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        Data Pembayaran Langganan Member Terverifikasi
      </div>
      <div class="text-xs text-gray-500">
        Total Penerimaan Member: <strong class="text-emerald-600">Rp {{ number_format($summary['non_tunai_member'] ?? 0, 0, ',', '.') }}</strong>
      </div>
    </div>
    <div class="ui-table-wrap">
      <table class="ui-table">
        <thead>
          <tr>
            <th>Kode Member</th>
            <th>Pengendara</th>
            <th>Kendaraan</th>
            <th>Paket Langganan</th>
            <th>Waktu Bayar / Registrasi</th>
            <th>Mulai Aktif</th>
            <th class="text-right">Biaya Langganan</th>
            <th>Verifikator</th>
            <th class="text-center">Status Bayar</th>
          </tr>
        </thead>
        <tbody>
        @forelse($members as $m)
          <tr>
            <td class="font-mono font-bold text-gray-900">{{ $m->kode_member }}</td>
            <td>
              <div class="font-bold text-gray-900">{{ $m->pengendara->nama_lengkap ?? 'Rider' }}</div>
              <div class="text-[11px] text-gray-400 font-mono">{{ $m->pengendara->no_telepon ?? '-' }}</div>
            </td>
            <td>
              <span class="badge-plat">{{ $m->kendaraan->plat_nomor ?? '-' }}</span>
              <div class="text-xs text-gray-500 mt-0.5">{{ $m->kendaraan->jenis_kendaraan ?? '-' }}</div>
            </td>
            <td>
              <span class="font-semibold text-gray-800">{{ $m->paket->nama_paket ?? 'Paket Member' }}</span>
              <div class="text-[11px] text-gray-400">{{ $m->paket->durasi_hari ?? 30 }} hari &bull; Max {{ $m->paket->batas_masuk_harian ?? '∞' }}x/hari</div>
            </td>
            <td class="whitespace-nowrap text-gray-600">
              {{ $m->waktu_unggah_bukti ? \Carbon\Carbon::parse($m->waktu_unggah_bukti)->format('d/m/Y H:i') : ($m->waktu_registrasi ? \Carbon\Carbon::parse($m->waktu_registrasi)->format('d/m/Y H:i') : '-') }}
            </td>
            <td class="whitespace-nowrap text-gray-600">
              {{ $m->tanggal_mulai ? \Carbon\Carbon::parse($m->tanggal_mulai)->format('d/m/Y') : '-' }}
            </td>
            <td class="whitespace-nowrap text-right font-black text-emerald-600">
              Rp {{ number_format($m->biaya_langganan, 0, ',', '.') }}
            </td>
            <td class="text-gray-600 text-xs">
              {{ $m->userVerifikasi->nama_lengkap ?? 'Sistem / Otomatis' }}
            </td>
            <td class="text-center">
              <span class="ui-badge {{ $m->status_pembayaran === 'Lunas' ? 'ui-badge-success' : 'ui-badge-warning' }}">
                {{ $m->status_pembayaran }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-gray-500 py-10">Tidak ada transaksi langganan member pada filter ini.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-100">{{ $members->links() }}</div>
  </div>

@elseif(($tab ?? 'karcis') === 'booking')
  {{-- Table Booking Inap --}}
  <div class="ui-card overflow-hidden">
    <div class="p-3 bg-slate-50 border-b border-gray-100 flex items-center justify-between">
      <div class="text-xs font-bold text-gray-700 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
        Data Pembayaran Booking Inap Terverifikasi
      </div>
      <div class="text-xs text-gray-500">
        Total Penerimaan Inap: <strong class="text-purple-600">Rp {{ number_format($summary['non_tunai_booking'] ?? 0, 0, ',', '.') }}</strong>
      </div>
    </div>
    <div class="ui-table-wrap">
      <table class="ui-table">
        <thead>
          <tr>
            <th>Kode Booking</th>
            <th>Pengendara</th>
            <th>Kendaraan</th>
            <th>Jadwal Menginap</th>
            <th>Waktu Booking</th>
            <th class="text-right">Biaya Inap Lunas</th>
            <th>Verifikator</th>
            <th class="text-center">Status Bayar</th>
            <th class="text-center">Status Inap</th>
          </tr>
        </thead>
        <tbody>
        @forelse($bookings as $b)
          <tr>
            <td class="font-mono font-bold text-gray-900">{{ $b->kode_booking }}</td>
            <td>
              <div class="font-bold text-gray-900">{{ $b->pengendara->nama_lengkap ?? 'Rider' }}</div>
              <div class="text-[11px] text-gray-400 font-mono">{{ $b->pengendara->no_telepon ?? '-' }}</div>
            </td>
            <td>
              <span class="badge-plat">{{ $b->kendaraan->plat_nomor ?? '-' }}</span>
              <div class="text-xs text-gray-500 mt-0.5">{{ $b->kendaraan->jenis_kendaraan ?? '-' }}</div>
            </td>
            <td class="text-gray-700">
              <div class="font-semibold">{{ \Carbon\Carbon::parse($b->waktu_mulai_inap)->format('d/m/Y') }} &rarr; {{ \Carbon\Carbon::parse($b->waktu_rencana_keluar)->format('d/m/Y') }}</div>
            </td>
            <td class="whitespace-nowrap text-gray-600">
              {{ $b->waktu_booking ? \Carbon\Carbon::parse($b->waktu_booking)->format('d/m/Y H:i') : '-' }}
            </td>
            <td class="whitespace-nowrap text-right font-black text-purple-600">
              Rp {{ number_format($b->biaya_inap_dibayar, 0, ',', '.') }}
            </td>
            <td class="text-gray-600 text-xs">
              {{ $b->userVerifikasi->nama_lengkap ?? 'Sistem / Otomatis' }}
            </td>
            <td class="text-center">
              <span class="ui-badge {{ $b->status_pembayaran === 'Lunas' ? 'ui-badge-success' : 'ui-badge-warning' }}">
                {{ $b->status_pembayaran }}
              </span>
            </td>
            <td class="text-center">
              <span class="ui-badge ui-badge-info">
                {{ ucfirst($b->status ?? 'menunggu') }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-gray-500 py-10">Tidak ada transaksi booking inap pada filter ini.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-100">{{ $bookings->links() }}</div>
  </div>

@else
  {{-- Table Karcis Gerbang --}}
  <div class="ui-card overflow-hidden">
    <div class="p-3 bg-slate-50 border-b border-gray-100 flex items-center justify-between">
      <div class="text-xs font-bold text-gray-700 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-primary"></span>
        Data Transaksi Karcis Gerbang Masuk & Keluar
      </div>
      <div class="text-xs text-gray-500">
        Penerimaan Karcis: <strong class="text-emerald-600">Rp {{ number_format($summary['pendapatan_karcis'] ?? 0, 0, ',', '.') }}</strong>
        (Tunai: Rp {{ number_format($summary['tunai'] ?? 0, 0, ',', '.') }} &bull; Non-Tunai QRIS: Rp {{ number_format($summary['non_tunai_karcis'] ?? 0, 0, ',', '.') }})
      </div>
    </div>
    <div class="ui-table-wrap">
      <table class="ui-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Plat / Jenis</th>
            <th>Pengendara</th>
            <th>Waktu Masuk</th>
            <th>Waktu Keluar</th>
            <th>Durasi</th>
            <th>Biaya</th>
            <th>Metode</th>
            <th>Pembayaran</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        @forelse($transaksis as $t)
          <tr>
            <td class="text-gray-500">#{{ $t->id_transaksi }}</td>
            <td>
              <span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span>
              <div class="text-xs text-gray-500 mt-0.5">{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</div>
            </td>
            <td>{{ $t->pengendara->nama_lengkap ?? 'Offline' }}</td>
            <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_masuk)->format('d/m/Y H:i') }}</td>
            <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_keluar)->format('d/m/Y H:i') ?? '-' }}</td>
            <td class="whitespace-nowrap text-gray-600">{{ $t->durasi_jam !== null ? $t->durasi_jam.' jam' : '-' }}</td>
            <td class="whitespace-nowrap font-medium">{{ $t->biaya_total ? 'Rp '.number_format($t->biaya_total, 0, ',', '.') : '-' }}</td>
            <td class="whitespace-nowrap">
              @if($t->metode_pembayaran === 'Tunai')
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Tunai</span>
              @elseif($t->metode_pembayaran)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $t->metode_pembayaran }}</span>
              @else
                <span class="text-gray-400 text-xs">-</span>
              @endif
            </td>
            <td>
              <span class="ui-badge {{ $t->status_pembayaran === 'Sudah Bayar' ? 'ui-badge-success' : 'ui-badge-warning' }}">
                {{ $t->status_pembayaran }}
              </span>
            </td>
            <td>
              <span class="ui-badge {{ $t->status === 'masuk' ? 'ui-badge-warning' : 'ui-badge-success' }}">
                {{ ucfirst($t->status) }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center text-gray-500 py-10">Tidak ada transaksi yang cocok dengan filter ini.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-100">{{ $transaksis->links() }}</div>
  </div>
@endif
@endsection
