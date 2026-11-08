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
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.tarif.edit',$t) }}" class="ui-link mr-2">Edit</a>
                            <form method="POST" action="{{ route('admin.tarif.destroy',$t) }}" class="inline" onsubmit="return confirm('Hapus tarif ini?')">@csrf @method('DELETE')<button class="text-danger hover:underline">Hapus</button></form>
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
