@extends('layouts.staff')
@section('title','Dashboard Super Admin')
@section('page-title','Dashboard Super Admin')
@section('page-desc','Ringkasan platform: tenant, owner, dan status operasional.')

@section('staff-content')
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Total Tenant</div><div class="ui-kpi mt-2">{{ number_format($stats['total_tenant']) }}</div><div class="ui-kpi-sub">Tempat parkir terdaftar</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Tenant Aktif</div><div class="ui-kpi mt-2" style="color:#16a34a">{{ number_format($stats['tenant_aktif']) }}</div><div class="ui-kpi-sub">{{ $stats['total_tenant'] > 0 ? round(($stats['tenant_aktif']/$stats['total_tenant'])*100) : 0 }}% dari total</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Total Owner</div><div class="ui-kpi mt-2">{{ number_format($stats['total_owner']) }}</div><div class="ui-kpi-sub">Akun owner</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Owner Aktif</div><div class="ui-kpi mt-2" style="color:#16a34a">{{ number_format($stats['owner_aktif']) }}</div><div class="ui-kpi-sub">{{ $stats['total_owner'] > 0 ? round(($stats['owner_aktif']/$stats['total_owner'])*100) : 0 }}% dari total</div></div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
  <div class="ui-card">
    <div class="ui-card-h"><div class="ui-card-t">Komposisi Tenant</div><div class="ui-card-d">Status aktif vs nonaktif.</div></div>
    <div class="ui-card-b"><div style="position:relative;height:220px"><canvas id="saTenantChart"></canvas></div></div>
  </div>
  <div class="ui-card">
    <div class="ui-card-h"><div class="ui-card-t">Komposisi Owner</div><div class="ui-card-d">Status akun owner.</div></div>
    <div class="ui-card-b"><div style="position:relative;height:220px"><canvas id="saOwnerChart"></canvas></div></div>
  </div>
  <div class="ui-card">
    <div class="ui-card-h"><div class="ui-card-t">Tindakan Cepat</div><div class="ui-card-d">Manajemen platform B2B.</div></div>
    <div class="ui-card-b space-y-2">
      <a href="{{ route('super-admin.tenants.create') }}" class="ui-btn ui-btn-primary w-full">Tambah Tenant</a>
      <a href="{{ route('super-admin.owners.create') }}" class="ui-btn ui-btn-secondary w-full">Tambah Owner</a>
      <a href="{{ route('super-admin.tenants.index') }}" class="ui-btn ui-btn-ghost w-full">Lihat Semua Tenant</a>
      <div class="border-t border-gray-100 pt-3 mt-2 text-[12.5px] text-gray-600">
        <div class="flex justify-between py-1"><span>Tenant nonaktif</span><strong>{{ max(0, $stats['total_tenant']-$stats['tenant_aktif']) }}</strong></div>
        <div class="flex justify-between py-1"><span>Owner nonaktif</span><strong>{{ max(0, $stats['total_owner']-$stats['owner_aktif']) }}</strong></div>
      </div>
    </div>
  </div>
</div>

<div class="ui-card">
  <div class="ui-card-h flex items-center justify-between gap-2">
    <div><div class="ui-card-t">Tenant Terbaru</div><div class="ui-card-d">Pendaftaran dan kapasitas terbaru.</div></div>
    <a href="{{ route('super-admin.tenants.index') }}" class="ui-btn ui-btn-ghost text-primary">Kelola tenant</a>
  </div>
  <div class="ui-table-wrap">
    <table class="ui-table"><thead><tr><th>Nama Tempat</th><th>Kapasitas</th><th>User</th><th>Status</th></tr></thead>
    <tbody>
      @forelse($recentTenants as $t)
        <tr>
          <td class="font-medium">{{ $t->nama_tempat }}</td>
          <td>{{ number_format($t->kapasitas) }}</td>
          <td>{{ $t->users_count }}</td>
          <td><span class="ui-badge {{ $t->status_aktif ? 'ui-badge-success':'ui-badge-muted' }}">{{ $t->status_aktif ? 'Aktif':'Nonaktif' }}</span></td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-gray-500 py-8">Belum ada tenant.</td></tr>
      @endforelse
    </tbody></table>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  if(typeof Chart === 'undefined') return;
  const chart = @json($chart);
  const mk = (id, obj, colors) => {
    const el = document.getElementById(id); if(!el) return;
    const labels = Object.keys(obj), data = Object.values(obj);
    new Chart(el,{type:'doughnut',data:{labels,datasets:[{data,backgroundColor:colors,borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}}}});
  };
  mk('saTenantChart', chart.tenant, ['#16A34A','#d1d5db']);
  mk('saOwnerChart', chart.owner, ['#0D63A5','#d1d5db']);
})();
</script>
@endpush
