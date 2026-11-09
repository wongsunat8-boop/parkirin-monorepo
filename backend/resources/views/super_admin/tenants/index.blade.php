@extends('layouts.staff')
@section('title','Kelola Tenant')
@section('sidebar')
    <a href="{{ route('super-admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Dashboard</a>
    <a href="{{ route('super-admin.tenants.index') }}" class="block px-3 py-2 rounded-lg bg-primary text-white">Kelola Tenant</a>
    <a href="{{ route('super-admin.owners.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Kelola Owner</a>
@endsection
@section('staff-content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="page-title">Kelola Tenant</h1>
        <a href="{{ route('super-admin.tenants.create') }}" class="ui-btn ui-btn-primary">Tambah Tenant</a>
    </div>
    <form method="GET" class="ui-card ui-card-b mb-4 flex flex-wrap gap-2 items-end">
        <div><label class="ui-label">Cari</label><input name="q" value="{{ request('q') }}" class="ui-select" placeholder="Nama tempat"></div>
        <div><label class="ui-label">Status</label><select name="status" class="ui-select"><option value="">Semua</option><option value="1" @selected(request('status')==='1')>Aktif</option><option value="0" @selected(request('status')==='0')>Nonaktif</option></select></div>
        <button class="ui-btn ui-btn-dark ui-btn-sm">Filter</button>
    </form>
    <div class="ui-card overflow-hidden">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr><th>Nama Tempat</th><th>Kapasitas</th><th>User</th><th>Booking</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($tenants as $t)
                    <tr>
                        <td class="font-medium">{{ $t->nama_tempat }}</td>
                        <td>{{ $t->kapasitas }}</td>
                        <td>{{ $t->users_count }}</td>
                        <td>{{ $t->sediakan_booking ? 'Ya' : 'Tidak' }}</td>
                        <td>{!! $t->status_aktif ? '<span class="ui-badge ui-badge-success">Aktif</span>' : '<span class="text-gray-400">Nonaktif</span>' !!}</td>
                        <td class="text-right whitespace-nowrap space-x-1.5">
                            <a href="{{ route('super-admin.tenants.edit', $t) }}" class="btn-action-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>
                            <form method="POST" action="{{ route('super-admin.tenants.toggle', $t) }}" class="inline">
                                @csrf @method('PATCH')
                                @if($t->status_aktif)
                                    <button class="btn-action-warning" title="Nonaktifkan operasional tenant">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Nonaktifkan</span>
                                    </button>
                                @else
                                    <button class="btn-action-success" title="Aktifkan operasional tenant">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Aktifkan</span>
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-gray-100">{{ $tenants->links() }}</div>
    </div>
@endsection
