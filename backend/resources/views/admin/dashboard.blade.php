@extends('layouts.staff')
@section('title', 'Dashboard Admin')
@section('page-title', 'Ringkasan Admin')
@section('page-desc', ($tenant->nama_tempat ?? 'Lokasi Parkir').' — ringkasan operasional harian, kapasitas tempat parkir, tarif, dan aktivitas petugas.')

@section('staff-content')
@php
    $kap = (int)($tenant->kapasitas ?? 0);
    $ter = (int)($tenant->terisi ?? 0);
    $sisa = max(0, $kap - $ter);
    $pct = $kap > 0 ? round(($ter / $kap) * 100) : 0;
    $unv = (int)($unverifiedCount ?? 0);
@endphp

{{-- ═══════════════════════════════════════════
    PEMBERITAHUAN BUKTI BAYAR BELUM DICEK
═══════════════════════════════════════════ --}}
@if($unv > 0)
<div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200/90 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
    <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <div class="font-heading font-extrabold text-sm text-amber-950 flex items-center gap-2">
                <span>Pemberitahuan: Ada {{ $unv }} Pembayaran QRIS yang Perlu Dicek</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900">{{ $unv }} Belum Dicek</span>
            </div>
            <p class="text-xs text-amber-800 mt-0.5">Segera periksa bukti transfer yang diunggah dengan uang masuk di mutasi QRIS/bank.</p>
        </div>
    </div>
    <a href="{{ route('admin.verifikasi.index') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0">
        Buka Verifikasi &rarr;
    </a>
</div>
@endif

{{-- ═══════════════════════════════════════════
    TOP KPI BENTO GRID (4 Cards)
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
    {{-- Card 1: Kendaraan Aktif --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-primary border border-blue-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                Di Area
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kendaraan Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-primary tracking-tight">
                {{ number_format($stats['aktif']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Sedang berada di area parkir</p>
        </div>
    </div>

    {{-- Card 2: Okupansi Parkir --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pct >= 90 ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60' }}">
                {{ $pct }}% Terisi
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tempat Parkir Terisi</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ $ter }} <span class="text-base font-normal text-slate-400">/ {{ $kap }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $sisa }} tempat masih kosong</p>
        </div>
    </div>

    {{-- Card 3: Skema Tarif & Paket --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                Layanan
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tarif & Paket</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ number_format($stats['tarif']) }} <span class="text-base font-normal text-slate-400">/ {{ number_format($stats['paket']) }} paket</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Skema tarif & paket langganan</p>
        </div>
    </div>

    {{-- Card 4: Verifikasi Bukti Bayar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl {{ $unv > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $unv > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $unv > 0 ? 'bg-amber-500 animate-ping' : 'bg-emerald-500' }}"></span>
                {{ $unv > 0 ? 'Perlu Dicek' : 'Sudah Beres' }}
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Cek Pembayaran Online</div>
            <div class="text-3xl font-heading font-extrabold {{ $unv > 0 ? 'text-amber-600' : 'text-emerald-600' }} tracking-tight">
                {{ number_format($unv) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $unv > 0 ? 'Ada bukti bayar yang belum dicek' : 'Semua pembayaran sudah dicek' }}</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    MIDDLE BENTO GRID: 7-Day Bar Chart & Okupansi / Service Controls
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Wide Chart Card (2 Cols) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Aktivitas Transaksi 7 Hari Terakhir</h3>
                <p class="text-xs text-slate-500">Volume pergerakan kendaraan masuk vs keluar di gerbang</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200/60">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#0D63A5]"></span> Masuk
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200/60">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#16A34A]"></span> Keluar
                </span>
            </div>
        </div>

        <div class="py-4">
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="adminFlowChart"></canvas>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
            <span>Data diperbarui otomatis dari sistem gerbang</span>
            <span class="font-semibold text-primary">Live Data</span>
        </div>
    </div>

    {{-- Right Card (1 Col): Status Kapasitas, Booking & Actions --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-[#212121]">Keterisian & Layanan</h3>
                    <p class="text-xs text-slate-500">Kondisi tempat parkir dan status layanan</p>
                </div>
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">Status</span>
            </div>

            {{-- Mini Chart / Meter --}}
            <div class="py-2 flex flex-col items-center justify-center">
                <div class="relative w-36 h-36">
                    <canvas id="adminOccChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-heading font-black text-[#212121]">{{ $pct }}%</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Terisi</span>
                    </div>
                </div>
            </div>

            {{-- Feature Status Badges --}}
            <div class="space-y-2.5 text-xs pt-2">
                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-600">Layanan Booking Inap</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($tenant->sediakan_booking_inap ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->sediakan_booking_inap ?? 0) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ ($tenant->sediakan_booking_inap ?? 0) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-600">Layanan Paket Member</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($tenant->sediakan_member ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->sediakan_member ?? 0) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ ($tenant->sediakan_member ?? 0) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-slate-600">Status Operasional Lahan</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ ($tenant->status_aktif ?? 0) ? 'Buka' : 'Tutup' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Direct Shortcut Grid --}}
        <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 mt-2">
            <a href="{{ route('admin.verifikasi.index') }}" class="ui-btn ui-btn-primary text-xs">
                Verifikasi Bayar
            </a>
            <a href="{{ route('admin.paket-langganan.index') }}" class="ui-btn ui-btn-secondary text-xs">
                Paket Member
            </a>
            <a href="{{ route('admin.tarif.index') }}" class="ui-btn ui-btn-secondary text-xs">
                Kelola Tarif
            </a>
            <a href="{{ route('admin.kapasitas.index') }}" class="ui-btn ui-btn-secondary text-xs">
                Kapasitas
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    BOTTOM BENTO GRID: Operational Management Hub
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
    <a href="{{ route('admin.verifikasi.index') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 hover:shadow-md hover:border-primary/40 transition flex items-center justify-between group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="font-bold text-sm text-[#212121]">Verifikasi Bayar</div>
                <div class="text-xs text-slate-400">Periksa bukti transfer QRIS</div>
            </div>
        </div>
        <span class="text-slate-300 group-hover:text-amber-600 transition font-bold text-base">&rarr;</span>
    </a>

    <a href="{{ route('admin.paket-langganan.index') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 hover:shadow-md hover:border-primary/40 transition flex items-center justify-between group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <div class="font-bold text-sm text-[#212121]">Paket Langganan</div>
                <div class="text-xs text-slate-400">Batas masuk & tarif member</div>
            </div>
        </div>
        <span class="text-slate-300 group-hover:text-indigo-600 transition font-bold text-base">&rarr;</span>
    </a>

    <a href="{{ route('admin.petugas.index') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 hover:shadow-md hover:border-primary/40 transition flex items-center justify-between group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="font-bold text-sm text-[#212121]">Petugas Gerbang</div>
                <div class="text-xs text-slate-400">Atur akun shift operasional</div>
            </div>
        </div>
        <span class="text-slate-300 group-hover:text-primary transition font-bold text-base">&rarr;</span>
    </a>

    <a href="{{ route('admin.pengendara.index') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 hover:shadow-md hover:border-primary/40 transition flex items-center justify-between group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            </div>
            <div>
                <div class="font-bold text-sm text-[#212121]">Daftar Pengendara</div>
                <div class="text-xs text-slate-400">Riwayat kunjungan & reputasi</div>
            </div>
        </div>
        <span class="text-slate-300 group-hover:text-purple-600 transition font-bold text-base">&rarr;</span>
    </a>
</div>
@endsection

@push('scripts')
<script>
function renderAdminCharts() {
    const flow = document.getElementById('adminFlowChart');
    const occ = document.getElementById('adminOccChart');
    if (typeof Chart === 'undefined') {
        setTimeout(renderAdminCharts, 50);
        return;
    }

    const data = @json($chart);

    if(flow && data && data.labels){
        new Chart(flow, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Masuk',
                        data: data.masuk,
                        backgroundColor: 'rgba(13, 99, 165, 0.85)',
                        borderRadius: 6,
                        maxBarThickness: 32
                    },
                    {
                        label: 'Keluar',
                        data: data.keluar,
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: 6,
                        maxBarThickness: 32
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': ' + context.raw + ' transaksi';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#64748B', font: { size: 11 } },
                        grid: { color: '#F1F5F9' }
                    }
                }
            }
        });
    }

    if(occ){
        const ter = {{ $ter }};
        const sisa = {{ $sisa }};
        new Chart(occ, {
            type: 'doughnut',
            data: {
                labels: ['Terisi', 'Tersedia'],
                datasets: [{
                    data: [ter, sisa],
                    backgroundColor: ['#0D63A5', '#E2E8F0'],
                    borderWidth: 0,
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + ' slot';
                            }
                        }
                    }
                }
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderAdminCharts);
} else {
    renderAdminCharts();
}
</script>
@endpush
