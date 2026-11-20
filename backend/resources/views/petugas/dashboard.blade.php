@extends('layouts.staff')
@section('title', 'Dashboard Petugas')
@section('page-title', 'Pantau Area Parkir')
@section('page-desc', ($tenant->nama_tempat ?? 'Lokasi').' — Pantau kendaraan masuk & keluar secara langsung.')

@section('staff-content')
@php
    $kap = (int)($tenant->kapasitas ?? 0);
    $ter = (int)($tenant->terisi ?? 0);
    $sisa = max(0, $kap - $ter);
    $pct = $kap > 0 ? round(($ter / $kap) * 100) : 0;
    $isCritical = $kap > 0 && $sisa <= 3;
@endphp

{{-- ═══════════════════════════════════════════
    KPI CARDS (3 Kartu Utama)
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-6">
    {{-- Card 1: Slot Tersedia --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isCritical ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-blue-50 text-primary border border-blue-200/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $isCritical ? 'bg-rose-500 animate-ping' : 'bg-primary' }}"></span>
                {{ $sisa }} Slot Tersedia
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kendaraan di Area</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ $ter }} <span class="text-base font-normal text-slate-400">/ {{ $kap }}</span>
            </div>
            <p class="text-xs {{ $isCritical ? 'text-rose-600 font-semibold' : 'text-slate-500' }} mt-1">
                {{ $isCritical ? 'Perhatian: Slot hampir penuh!' : $pct.'% terisi' }}
            </p>
        </div>
    </div>

    {{-- Card 2: Kendaraan Aktif --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Sedang Parkir
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kendaraan Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-primary tracking-tight">
                {{ number_format($transaksiAktif->total()) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Belum keluar dari area parkir</p>
        </div>
    </div>

    {{-- Card 3: Status Layanan --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                Fasilitas
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Layanan Tersedia</div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ ($tenant->sediakan_booking ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->sediakan_booking ?? 0) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    Booking {{ ($tenant->sediakan_booking ?? 0) ? 'Aktif' : 'Nonaktif' }}
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    Lokasi {{ ($tenant->status_aktif ?? 0) ? 'Buka' : 'Tutup' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Pengaturan oleh Admin lokasi</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    TOMBOL INPUT MANUAL (FALLBACK)
═══════════════════════════════════════════ --}}
<div class="mb-6" x-data="{ showManual: false }">
    <button type="button" @click="showManual = !showManual"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:bg-primary-dark transition active:scale-[0.98]">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span>Catat Kendaraan Masuk (Manual)</span>
    </button>
    <p class="text-xs text-slate-500 mt-1.5">Gunakan tombol ini jika HP pengendara mati atau tidak bisa scan sendiri.</p>

    {{-- Form Input Manual --}}
    <div x-show="showManual" x-transition x-cloak class="mt-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-bold text-sm text-[#212121]">Formulir Catat Masuk Manual</h3>
                <p class="text-xs text-slate-500">Dicatat oleh petugas — karcis terbit otomatis</p>
            </div>
        </div>

        <form action="{{ route('petugas.manual-checkin') }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="ui-label">Plat Nomor</label>
                    <input type="text" name="plat_nomor" required placeholder="B 1234 XYZ" class="ui-input uppercase" autocomplete="off">
                </div>
                <div>
                    <label class="ui-label">Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" required class="ui-select">
                        <option value="">— Pilih —</option>
                        @foreach($supportedVehicles as $v)
                            <option value="{{ $v }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label">Warna (opsional)</label>
                    <input type="text" name="warna" placeholder="Hitam, Merah, dsb" class="ui-input">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-xs shadow-sm hover:bg-primary-dark transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Catat Masuk
                </button>
                <button type="button" @click="showManual = false" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    TABEL KENDARAAN DI AREA
═══════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
        <div>
            <h3 class="font-heading font-extrabold text-base text-[#212121]">Kendaraan Sedang Parkir</h3>
            <p class="text-xs text-slate-500">Semua kendaraan yang sudah masuk tapi belum keluar</p>
        </div>
        <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
            <span>{{ $transaksiAktif->total() }} Kendaraan</span>
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                    <th class="py-3 px-3">Plat Nomor</th>
                    <th class="py-3 px-3">Jenis</th>
                    <th class="py-3 px-3">Pengendara</th>
                    <th class="py-3 px-3">Waktu Masuk</th>
                    <th class="py-3 px-3">Lama Parkir</th>
                    <th class="py-3 px-3 text-right">Tagihan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transaksiAktif as $t)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-3">
                            <span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-semibold text-slate-700">{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-slate-600">{{ $t->pengendara->nama_lengkap ?? 'Walk-in / Karcis Fisik' }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap">
                            {{ optional($t->waktu_masuk)->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-primary border border-blue-200/60">
                                {{ optional($t->waktu_masuk)->diffForHumans(null, true) }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            @if($t->metode_pembayaran === 'Member_Pass' && $t->status_pembayaran === 'Sudah Bayar')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    Rp 0 (Member)
                                </span>
                            @elseif($t->metode_pembayaran === 'Prepaid_Booking' && $t->status_pembayaran === 'Sudah Bayar')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                                    Rp 0 (Booking)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    {{ $t->status_pembayaran }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            Belum ada kendaraan di area parkir. Semua slot kosong dan siap digunakan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transaksiAktif->hasPages())
        <div class="pt-4 border-t border-slate-100 mt-4">
            {{ $transaksiAktif->links() }}
        </div>
    @endif
</div>
@endsection
