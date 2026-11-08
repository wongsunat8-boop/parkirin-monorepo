@extends('layouts.staff')
@section('title','Dashboard Owner')
@section('page-title','Dashboard Owner')
@section('page-desc','Monitor operasional, deteksi anomali anti-fraud, dan pantau arus kendaraan tenant.')

@section('staff-content')
@php $selisih = $totalMasukHariIni - $totalKeluarHariIni; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Kendaraan Aktif Saat Ini</div>
    <div class="ui-kpi mt-2 {{ $kendaraanAktif > 0 ? 'text-primary' : '' }}">{{ number_format($kendaraanAktif) }}</div>
    <div class="ui-kpi-sub">Status <em>masuk</em> & belum keluar (real-time indikator)</div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Total Masuk Hari Ini</div>
    <div class="ui-kpi mt-2">{{ number_format($totalMasukHariIni) }}</div>
    <div class="ui-kpi-sub">{{ now()->translatedFormat('d M Y') }}</div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Total Keluar Hari Ini</div>
    <div class="ui-kpi mt-2" style="color:#16a34a">{{ number_format($totalKeluarHariIni) }}</div>
    <div class="ui-kpi-sub">Checkout tercatat hari ini</div>
  </div>
  <div class="ui-card ui-card-b">
    <div class="ui-kpi-label">Anomali Menginap &gt; 24 Jam</div>
    <div class="ui-kpi mt-2 {{ $anomaliMenginap->isNotEmpty() ? 'text-danger' : '' }}">{{ $anomaliMenginap->count() }}</div>
    <div class="ui-kpi-sub">{{ $anomaliMenginap->isNotEmpty() ? 'Perlu verifikasi petugas' : 'Tidak ada anomali terdeteksi' }}</div>
  </div>
</div>

@if($kendaraanAktif !== $selisih)
  <div class="ui-alert ui-alert-warning mb-4">
    <strong>Indikasi selisih data (anti-fraud check):</strong> Kendaraan aktif saat ini ({{ $kendaraanAktif }}) berbeda dari selisih masuk–keluar hari ini ({{ $selisih }}). Umumnya karena kendaraan masuk sebelum hari ini masih berada di area. Tinjau daftar anomali & transaksi aktif bila selisih tidak wajar.
  </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
  <div class="ui-card xl:col-span-2">
    <div class="ui-card-h">
      <div class="ui-card-t">Arus Kendaraan 7 Hari Terakhir</div>
      <div class="ui-card-d">Perbandingan transaksi masuk vs keluar berdasarkan tanggal (tenant Anda).</div>
    </div>
    <div class="ui-card-b"><div style="position:relative;height:260px"><canvas id="ownerFlowChart"></canvas></div></div>
  </div>
  <div class="ui-card">
    <div class="ui-card-h">
      <div class="ui-card-t">Ringkasan Hari Ini</div>
      <div class="ui-card-d">Status operasional cepat.</div>
    </div>
    <div class="ui-card-b space-y-3">
      <div class="flex items-center justify-between"><span class="text-[13px] text-gray-600">Masuk hari ini</span><span class="font-semibold">{{ number_format($totalMasukHariIni) }}</span></div>
      <div class="flex items-center justify-between"><span class="text-[13px] text-gray-600">Keluar hari ini</span><span class="font-semibold">{{ number_format($totalKeluarHariIni) }}</span></div>
      <div class="flex items-center justify-between"><span class="text-[13px] text-gray-600">Masih aktif</span><span class="font-semibold">{{ number_format($kendaraanAktif) }}</span></div>
      <div class="border-t border-gray-100 pt-3 flex items-center justify-between"><span class="text-[13px] text-gray-600">Anomali &gt;24 jam</span>
        <span class="ui-badge {{ $anomaliMenginap->isNotEmpty() ? 'ui-badge-danger' : 'ui-badge-success' }}">{{ $anomaliMenginap->count() }}</span>
      </div>
      <a href="{{ route('owner.transaksi.index') }}" class="ui-btn ui-btn-secondary w-full mt-2">Lihat Rekap Transaksi</a>
    </div>
  </div>
</div>

@if($anomaliMenginap->isNotEmpty())
  <div class="ui-card mb-4" style="border-color:#fecaca">
    <div class="ui-card-h" style="background:#fef2f2;border-bottom-color:#fecaca">
      <div class="flex items-center justify-between gap-2">
        <div><div class="ui-card-t" style="color:#991b1b">Peringatan Anomali: Kendaraan Menginap &gt; 24 Jam</div><div class="ui-card-d" style="color:#b91c1c">Kendaraan masih berstatus masuk tanpa checkout melewati 24 jam.</div></div>
        <span class="ui-badge ui-badge-danger">{{ $anomaliMenginap->count() }} unit</span>
      </div>
    </div>
    <div class="ui-table-wrap">
      <table class="ui-table"><thead><tr><th>Plat Nomor</th><th>Pengendara</th><th>Waktu Masuk</th><th>Durasi</th></tr></thead>
      <tbody>
        @foreach($anomaliMenginap as $t)
          <tr>
            <td><span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span></td>
            <td>{{ $t->pengendara->nama_lengkap ?? 'Offline/Umum' }}</td>
            <td class="whitespace-nowrap">{{ optional($t->waktu_masuk)->format('d/m/Y H:i') }}</td>
            <td><span class="ui-badge ui-badge-danger">{{ optional($t->waktu_masuk)->diffForHumans(now(), true) }}</span></td>
          </tr>
        @endforeach
      </tbody></table>
    </div>
  </div>
@endif

<div class="ui-card">
  <div class="ui-card-h flex items-center justify-between gap-2">
    <div><div class="ui-card-t">Transaksi Terbaru</div><div class="ui-card-d">Aktivitas transaksi terakhir di tenant.</div></div>
    <a href="{{ route('owner.transaksi.index') }}" class="ui-btn ui-btn-ghost text-primary">Lihat semua</a>
  </div>
  <div class="ui-table-wrap">
    <table class="ui-table"><thead><tr><th>Plat</th><th>Masuk</th><th>Keluar</th><th>Biaya</th><th>Status</th></tr></thead>
    <tbody>
      @forelse($recentTransaksi as $t)
        <tr>
          <td><span class="badge-plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span></td>
          <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_masuk)->format('d/m H:i') }}</td>
          <td class="whitespace-nowrap text-gray-600">{{ optional($t->waktu_keluar)->format('d/m H:i') ?? '-' }}</td>
          <td class="whitespace-nowrap">{{ $t->biaya_total ? 'Rp '.number_format($t->biaya_total,0,',','.') : '-' }}</td>
          <td><span class="ui-badge {{ $t->status==='masuk' ? 'ui-badge-warning' : 'ui-badge-success' }}">{{ $t->status }}</span></td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-gray-500 py-8">Belum ada transaksi.</td></tr>
      @endforelse
    </tbody></table>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const el = document.getElementById('ownerFlowChart');
  if(!el || typeof Chart === 'undefined') return;
  const data = @json($chart);
  new Chart(el, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [
        { label: 'Masuk', data: data.masuk, backgroundColor: 'rgba(13,99,165,.75)', borderRadius: 6, maxBarThickness: 28 },
        { label: 'Keluar', data: data.keluar, backgroundColor: 'rgba(22,163,74,.7)', borderRadius: 6, maxBarThickness: 28 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } },
      scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef0f4' } } }
    }
  });
})();
</script>
@endpush
