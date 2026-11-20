@extends('layouts.staff')
@section('title', 'Kelola Lokasi Parkir')
@section('page-title', 'Kelola Lokasi Parkir')
@section('page-desc', 'Daftar seluruh tempat parkir terdaftar, kapasitas kendaraan, dan status operasional.')

@section('staff-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Lokasi Parkir</h1>
            <p class="text-xs text-slate-500 mt-1">Pengaturan identitas dan alokasi tempat parkir pada sistem Parkirin.</p>
        </div>
        <a href="{{ route('super-admin.tenants.create') }}" class="ui-btn ui-btn-primary text-xs shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Lokasi Baru</span>
        </a>
    </div>

    {{-- Filter Toolbar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="ui-label">Cari Nama Lokasi</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama tempat..." class="ui-input text-xs">
        </div>
        <div class="w-36">
            <label class="ui-label">Status</label>
            <select name="status" class="ui-select text-xs">
                <option value="">Semua</option>
                <option value="1" @selected(request('status') === '1')>Aktif</option>
                <option value="0" @selected(request('status') === '0')>Nonaktif</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="ui-btn ui-btn-dark ui-btn-sm">Filter</button>
            <a href="{{ route('super-admin.tenants.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm">Reset</a>
        </div>
    </form>

    {{-- Tenants Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Nama Tempat Parkir</th>
                        <th class="py-3 px-4">Kapasitas Reguler</th>
                        <th class="py-3 px-4">Kuota Inap</th>
                        <th class="py-3 px-4">Akun Terdaftar</th>
                        <th class="py-3 px-4">Layanan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($tenants as $t)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $t->nama_tempat }}
                            <div class="text-[10px] text-slate-400 font-mono">ID: #{{ $t->id_tempat_parkir }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#212121]">
                            {{ number_format($t->kapasitas_reguler) }} slot
                        </td>
                        <td class="py-3.5 px-4 font-bold text-purple-600">
                            {{ number_format($t->kuota_inap) }} slot
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            {{ $t->users_count }} akun
                        </td>
                        <td class="py-3.5 px-4 space-x-1">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $t->sediakan_booking_inap ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                Booking: {{ $t->sediakan_booking_inap ? 'Ya' : 'Tidak' }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $t->sediakan_member ? 'bg-blue-50 text-primary' : 'bg-slate-100 text-slate-400' }}">
                                Member: {{ $t->sediakan_member ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($t->status_aktif)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('super-admin.tenants.edit', $t) }}" class="btn-action-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('super-admin.tenants.toggle', $t) }}" class="inline">
                                    @csrf @method('PATCH')
                                    @if($t->status_aktif)
                                        <button type="submit" class="btn-action-warning" title="Nonaktifkan tenant">
                                            <span>Nonaktifkan</span>
                                        </button>
                                    @else
                                        <button type="submit" class="btn-action-success" title="Aktifkan tenant">
                                            <span>Aktifkan</span>
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data tenant.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
