@extends('layouts.app')

@section('title', 'Struk Digital #'.$transaksi->id_transaksi.' — Parkirin')

@section('content')
<div class="min-h-screen bg-secondary py-8 px-4">
  <div class="max-w-lg mx-auto">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden print:shadow-none print:border-slate-300">
      <div class="px-6 py-5 bg-primary text-white">
        <div class="flex items-center justify-between gap-3">
          <div>
            <div class="font-heading font-extrabold text-xl tracking-tight">Parkirin</div>
            <div class="text-white/80 text-xs mt-0.5">Struk / Karcis Digital</div>
          </div>
          <div class="text-right text-xs text-white/85">
            <div>ID #{{ $transaksi->id_transaksi }}</div>
            <div>{{ now()->format('d M Y H:i') }}</div>
          </div>
        </div>
      </div>
      <div class="p-6 space-y-4 text-sm text-[#212121]">
        <div class="grid grid-cols-2 gap-3">
          <div><div class="text-[11px] uppercase tracking-[.05em] text-slate-500 font-semibold">Lokasi</div><div class="font-semibold mt-0.5">{{ $transaksi->tempatParkir->nama_tempat ?? '-' }}</div></div>
          <div><div class="text-[11px] uppercase tracking-[.05em] text-slate-500 font-semibold">Kendaraan</div><div class="mt-1"><span class="badge-plat" style="font-size:12px;">{{ $transaksi->kendaraan->plat_nomor ?? '-' }}</span></div><div class="text-xs text-slate-500 mt-1">{{ $transaksi->kendaraan->jenis_kendaraan ?? '-' }} • {{ $transaksi->kendaraan->warna ?? '-' }}</div></div>
        </div>
        <div class="border-t border-dashed border-slate-200"></div>
        <div class="grid grid-cols-2 gap-3 text-[13px]">
          <div><span class="text-slate-500">Waktu Masuk</span><div class="font-semibold">{{ $transaksi->waktu_masuk?->format('d M Y, H:i') ?? '-' }}</div></div>
          <div><span class="text-slate-500">Waktu Keluar</span><div class="font-semibold">{{ $transaksi->waktu_keluar?->format('d M Y, H:i') ?? '-' }}</div></div>
          <div><span class="text-slate-500">Durasi</span><div class="font-semibold">{{ $transaksi->durasi_jam ? $transaksi->durasi_jam.' jam' : '-' }}</div></div>
          <div><span class="text-slate-500">Tarif</span><div class="font-semibold">{{ $transaksi->tarif->tipe_tarif ?? '-' }} ({{ $transaksi->tarif->jenis_kendaraan ?? '-' }})</div></div>
          <div><span class="text-slate-500">Status Parkir</span><div><span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $transaksi->status==='masuk' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($transaksi->status) }}</span></div></div>
          <div><span class="text-slate-500">Pembayaran</span><div><span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $transaksi->status_pembayaran==='Sudah Bayar' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $transaksi->status_pembayaran }}</span></div></div>
        </div>
        <div class="border-t border-dashed border-slate-200"></div>
        <div class="flex items-center justify-between gap-3">
          <span class="font-semibold text-slate-600">Biaya Total</span>
          <span class="font-heading font-extrabold text-xl text-[#212121]">@if(!is_null($transaksi->biaya_total)) Rp {{ number_format((float)$transaksi->biaya_total,0,',','.') }} @else - @endif</span>
        </div>
        @if($transaksi->booking)
          <div class="text-[12px] text-slate-500">Terkait booking: <span class="font-mono font-semibold text-slate-700">{{ $transaksi->booking->kode_booking }}</span></div>
        @endif
      </div>
      <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 print:hidden">
        <a href="{{ route('pengendara.riwayat.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:.75rem;padding:6px 12px;font:600 12px/1.2 Inter,sans-serif;border:1px solid #e2e8f0;background:#fff;color:#212121;text-decoration:none;">Kembali</a>
        <button type="button" onclick="window.print()" class="ui-btn ui-btn-primary ui-btn-sm" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:.75rem;padding:6px 12px;font:600 12px/1.2 Inter,sans-serif;border:1px solid transparent;background:#0D63A5;color:#fff;cursor:pointer;">Cetak / Unduh PDF</button>
      </div>
    </div>
  </div>
</div>
@endsection
