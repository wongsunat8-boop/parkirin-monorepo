@extends('layouts.staff')
@section('title', 'Dashboard Admin')
@section('page-title', 'Overview Admin')
@section('page-desc', ($tenant->nama_tempat ?? 'Tenant').' — ringkasan operasional harian, manajemen kuota kapasitas, tarif, dan log petugas.')

@section('staff-content')
@php
    $kap = (int)($tenant->kapasitas ?? 0);
    $ter = (int)($tenant->terisi ?? 0);
    $sisa = max(0, $kap - $ter);
    $pct = $kap > 0 ? round(($ter / $kap) * 100) : 0;
@endphp

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
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kapasitas Okupansi</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ $ter }} <span class="text-base font-normal text-slate-400">/ {{ $kap }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $sisa }} slot sisa tersedia</p>
        </div>
    </div>

    {{-- Card 3: Skema Tarif Aktif --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                Tarif
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tarif Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ number_format($stats['tarif']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Skema tarif operasional berjalan</p>
        </div>
    </div>

    {{-- Card 4: Petugas Aktif --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Siap Bertugas
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Petugas Lapangan</div>
            <div class="text-3xl font-heading font-extrabold text-emerald-600 tracking-tight">
                {{ number_format($stats['petugas']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Akun operator gerbang aktif</p>
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
            <span>Sinkronisasi data otomatis dengan database tenant</span>
            <span class="font-semibold text-primary">Live Data</span>
        </div>
    </div>

    {{-- Right Card (1 Col): Status Kapasitas, Booking & Actions --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-[#212121]">Okupansi & Layanan</h3>
                    <p class="text-xs text-slate-500">Kondisi slot dan fasilitas tenant</p>
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
                    <span class="text-slate-600">Layanan Booking Online</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($tenant->sediakan_booking ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->sediakan_booking ?? 0) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ ($tenant->sediakan_booking ?? 0) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-600">Status Operasional Lahan</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($tenant->status_aktif ?? 0) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ ($tenant->status_aktif ?? 0) ? 'Buka' : 'Tutup' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-slate-600">Kendaraan Langganan (Offline)</span>
                    <span class="font-extrabold text-[#212121]">{{ number_format($stats['kendaraan']) }} unit</span>
                </div>
            </div>
        </div>

        {{-- Direct Shortcut Grid --}}
        <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 mt-2">
            <a href="{{ route('admin.tarif.index') }}" class="ui-btn ui-btn-primary text-xs">
                Kelola Tarif
            </a>
            <a href="{{ route('admin.kapasitas.index') }}" class="ui-btn ui-btn-secondary text-xs">
                Kapasitas
            </a>
            <a href="{{ route('admin.log-aktivitas.index') }}" class="ui-btn ui-btn-secondary col-span-2 text-xs">
                Log Aktivitas Tenant
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    BOTTOM BENTO GRID: Operational Management Hub
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="{{ route('admin.petugas.index') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 hover:shadow-md hover:border-primary/40 transition flex items-center justify-between group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="font-bold text-sm text-[#212121]">Kelola Petugas Gerbang</div>
                <div class="text-xs text-slate-400">Atur akun shift operasional</div>
            </div>
        </div>
        <span class="text-slate-300 group-hover:text-primary transition font-bold text-base">&rarr;</span>
    </a>

    <a href="{{ route('admin.kendaraan.index') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 hover:shadow-md hover:border-primary/40 transition flex items-center justify-between group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
            </div>
            <div>
                <div class="font-bold text-sm text-[#212121]">Master Kendaraan Offline</div>
                <div class="text-xs text-slate-400">Pendaftaran & pengajuan banned</div>
            </div>
        </div>
        <span class="text-slate-300 group-hover:text-amber-600 transition font-bold text-base">&rarr;</span>
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
