@extends('layouts.staff')
@section('title','Log Aktivitas')
@section('page-title','Log Aktivitas Sistem')
@section('page-desc','Hanya menampilkan aktivitas dari sesama Super Admin.')

@section('staff-content')
<div class="ui-card overflow-hidden">
  <div class="ui-table-wrap">
    <table class="ui-table">
      <thead><tr><th>Waktu</th><th>User</th><th>Tenant Terkait</th><th>Aktivitas</th></tr></thead>
      <tbody>
      @forelse($logs as $log)
        <tr>
          <td class="whitespace-nowrap text-xs text-slate-700 font-mono font-medium">{{ $log->waktu_aktivitas ? \Carbon\Carbon::parse($log->waktu_aktivitas)->format('d/m/Y H:i:s') : '-' }}</td>
          <td>
            <div class="font-medium">{{ $log->user->nama_lengkap ?? '-' }}</div>
            <div class="text-[11px] text-gray-500">{{ $log->user->role ?? '' }}</div>
          </td>
          <td>{{ $log->tempatParkir->nama_tempat ?? '— (Sistem/Global)' }}</td>
          <td>{{ $log->aktivitas }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-gray-500 py-10">Belum ada log aktivitas Super Admin.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm">
    @if($logs->previousPageUrl())<a href="{{ $logs->previousPageUrl() }}" class="ui-btn ui-btn-secondary ui-btn-sm">&larr; Sebelumnya</a>@else<span></span>@endif
    @if($logs->nextPageUrl())<a href="{{ $logs->nextPageUrl() }}" class="ui-btn ui-btn-secondary ui-btn-sm">Berikutnya &rarr;</a>@endif
  </div>
</div>
@endsection
