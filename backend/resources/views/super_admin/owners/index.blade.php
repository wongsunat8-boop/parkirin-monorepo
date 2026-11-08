@extends('layouts.staff')
@section('title','Kelola Owner')
@section('sidebar')
    <a href="{{ route('super-admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Dashboard</a>
    <a href="{{ route('super-admin.tenants.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Kelola Tenant</a>
    <a href="{{ route('super-admin.owners.index') }}" class="block px-3 py-2 rounded-lg bg-primary text-white">Kelola Owner</a>
@endsection
@section('staff-content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="page-title">Kelola Owner</h1>
        <a href="{{ route('super-admin.owners.create') }}" class="ui-btn ui-btn-primary">Tambah Owner</a>
    </div>
    <form method="GET" class="ui-card ui-card-b mb-4 flex flex-wrap gap-2 items-end">
        <div><label class="ui-label">Cari</label><input name="q" value="{{ request('q') }}" class="ui-select" placeholder="Nama / username"></div>
        <button class="ui-btn ui-btn-dark ui-btn-sm">Filter</button>
    </form>
    <div class="ui-card overflow-hidden">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr><th>Nama</th><th>Username</th><th>Tenant</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($owners as $o)
                    <tr>
                        <td class="font-medium">{{ $o->nama_lengkap }}</td>
                        <td>{{ $o->username }}</td>
                        <td>{{ $o->tempatParkir->nama_tempat ?? '-' }}</td>
                        <td>{!! $o->status_aktif ? '<span class="ui-badge ui-badge-success">Aktif</span>' : '<span class="text-gray-400">Nonaktif</span>' !!}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('super-admin.owners.edit', $o) }}" class="ui-link mr-2">Edit</a>
                            <form method="POST" action="{{ route('super-admin.owners.toggle', $o) }}" class="inline">@csrf @method('PATCH')
                                <button class="text-gray-600 hover:underline">{{ $o->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-gray-100">{{ $owners->links() }}</div>
    </div>
@endsection
