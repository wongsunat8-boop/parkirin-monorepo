@extends('layouts.pengendara')

@section('title', 'Dashboard Pengendara — Parkirin')
@section('page-title', 'Dashboard')
@section('page-desc', 'Frekuensi parkir, karcis digital aktif, dan kendaraan pribadi Anda.')

@section('pengendara-content')
<style>
  .bento-grid { display:grid; grid-template-columns: 1fr; gap: 16px; }
  @media (min-width: 1024px) { .bento-grid { grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 18px; } .span-7{grid-column:span 7;} .span-5{grid-column:span 5;} .span-12{grid-column:span 12;} .span-6{grid-column:span 6;} .span-8{grid-column:span 8;} .span-4{grid-column:span 4;} }
  .stack-area { position: relative; min-height: 120px; padding: 8px 4px 18px; }
  .stack-card { position: relative; background: linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); border:1px solid rgba(226,232,240,.9); border-radius: 16px; padding: 14px 14px 14px 16px; box-shadow: 0 8px 24px rgba(15,23,42,.06); transition: transform .18s ease, box-shadow .18s ease; }
  .stack-card + .stack-card { margin-top: -10px; }
  .stack-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(15,23,42,.09); z-index: 2; }
  .stack-card::before { content:''; position:absolute; left:0; top:12px; bottom:12px; width:4px; border-radius:999px; background: var(--stack-accent, #0D63A5); }
  .qr-box { display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:10px; }
  .qr-box canvas, .qr-box img { display:block; border-radius:6px; }
  .modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.45); backdrop-filter: blur(4px); z-index:60; display:flex; align-items:flex-end; justify-content:center; padding:16px; }
  @media (min-width: 640px){ .modal-backdrop{ align-items:center; } }
  .modal-panel { width:100%; max-width:520px; max-height:92vh; overflow:auto; background:#fff; border:1px solid rgba(226,232,240,.9); border-radius:18px; box-shadow:0 20px 50px rgba(15,23,42,.18); }
  .ticket-strip { background: linear-gradient(135deg,#0D63A5 0%,#0A4F84 100%); color:#fff; border-radius:16px; padding:16px; }
  .chart-wrap { position: relative; height: 240px; }
  @media (max-width: 640px){ .chart-wrap { height: 210px; } }
</style>

<div class="page-h">
  <div>
    <h1 class="page-title">Dashboard Pengendara</h1>
    <p class="page-desc">Lihat frekuensi parkir, karcis digital aktif, dan kelola kendaraan pribadi Anda.</p>
  </div>
  <a href="{{ route('pengendara.katalog.index') }}" class="ui-btn ui-btn-primary">Lihat Katalog Parkir</a>
</div>

<div class="bento-grid">
  {{-- Frekuensi Parkir (Chart) --}}
  <section class="bento-card span-8" x-data="{ mode: 'mingguan' }">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
      <div>
        <div class="ui-kpi-label">Frekuensi Parkir</div>
        <div class="text-sm text-slate-500 mt-0.5">Total transaksi parkir: <span class="font-semibold text-[#212121]">{{ $totalParkir }}</span></div>
      </div>
      <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1 gap-1">
        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold transition" :class="mode==='mingguan' ? 'bg-white text-primary shadow-sm border border-slate-200' : 'text-slate-600 hover:text-slate-900'" @click="mode='mingguan'; window.renderParkirChart && window.renderParkirChart('mingguan')">Mingguan</button>
        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold transition" :class="mode==='bulanan' ? 'bg-white text-primary shadow-sm border border-slate-200' : 'text-slate-600 hover:text-slate-900'" @click="mode='bulanan'; window.renderParkirChart && window.renderParkirChart('bulanan')">Bulanan</button>
      </div>
    </div>
    <div class="chart-wrap">
      <canvas id="parkirFreqChart" aria-label="Grafik frekuensi parkir" role="img"></canvas>
    </div>
    <div class="text-[11.5px] text-slate-500 mt-2">Grafik menampilkan jumlah transaksi parkir (berdasarkan waktu masuk) per periode.</div>
  </section>

  {{-- Karcis Digital Aktif --}}
  <section class="bento-card span-4">
    <div class="flex items-start justify-between gap-3 mb-3">
      <div>
        <div class="ui-kpi-label">Karcis Digital Aktif</div>
        <div class="text-sm text-slate-500 mt-0.5">Saat kendaraan berstatus masuk</div>
      </div>
      @if($karcisAktif)
        <span class="ui-badge {{ $karcisAktif->status_pembayaran === 'Sudah Bayar' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $karcisAktif->status_pembayaran }}</span>
      @endif
    </div>

    @if($karcisAktif)
      <div class="ticket-strip mb-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-[11px] uppercase tracking-[.05em] text-white/80 font-semibold">Lokasi</div>
            <div class="font-heading font-extrabold text-sm leading-tight">{{ $karcisAktif->tempatParkir->nama_tempat ?? '-' }}</div>
            <div class="mt-2 flex items-center gap-2 flex-wrap">
              <span class="badge-plat" style="background:rgba(255,255,255,.14); border-color:rgba(255,255,255,.25); font-size:11px;">{{ $karcisAktif->kendaraan->plat_nomor ?? '-' }}</span>
            </div>
            <div class="text-[11px] text-white/80 mt-1">Masuk: {{ $karcisAktif->waktu_masuk?->format('d M Y, H:i') }}</div>
          </div>
          <div class="qr-box shrink-0" style="padding:6px; border-radius:10px;" data-qr="TICKET-{{ $karcisAktif->id_transaksi }}" aria-label="QR Karcis Digital"></div>
        </div>
      </div>
      <div class="flex items-center justify-between gap-2 flex-wrap">
        <div class="text-[11.5px] text-slate-600">ID <span class="font-mono font-semibold">#{{ $karcisAktif->id_transaksi }}</span></div>
        @if($karcisAktif->status_pembayaran !== 'Sudah Bayar')
          <form method="POST" action="{{ route('pengendara.transaksi.bayar', $karcisAktif) }}" class="m-0">@csrf<button type="submit" class="btn-action-success" style="padding:5px 10px;">Bayar QRIS</button></form>
        @else
          <span class="ui-badge ui-badge-success">Lunas</span>
        @endif
      </div>
    @else
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-5 text-center min-h-[180px] flex flex-col items-center justify-center">
        <div class="text-sm font-semibold text-slate-700">Tidak ada karcis aktif</div>
        <div class="text-xs text-slate-500 mt-1">Muncul saat transaksi parkir berstatus masuk.</div>
      </div>
    @endif
  </section>

  {{-- Kendaraan Pribadi - Stacked Bento Widget --}}
  <section class="bento-card span-12" x-data="kendaraanWidget()" @keydown.escape.window="closeAll()">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
      <div>
        <div class="ui-kpi-label">Kendaraan Pribadi</div>
        <div class="text-sm text-slate-500 mt-0.5">Kelola kendaraan, unlink, atau ajukan klaim tanpa keluar dari dashboard.</div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button type="button" class="ui-btn ui-btn-secondary ui-btn-sm" @click="openKlaim()">Ajukan Klaim Kendaraan</button>
        <button type="button" class="ui-btn ui-btn-primary ui-btn-sm" @click="openAdd()">+ Tambah Kendaraan</button>
      </div>
    </div>

    @if($kendaraan->isEmpty())
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
        <div class="text-sm font-semibold text-slate-700">Belum ada kendaraan terdaftar</div>
        <div class="text-xs text-slate-500 mt-1">Tambahkan kendaraan untuk memudahkan identifikasi parkir Anda.</div>
      </div>
    @else
      <div class="stack-area">
        @foreach($kendaraan as $i => $k)
          @php $locked = $k->transaksi_count > 0; $accents = ['#0D63A5','#0891B2','#7C3AED','#059669','#D97706']; @endphp
          <article class="stack-card" style="--stack-accent: {{ $accents[$i % count($accents)] }}; z-index: {{ 10 + $i }};">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="badge-plat">{{ $k->plat_nomor }}</span>
                  <span class="ui-badge ui-badge-info">{{ $k->jenis_kendaraan }}</span>
                  <span class="ui-badge ui-badge-muted">{{ $k->warna }}</span>
                  @if($locked)<span class="ui-badge ui-badge-warning" title="Plat & jenis dikunci karena memiliki riwayat transaksi">Riwayat Terkunci</span>@endif
                </div>
                <div class="text-[11.5px] text-slate-500 mt-2">{{ $locked ? 'Plat nomor & jenis dikunci (Immutable History). Hanya warna dapat diperbarui.' : 'Belum ada riwayat transaksi. Plat, jenis, dan warna dapat diubah.' }}</div>
              </div>
              <div class="flex items-center gap-2 flex-wrap shrink-0">
                <button type="button" class="btn-action-edit" @click="openEdit({{ Js::from(['id'=>$k->id_kendaraan,'plat_nomor'=>$k->plat_nomor,'jenis_kendaraan'=>$k->jenis_kendaraan,'warna'=>$k->warna,'locked'=>(bool)$locked]) }})">Edit</button>
                <form method="POST" action="{{ route('pengendara.kendaraan.unlink', $k) }}" onsubmit="return confirm('{{ $locked ? 'Lepas kendaraan ini dari akun? Riwayat transaksi tetap tersimpan.' : 'Hapus kendaraan ini dari akun?' }}');" class="m-0">
                  @csrf
                  <button type="submit" class="btn-action-danger">Unlink</button>
                </form>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif

    {{-- Modal Tambah --}}
    <div x-cloak x-show="showAdd" class="modal-backdrop" @click.self="showAdd=false">
      <div class="modal-panel" @click.stop>
        <div class="ui-card-h flex items-center justify-between gap-3">
          <div><div class="ui-card-t">Tambah Kendaraan</div><div class="ui-card-d">Daftarkan kendaraan pribadi Anda.</div></div>
          <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showAdd=false">Tutup</button>
        </div>
        <form method="POST" action="{{ route('pengendara.kendaraan.store') }}" class="ui-card-b space-y-3">
          @csrf
          <div><label class="ui-label">Plat Nomor</label><input name="plat_nomor" class="ui-input" placeholder="Contoh: B 1234 XYZ" required></div>
          <div class="grid sm:grid-cols-2 gap-3">
            <div><label class="ui-label">Jenis Kendaraan</label><select name="jenis_kendaraan" class="ui-select" required><option value="Motor">Motor</option><option value="Mobil">Mobil</option><option value="Sepeda">Sepeda</option></select></div>
            <div><label class="ui-label">Warna</label><input name="warna" class="ui-input" placeholder="Contoh: Hitam" required></div>
          </div>
          <div class="flex justify-end gap-2 pt-1"><button type="button" class="ui-btn ui-btn-secondary" @click="showAdd=false">Batal</button><button type="submit" class="ui-btn ui-btn-primary">Simpan Kendaraan</button></div>
        </form>
      </div>
    </div>

    {{-- Modal Edit --}}
    <div x-cloak x-show="showEdit" class="modal-backdrop" @click.self="showEdit=false">
      <div class="modal-panel" @click.stop>
        <div class="ui-card-h flex items-center justify-between gap-3">
          <div><div class="ui-card-t">Edit Kendaraan</div><div class="ui-card-d" x-text="edit.locked ? 'Immutable History: hanya warna yang dapat diubah.' : 'Perbarui data kendaraan.'"></div></div>
          <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showEdit=false">Tutup</button>
        </div>
        <form method="POST" :action="editAction()" class="ui-card-b space-y-3">
          @csrf @method('PATCH')
          <div><label class="ui-label">Plat Nomor</label><input name="plat_nomor" class="ui-input" x-model="edit.plat_nomor" :disabled="edit.locked" :required="!edit.locked"></div>
          <div class="grid sm:grid-cols-2 gap-3">
            <div><label class="ui-label">Jenis Kendaraan</label><select name="jenis_kendaraan" class="ui-select" x-model="edit.jenis_kendaraan" :disabled="edit.locked"><option value="Motor">Motor</option><option value="Mobil">Mobil</option><option value="Sepeda">Sepeda</option></select></div>
            <div><label class="ui-label">Warna</label><input name="warna" class="ui-input" x-model="edit.warna" required></div>
          </div>
          <template x-if="edit.locked"><div class="ui-alert ui-alert-warning text-xs">Kendaraan pernah bertransaksi. <strong>Plat nomor</strong> dan <strong>jenis</strong> dikunci (Immutable History).</div></template>
          <div class="flex justify-end gap-2 pt-1"><button type="button" class="ui-btn ui-btn-secondary" @click="showEdit=false">Batal</button><button type="submit" class="ui-btn ui-btn-primary">Simpan Perubahan</button></div>
        </form>
      </div>
    </div>

    {{-- Modal Klaim --}}
    <div x-cloak x-show="showKlaim" class="modal-backdrop" @click.self="showKlaim=false">
      <div class="modal-panel" @click.stop>
        <div class="ui-card-h flex items-center justify-between gap-3">
          <div><div class="ui-card-t">Ajukan Klaim Kendaraan</div><div class="ui-card-d">Klaim kepemilikan plat nomor yang terdaftar pada akun lain.</div></div>
          <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="showKlaim=false">Tutup</button>
        </div>
        <form method="POST" action="{{ route('pengendara.kendaraan.klaim') }}" class="ui-card-b space-y-3">
          @csrf
          <div><label class="ui-label">Plat Nomor Kendaraan</label><input name="plat_nomor" class="ui-input" placeholder="Masukkan plat nomor yang ingin diklaim" required></div>
          <div class="ui-alert ui-alert-warning text-xs">Jika kendaraan dimiliki pengendara lain, permintaan klaim dikirim ke Notifikasi pemilik untuk disetujui/ditolak.</div>
          <div class="flex justify-end gap-2 pt-1"><button type="button" class="ui-btn ui-btn-secondary" @click="showKlaim=false">Batal</button><button type="submit" class="ui-btn ui-btn-primary">Kirim Pengajuan Klaim</button></div>
        </form>
      </div>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const data = {
      mingguan: { labels: @json($weeklyLabels), counts: @json($weeklyCounts) },
      bulanan: { labels: @json($monthlyLabels), counts: @json($monthlyCounts) }
    };
    let chart;
    function render(mode){
      const ctx = document.getElementById('parkirFreqChart');
      if (!ctx || typeof Chart === 'undefined') return;
      const d = data[mode] || data.mingguan;
      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: d.labels,
          datasets: [{
            label: 'Transaksi Parkir',
            data: d.counts,
            backgroundColor: 'rgba(13, 99, 165, 0.18)',
            borderColor: '#0D63A5',
            borderWidth: 2,
            borderRadius: 8,
            maxBarThickness: 42
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,0.18)' } },
            x: { grid: { display: false } }
          }
        }
      });
    }
    window.renderParkirChart = render;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ render('mingguan'); });
    else render('mingguan');
  })();

  function kendaraanWidget(){
    return {
      showAdd:false, showEdit:false, showKlaim:false,
      edit:{id:null, plat_nomor:'', jenis_kendaraan:'Motor', warna:'', locked:false},
      openAdd(){ this.closeAll(); this.showAdd=true; },
      openEdit(k){ this.closeAll(); this.edit = Object.assign({}, k); this.showEdit=true; },
      openKlaim(){ this.closeAll(); this.showKlaim=true; },
      closeAll(){ this.showAdd=false; this.showEdit=false; this.showKlaim=false; },
      editAction(){ return `{{ url('/pengendara/kendaraan') }}/${this.edit.id}`; }
    }
  }
</script>
@endpush
