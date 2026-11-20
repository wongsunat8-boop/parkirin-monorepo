<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Transaksi Parkir (A4) — {{ $tenant->nama_tempat ?? 'Tempat Parkir' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('assets/js/html2canvas.min.js') }}"></script>
    <script src="{{ asset('assets/js/jspdf.umd.min.js') }}"></script>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #cbd5e1;
            --line-light: #e2e8f0;
            --bg-soft: #f8fafc;
            --brand-blue: #0D63A5;
            --brand-yellow: #FFD717;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            color: var(--ink);
            background: #e2e8f0;
            font: 11px/1.4 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Toolbar Layar */
        .toolbar {
            width: 210mm;
            margin: 12px auto 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .toolbar-left, .toolbar-right {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            transition: all 0.15s ease;
        }
        .btn:hover { background: #f1f5f9; }
        .btn.primary {
            background: var(--brand-blue);
            color: #fff;
            border-color: var(--brand-blue);
        }
        .btn.primary:hover { background: #094b7e; }
        .btn.success {
            background: #059669;
            color: #fff;
            border-color: #059669;
        }
        .btn.success:hover { background: #047857; }

        /* Format Kertas A4 */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            background: #ffffff;
            padding: 12mm 14mm 14mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* Kop Laporan */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--brand-blue);
            margin-bottom: 12px;
            gap: 16px;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-brand img {
            height: 38px;
            width: auto;
            object-fit: contain;
        }
        .header-brand-info h1 {
            margin: 0;
            font-family: 'Nunito', 'Inter', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: var(--brand-blue);
            line-height: 1.15;
            letter-spacing: -0.01em;
        }
        .header-brand-info .tagline {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            font-weight: 600;
            margin-top: 2px;
        }
        .header-tenant {
            margin-top: 5px;
            font-size: 11px;
            font-weight: 700;
            color: var(--ink);
        }
        .header-tenant-sub {
            font-size: 10px;
            color: var(--muted);
            margin-top: 1px;
        }
        .header-meta {
            text-align: right;
            font-size: 10px;
            min-width: 60mm;
            background: var(--bg-soft);
            border: 1px solid var(--line-light);
            border-radius: 6px;
            padding: 6px 10px;
        }
        .header-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-meta td {
            padding: 1px 0;
            vertical-align: top;
        }
        .header-meta td:first-child {
            color: var(--muted);
            text-align: left;
            padding-right: 6px;
            white-space: nowrap;
        }
        .header-meta td:last-child {
            text-align: right;
            font-weight: 600;
            color: var(--ink);
        }

        /* Section Block */
        .section-block {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-soft);
            border: 1px solid var(--line);
            border-left: 4px solid var(--brand-blue);
            padding: 5px 10px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .section-title {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-tag {
            background: var(--brand-blue);
            color: #fff;
            font-size: 8.5px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* KPI Sub-cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 6px 8px;
        }
        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
            font-weight: 600;
        }
        .kpi-value {
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            margin-top: 2px;
            line-height: 1.15;
        }
        .kpi-sub {
            font-size: 9px;
            color: var(--muted);
            margin-top: 2px;
        }
        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .text-warning { color: var(--warning); }
        .text-brand { color: var(--brand-blue); }

        /* Chart Container */
        .chart-box {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 9.5px;
        }
        .chart-legend {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 9.5px;
            font-weight: 600;
        }
        .legend-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
        }

        /* Kesimpulan Box */
        .kesimpulan-box {
            background: #f0f7ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 10px;
            line-height: 1.45;
            color: #0369a1;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .kesimpulan-box strong {
            color: #0c4a6e;
        }
        .kesimpulan-icon {
            font-size: 13px;
            line-height: 1;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Filter Box (Section 3) */
        .filter-banner {
            background: var(--bg-soft);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .filter-item {
            display: inline-flex;
            gap: 4px;
        }
        .filter-item .label {
            color: var(--muted);
            font-weight: 500;
        }
        .filter-item .val {
            color: var(--ink);
            font-weight: 700;
        }

        /* Data Table (Section 3) */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-top: 6px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid var(--line);
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.data-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        table.data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .plat-badge {
            display: inline-block;
            font: 700 9px monospace;
            background: #0f172a;
            color: #ffffff;
            padding: 1px 5px;
            border-radius: 3px;
            letter-spacing: 0.05em;
        }
        .status-badge {
            display: inline-block;
            font-size: 8.5px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 3px;
            text-transform: capitalize;
        }
        .status-badge.lunas { background: #dcfce7; color: #15803d; }
        .status-badge.belum { background: #fef3c7; color: #b45309; }
        .status-badge.masuk { background: #dbeafe; color: #1d4ed8; }
        .status-badge.keluar { background: #e2e8f0; color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .muted { color: var(--muted); }

        /* Footer & Signatures */
        .report-footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            page-break-inside: avoid;
        }
        .footer-note {
            font-size: 9px;
            color: var(--muted);
            margin-bottom: 16px;
            font-style: italic;
        }
        .signature-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 0 20px;
        }
        .signature-box {
            width: 60mm;
            text-align: center;
            font-size: 10px;
        }
        .signature-role {
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 3px;
        }
        .signature-line {
            margin-top: 20mm;
            border-top: 1px solid #334155;
            padding-top: 4px;
            font-weight: 600;
            color: var(--ink);
        }

        /* Cetak (Print Stylesheet A4) */
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }
        @media print {
            html, body {
                background: #ffffff;
                color: #000000;
                font-size: 10px;
            }
            .toolbar {
                display: none !important;
            }
            .page-sheet {
                width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .section-block {
                page-break-inside: avoid;
            }
            table.data-table {
                page-break-inside: auto;
            }
            table.data-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            table.data-table thead {
                display: table-header-group;
            }
            .report-footer {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (Tidak ikut dicetak) -->
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <a href="{{ route('owner.transaksi.index', request()->except('auto')) }}" class="btn">
                <span>←</span> Kembali ke Riwayat
            </a>
            <span style="font-size:11px; color:#475569; font-weight:500;">
                Pratinjau Lembar Standar A4 Parkirin
            </span>
        </div>
        <div class="toolbar-right">
            <a href="{{ route('owner.transaksi.export', request()->query()) }}" class="btn">
                <span>📥</span> Ekspor CSV
            </a>
            <button onclick="downloadPdfDirect()" id="btnDirectPdf" class="btn success">
                <span>📄</span> Unduh PDF Langsung
            </button>
            <button onclick="window.print()" class="btn primary">
                <span>🖨️</span> Cetak Lembar A4
            </button>
        </div>
    </div>

    <!-- Halaman Dokumen A4 -->
    <main class="page-sheet" id="printableSheet">

        <!-- KOP DOKUMEN -->
        <header class="report-header">
            <div class="header-brand">
                <img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" onerror="this.onerror=null; this.src='{{ asset('assets/logo/primary/Icon.svg') }}'">
                <div class="header-brand-info">
                    <h1>REKAPITULASI LAPORAN TRANSAKSI PARKIR</h1>
                    <div class="tagline">Sistem Manajemen Parkir Digital & Mandiri</div>
                    <div class="header-tenant">{{ $tenant->nama_tempat ?? 'Tempat Parkir' }}</div>
                    <div class="header-tenant-sub">
                        ID Lahan: #{{ $tenant->id_tempat_parkir ?? 1 }} &bull;
                        Kapasitas: {{ number_format($tenant->kapasitas_reguler ?? 0) }} Reguler / {{ number_format($tenant->kuota_inap ?? 0) }} Inap &bull;
                        Status: {{ ($tenant->status_aktif ?? true) ? 'Operasional Aktif' : 'Nonaktif' }}
                    </div>
                </div>
            </div>
            <div class="header-meta">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>LAP-TRX/{{ date('Ymd') }}/{{ str_pad($tenant->id_tempat_parkir ?? 1, 3, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td>Waktu Cetak</td>
                        <td>{{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Pencetak</td>
                        <td>{{ auth('web')->user()->nama_lengkap ?? 'Owner' }}{{ !str_contains(strtolower(auth('web')->user()->nama_lengkap ?? ''), 'owner') ? ' (Owner)' : '' }}</td>
                    </tr>
                    <tr>
                        <td>Format Lembar</td>
                        <td>Standar Kertas A4</td>
                    </tr>
                </table>
            </div>
        </header>

        <!-- ============================================================== -->
        <!-- BAGIAN 1: RANGKUMAN TRANSAKSI PERBANDINGAN TAHUNAN (YOY)       -->
        <!-- ============================================================== -->
        <section class="section-block">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-tag">Bagian 1</span>
                    <span>Rangkuman Transaksi Perbandingan Tahunan (2025 vs 2026)</span>
                </div>
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background:#94a3b8"></span> 2025 (Tahun Lalu)</span>
                    <span><span class="legend-dot" style="background:#0D63A5"></span> 2026 (Tahun Berjalan)</span>
                </div>
            </div>

            <!-- KPI Sub-cards Bagian 1 -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Pemasukan 2026</div>
                    <div class="kpi-value text-brand">Rp {{ number_format($yoy['total_omzet_2026'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">Total penerimaan tahun berjalan</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Pemasukan 2025</div>
                    <div class="kpi-value">Rp {{ number_format($yoy['total_omzet_2025'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">Penerimaan tahun sebelumnya</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Pertumbuhan Pemasukan</div>
                    <div class="kpi-value {{ $yoy['growth_omzet'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ ($yoy['growth_omzet'] >= 0 ? '+' : '') . $yoy['growth_omzet'] }}%
                    </div>
                    <div class="kpi-sub">Lalu lintas: {{ ($yoy['growth_vol'] >= 0 ? '+' : '') . $yoy['growth_vol'] }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Bulan Puncak 2026</div>
                    <div class="kpi-value" style="font-size:12px;">{{ $yoy['peak_month_2026'] }}</div>
                    <div class="kpi-sub">Rp {{ number_format($yoy['peak_omzet_2026'], 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Grafik Batang Vektor SVG YoY (Zero JS Latency & Crisp 300+ DPI Print) -->
            @php
                $svgW = 730;
                $svgH = 125;
                $padL = 50;
                $padR = 15;
                $padT = 18;
                $padB = 22;
                $plotW = $svgW - $padL - $padR;
                $plotH = $svgH - $padT - $padB;
                $maxYoy = max($yoy['max_omzet'] ?? 1, 1);
                $stepX = $plotW / 12;
                $barW = 10;
                $barGap = 2;
            @endphp
            <div class="chart-box">
                <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width:100%; height:auto; display:block;" font-family="Inter, sans-serif">
                    <!-- Garis Panduan Horizontal Sumbu Y -->
                    @for($g = 0; $g <= 4; $g++)
                        @php
                            $gy = $padT + ($plotH * (4 - $g) / 4);
                            $gVal = ($maxYoy * $g / 4);
                            $gLabel = $gVal >= 1000000 ? round($gVal / 1000000, 1).'jt' : ($gVal > 0 ? round($gVal / 1000).'rb' : '0');
                        @endphp
                        <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#e2e8f0" stroke-width="0.7" stroke-dasharray="{{ $g == 0 ? 'none' : '3,3' }}"/>
                        <text x="{{ $padL - 6 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="8.5" fill="#64748b">{{ $gLabel }}</text>
                    @endfor

                    <!-- Batang 12 Bulan (2025 vs 2026) -->
                    @for($i = 0; $i < 12; $i++)
                        @php
                            $xCenter = $padL + ($i * $stepX) + ($stepX / 2);
                            $x25 = $xCenter - $barW - ($barGap / 2);
                            $x26 = $xCenter + ($barGap / 2);

                            $val25 = $yoy['omzet_2025'][$i] ?? 0;
                            $val26 = $yoy['omzet_2026'][$i] ?? 0;

                            $h25 = max(round(($val25 / $maxYoy) * $plotH), ($val25 > 0 ? 2 : 0));
                            $y25 = $padT + $plotH - $h25;

                            $h26 = max(round(($val26 / $maxYoy) * $plotH), ($val26 > 0 ? 2 : 0));
                            $y26 = $padT + $plotH - $h26;
                        @endphp

                        <!-- Bar 2025 (Abu-abu) -->
                        <rect x="{{ $x25 }}" y="{{ $y25 }}" width="{{ $barW }}" height="{{ $h25 }}" fill="#94a3b8" rx="1.5"/>

                        <!-- Bar 2026 (Biru Parkirin) -->
                        <rect x="{{ $x26 }}" y="{{ $y26 }}" width="{{ $barW }}" height="{{ $h26 }}" fill="#0D63A5" rx="1.5"/>

                        <!-- Label Bulan -->
                        <text x="{{ $xCenter }}" y="{{ $padT + $plotH + 14 }}" text-anchor="middle" font-size="8.5" font-weight="600" fill="#334155">{{ $yoy['labels'][$i] }}</text>
                    @endfor
                </svg>
            </div>

            <!-- Kesimpulan Bagian 1 -->
            <div class="kesimpulan-box">
                <div class="kesimpulan-icon">📊</div>
                <div>
                    <strong>Kesimpulan Analisis Tahunan:</strong> {{ $yoy['kesimpulan'] }}
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- BAGIAN 2: RANGKUMAN TRANSAKSI 7 HARI TERAKHIR                  -->
        <!-- ============================================================== -->
        <section class="section-block">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-tag">Bagian 2</span>
                    <span>Rangkuman Transaksi 7 Hari Terakhir</span>
                </div>
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background:#0D63A5"></span> Kendaraan Masuk</span>
                    <span><span class="legend-dot" style="background:#d97706"></span> Kendaraan Keluar</span>
                </div>
            </div>

            <!-- KPI Sub-cards Bagian 2 -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Kendaraan Masuk</div>
                    <div class="kpi-value text-brand">{{ number_format($sevenDays['total_masuk'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">Total 7 hari terakhir</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Kendaraan Keluar</div>
                    <div class="kpi-value text-warning">{{ number_format($sevenDays['total_keluar'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">Total 7 hari terakhir</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Rata-Rata Harian</div>
                    <div class="kpi-value">{{ number_format($sevenDays['rata_rata_harian'], 1, ',', '.') }}</div>
                    <div class="kpi-sub">Kendaraan per hari</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Hari Terpadat</div>
                    <div class="kpi-value" style="font-size:11.5px;">{{ $sevenDays['busiest_day'] }}</div>
                    <div class="kpi-sub">{{ number_format($sevenDays['busiest_count'], 0, ',', '.') }} kendaraan</div>
                </div>
            </div>

            <!-- Grafik Batang Vektor SVG 7 Hari Terakhir -->
            @php
                $svg7W = 730;
                $svg7H = 120;
                $pad7L = 40;
                $pad7R = 15;
                $pad7T = 20;
                $pad7B = 22;
                $plot7W = $svg7W - $pad7L - $pad7R;
                $plot7H = $svg7H - $pad7T - $pad7B;
                $max7 = max($sevenDays['max_count'] ?? 1, 1);
                $step7X = $plot7W / 7;
                $bar7W = 16;
                $bar7Gap = 4;
            @endphp
            <div class="chart-box">
                <svg viewBox="0 0 {{ $svg7W }} {{ $svg7H }}" style="width:100%; height:auto; display:block;" font-family="Inter, sans-serif">
                    <!-- Garis Panduan Horizontal Sumbu Y -->
                    @for($g = 0; $g <= 3; $g++)
                        @php
                            $gy = $pad7T + ($plot7H * (3 - $g) / 3);
                            $gVal = round($max7 * $g / 3);
                        @endphp
                        <line x1="{{ $pad7L }}" y1="{{ $gy }}" x2="{{ $pad7L + $plot7W }}" y2="{{ $gy }}" stroke="#e2e8f0" stroke-width="0.7" stroke-dasharray="{{ $g == 0 ? 'none' : '3,3' }}"/>
                        <text x="{{ $pad7L - 6 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="8.5" fill="#64748b">{{ $gVal }}</text>
                    @endfor

                    <!-- Batang 7 Hari (Masuk vs Keluar) -->
                    @foreach($sevenDays['days'] as $idx => $d)
                        @php
                            $xCenter = $pad7L + ($idx * $step7X) + ($step7X / 2);
                            $xIn = $xCenter - $bar7W - ($bar7Gap / 2);
                            $xOut = $xCenter + ($bar7Gap / 2);

                            $valIn = $d['masuk'];
                            $valOut = $d['keluar'];

                            $hIn = max(round(($valIn / $max7) * $plot7H), ($valIn > 0 ? 2 : 0));
                            $yIn = $pad7T + $plot7H - $hIn;

                            $hOut = max(round(($valOut / $max7) * $plot7H), ($valOut > 0 ? 2 : 0));
                            $yOut = $pad7T + $plot7H - $hOut;
                        @endphp

                        <!-- Bar Masuk (Biru Parkirin) -->
                        <rect x="{{ $xIn }}" y="{{ $yIn }}" width="{{ $bar7W }}" height="{{ $hIn }}" fill="#0D63A5" rx="2"/>
                        @if($valIn > 0)
                            <text x="{{ $xIn + ($bar7W / 2) }}" y="{{ $yIn - 3 }}" text-anchor="middle" font-size="7.5" font-weight="700" fill="#0D63A5">{{ $valIn }}</text>
                        @endif

                        <!-- Bar Keluar (Oranye/Amber) -->
                        <rect x="{{ $xOut }}" y="{{ $yOut }}" width="{{ $bar7W }}" height="{{ $hOut }}" fill="#d97706" rx="2"/>
                        @if($valOut > 0)
                            <text x="{{ $xOut + ($bar7W / 2) }}" y="{{ $yOut - 3 }}" text-anchor="middle" font-size="7.5" font-weight="700" fill="#d97706">{{ $valOut }}</text>
                        @endif

                        <!-- Label Tanggal -->
                        <text x="{{ $xCenter }}" y="{{ $pad7T + $plot7H + 14 }}" text-anchor="middle" font-size="8.5" font-weight="600" fill="#334155">{{ $d['label'] }}</text>
                    @endforeach
                </svg>
            </div>

            <!-- Kesimpulan Bagian 2 -->
            <div class="kesimpulan-box">
                <div class="kesimpulan-icon">📅</div>
                <div>
                    <strong>Kesimpulan Analisis 7 Hari:</strong> {{ $sevenDays['kesimpulan'] }}
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- BAGIAN 3: RANGKUMAN TRANSAKSI TERPILIH (SESUAI FILTER AKTIF)   -->
        <!-- ============================================================== -->
        <section class="section-block">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-tag">Bagian 3</span>
                    <span>Rangkuman Transaksi Terpilih (Sesuai Filter)</span>
                </div>
                <div style="font-size:9.5px; font-weight:600; color:var(--muted)">
                    Total {{ number_format($filterSummary['total'], 0, ',', '.') }} Transaksi Terdata
                </div>
            </div>

            <!-- Indikator Filter Aktif -->
            <div class="filter-banner">
                <div class="filter-item">
                    <span class="label">Rentang Periode:</span>
                    <span class="val">{{ $filterSummary['periode_str'] }}</span>
                </div>
                <div class="filter-item">
                    <span class="label">Status Parkir:</span>
                    <span class="val">{{ ucfirst($filters['status'] ?: 'Semua Status') }}</span>
                </div>
                <div class="filter-item">
                    <span class="label">Status Pembayaran:</span>
                    <span class="val">{{ $filters['pembayaran'] ?: 'Semua Pembayaran' }}</span>
                </div>
            </div>

            <!-- KPI Sub-cards Bagian 3 -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Total Transaksi</div>
                    <div class="kpi-value">{{ number_format($filterSummary['total'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">Karcis & Non-Tunai Online</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Penerimaan Lunas</div>
                    <div class="kpi-value text-success">Rp {{ number_format($filterSummary['pendapatan'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">Tunai: Rp {{ number_format($filterSummary['tunai'] ?? 0, 0, ',', '.') }} &bull; Non-Tunai: Rp {{ number_format($filterSummary['total_non_tunai'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Non-Tunai Total</div>
                    <div class="kpi-value text-brand">Rp {{ number_format($filterSummary['total_non_tunai'] ?? 0, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Pos: Rp {{ number_format($filterSummary['non_tunai_kasir'] ?? 0, 0, ',', '.') }} &bull; Mbr: Rp {{ number_format($filterSummary['non_tunai_member'] ?? 0, 0, ',', '.') }} &bull; Inap: Rp {{ number_format($filterSummary['non_tunai_booking'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Nilai Belum Bayar</div>
                    <div class="kpi-value text-warning">Rp {{ number_format($filterSummary['belum_bayar'], 0, ',', '.') }}</div>
                    <div class="kpi-sub">{{ number_format($filterSummary['belum_count'], 0, ',', '.') }} trx belum lunas</div>
                </div>
            </div>

            <!-- Kesimpulan Bagian 3 -->
            <div class="kesimpulan-box" style="margin-bottom:10px;">
                <div class="kesimpulan-icon">📋</div>
                <div>
                    <strong>Kesimpulan Data Terfilter:</strong> {{ $filterSummary['kesimpulan'] }}
                </div>
            </div>

            <!-- Tabel Detail Riwayat Transaksi -->
            <div style="font-weight:700; font-size:10.5px; color:var(--ink); margin-bottom:4px; display:flex; justify-content:space-between; align-items:center;">
                <span>Daftar Detail Transaksi Gerbang Terfilter:</span>
                @if(($filterSummary['karcis_total'] ?? $filterSummary['total']) > count($transaksis))
                    <span class="muted" style="font-size:9px; font-weight:normal;">
                        * Menampilkan {{ count($transaksis) }} transaksi terbaru di lembar cetak. Gunakan Ekspor CSV untuk mengunduh seluruh transaksi.
                    </span>
                @endif
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:28px" class="text-center">No</th>
                        <th style="width:48px" class="text-center">ID</th>
                        <th style="width:110px">Plat / Jenis</th>
                        <th>Pengendara</th>
                        <th>Petugas</th>
                        <th class="nowrap">Waktu Masuk</th>
                        <th class="nowrap">Waktu Keluar</th>
                        <th class="text-right" style="width:46px">Durasi</th>
                        <th class="text-right" style="width:72px">Biaya Total</th>
                        <th class="text-center" style="width:75px">Pembayaran</th>
                        <th class="text-center" style="width:55px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $i => $t)
                        <tr>
                            <td class="text-center muted">{{ $i + 1 }}</td>
                            <td class="text-center muted">#{{ $t->id_transaksi }}</td>
                            <td>
                                <span class="plat-badge">{{ $t->kendaraan->plat_nomor ?? '-' }}</span>
                                <div class="muted" style="font-size:8.5px; margin-top:1px;">{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</div>
                            </td>
                            <td>{{ $t->pengendara->nama_lengkap ?? 'Offline' }}</td>
                            <td class="muted">{{ $t->petugasKeluar->nama_lengkap ?? '-' }}</td>
                            <td class="nowrap">{{ optional($t->waktu_masuk)->format('d/m/y H:i') ?? '-' }}</td>
                            <td class="nowrap">{{ optional($t->waktu_keluar)->format('d/m/y H:i') ?? '-' }}</td>
                            <td class="text-right">{{ $t->durasi_jam !== null ? $t->durasi_jam.' jam' : '-' }}</td>
                            <td class="text-right font-semibold">
                                {{ $t->biaya_total !== null ? 'Rp '.number_format($t->biaya_total, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ $t->status_pembayaran === 'Sudah Bayar' ? 'lunas' : 'belum' }}">
                                    {{ $t->status_pembayaran }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ $t->status === 'masuk' ? 'masuk' : 'keluar' }}">
                                    {{ ucfirst($t->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center muted" style="padding:18px;">
                                Tidak ada transaksi parkir yang cocok dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if((isset($members) && count($members) > 0) || (isset($bookings) && count($bookings) > 0))
            <!-- Tabel Penerimaan Non-Tunai Online (Member & Booking) -->
            <div style="font-weight:700; font-size:10.5px; color:var(--ink); margin-top:14px; margin-bottom:4px;">
                <span>Daftar Penerimaan Non-Tunai Online Terverifikasi (Member Pass & Booking Inap):</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:28px" class="text-center">No</th>
                        <th style="width:75px">Layanan</th>
                        <th style="width:100px">Kode Trx</th>
                        <th style="width:110px">Plat / Jenis</th>
                        <th>Pengendara</th>
                        <th>Keterangan / Paket</th>
                        <th class="nowrap">Waktu Bayar</th>
                        <th class="text-right" style="width:80px">Nominal Lunas</th>
                        <th class="text-center" style="width:70px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $onlineIdx = 1; @endphp
                    @foreach($members as $m)
                        <tr>
                            <td class="text-center muted">{{ $onlineIdx++ }}</td>
                            <td><strong style="color:#059669;">Member</strong></td>
                            <td style="font-family: monospace;">{{ $m->kode_member }}</td>
                            <td>
                                <span class="plat-badge">{{ $m->kendaraan->plat_nomor ?? '-' }}</span>
                                <div class="muted" style="font-size:8.5px;">{{ $m->kendaraan->jenis_kendaraan ?? '-' }}</div>
                            </td>
                            <td>{{ $m->pengendara->nama_lengkap ?? '-' }}</td>
                            <td>{{ $m->paket->nama_paket ?? 'Member' }}</td>
                            <td class="nowrap">{{ $m->waktu_unggah_bukti ? \Carbon\Carbon::parse($m->waktu_unggah_bukti)->format('d/m/y H:i') : ($m->waktu_registrasi ? \Carbon\Carbon::parse($m->waktu_registrasi)->format('d/m/y H:i') : '-') }}</td>
                            <td class="text-right font-semibold" style="color:#059669;">Rp {{ number_format($m->biaya_langganan, 0, ',', '.') }}</td>
                            <td class="text-center"><span class="status-badge lunas">{{ $m->status_pembayaran }}</span></td>
                        </tr>
                    @endforeach
                    @foreach($bookings as $b)
                        <tr>
                            <td class="text-center muted">{{ $onlineIdx++ }}</td>
                            <td><strong style="color:#7c3aed;">Booking</strong></td>
                            <td style="font-family: monospace;">{{ $b->kode_booking }}</td>
                            <td>
                                <span class="plat-badge">{{ $b->kendaraan->plat_nomor ?? '-' }}</span>
                                <div class="muted" style="font-size:8.5px;">{{ $b->kendaraan->jenis_kendaraan ?? '-' }}</div>
                            </td>
                            <td>{{ $b->pengendara->nama_lengkap ?? '-' }}</td>
                            <td>Inap {{ \Carbon\Carbon::parse($b->waktu_mulai_inap)->format('d/m') }} - {{ \Carbon\Carbon::parse($b->waktu_rencana_keluar)->format('d/m/y') }}</td>
                            <td class="nowrap">{{ $b->waktu_unggah_bukti ? \Carbon\Carbon::parse($b->waktu_unggah_bukti)->format('d/m/y H:i') : ($b->waktu_booking ? \Carbon\Carbon::parse($b->waktu_booking)->format('d/m/y H:i') : '-') }}</td>
                            <td class="text-right font-semibold" style="color:#7c3aed;">Rp {{ number_format($b->biaya_inap_dibayar, 0, ',', '.') }}</td>
                            <td class="text-center"><span class="status-badge lunas">{{ $b->status_pembayaran }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </section>

        <!-- FOOTER & TANDA TANGAN -->
        <footer class="report-footer">
            <div class="footer-note">
                Catatan: Laporan rekapitulasi transaksi ini digenerate secara otomatis oleh sistem Parkirin. Data divalidasi langsung melalui transaksi karcis gate dan sistem pembayaran resmi tempat parkir.
            </div>

            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-role">Petugas / Operator Kasir</div>
                    <div class="muted" style="font-size:9px;">Mengetahui dan Memverifikasi</div>
                    <div class="signature-line">
                        ( .................................................. )<br>
                        <span class="muted" style="font-weight:normal; font-size:8.5px;">Petugas Bertugas</span>
                    </div>
                </div>

                <div class="signature-box">
                    <div class="signature-role">{{ $tenant->nama_tempat ?? 'Tempat Parkir' }}</div>
                    <div class="muted" style="font-size:9px;">{{ now()->locale('id')->translatedFormat('d F Y') }}</div>
                    <div class="signature-line">
                        {{ auth('web')->user()->nama_lengkap ?? 'Owner' }}<br>
                        <span class="muted" style="font-weight:normal; font-size:8.5px;">Pemilik Lahan / Penanggung Jawab</span>
                    </div>
                </div>
            </div>
        </footer>

    </main>

    @if($autoPrint)
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
    @endif

    <script>
        // Fitur Unduh PDF Langsung (Client-Side Vector/Raster A4 menggunakan jsPDF)
        async function downloadPdfDirect() {
            const btn = document.getElementById('btnDirectPdf');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>⏳</span> Memproses PDF...';
            btn.disabled = true;

            try {
                if (typeof window.jspdf === 'undefined' || typeof window.html2canvas === 'undefined') {
                    // Fallback langsung ke window.print jika pustaka JS belum siap
                    window.print();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }

                const sheet = document.getElementById('printableSheet');
                const canvas = await html2canvas(sheet, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff'
                });

                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = 210;
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

                let heightLeft = pdfHeight;
                let position = 0;
                const pageHeight = 297;

                pdf.addImage(imgData, 'JPEG', 0, position, pdfWidth, pdfHeight);
                heightLeft -= pageHeight;

                while (heightLeft > 0) {
                    position = heightLeft - pdfHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'JPEG', 0, position, pdfWidth, pdfHeight);
                    heightLeft -= pageHeight;
                }

                const safeTenant = "{{ \Illuminate\Support\Str::slug($tenant->nama_tempat ?? 'lahan') }}";
                const dateStr = "{{ now()->format('Ymd-His') }}";
                pdf.save(`rekap-transaksi-${safeTenant}-${dateStr}.pdf`);
            } catch (err) {
                console.error('Gagal generate PDF:', err);
                // Fallback otomatis ke dialog print sistem
                window.print();
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
