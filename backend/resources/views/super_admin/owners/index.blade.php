@extends('layouts.staff')
@section('title', 'Kelola Owner')
@section('page-title', 'Kelola Akun Owner')
@section('page-desc', 'Daftar pemilik/pengelola tempat parkir mitra terdaftar.')

@section('staff-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Master Akun Owner</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola akun pemilik lahan parkir yang memiliki hak akses analitik tenant.</p>
        </div>
        <a href="{{ route('super-admin.owners.create') }}" class="ui-btn ui-btn-primary text-xs shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Owner</span>
        </a>
    </div>

    {{-- Search Toolbar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="ui-label">Cari Nama atau Username</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau username..." class="ui-input text-xs">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="ui-btn ui-btn-dark ui-btn-sm">Cari</button>
            @if(request()->filled('q'))
                <a href="{{ route('super-admin.owners.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm">Reset</a>
            @endif
        </div>
    </form>

    {{-- Owners Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Nama Lengkap Owner</th>
                        <th class="py-3 px-4">Username</th>
                        <th class="py-3 px-4">Tempat Parkir Terikat</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($owners as $o)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $o->nama_lengkap }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">ID Akun: #{{ $o->id_user }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-primary">
                            &#64;{{ $o->username }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            {{ $o->tempatParkir->nama_tempat ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($o->status_aktif)
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
                                <a href="{{ route('super-admin.owners.edit', $o) }}" class="btn-action-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('super-admin.owners.toggle', $o) }}" class="inline">
                                    @csrf @method('PATCH')
                                    @if($o->status_aktif)
                                        <button type="submit" class="btn-action-warning" title="Nonaktifkan akun">
                                            <span>Nonaktifkan</span>
                                        </button>
                                    @else
                                        <button type="submit" class="btn-action-success" title="Aktifkan akun">
                                            <span>Aktifkan</span>
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-400">Tidak ada data akun owner.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($owners->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $owners->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
