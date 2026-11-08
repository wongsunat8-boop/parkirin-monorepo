@extends('layouts.staff')
@section('title','Kelola Petugas')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><h1 class="page-title">Kelola Petugas</h1><a href="{{ route('admin.petugas.create') }}" class="ui-btn ui-btn-primary">Tambah Petugas</a></div>
    <form method="GET" class="ui-card ui-card-b mb-4 flex gap-2 items-end"><input name="q" value="{{ request('q') }}" class="ui-input" placeholder="Cari nama/username"><button class="ui-btn ui-btn-dark ui-btn-sm">Cari</button></form>
    <div class="ui-card overflow-hidden"><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Nama</th><th>Username</th><th>Transaksi Dilayani</th><th>Status</th><th></th></tr></thead>
    <tbody>@forelse($petugasList as $p)<tr><td class="font-medium">{{ $p->nama_lengkap }}</td><td>{{ $p->username }}</td><td>{{ $p->transaksi_dilayani_count }}</td>
    <td>{!! $p->status_aktif?'<span class="text-success">Aktif</span>':'<span class="text-gray-400">Nonaktif</span>' !!}</td>
    <td class="text-right whitespace-nowrap"><a href="{{ route('admin.petugas.edit',$p) }}" class="ui-link mr-2">Edit</a><form method="POST" action="{{ route('admin.petugas.toggle',$p) }}" class="inline">@csrf @method('PATCH')<button class="text-gray-600 hover:underline">{{ $p->status_aktif?'Nonaktifkan':'Aktifkan' }}</button></form></td></tr>
    @empty<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada petugas.</td></tr>@endforelse</tbody></table></div><div class="p-3 border-t border-gray-100">{{ $petugasList->links() }}</div></div>
@endsection
