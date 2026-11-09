@extends('layouts.staff')
@section('title', 'Dashboard Owner')
@section('page-title', 'Overview Owner')
@section('page-desc', ($tenant->nama_tempat ?? 'Tenant').' — monitoring arus operasional harian, deteksi anomali anti-fraud, dan rekap finansial.')

@section('staff-content')
@php
    $selisih = $totalMasukHariIni - $totalKeluarHariIni;
    $hasAnomaly = $anomaliMenginap->isNotEmpty();
    $ratioKeluar = $totalMasukHariIni > 0 ? round(($totalKeluarHariIni / $totalMasukHariIni) * 100) : 0;
@endphp

{{-- ═══════════════════════════════════════════
    TOP KPI BENTO GRID (4 Cards)
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
    {{-- Card 1: Kendaraan Aktif Saat Ini --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-primary border border-blue-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                Real-time
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kendaraan Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-primary tracking-tight">
                {{ number_format($kendaraanAktif) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Sedang berada di area parkir</p>
        </div>
    </div>

    {{-- Card 2: Total Masuk Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                Hari Ini
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Masuk</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ number_format($totalMasukHariIni) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ now()->translatedFormat('d M Y') }}</p>
        </div>
    </div>

    {{-- Card 3: Total Keluar Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200/60">
                Checkout
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Keluar</div>
            <div class="text-3xl font-heading font-extrabold text-sky-600 tracking-tight">
                {{ number_format($totalKeluarHariIni) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Transaksi checkout selesai</p>
        </div>
    </div>

    {{-- Card 4: Anomali Menginap (> 24 Jam) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl {{ $hasAnomaly ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $hasAnomaly ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' }}">
                {{ $hasAnomaly ? 'Perlu Cek' : 'Normal' }}
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Anomali &gt; 24 Jam</div>
            <div class="text-3xl font-heading font-extrabold {{ $hasAnomaly ? 'text-rose-600' : 'text-emerald-600' }} tracking-tight">
                {{ $anomaliMenginap->count() }}
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $hasAnomaly ? 'Kendaraan menginap lama' : 'Tidak ada potensi anomali' }}</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    ANTI-FRAUD AUDIT ALERT BANNER (If Selisih Detected)
═══════════════════════════════════════════ --}}
@if($kendaraanAktif !== $selisih)
    <div class="mb-6 p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 shadow-2xs flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 text-xs text-amber-900 leading-relaxed">
            <div class="font-bold text-sm text-amber-950 mb-0.5">Indikasi Audit Anti-Fraud: Perbedaan Selisih Kendaraan</div>
            Kendaraan aktif saat ini (<strong class="font-semibold">{{ $kendaraanAktif }}</strong> unit) berbeda dari selisih transaksi masuk–keluar hari ini (<strong class="font-semibold">{{ $selisih }}</strong> unit).
            Hal ini lazim terjadi bila ada kendaraan yang masuk pada hari sebelumnya dan belum checkout. Silakan periksa daftar kendaraan menginap di bawah bila angka menunjukkan perbedaan tidak wajar.
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════
    MIDDLE BENTO GRID: 7-Day Flow Chart & Daily Audit Summary
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Wide Chart Card (2 Cols) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Arus Kendaraan 7 Hari Terakhir</h3>
                <p class="text-xs text-slate-500">Volume transaksi masuk vs keluar di {{ $tenant->nama_tempat ?? 'Tenant' }}</p>
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
                <canvas id="ownerFlowChart"></canvas>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
            <span>Rata-rata mingguan terkalkulasi otomatis</span>
            <span class="font-semibold text-slate-700">Update setiap transaksi baru</span>
        </div>
    </div>

    {{-- Right Summary Card (1 Col) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-[#212121]">Ringkasan Hari Ini</h3>
                    <p class="text-xs text-slate-500">Kondisi operasional cepat</p>
                </div>
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">Audit</span>
            </div>

            <div class="space-y-3.5">
                {{-- Progress Bar: Tingkat Checkout --}}
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-semibold text-slate-700">Rasio Checkout Keluar</span>
                        <span class="font-bold text-primary">{{ $ratioKeluar }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ min(100, $ratioKeluar) }}%"></div>
                    </div>
                </div>

                {{-- Key Metric Rows --}}
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-600">Total Kedatangan</span>
                        <span class="font-bold text-[#212121]">{{ number_format($totalMasukHariIni) }} unit</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-600">Total Kepulangan</span>
                        <span class="font-bold text-sky-600">{{ number_format($totalKeluarHariIni) }} unit</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-600">Sisa Aktif di Area</span>
                        <span class="font-bold text-primary">{{ number_format($kendaraanAktif) }} unit</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-600">Anomali &gt; 24 Jam</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $hasAnomaly ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $anomaliMenginap->count() }} unit
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2">
            <a href="{{ route('owner.transaksi.index') }}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Lihat Rekap Transaksi</span>
            </a>
            <a href="{{ route('owner.pengajuan-banned.index') }}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 text-[#212121] border border-slate-200/80 font-semibold text-xs rounded-xl hover:bg-slate-100 transition">
                <span>Kelola Pengajuan Banned</span>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    BOTTOM BENTO GRID: Anomali Menginap & Recent Transactions
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5">
    {{-- Card: Anomali Menginap --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Kendaraan Menginap &gt; 24 Jam</h3>
                <p class="text-xs text-slate-500">Daftar transaksi berstatus aktif yang melewati ambang 24 jam untuk pencegahan kecurangan</p>
            </div>
            <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $hasAnomaly ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $hasAnomaly ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                {{ $anomaliMenginap->count() }} Kendaraan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-3">Plat Nomor</th>
                        <th class="py-3 px-3">Jenis Kendaraan</th>
                        <th class="py-3 px-3">Waktu Masuk</th>
                        <th class="py-3 px-3">Durasi Berada di Area</th>
                        <th class="py-3 px-3">Pengendara</th>
                        <th class="py-3 px-3 text-right">Status Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($anomaliMenginap as $t)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3">
                                <span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-700">{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ optional($t->waktu_masuk)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                    {{ optional($t->waktu_masuk)->diffForHumans(null, true) }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="text-slate-600">{{ $t->pengendara->nama_lengkap ?? 'Offline / Walk-in' }}</span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    Verifikasi Lapangan
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada transaksi yang melebihi batas 24 jam. Operasional tenant bersih dari anomali.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Card: Transaksi Terbaru Lapangan --}}
    @if(isset($recentTransaksi) && $recentTransaksi->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Aktivitas Transaksi Terbaru</h3>
                <p class="text-xs text-slate-500">8 pergerakan gerbang terakhir di lokasi parkir Anda</p>
            </div>
            <a href="{{ route('owner.transaksi.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                <span>Semua Transaksi</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-3">Plat Nomor</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Status Gerbang</th>
                        <th class="py-3 px-3">Waktu Masuk</th>
                        <th class="py-3 px-3">Waktu Keluar</th>
                        <th class="py-3 px-3 text-right">Biaya / Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentTransaksi as $rt)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3">
                                <span class="badge-plat">{{ $rt->kendaraan->plat_nomor ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-700">
                                {{ $rt->kendaraan->jenis_kendaraan ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                @if($rt->status === 'masuk')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-primary border border-blue-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                        Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ optional($rt->waktu_masuk)->translatedFormat('d M, H:i') }}
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $rt->waktu_keluar ? optional($rt->waktu_keluar)->translatedFormat('d M, H:i') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($rt->biaya_total)
                                    <span class="font-bold text-[#212121]">Rp {{ number_format($rt->biaya_total, 0, ',', '.') }}</span>
                                    <span class="text-[10px] block text-emerald-600 font-semibold">{{ $rt->status_pembayaran }}</span>
                                @else
                                    <span class="text-slate-400 font-semibold">Sedang Parkir</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function renderOwnerChart() {
    const el = document.getElementById('ownerFlowChart');
    if(!el) return;
    if(typeof Chart === 'undefined') {
        setTimeout(renderOwnerChart, 50);
        return;
    }

    const chartData = @json($chart ?? []);
    if (!chartData || !chartData.labels) return;

    new Chart(el, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Masuk',
                    data: chartData.masuk,
                    backgroundColor: 'rgba(13, 99, 165, 0.85)',
                    borderRadius: 6,
                    maxBarThickness: 32
                },
                {
                    label: 'Keluar',
                    data: chartData.keluar,
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
                            return ' ' + context.dataset.label + ': ' + context.raw + ' kendaraan';
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
                    grid: { color: '#F1F5F9' },
                    ticks: { precision: 0, color: '#64748B', font: { size: 11 } }
                }
            }
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderOwnerChart);
} else {
    renderOwnerChart();
}
</script>
@endpush
