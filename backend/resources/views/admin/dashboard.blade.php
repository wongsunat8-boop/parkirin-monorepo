@extends('layouts.staff')
@section('title','Dashboard Admin')
@section('page-title','Dashboard Admin')
@section('page-desc', ($tenant->nama_tempat ?? 'Tenant').' — ringkasan operasional dan pengaturan harian.')

@section('staff-content')
@php $kap = (int)($tenant->kapasitas ?? 0); $ter = (int)($tenant->terisi ?? 0); $pct = $kap > 0 ? round(($ter/$kap)*100) : 0; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Kendaraan Aktif</div><div class="ui-kpi mt-2 text-primary">{{ number_format($stats['aktif']) }}</div><div class="ui-kpi-sub">Sedang berada di area parkir</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Okupansi</div><div class="ui-kpi mt-2">{{ $ter }}<span class="text-lg text-gray-400 font-semibold">/{{ $kap }}</span></div><div class="ui-kpi-sub">{{ $kap > 0 ? $pct.'% terisi' : 'Kapasitas belum diatur' }}</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Tarif Aktif</div><div class="ui-kpi mt-2">{{ number_format($stats['tarif']) }}</div><div class="ui-kpi-sub">Skema tarif berjalan</div></div>
  <div class="ui-card ui-card-b"><div class="ui-kpi-label">Petugas Aktif</div><div class="ui-kpi mt-2">{{ number_format($stats['petugas']) }}</div><div class="ui-kpi-sub">Siap melayani operasional</div></div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
  <div class="ui-card xl:col-span-2">
    <div class="ui-card-h"><div class="ui-card-t">Aktivitas Transaksi 7 Hari Terakhir</div><div class="ui-card-d">Volume masuk vs keluar harian di tenant.</div></div>
    <div class="ui-card-b"><div style="position:relative;height:250px"><canvas id="adminFlowChart"></canvas></div></div>
  </div>
  <div class="ui-card">
    <div class="ui-card-h"><div class="ui-card-t">Okupansi & Status</div><div class="ui-card-d">Kondisi kapasitas dan layanan.</div></div>
    <div class="ui-card-b">
      <div style="position:relative;height:170px" class="mb-3"><canvas id="adminOccChart"></canvas></div>
      <div class="space-y-2 text-[13px]">
        <div class="flex justify-between"><span class="text-gray-600">Booking</span><span class="ui-badge {{ ($tenant->sediakan_booking ?? 0) ? 'ui-badge-success':'ui-badge-muted' }}">{{ ($tenant->sediakan_booking ?? 0) ? 'Aktif' : 'Nonaktif' }}</span></div>
        <div class="flex justify-between"><span class="text-gray-600">Status tenant</span><span class="ui-badge {{ ($tenant->status_aktif ?? 0) ? 'ui-badge-success':'ui-badge-danger' }}">{{ ($tenant->status_aktif ?? 0) ? 'Aktif' : 'Nonaktif' }}</span></div>
        <div class="flex justify-between"><span class="text-gray-600">Kendaraan terdaftar (offline)</span><span class="font-semibold">{{ number_format($stats['kendaraan']) }}</span></div>
      </div>
      <div class="grid grid-cols-2 gap-2 mt-4">
        <a href="{{ route('admin.tarif.index') }}" class="ui-btn ui-btn-primary">Kelola Tarif</a>
        <a href="{{ route('admin.kapasitas.index') }}" class="ui-btn ui-btn-secondary">Kapasitas</a>
        <a href="{{ route('admin.log-aktivitas.index') }}" class="ui-btn ui-btn-secondary col-span-2">Buka Log Aktivitas</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const flow = document.getElementById('adminFlowChart');
  const occ = document.getElementById('adminOccChart');
  if(typeof Chart === 'undefined') return;
  const data = @json($chart);
  if(flow){
    new Chart(flow,{type:'bar',data:{labels:data.labels,datasets:[
      {label:'Masuk',data:data.masuk,backgroundColor:'rgba(13,99,165,.75)',borderRadius:6,maxBarThickness:28},
      {label:'Keluar',data:data.keluar,backgroundColor:'rgba(22,163,74,.7)',borderRadius:6,maxBarThickness:28}
    ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#eef0f4'}}}}});
  }
  if(occ){
    const o = data.occupancy || {terisi:0,sisa:0};
    const total = (o.terisi||0)+(o.sisa||0);
    new Chart(occ,{type:'doughnut',data:{labels:['Terisi','Sisa'],datasets:[{data: total>0 ? [o.terisi,o.sisa] : [0,1], backgroundColor:['#0D63A5','#e5e7eb'], borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'70%',plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}}}});
  }
})();
</script>
@endpush
