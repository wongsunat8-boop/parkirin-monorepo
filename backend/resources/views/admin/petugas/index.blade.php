@extends('layouts.staff')
@section('title','Kelola Petugas')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><h1 class="page-title">Kelola Petugas</h1><a href="{{ route('admin.petugas.create') }}" class="ui-btn ui-btn-primary">Tambah Petugas</a></div>
    <form method="GET" class="ui-card ui-card-b mb-4 flex gap-2 items-end"><input name="q" value="{{ request('q') }}" class="ui-input" placeholder="Cari nama/username"><button class="ui-btn ui-btn-dark ui-btn-sm">Cari</button></form>
    <div class="ui-card overflow-hidden"><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Nama</th><th>Username</th><th>Transaksi Dilayani</th><th>Status</th><th></th></tr></thead>
    <tbody>@forelse($petugasList as $p)<tr><td class="font-medium">{{ $p->nama_lengkap }}</td><td>{{ $p->username }}</td><td>{{ $p->transaksi_dilayani_count }}</td>
    <td>{!! $p->status_aktif?'<span class="text-success">Aktif</span>':'<span class="text-gray-400">Nonaktif</span>' !!}</td>
    <td class="text-right whitespace-nowrap space-x-1.5">
        <a href="{{ route('admin.petugas.edit', $p) }}" class="btn-action-edit">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Edit</span>
        </a>
        <form method="POST" action="{{ route('admin.petugas.toggle', $p) }}" class="inline">
            @csrf @method('PATCH')
            @if($p->status_aktif)
                <button class="btn-action-warning" title="Nonaktifkan akun petugas">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Nonaktifkan</span>
                </button>
            @else
                <button class="btn-action-success" title="Aktifkan akun petugas">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Aktifkan</span>
                </button>
            @endif
        </form>
    </td></tr>
    @empty<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada petugas.</td></tr>@endforelse</tbody></table></div><div class="p-3 border-t border-gray-100">{{ $petugasList->links() }}</div></div>
@endsection
