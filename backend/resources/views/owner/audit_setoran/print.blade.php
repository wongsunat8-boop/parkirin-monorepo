<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Setoran Kasir - {{ $tenant->nama_tempat }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; line-height: 1.4; }
        .header { border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .meta { font-size: 11px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f4f4f4; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box { margin-top: 20px; float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #ccc; }
        .total-row.grand { font-size: 14px; font-weight: bold; border-top: 2px solid #111; border-bottom: 2px solid #111; margin-top: 6px; padding: 6px 0; }
        .sign-area { margin-top: 80px; clear: both; display: flex; justify-content: space-between; padding-top: 20px; }
        .sign-box { text-align: center; width: 200px; }
        .sign-line { border-bottom: 1px solid #333; margin-top: 60px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div>
            <h1 class="title">LEMBAR REKAP DAN PENGECEKAN SETORAN KASIR</h1>
            <div class="meta">{{ $tenant->nama_tempat }} &bull; Periode: {{ $dari }} s/d {{ $sampai }}</div>
        </div>
        <div class="meta text-right">
            Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal Shift</th>
                <th>Petugas Kasir</th>
                <th class="text-center">Volume Karcis</th>
                <th class="text-right">Setoran Tunai (Rp)</th>
                <th class="text-right">Non-Tunai (Rp)</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shifts as $idx => $s)
                @php $kasir = $petugasMap->get($s->id_petugas_keluar); @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->tanggal_shift)->translatedFormat('d/m/Y') }}</td>
                    <td>{{ $kasir->nama_lengkap ?? 'Self / Sistem' }}</td>
                    <td class="text-center">{{ number_format($s->total_transaksi) }}</td>
                    <td class="text-right">{{ number_format($s->setoran_tunai, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($s->non_tunai, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($s->total_omzet, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Tidak ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>

    @php
        $onlineTrx = collect();
        if (isset($memberList)) {
            foreach ($memberList as $m) {
                $onlineTrx->push([
                    'tipe' => 'Member Pass',
                    'kode' => $m->kode_member,
                    'waktu' => $m->waktu_unggah_bukti ?? $m->waktu_registrasi ?? $m->tanggal_mulai,
                    'pengendara' => $m->pengendara?->nama_lengkap ?? 'Rider',
                    'plat' => $m->kendaraan?->plat_nomor ?? '-',
                    'jenis' => $m->kendaraan?->jenis_kendaraan ?? '-',
                    'rincian' => $m->paket?->nama_paket ?? 'Paket Langganan',
                    'nominal' => (float) $m->biaya_langganan,
                    'verifikator' => $m->userVerifikasi?->nama_lengkap ?? 'Admin / Otomatis',
                    'status' => $m->status_pembayaran,
                ]);
            }
        }
        if (isset($bookingList)) {
            foreach ($bookingList as $b) {
                $onlineTrx->push([
                    'tipe' => 'Booking Inap',
                    'kode' => $b->kode_booking,
                    'waktu' => $b->waktu_unggah_bukti ?? $b->waktu_booking,
                    'pengendara' => $b->pengendara?->nama_lengkap ?? 'Rider',
                    'plat' => $b->kendaraan?->plat_nomor ?? '-',
                    'jenis' => $b->kendaraan?->jenis_kendaraan ?? '-',
                    'rincian' => 'Inap ' . \Carbon\Carbon::parse($b->waktu_mulai_inap)->format('d/m') . ' s/d ' . \Carbon\Carbon::parse($b->waktu_rencana_keluar)->format('d/m/Y'),
                    'nominal' => (float) $b->biaya_inap_dibayar,
                    'verifikator' => $b->userVerifikasi?->nama_lengkap ?? 'Admin / Otomatis',
                    'status' => $b->status_pembayaran,
                ]);
            }
        }
        $onlineTrx = $onlineTrx->sortByDesc('waktu');
    @endphp

    @if(!$petugasId)
    <h3 style="margin-top: 26px; margin-bottom: 6px; font-size: 13px; font-weight: bold; text-transform: uppercase;">
        Penerimaan Non-Tunai Online (Member Pass & Booking Inap Terverifikasi)
    </h3>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th>Waktu Transaksi</th>
                <th>Layanan</th>
                <th>Kode Trx</th>
                <th>Pengendara</th>
                <th>Kendaraan</th>
                <th>Paket / Jadwal</th>
                <th class="text-right">Nominal (Rp)</th>
                <th>Verifikator</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($onlineTrx as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item['waktu'] ? \Carbon\Carbon::parse($item['waktu'])->translatedFormat('d/m/Y H:i') : '-' }}</td>
                    <td><strong>{{ $item['tipe'] }}</strong></td>
                    <td style="font-family: monospace;">{{ $item['kode'] }}</td>
                    <td>{{ $item['pengendara'] }}</td>
                    <td>{{ $item['plat'] }} ({{ $item['jenis'] }})</td>
                    <td>{{ $item['rincian'] }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item['nominal'], 0, ',', '.') }}</td>
                    <td>{{ $item['verifikator'] }}</td>
                    <td class="text-center" style="color: #15803d; font-weight: bold;">{{ $item['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">Tidak ada transaksi non-tunai online pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <div class="total-box">
        <div class="total-row">
            <span>Total Tunai Wajib Setor:</span>
            <strong>Rp {{ number_format($totalTunai, 0, ',', '.') }}</strong>
        </div>
        <div class="total-row">
            <span>Non-Tunai Pos Kasir:</span>
            <span>Rp {{ number_format($totalNonTunaiKasir ?? 0, 0, ',', '.') }}</span>
        </div>
        @if(!$petugasId)
        <div class="total-row">
            <span>Non-Tunai Member Pass:</span>
            <span>Rp {{ number_format($nonTunaiMember ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Non-Tunai Booking Inap:</span>
            <span>Rp {{ number_format($nonTunaiBooking ?? 0, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row" style="font-weight: bold;">
            <span>Total Non-Tunai:</span>
            <span>Rp {{ number_format($totalNonTunai, 0, ',', '.') }}</span>
        </div>
        <div class="total-row grand">
            <span>Grand Total:</span>
            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="sign-area">
        <div class="sign-box">
            <div>Diserahkan oleh Kasir,</div>
            <div class="sign-line"></div>
            <div style="margin-top: 4px;">Petugas Kasir</div>
        </div>
        <div class="sign-box">
            <div>Diterima & Diperiksa,</div>
            <div class="sign-line"></div>
            <div style="margin-top: 4px;">Owner / Pengelola</div>
        </div>
    </div>

</body>
</html>
