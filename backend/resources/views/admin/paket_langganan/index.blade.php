@extends('layouts.staff')
@section('title', 'Kelola Paket Langganan')
@section('page-title', 'Kelola Paket Langganan')
@section('page-desc', 'Atur pilihan paket langganan parkir, masa aktif hari, batas masuk harian, dan harga paket.')

@section('staff-content')
<div class="space-y-6">

    {{-- Top Action Header & Info --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Paket Membership</h1>
            <p class="text-xs text-slate-500 mt-1">
                Setiap paket dapat dibatasi <strong>Batas Masuk Harian</strong>. Jika batas harian pengendara habis, palang gerbang tetap terbuka dan mengenakan tarif parkir biasa (reguler) tanpa menolak kendaraan.
            </p>
        </div>
        <a href="{{ route('admin.paket-langganan.create') }}" class="ui-btn ui-btn-primary text-xs shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Paket Baru</span>
        </a>
    </div>

    {{-- Filter & Search Toolbar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="ui-label">Cari Paket</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama paket..." class="ui-input text-xs">
        </div>
        <div class="w-36">
            <label class="ui-label">Jenis Kendaraan</label>
            <select name="jenis" class="ui-select text-xs">
                <option value="">Semua</option>
                @foreach(['Motor','Mobil','Sepeda'] as $j)
                    <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="ui-label">Status</label>
            <select name="status" class="ui-select text-xs">
                <option value="">Semua</option>
                <option value="1" @selected(request('status') === '1')>Aktif</option>
                <option value="0" @selected(request('status') === '0')>Nonaktif</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="ui-btn ui-btn-dark ui-btn-sm">Filter</button>
            @if(request()->anyFilled(['q','jenis','status']))
                <a href="{{ route('admin.paket-langganan.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm">Reset</a>
            @endif
        </div>
    </form>

    {{-- Bento Cards Grid for Packages --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($pakets as $p)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                {{-- Card Header --}}
                <div class="flex items-start justify-between gap-3 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $p->jenis_kendaraan === 'Mobil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/60' : ($p->jenis_kendaraan === 'Motor' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60') }}">
                        {{ $p->jenis_kendaraan }}
                    </span>
                    <form method="POST" action="{{ route('admin.paket-langganan.toggle', $p) }}" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold transition {{ $p->status_aktif ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}" title="Klik untuk ubah status aktif">
                            <span class="w-1.5 h-1.5 rounded-full {{ $p->status_aktif ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $p->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>
                </div>

                <h3 class="font-heading font-extrabold text-base text-[#212121] leading-tight mb-1">{{ $p->nama_paket }}</h3>
                <div class="text-2xl font-heading font-black text-primary tracking-tight mb-4">
                    Rp {{ number_format($p->harga_paket, 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">/ {{ $p->durasi_hari }} Hari</span>
                </div>

                {{-- Detail Specs --}}
                <div class="space-y-2 py-3 border-y border-slate-100 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Masa Berlaku:</span>
                        <span class="font-bold text-slate-800">{{ $p->durasi_hari }} Hari Kalender</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Batas Masuk Harian:</span>
                        @if($p->batas_masuk_harian)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 font-bold text-[11px] border border-amber-200">
                                Max {{ $p->batas_masuk_harian }}x / Hari
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-primary font-bold text-[11px] border border-blue-200">
                                Bebas Masuk (Tanpa Batas)
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Jika batas harian habis:</span>
                        <span class="text-slate-600 font-medium text-[11px]">Otomatis tarif biasa (reguler)</span>
                    </div>
                </div>
            </div>

            {{-- Card Footer Action Buttons --}}
            <div class="pt-4 flex items-center justify-end gap-2">
                <a href="{{ route('admin.paket-langganan.edit', $p) }}" class="btn-action-edit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                </a>
                <form method="POST" action="{{ route('admin.paket-langganan.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus paket langganan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action-danger" title="Hapus Paket">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 px-4 text-center bg-white rounded-2xl border border-slate-200/80 shadow-2xs text-slate-400">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <p class="font-bold text-slate-700 text-sm">Belum Ada Paket Langganan</p>
            <p class="text-xs text-slate-400 mt-1 mb-4">Buat paket membership pertama untuk membuka fitur langganan bagi pengendara.</p>
            <a href="{{ route('admin.paket-langganan.create') }}" class="ui-btn ui-btn-primary ui-btn-sm">
                + Tambah Paket Baru
            </a>
        </div>
        @endforelse
    </div>

    @if($pakets->hasPages())
    <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs">
        {{ $pakets->links() }}
    </div>
    @endif

</div>
@endsection
