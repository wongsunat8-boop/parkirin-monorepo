@extends('layouts.staff')
@section('title', 'Dashboard Pemilik & Perbandingan Tahunan')
@section('page-title', 'Ringkasan Pendapatan & Perbandingan Tahunan')
@section('page-desc', ($tenant->nama_tempat ?? 'Lokasi Parkir').' — pantau pemasukan, perbandingan dengan tahun lalu (2025 vs 2026), perputaran tempat parkir, dan rekap kasir.')

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
    {{-- Card 1: Perputaran Tempat Parkir --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-primary border border-blue-200/60">
                Perputaran
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Perputaran Tempat Parkir</div>
            <div class="text-3xl font-heading font-extrabold text-primary tracking-tight">
                {{ $yoyMetrics['slot_turnover'] }}<span class="text-base font-normal text-slate-400">x / tempat</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Rata-rata pergantian mobil/motor per tempat hari ini</p>
        </div>
    </div>

    {{-- Card 2: Kenaikan dari Tahun Lalu --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl {{ $yoyMetrics['growth_omzet'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $yoyMetrics['growth_omzet'] >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                {{ $yoyMetrics['growth_omzet'] >= 0 ? '+' : '' }}{{ $yoyMetrics['growth_omzet'] }}% vs Thn Lalu
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Pemasukan 2026 (Tahun Ini)</div>
            <div class="text-2xl font-heading font-extrabold text-[#212121] tracking-tight">
                Rp {{ number_format($yoyMetrics['omzet_2026'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">vs Rp {{ number_format($yoyMetrics['omzet_2025'], 0, ',', '.') }} (Tahun 2025)</p>
        </div>
    </div>

    {{-- Card 3: Kendaraan Aktif Real-time --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                Di Lahan
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kendaraan Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ number_format($kendaraanAktif) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Total masuk hari ini: {{ number_format($totalMasukHariIni) }}</p>
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
            <p class="text-xs text-slate-500 mt-1">{{ $hasAnomaly ? 'Kendaraan menginap lama tanpa tiket inap' : 'Tidak ada potensi manipulasi' }}</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    PEMBERITAHUAN SELISIH KENDARAAN
═══════════════════════════════════════════ --}}
@if($kendaraanAktif !== $selisih)
    <div class="mb-6 p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 shadow-2xs flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 text-xs text-amber-900 leading-relaxed">
            <div class="font-bold text-sm text-amber-950 mb-0.5">Pemberitahuan Selisih: Jumlah Kendaraan Berbeda</div>
            Kendaraan aktif saat ini (<strong class="font-semibold">{{ $kendaraanAktif }}</strong> unit) berbeda dari selisih transaksi masuk–keluar hari ini (<strong class="font-semibold">{{ $selisih }}</strong> unit).
            Hal ini wajar jika ada kendaraan menginap dari hari sebelumnya, namun periksa daftar parkir lebih dari 24 jam di bawah untuk memastikan tidak ada karcis yang lupa diproses kasir.
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════
    SECTION ANALITIK MULTI-TAHUN & PERBANDINGAN ARUS (YoY)
═══════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md bg-primary/10 text-primary font-bold text-xs uppercase tracking-wider">Perkembangan Tahunan</span>
                <h3 class="font-heading font-extrabold text-lg text-[#212121]">
                    Perkembangan Pendapatan &amp; Jumlah Kendaraan: Tahun {{ min($yoyMetrics['available_years'] ?? [2025, 2026]) }} s/d {{ max($yoyMetrics['available_years'] ?? [2025, 2026]) }}
                </h3>
            </div>
            <p class="text-xs text-slate-500 mt-1">Pantau perbandingan uang masuk dan ramainya kendaraan yang parkir dari tahun ke tahun.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach($yoyChart['datasets'] as $ds)
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg border border-slate-200/80 bg-slate-50 text-slate-700">
                    <span class="w-2.5 h-2.5 rounded-xs" style="background-color: {{ $ds['borderColor'] }}"></span>
                    {{ $ds['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- Ringkasan Metrik Tahunan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 my-5">
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pendapatan {{ $yoyMetrics['current_year'] - 1 }} (Tahun Lalu)</div>
            <div class="text-lg font-heading font-extrabold text-slate-700 mt-0.5">
                Rp {{ number_format($yoyMetrics['omzet_2025'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ number_format($yoyMetrics['vol_2025']) }} total kendaraan</div>
        </div>

        <div class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-200/60">
            <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Pendapatan {{ $yoyMetrics['current_year'] }} (Tahun Ini)</div>
            <div class="text-lg font-heading font-black text-primary mt-0.5">
                Rp {{ number_format($yoyMetrics['omzet_2026'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-blue-700 mt-0.5">{{ number_format($yoyMetrics['vol_2026']) }} total kendaraan</div>
        </div>

        <div class="p-3.5 rounded-xl {{ $yoyMetrics['growth_omzet'] >= 0 ? 'bg-emerald-50/70 border-emerald-200/70' : 'bg-rose-50 border-rose-200' }} border">
            <div class="text-[11px] font-bold uppercase tracking-wider {{ $yoyMetrics['growth_omzet'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Kenaikan Pendapatan</div>
            <div class="text-lg font-heading font-black {{ $yoyMetrics['growth_omzet'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-0.5">
                {{ $yoyMetrics['growth_omzet'] >= 0 ? '+' : '' }}{{ $yoyMetrics['growth_omzet'] }}%
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">Dibanding tahun sebelumnya</div>
        </div>

        <div class="p-3.5 rounded-xl bg-purple-50/60 border border-purple-200/60">
            <div class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Kenaikan Jumlah Kendaraan</div>
            <div class="text-lg font-heading font-black text-purple-700 mt-0.5">
                {{ $yoyMetrics['growth_vol'] >= 0 ? '+' : '' }}{{ $yoyMetrics['growth_vol'] }}%
            </div>
            <div class="text-[11px] text-purple-600 mt-0.5">Dibanding tahun sebelumnya</div>
        </div>
    </div>

    {{-- Grid 2 Kolom: Kiri (Grafik Garis Pendapatan dengan Tab) & Kanan (Grafik Mini Jumlah Kendaraan Tiap Tahun) --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-2">
        {{-- Kolom Kiri: Diagram Garis Pendapatan Murni dengan Tab (2 Kolom) --}}
        <div class="xl:col-span-2 flex flex-col justify-between">
            {{-- Toolbar Tab: Pilihan Tampilan (Per Bulan vs Per Tahun) & Pilihan Tahun --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-600">Pilihan Tampilan:</span>
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 text-xs font-semibold">
                        <button type="button" id="btnModeMonthly" onclick="switchChartMode('monthly')"
                                class="px-3.5 py-1.5 rounded-lg transition-all duration-150 bg-white text-slate-900 shadow-2xs font-bold">
                            Per Bulan (Jan – Des)
                        </button>
                        <button type="button" id="btnModeYearly" onclick="switchChartMode('yearly')"
                                class="px-3.5 py-1.5 rounded-lg transition-all duration-150 text-slate-500 hover:text-slate-800">
                            Per Tahun
                        </button>
                    </div>
                </div>

                {{-- Tab Pilihan Tahun --}}
                <div class="flex items-center gap-2" id="yearTabWrapper">
                    <span class="text-xs font-bold text-slate-600">Pilih Tahun:</span>
                    <div id="yearTabGroup" class="inline-flex flex-wrap items-center gap-1 p-1 bg-slate-100 rounded-xl border border-slate-200/80 text-xs font-semibold transition-opacity duration-200">
                        <button type="button" data-year="all" onclick="selectYearFilter('all')"
                                class="year-tab-btn px-2.5 py-1 rounded-lg transition-all duration-150 text-slate-600 hover:text-slate-900">
                            Semua Tahun
                        </button>
                        @foreach($yoyMetrics['available_years'] as $yr)
                            @php $isCurrent = ($yr === $yoyMetrics['current_year']); @endphp
                            <button type="button" data-year="{{ $yr }}" onclick="selectYearFilter({{ $yr }})"
                                    class="year-tab-btn px-2.5 py-1 rounded-lg transition-all duration-150 {{ $isCurrent ? 'bg-primary text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                                Tahun {{ $yr }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Langsung Menampilkan Canvas Diagram Garis Tanpa Banner Tambahan --}}
            <div class="relative h-72 sm:h-84 w-full">
                <canvas id="yoyOmzetChart"></canvas>
            </div>
            <p class="text-xs text-slate-500 mt-2 text-right" id="chartSubDesc">
                Grafik garis pendapatan parkir tahun {{ $yoyMetrics['current_year'] }} per bulan (Januari – Desember)
            </p>
        </div>

        {{-- Kolom Kanan: Grafik Mini Perbandingan Jumlah Kendaraan Tiap Tahun (1 Kolom) --}}
        <div class="xl:col-span-1 flex flex-col justify-between bg-slate-50/80 rounded-xl p-4 border border-slate-200/70">
            <div>
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/70">
                    <div>
                        <h4 class="font-heading font-bold text-sm text-slate-800">Perbandingan Kendaraan Tiap Tahun</h4>
                        <p class="text-[11px] text-slate-500">Total kendaraan yang parkir tiap tahun</p>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">Jumlah Kendaraan</span>
                </div>

                {{-- Canvas Mini Chart Arus Tahunan --}}
                <div class="relative h-44 sm:h-48 w-full mb-3">
                    <canvas id="annualFlowChart"></canvas>
                </div>

                {{-- Detail Peningkatan Tiap Tahun --}}
                <div class="space-y-2">
                    @foreach($yoyMetrics['annual_summary'] as $sum)
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-slate-200/80 text-xs shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-xs" style="background-color: {{ $sum['color'] }}"></span>
                                <div>
                                    <span class="font-bold text-slate-800">Tahun {{ $sum['year'] }}</span>
                                    @if($sum['is_current'])
                                        <span class="text-[9px] font-extrabold text-primary bg-primary/10 px-1.5 py-0.2 rounded ml-1">Tahun Ini</span>
                                    @endif
                                    <div class="text-[10.5px] text-slate-400">{{ number_format($sum['volume']) }} kendaraan</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-700">Rp {{ number_format($sum['omzet'], 0, ',', '.') }}</div>
                                @if($sum['growth_volume'] !== null)
                                    <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.2 rounded {{ $sum['growth_volume'] >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50' }}">
                                        {{ $sum['growth_volume'] >= 0 ? '+' : '' }}{{ $sum['growth_volume'] }}% Kendaraan
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400">Tahun Pertama</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="text-[10px] text-slate-400 mt-3 pt-2 border-t border-slate-200/60 text-center">
                Memudahkan pemilik melihat apakah jumlah kendaraan yang parkir semakin ramai setiap tahunnya.
            </p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    MIDDLE BENTO GRID: 7-Day Flow Chart & Ringkasan Harian
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Wide Chart Card (2 Cols) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Arus Kendaraan 7 Hari Terakhir</h3>
                <p class="text-xs text-slate-500">Volume transaksi masuk vs keluar di gerbang</p>
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
            <span>Rata-rata perputaran slot harian</span>
            <span class="font-semibold text-slate-700">Live Data</span>
        </div>
    </div>

    {{-- Right Summary Card (1 Col) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-[#212121]">Aksi Cepat & Audit</h3>
                    <p class="text-xs text-slate-500">Tindakan operasional pemilik</p>
                </div>
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">Menu</span>
            </div>

            <div class="space-y-3">
                <a href="{{ route('owner.transaksi.index') }}"
                   class="w-full flex items-center justify-between px-4 py-3 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-xs">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Rekap Semua Transaksi
                    </span>
                    <span>&rarr;</span>
                </a>

                <a href="{{ route('owner.audit-setoran.index') }}"
                   class="w-full flex items-center justify-between px-4 py-3 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-black transition shadow-xs">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Cek Setoran Kasir per Shift
                    </span>
                    <span>&rarr;</span>
                </a>

                <a href="{{ route('owner.admins.index') }}"
                   class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 text-slate-800 border border-slate-200/80 font-semibold text-xs rounded-xl hover:bg-slate-100 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Kelola Akun Admin
                    </span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1 mt-4">
            <div class="flex items-center justify-between">
                <span>Persentase Kendaraan Keluar:</span>
                <span class="font-bold text-primary">{{ $ratioKeluar }}%</span>
            </div>
            <div class="flex items-center justify-between">
                <span>Total Tempat Parkir Reguler:</span>
                <span class="font-bold text-slate-800">{{ number_format($tenant->kapasitas_reguler) }} tempat</span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    BOTTOM: TABEL PARKIR LEBIH DARI 24 JAM
═══════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
        <div>
            <h3 class="font-heading font-extrabold text-base text-[#212121]">Daftar Parkir Lebih dari 24 Jam</h3>
            <p class="text-xs text-slate-500">Kendaraan yang belum keluar setelah 24 jam untuk memastikan pencatatan kasir tertib.</p>
        </div>
        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $hasAnomaly ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
            {{ $anomaliMenginap->count() }} Terdeteksi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                    <th class="py-3 px-3">Kode Karcis / Plat</th>
                    <th class="py-3 px-3">Pengendara</th>
                    <th class="py-3 px-3">Waktu Masuk</th>
                    <th class="py-3 px-3">Durasi Menginap</th>
                    <th class="py-3 px-3">Status Skema</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($anomaliMenginap as $a)
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="py-3.5 px-3">
                        <div class="font-bold text-slate-900 font-mono">{{ $a->kode_karcis }}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-900 text-white font-mono text-[10px] font-bold mt-0.5">
                            {{ $a->kendaraan->plat_nomor ?? '-' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-3 text-slate-700">
                        {{ $a->pengendara->nama_lengkap ?? 'Offline / Tamu' }}
                    </td>
                    <td class="py-3.5 px-3 font-mono text-slate-600">
                        {{ optional(\Carbon\Carbon::parse($a->waktu_masuk))->format('d M Y H:i') }}
                    </td>
                    <td class="py-3.5 px-3 font-bold text-rose-600">
                        {{ optional(\Carbon\Carbon::parse($a->waktu_masuk))->diffForHumans(['parts' => 2]) }}
                    </td>
                    <td class="py-3.5 px-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            Perlu Audit Lapangan
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-400">
                        Tidak ada indikasi anomali menginap. Operasional gerbang berjalan normal.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function renderOwnerCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(renderOwnerCharts, 50);
        return;
    }

    // 1. Chart 7-Day Flow
    const flow = document.getElementById('ownerFlowChart');
    const flowData = @json($chart);
    if(flow && flowData && flowData.labels){
        new Chart(flow, {
            type: 'bar',
            data: {
                labels: flowData.labels,
                datasets: [
                    {
                        label: 'Masuk',
                        data: flowData.masuk,
                        backgroundColor: 'rgba(13, 99, 165, 0.85)',
                        borderRadius: 6,
                        maxBarThickness: 30
                    },
                    {
                        label: 'Keluar',
                        data: flowData.keluar,
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: 6,
                        maxBarThickness: 30
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748B', font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#64748B', font: { size: 11 } }, grid: { color: '#F1F5F9' } }
                }
            }
        });
    }

    // 2. Chart Utama: Diagram Garis Pendapatan Ala Google Currency / Finance (Per Bulan & Per Tahun)
    const yoyCanvas = document.getElementById('yoyOmzetChart');
    const yoyData = @json($yoyChart);
    let mainLineChart = null;
    let currentChartMode = 'monthly'; // 'monthly' | 'yearly'
    let selectedYear = yoyData.current_year || (yoyData.years && yoyData.years.length ? yoyData.years[yoyData.years.length - 1] : 2026);

    function formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
    }

    function getGradient(ctx, hexOrRgba) {
        const grad = ctx.createLinearGradient(0, 0, 0, 320);
        if (hexOrRgba && hexOrRgba.startsWith('#')) {
            // Convert hex to rgb
            let c = hexOrRgba.substring(1);
            if(c.length === 3) c = c.split('').map(x => x+x).join('');
            const num = parseInt(c, 16);
            const r = (num >> 16) & 255;
            const g = (num >> 8) & 255;
            const b = num & 255;
            grad.addColorStop(0, `rgba(${r}, ${g}, ${b}, 0.28)`);
            grad.addColorStop(0.75, `rgba(${r}, ${g}, ${b}, 0.06)`);
            grad.addColorStop(1, `rgba(${r}, ${g}, ${b}, 0.00)`);
        } else {
            grad.addColorStop(0, 'rgba(13, 99, 165, 0.28)');
            grad.addColorStop(0.75, 'rgba(13, 99, 165, 0.06)');
            grad.addColorStop(1, 'rgba(13, 99, 165, 0.00)');
        }
        return grad;
    }

    function renderMainChart() {
        if (!yoyCanvas || !yoyData) return;
        const ctx = yoyCanvas.getContext('2d');

        if (mainLineChart) {
            mainLineChart.destroy();
        }

        let labels = [];
        let datasets = [];

        if (currentChartMode === 'yearly') {
            // Mode Per Tahun (Tren Tahunan)
            const trend = yoyData.annual_trend || { labels: [], omzets: [] };
            labels = trend.labels || [];
            datasets = [{
                label: 'Total Pendapatan Tahunan',
                data: trend.omzets || [],
                volumes: trend.volumes || [],
                borderColor: '#0D63A5',
                borderWidth: 3,
                backgroundColor: getGradient(ctx, '#0D63A5'),
                fill: true,
                tension: 0.38,
                pointRadius: 5,
                pointBackgroundColor: '#FFFFFF',
                pointBorderColor: '#0D63A5',
                pointBorderWidth: 2.5,
                pointHoverRadius: 8,
                pointHoverBorderWidth: 3.5,
                pointHoverBackgroundColor: '#FFD717',
            }];

            const descEl = document.getElementById('chartSubDesc');
            if (descEl) descEl.textContent = 'Grafik garis tren perkembangan pendapatan parkir dari tahun ke tahun';

        } else {
            // Mode Per Bulan (Monthly)
            labels = yoyData.labels || [];

            if (selectedYear === 'all') {
                // Komparasi Semua Tahun (Multi-line)
                datasets = (yoyData.datasets || []).map(function(ds) {
                    const color = ds.borderColor || '#0D63A5';
                    return {
                        label: ds.label,
                        data: ds.data,
                        volumes: ds.volume_data || [],
                        borderColor: color,
                        borderWidth: 2.5,
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.38,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: color,
                        pointBorderWidth: 2
                    };
                });

                const descEl = document.getElementById('chartSubDesc');
                if (descEl) descEl.textContent = 'Perbandingan diagram garis 12 bulan (Januari – Desember) lintas tahun';

            } else {
                // Single Year Monthly Curve Line Chart
                const yrData = yoyData.year_analytics ? yoyData.year_analytics[selectedYear] : null;
                const lineColor = yrData ? yrData.color : '#0D63A5';
                
                datasets = [{
                    label: 'Pendapatan Tahun ' + selectedYear,
                    data: yrData ? yrData.monthly_omzet : [],
                    volumes: yrData ? yrData.monthly_volume : [],
                    borderColor: lineColor,
                    borderWidth: 2.8,
                    backgroundColor: getGradient(ctx, lineColor),
                    fill: true,
                    tension: 0.38,
                    pointRadius: 4,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: lineColor,
                    pointBorderWidth: 2,
                    pointHoverRadius: 7,
                    pointHoverBorderWidth: 3,
                    pointHoverBackgroundColor: '#FFD717',
                }];

                const descEl = document.getElementById('chartSubDesc');
                if (descEl) descEl.textContent = 'Grafik garis pendapatan parkir tahun ' + selectedYear + ' per bulan (Januari – Desember)';
            }
        }

        mainLineChart = new Chart(yoyCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: (selectedYear === 'all' && currentChartMode === 'monthly') },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleColor: '#94A3B8',
                        titleFont: { size: 11, weight: 'bold' },
                        bodyColor: '#FFFFFF',
                        bodyFont: { size: 12, weight: 'bold' },
                        padding: 10,
                        cornerRadius: 8,
                        borderColor: '#334155',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const val = context.raw || 0;
                                const ds = context.dataset;
                                const idx = context.dataIndex;
                                const vol = (ds.volumes && ds.volumes[idx] !== undefined) ? ds.volumes[idx] : null;
                                let str = ' ' + ds.label + ': ' + formatRupiah(val);
                                if (vol !== null) {
                                    str += ' (' + new Intl.NumberFormat('id-ID').format(vol) + ' kendaraan)';
                                }
                                return str;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 11, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(0) + 'jt';
                                if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + 'rb';
                                return 'Rp ' + v;
                            },
                            color: '#64748B',
                            font: { size: 11 }
                        },
                        grid: { color: '#F1F5F9' }
                    }
                }
            }
        });
    }

    window.switchChartMode = function(mode) {
        currentChartMode = mode;
        const btnMonthly = document.getElementById('btnModeMonthly');
        const btnYearly = document.getElementById('btnModeYearly');
        const yearTabGroup = document.getElementById('yearTabGroup');

        if (mode === 'yearly') {
            btnYearly.classList.add('bg-white', 'text-slate-900', 'shadow-2xs', 'font-bold');
            btnYearly.classList.remove('text-slate-500');
            btnMonthly.classList.remove('bg-white', 'text-slate-900', 'shadow-2xs', 'font-bold');
            btnMonthly.classList.add('text-slate-500');
            if (yearTabGroup) yearTabGroup.classList.add('opacity-30', 'pointer-events-none');
        } else {
            btnMonthly.classList.add('bg-white', 'text-slate-900', 'shadow-2xs', 'font-bold');
            btnMonthly.classList.remove('text-slate-500');
            btnYearly.classList.remove('bg-white', 'text-slate-900', 'shadow-2xs', 'font-bold');
            btnYearly.classList.add('text-slate-500');
            if (yearTabGroup) yearTabGroup.classList.remove('opacity-30', 'pointer-events-none');
        }

        renderMainChart();
    };

    window.selectYearFilter = function(year) {
        if (currentChartMode === 'yearly') {
            switchChartMode('monthly');
        }
        selectedYear = year;
        document.querySelectorAll('.year-tab-btn').forEach(function(btn) {
            const btnYear = btn.getAttribute('data-year');
            if (btnYear == year) {
                btn.classList.add('bg-primary', 'text-white', 'shadow-2xs', 'font-bold');
                btn.classList.remove('text-slate-600');
            } else {
                btn.classList.remove('bg-primary', 'text-white', 'shadow-2xs', 'font-bold');
                btn.classList.add('text-slate-600');
            }
        });

        renderMainChart();
    };

    renderMainChart();

    // 3. Mini Chart Perbandingan Arus Tiap Tahun (Annual Flow Comparison)
    const annualFlowEl = document.getElementById('annualFlowChart');
    if (annualFlowEl && yoyData && yoyData.annual_flow) {
        new Chart(annualFlowEl, {
            type: 'bar',
            data: {
                labels: yoyData.annual_flow.labels,
                datasets: [
                    {
                        label: 'Jumlah Kendaraan',
                        data: yoyData.annual_flow.volumes,
                        backgroundColor: yoyData.annual_flow.colors || 'rgba(13, 99, 165, 0.85)',
                        borderColor: yoyData.annual_flow.borderColors || '#0D63A5',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 32
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                const vol = context.raw;
                                const omzet = (yoyData.annual_flow.omzets && yoyData.annual_flow.omzets[idx] !== undefined)
                                    ? yoyData.annual_flow.omzets[idx] : 0;
                                return [
                                    ' Kendaraan: ' + new Intl.NumberFormat('id-ID').format(vol) + ' kendaraan',
                                    ' Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(omzet)
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#475569', font: { size: 11, weight: 'bold' } } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#64748B',
                            font: { size: 10 },
                            callback: function(v) {
                                return v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v;
                            }
                        },
                        grid: { color: '#F1F5F9' }
                    }
                }
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderOwnerCharts);
} else {
    renderOwnerCharts();
}
</script>
@endpush
