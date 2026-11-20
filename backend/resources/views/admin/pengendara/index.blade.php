@extends('layouts.staff')
@section('title', 'Daftar Pengendara')
@section('page-title', 'Daftar Pengendara')
@section('page-desc', 'Daftar pengendara terdaftar yang pernah melakukan transaksi parkir di tenant ini.')

@section('sidebar') 
    @include('admin.partials.sidebar') 
@endsection

@section('staff-content')
<div class="space-y-4">
    
    {{-- Search & Filter Toolbar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pengendara, username, atau email..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary focus:outline-none transition">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                Cari Pengendara
            </button>
            @if(request()->filled('q'))
            <a href="{{ route('admin.pengendara.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition">
                Reset
            </a>
            @endif
        </div>
    </form>

    {{-- Main Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Pengendara</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4">Kendaraan Terkait</th>
                        <th class="py-3.5 px-4 text-center">Frekuensi Parkir</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($pengendaras as $p)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-heading font-black text-xs shrink-0 ring-1 ring-primary/20">
                                    {{ strtoupper(mb_substr($p->nama_lengkap ?? 'P', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate">{{ $p->nama_lengkap }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">@<span>{{ $p->username ?? 'tamu' }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="text-slate-700 font-medium">{{ $p->email }}</div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $p->no_telepon ?? 'No HP belum diatur' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($p->kendaraan->take(3) as $k)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-900 text-white font-mono text-[10px] font-bold tracking-wider">
                                        {{ $k->plat_nomor }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 italic text-[11px]">Belum mendaftarkan plat</span>
                                @endforelse
                                @if($p->kendaraan->count() > 3)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[10px]">
                                        +{{ $p->kendaraan->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-blue-50 text-primary font-heading font-bold text-xs border border-blue-100">
                                {{ number_format($p->frekuensi_parkir ?? 0) }}x
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ ($p->status_aktif ?? 1) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ ($p->status_aktif ?? 1) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ ($p->status_aktif ?? 1) ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 px-4 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            </div>
                            <p class="font-bold text-slate-700 text-sm">Belum Ada Riwayat Pengendara</p>
                            <p class="text-xs text-slate-400 mt-0.5">Pengendara yang pernah parkir di tenant ini akan tercatat di sini secara otomatis.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($pengendaras->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $pengendaras->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
