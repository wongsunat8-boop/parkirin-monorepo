@extends('layouts.staff')
@section('title', 'Dashboard Petugas')
@section('page-title', 'Overview Petugas Lapangan')
@section('page-desc', ($tenant->nama_tempat ?? 'Tenant').' — monitoring kuota slot parkir dan pantau transaksi aktif secara real-time.')

@section('staff-content')
@php
    $kap = (int)($tenant->kapasitas ?? 0);
    $ter = (int)($tenant->terisi ?? 0);
    $sisa = max(0, $kap - $ter);
    $pct = $kap > 0 ? round(($ter / $kap) * 100) : 0;
    $isCritical = $kap > 0 && $sisa <= 3;
@endphp

{{-- ═══════════════════════════════════════════
    TOP KPI BENTO GRID (3 Cards)
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-6">
    {{-- Card 1: Okupansi & Kuota Terisi --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isCritical ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-blue-50 text-primary border border-blue-200/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $isCritical ? 'bg-rose-500 animate-ping' : 'bg-primary' }}"></span>
                {{ $sisa }} Slot Sisa
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kuota Terisi</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ $ter }} <span class="text-base font-normal text-slate-400">/ {{ $kap }}</span>
            </div>
            <p class="text-xs {{ $isCritical ? 'text-rose-600 font-semibold' : 'text-slate-500' }} mt-1">
                {{ $isCritical ? 'Perhatian: Kuota slot hampir habis!' : $pct.'% kapasitas terisi' }}
            </p>
        </div>
    </div>

    {{-- Card 2: Transaksi Aktif Lapangan --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Di Area
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Transaksi Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-primary tracking-tight">
                {{ number_format($transaksiAktif->total()) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Kendaraan belum checkout dari gerbang</p>
        </div>
    </div>

    {{-- Card 3: Status Layanan Tenant --}}
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
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Status Layanan</div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ ($tenant->sediakan_booking ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->sediakan_booking ?? 0) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    Booking {{ ($tenant->sediakan_booking ?? 0) ? 'Aktif' : 'Nonaktif' }}
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    Gerbang {{ ($tenant->status_aktif ?? 0) ? 'Buka' : 'Tutup' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Sinkronisasi operasional gerbang</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    QUICK OPERATIONAL NOTICE BANNER
═══════════════════════════════════════════ --}}
<div class="mb-6 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="font-bold text-xs text-[#212121]">Aplikasi Mobile Petugas Parkirin</div>
            <div class="text-[11px] text-slate-500">Gunakan scanner OCR kamera di aplikasi Flutter untuk transaksi Masuk, Cetak Karcis Termal, dan Verifikasi QR Booking (grace period 10 menit).</div>
        </div>
    </div>
    <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Sistem Siap
    </span>
</div>

{{-- ═══════════════════════════════════════════
    MAIN BENTO CARD: Active Transactions Table
═══════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
        <div>
            <h3 class="font-heading font-extrabold text-base text-[#212121]">Daftar Kendaraan Aktif di Area</h3>
            <p class="text-xs text-slate-500">Seluruh kendaraan yang telah check-in dan belum melakukan checkout pembayaran</p>
        </div>
        <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
            <span>{{ $transaksiAktif->total() }} Kendaraan</span>
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                    <th class="py-3 px-3">Plat Nomor Kendaraan</th>
                    <th class="py-3 px-3">Jenis</th>
                    <th class="py-3 px-3">Nama Pengendara</th>
                    <th class="py-3 px-3">Waktu Masuk Gerbang</th>
                    <th class="py-3 px-3">Durasi Parkir</th>
                    <th class="py-3 px-3 text-right">Status Karcis</th>
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
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                {{ $t->status_pembayaran }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            Tidak ada kendaraan yang sedang parkir saat ini. Seluruh slot kosong dan siap menerima pengendara.
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
