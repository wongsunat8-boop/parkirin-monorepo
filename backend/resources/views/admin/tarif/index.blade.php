@extends('layouts.staff')
@section('title','Kelola Tarif')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="page-title">Kelola Tarif</h1>
        <a href="{{ route('admin.tarif.create') }}" class="ui-btn ui-btn-primary">Tambah Tarif</a>
    </div>
    <div class="ui-card overflow-hidden">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr><th>Jenis</th><th>Tipe</th><th>Per Jam</th><th>Jam 1 / Berikutnya</th><th>Inap</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($tarifs as $t)
                    <tr>
                        <td class="font-medium">{{ $t->jenis_kendaraan }}</td>
                        <td>{{ $t->tipe_tarif }}</td>
                        <td>Rp {{ number_format($t->tarif_per_jam,0,',','.') }}</td>
                        <td>Rp {{ number_format($t->tarif_jam_pertama,0,',','.') }} / Rp {{ number_format($t->tarif_berikutnya,0,',','.') }}</td>
                        <td>Rp {{ number_format($t->tarif_inap,0,',','.') }}</td>
                        <td>{!! $t->status_aktif ? '<span class="text-success">Aktif</span>' : '<span class="text-gray-400">Nonaktif</span>' !!}</td>
                        <td class="text-right whitespace-nowrap space-x-1.5">
                            <a href="{{ route('admin.tarif.edit',$t) }}" class="btn-action-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.tarif.destroy',$t) }}" class="inline" onsubmit="return confirm('Hapus tarif ini?')">
                                @csrf @method('DELETE')
                                <button class="btn-action-danger">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada tarif. Tambahkan tarif per jenis kendaraan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-gray-100">{{ $tarifs->links() }}</div>
    </div>
@endsection
