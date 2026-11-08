<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Transaksi Parkir — {{ $tenant->nama_tempat ?? 'Tenant' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#111827; --muted:#6b7280; --line:#d1d5db; --soft:#f3f4f6; --accent:#0D63A5; }
        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; color:var(--ink); font: 12px/1.45 Inter, Arial, sans-serif; background:#e5e7eb; }
        .sheet { width: 210mm; min-height: 297mm; margin: 12mm auto; background:#fff; padding: 14mm 14mm 16mm; box-shadow: 0 8px 30px rgba(0,0,0,.12); }
        .no-print.toolbar { width:210mm; margin: 10px auto 0; display:flex; gap:8px; justify-content:flex-end; }
        .btn { appearance:none; border:1px solid var(--line); background:#fff; color:var(--ink); padding:8px 14px; border-radius:8px; font:600 12px Inter, sans-serif; cursor:pointer; text-decoration:none; }
        .btn.primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        .report-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding-bottom:12px; border-bottom:2px solid var(--ink); margin-bottom:14px; }
        .brand { font: 800 11px Nunito, Inter, sans-serif; letter-spacing:.08em; color:var(--accent); text-transform:uppercase; margin-bottom:4px; }
        h1 { margin:0; font: 800 20px/1.15 Nunito, Inter, sans-serif; letter-spacing:.01em; }
        .subtitle { margin-top:4px; color:var(--muted); font-size:12px; }
        .meta { text-align:right; font-size:11px; color:#374151; min-width: 62mm; }
        .meta table { width:100%; border-collapse:collapse; }
        .meta td { padding:1px 0; vertical-align:top; }
        .meta td:first-child { color:var(--muted); padding-right:8px; white-space:nowrap; }
        .summary { display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin: 12px 0 14px; }
        .card { border:1px solid var(--line); border-radius:8px; padding:9px 10px; background:#fff; }
        .card .label { font-size:10px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); font-weight:600; }
        .card .value { font: 700 15px/1.2 Inter, sans-serif; margin-top:2px; }
        .filters { font-size:11px; color:#374151; background:var(--soft); border:1px solid #e5e7eb; border-radius:8px; padding:8px 10px; margin-bottom:12px; }
        .filters strong { color:var(--ink); }
        table.data { width:100%; border-collapse:collapse; font-size:10.5px; }
        table.data th, table.data td { border:1px solid var(--line); padding:6px 7px; vertical-align:top; }
        table.data th { background:#f9fafb; color:#374151; font-weight:700; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:.03em; }
        table.data tbody tr:nth-child(even) { background:#fcfcfd; }
        .num { text-align:right; white-space:nowrap; }
        .nowrap { white-space:nowrap; }
        .muted { color:var(--muted); }
        .plat { display:inline-block; font:700 10px Inter, monospace; letter-spacing:.06em; background:#111827; color:#fff; padding:2px 6px; border-radius:4px; }
        .footer { margin-top:14px; padding-top:10px; border-top:1px solid var(--line); display:flex; justify-content:space-between; gap:12px; font-size:10.5px; color:var(--muted); }
        .sign { width:58mm; text-align:center; color:#374151; }
        .sign .line { margin-top:18mm; border-top:1px solid #374151; padding-top:3px; }
        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            html, body { background:#fff; }
            .sheet { width:auto; min-height:0; margin:0; padding:0; box-shadow:none; }
            .no-print { display:none !important; }
            table.data { page-break-inside:auto; }
            tr { page-break-inside:avoid; page-break-after:auto; }
            thead { display:table-header-group; }
            .footer { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print toolbar">
        <a href="{{ route('owner.transaksi.index', request()->except('auto')) }}" class="btn">Kembali</a>
        <button onclick="window.print()" class="btn primary">Cetak / Simpan PDF</button>
    </div>

    <main class="sheet">
        <header class="report-header">
            <div>
                <div class="brand">Parkirin</div>
                <h1>Rekap Transaksi Parkir</h1>
                <div class="subtitle">{{ $tenant->nama_tempat ?? 'Tenant' }}</div>
            </div>
            <div class="meta">
                <table>
                    <tr><td>Dicetak</td><td>: {{ now()->translatedFormat('d F Y, H:i') }} WIB</td></tr>
                    <tr><td>Dicetak oleh</td><td>: {{ auth('web')->user()->nama_lengkap ?? '-' }}</td></tr>
                    <tr><td>Total data</td><td>: {{ number_format($summary['total'], 0, ',', '.') }} transaksi</td></tr>
                </table>
            </div>
        </header>

        @php
            $periode = 'Semua periode';
            if (!empty($filters['dari']) || !empty($filters['sampai'])) {
                $dari = !empty($filters['dari']) ? \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d/m/Y') : 'awal';
                $sampai = !empty($filters['sampai']) ? \Carbon\Carbon::parse($filters['sampai'])->translatedFormat('d/m/Y') : 'sekarang';
                $periode = $dari.' s/d '.$sampai;
            }
            $filterParts = ['Periode: '.$periode];
            if (!empty($filters['status'])) $filterParts[] = 'Status: '.$filters['status'];
            if (!empty($filters['pembayaran'])) $filterParts[] = 'Pembayaran: '.$filters['pembayaran'];
        @endphp
        <div class="filters"><strong>Filter laporan:</strong> {{ implode(' · ', $filterParts) }}</div>

        <section class="summary">
            <div class="card"><div class="label">Total Transaksi</div><div class="value">{{ number_format($summary['total'], 0, ',', '.') }}</div></div>
            <div class="card"><div class="label">Pendapatan (Sudah Bayar)</div><div class="value">Rp {{ number_format($summary['pendapatan'], 0, ',', '.') }}</div></div>
            <div class="card"><div class="label">Nilai Belum Bayar</div><div class="value">Rp {{ number_format($summary['belum_bayar'] ?? 0, 0, ',', '.') }}</div></div>
        </section>

        <table class="data">
            <thead>
                <tr>
                    <th style="width:10mm">No</th>
                    <th style="width:14mm">ID</th>
                    <th>Plat / Jenis</th>
                    <th>Pengendara</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                    <th class="num">Durasi</th>
                    <th class="num">Biaya</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $i => $t)
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td class="num muted">#{{ $t->id_transaksi }}</td>
                        <td><span class="plat">{{ $t->kendaraan->plat_nomor ?? '-' }}</span><div class="muted" style="margin-top:2px">{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</div></td>
                        <td>{{ $t->pengendara->nama_lengkap ?? 'Offline' }}</td>
                        <td class="nowrap">{{ optional($t->waktu_masuk)->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="nowrap">{{ optional($t->waktu_keluar)->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="num">{{ $t->durasi_jam !== null ? $t->durasi_jam.' jam' : '-' }}</td>
                        <td class="num">{{ $t->biaya_total !== null ? 'Rp '.number_format($t->biaya_total, 0, ',', '.') : '-' }}</td>
                        <td>{{ $t->status_pembayaran }}</td>
                        <td>{{ ucfirst($t->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="muted" style="text-align:center; padding:16px;">Tidak ada data transaksi untuk filter yang dipilih.</td></tr>
                @endforelse
            </tbody>
        </table>

        <footer class="footer">
            <div>Dokumen ini dihasilkan otomatis oleh sistem Parkirin. Data menampilkan hasil filter pada saat cetak.</div>
            <div class="sign">
                <div>{{ $tenant->nama_tempat ?? 'Tenant' }}, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="line">Owner / Penanggung Jawab</div>
            </div>
        </footer>
    </main>

    @if($autoPrint)
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>
    @endif
</body>
</html>
