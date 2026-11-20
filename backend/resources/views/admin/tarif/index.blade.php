@extends('layouts.staff')
@section('title', 'Kelola Tarif')
@section('page-title', 'Kelola Tarif Parkir')
@section('page-desc', 'Skema penetapan harga parkir per jam, flat, progresif, inap harian, dan denda karcis hilang.')

@section('staff-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Skema Tarif Parkir</h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasi tarif operasional gerbang untuk setiap jenis kendaraan.</p>
        </div>
        <a href="{{ route('admin.tarif.create') }}" class="ui-btn ui-btn-primary text-xs shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Tarif</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Jenis Kendaraan</th>
                        <th class="py-3 px-4">Tipe Tarif</th>
                        <th class="py-3 px-4">Tarif Flat / Jam</th>
                        <th class="py-3 px-4">Tarif Jam 1 / Jam Berikutnya</th>
                        <th class="py-3 px-4">Inap Harian</th>
                        <th class="py-3 px-4">Denda Hilang</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($tarifs as $t)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $t->jenis_kendaraan === 'Mobil' ? 'bg-indigo-50 text-indigo-700' : ($t->jenis_kendaraan === 'Motor' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                                {{ $t->jenis_kendaraan }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $t->tipe_tarif }}</td>
                        <td class="py-3.5 px-4 font-extrabold text-[#212121]">
                            Rp {{ number_format($t->tarif_per_jam, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            @if($t->tipe_tarif === 'Progresif Mal')
                                Rp {{ number_format($t->tarif_jam_pertama, 0, ',', '.') }} / Rp {{ number_format($t->tarif_berikutnya, 0, ',', '.') }}
                            @else
                                <span class="text-slate-400 italic">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            Rp {{ number_format($t->tarif_inap_harian, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-rose-600">
                            Rp {{ number_format($t->denda_karcis_hilang, 0, ',', '.') }}
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
                                <a href="{{ route('admin.tarif.edit', $t) }}" class="btn-action-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('admin.tarif.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus skema tarif ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action-danger" title="Hapus Tarif">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            Belum ada skema tarif yang diatur. Silakan tambahkan tarif baru.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($tarifs->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $tarifs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
