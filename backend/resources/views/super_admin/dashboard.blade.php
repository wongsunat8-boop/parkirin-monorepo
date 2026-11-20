@extends('layouts.staff')
@section('title', 'Dashboard Super Admin')
@section('page-title', 'Ringkasan Super Admin')
@section('page-desc', 'Pantau seluruh lokasi lahan parkir, akun pemilik, dan kelancaran sistem.')

@section('staff-content')
@php
    $tenantPct = $stats['total_tenant'] > 0 ? round(($stats['tenant_aktif'] / $stats['total_tenant']) * 100) : 0;
    $ownerPct = $stats['total_owner'] > 0 ? round(($stats['owner_aktif'] / $stats['total_owner']) * 100) : 0;
@endphp

{{-- ═══════════════════════════════════════════
    TOP KPI BENTO GRID (4 Cards)
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
    {{-- Card 1: Total Lokasi Parkir --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                Semua Lahan
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Lokasi Parkir</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ number_format($stats['total_tenant']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Tempat parkir terdaftar di sistem</p>
        </div>
    </div>

    {{-- Card 2: Lokasi Parkir Aktif --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ $tenantPct }}% Aktif
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Lokasi Parkir Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-emerald-600 tracking-tight">
                {{ number_format($stats['tenant_aktif']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Operasional siap melayani transaksi</p>
        </div>
    </div>

    {{-- Card 3: Total Owner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                Mitra Swadaya
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Pemilik (Owner)</div>
            <div class="text-3xl font-heading font-extrabold text-[#212121] tracking-tight">
                {{ number_format($stats['total_owner']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Akun pemilik lahan terdaftar</p>
        </div>
    </div>

    {{-- Card 4: Owner Aktif --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                {{ $ownerPct }}% Aktif
            </span>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Owner Aktif</div>
            <div class="text-3xl font-heading font-extrabold text-purple-600 tracking-tight">
                {{ number_format($stats['owner_aktif']) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Akun owner berstatus aktif</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    MIDDLE BENTO GRID: Visual Analytics & Quick Actions
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Card: Komposisi Tenant --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Komposisi Tenant</h3>
                <p class="text-xs text-slate-500">Rasio tempat parkir aktif vs nonaktif</p>
            </div>
            <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">Distribusi</span>
        </div>
        <div class="py-4 flex flex-col items-center justify-center">
            <div class="relative w-44 h-44 flex items-center justify-center">
                <canvas id="saTenantChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-heading font-black text-emerald-600 leading-tight">{{ $tenantPct }}%</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Aktif</span>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-xs">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/50 flex items-center justify-between">
                <span class="text-slate-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif
                </span>
                <span class="font-extrabold text-slate-800">{{ $stats['tenant_aktif'] }}</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/50 flex items-center justify-between">
                <span class="text-slate-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span> Nonaktif
                </span>
                <span class="font-extrabold text-slate-800">{{ max(0, $stats['total_tenant'] - $stats['tenant_aktif']) }}</span>
            </div>
        </div>
    </div>

    {{-- Card: Komposisi Owner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Komposisi Owner</h3>
                <p class="text-xs text-slate-500">Status akun pemilik lahan</p>
            </div>
            <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full">Operator</span>
        </div>
        <div class="py-4 flex flex-col items-center justify-center">
            <div class="relative w-44 h-44 flex items-center justify-center">
                <canvas id="saOwnerChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-heading font-black text-purple-600 leading-tight">{{ $ownerPct }}%</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Aktif</span>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-xs">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/50 flex items-center justify-between">
                <span class="text-slate-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-purple-600"></span> Aktif
                </span>
                <span class="font-extrabold text-slate-800">{{ $stats['owner_aktif'] }}</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/50 flex items-center justify-between">
                <span class="text-slate-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span> Nonaktif
                </span>
                <span class="font-extrabold text-slate-800">{{ max(0, $stats['total_owner'] - $stats['owner_aktif']) }}</span>
            </div>
        </div>
    </div>

    {{-- Card: Quick Actions Hub --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-[#212121]">Tindakan Cepat</h3>
                    <p class="text-xs text-slate-500">Kelola master data ekosistem SaaS</p>
                </div>
                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Aksi</span>
            </div>

            <div class="space-y-2.5">
                <a href="{{ route('super-admin.tenants.create') }}"
                   class="w-full flex items-center justify-between px-4 py-3 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-sm">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Tenant Baru
                    </span>
                    <span class="text-white/70">&rarr;</span>
                </a>

                <a href="{{ route('super-admin.owners.create') }}"
                   class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 text-[#212121] border border-slate-200/80 font-semibold text-xs rounded-xl hover:bg-slate-100 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Tambah Akun Owner
                    </span>
                    <span class="text-slate-400">&rarr;</span>
                </a>

                <a href="{{ route('super-admin.log-aktivitas.index') }}"
                   class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 text-[#212121] border border-slate-200/80 font-semibold text-xs rounded-xl hover:bg-slate-100 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Audit Trail & Log Aktivitas
                    </span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1.5">
            <div class="flex items-center justify-between">
                <span>Tenant Memerlukan Perhatian</span>
                <span class="font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                    {{ max(0, $stats['total_tenant'] - $stats['tenant_aktif']) }} Nonaktif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span>Akun Owner Nonaktif</span>
                <span class="font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">
                    {{ max(0, $stats['total_owner'] - $stats['owner_aktif']) }} Akun
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
    BOTTOM BENTO GRID: Recent Tenants Data Table
═══════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
        <div>
            <h3 class="font-heading font-extrabold text-base text-[#212121]">Tenant Terbaru Terdaftar</h3>
            <p class="text-xs text-slate-500">Daftar lokasi parkir swadaya beserta status dan kapasitasnya</p>
        </div>
        <a href="{{ route('super-admin.tenants.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:text-primary-dark transition self-start sm:self-auto bg-primary/5 hover:bg-primary/10 px-3 py-1.5 rounded-lg border border-primary/20">
            <span>Kelola Semua Tenant</span>
            <span>&rarr;</span>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                    <th class="py-3 px-3">Nama Lahan / Tenant</th>
                    <th class="py-3 px-3">Kapasitas Maksimal</th>
                    <th class="py-3 px-3">Total Akun Terikat</th>
                    <th class="py-3 px-3">Status Operasional</th>
                    <th class="py-3 px-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentTenants as $t)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-3">
                            <div class="font-bold text-[#212121] text-sm">{{ $t->nama_tempat }}</div>
                            <div class="text-[11px] text-slate-400">ID Tenant: #{{ $t->id_tempat_parkir }}</div>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-[#212121] font-semibold text-xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
                                {{ number_format($t->kapasitas) }} slot
                            </span>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="text-slate-600 font-medium">{{ $t->users_count }} Akun Staff</span>
                        </td>
                        <td class="py-3.5 px-3">
                            @if($t->status_aktif)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <a href="{{ route('super-admin.tenants.edit', $t) }}" class="btn-action-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit Tenant</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">
                            Belum ada tenant yang terdaftar di sistem.
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
function renderSuperAdminCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(renderSuperAdminCharts, 50);
        return;
    }
    const chart = @json($chart);

    const mkDoughnut = (id, obj, colors) => {
        const el = document.getElementById(id);
        if(!el) return;
        const labels = Object.keys(obj);
        const data = Object.values(obj);

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    };

    mkDoughnut('saTenantChart', chart.tenant, ['#16A34A', '#E2E8F0']);
    mkDoughnut('saOwnerChart', chart.owner, ['#7C3AED', '#E2E8F0']);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderSuperAdminCharts);
} else {
    renderSuperAdminCharts();
}
</script>
@endpush
