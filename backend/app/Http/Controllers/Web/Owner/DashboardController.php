<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index()
    {
        $tenantId = $this->tenantId();
        $tenant = TempatParkir::findOrFail($tenantId);
        $today = now()->toDateString();

        // 1. Metrik Operasional Real-Time & Anti-Fraud
        $kendaraanAktif = Transaksi::where('id_tempat_parkir', $tenantId)
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->count();

        $totalMasukHariIni = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereDate('waktu_masuk', $today)
            ->count();

        $totalKeluarHariIni = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereDate('waktu_keluar', $today)
            ->count();

        $anomaliMenginap = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara',
                'tarif:id_tarif,tipe_tarif',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->where('waktu_masuk', '<=', now()->subDay())
            ->orderBy('waktu_masuk')
            ->limit(20)
            ->get();

        $recentTransaksi = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara',
                'petugasKeluar:id_user,nama_lengkap',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->orderByDesc('id_transaksi')
            ->limit(8)
            ->get();

        // 2. Flow 7 Hari Terakhir
        $start = now()->subDays(6)->startOfDay();
        $period = collect(CarbonPeriod::create($start, now()->startOfDay()));
        $labels = $period->map(fn(Carbon $d) => $d->translatedFormat('d M'))->values();
        $keys = $period->map(fn(Carbon $d) => $d->toDateString())->values();

        $masuk = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereDate('waktu_masuk', '>=', $start->toDateString())
            ->select(DB::raw('DATE(waktu_masuk) as d'), DB::raw('count(*) as c'))
            ->groupBy('d')->pluck('c', 'd');
        $keluar = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereNotNull('waktu_keluar')
            ->whereDate('waktu_keluar', '>=', $start->toDateString())
            ->select(DB::raw('DATE(waktu_keluar) as d'), DB::raw('count(*) as c'))
            ->groupBy('d')->pluck('c', 'd');

        $chart = [
            'labels' => $labels,
            'masuk'  => $keys->map(fn($k) => (int) ($masuk[$k] ?? 0))->values(),
            'keluar' => $keys->map(fn($k) => (int) ($keluar[$k] ?? 0))->values(),
        ];

        // 3. Analitik Multi-Tahun & YoY Dinamis
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $currentYear = (int) now()->year;
        
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearTrxExpr = $isSqlite ? "CAST(strftime('%Y', waktu_masuk) AS INTEGER)" : "YEAR(waktu_masuk)";
        $monthTrxExpr = $isSqlite ? "CAST(strftime('%m', waktu_masuk) AS INTEGER)" : "MONTH(waktu_masuk)";
        $revExpr = DB::raw('SUM(biaya_dasar + denda_overstay + denda_karcis_hilang) as omzet');

        $monthMemberExpr = $isSqlite 
            ? "CAST(strftime('%m', COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)) AS INTEGER)" 
            : "MONTH(COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai))";
        $yearMemberExpr = $isSqlite 
            ? "CAST(strftime('%Y', COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)) AS INTEGER)" 
            : "YEAR(COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai))";

        $monthBookingExpr = $isSqlite 
            ? "CAST(strftime('%m', COALESCE(waktu_unggah_bukti, waktu_booking)) AS INTEGER)" 
            : "MONTH(COALESCE(waktu_unggah_bukti, waktu_booking))";
        $yearBookingExpr = $isSqlite 
            ? "CAST(strftime('%Y', COALESCE(waktu_unggah_bukti, waktu_booking)) AS INTEGER)" 
            : "YEAR(COALESCE(waktu_unggah_bukti, waktu_booking))";

        // Deteksi tahun terlama dari database tenant ini
        $earliestTrx = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereNotNull('waktu_masuk')
            ->when($isSqlite,
                fn($q) => $q->selectRaw("MIN(CAST(strftime('%Y', waktu_masuk) AS INTEGER)) as min_yr"),
                fn($q) => $q->selectRaw("MIN(YEAR(waktu_masuk)) as min_yr")
            )->value('min_yr');

        $earliestMember = \App\Models\LanggananMember::where('id_tempat_parkir', $tenantId)
            ->when($isSqlite,
                fn($q) => $q->selectRaw("MIN(CAST(strftime('%Y', COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)) AS INTEGER)) as min_yr"),
                fn($q) => $q->selectRaw("MIN(YEAR(COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai))) as min_yr")
            )->value('min_yr');

        $earliestBooking = \App\Models\Booking::where('id_tempat_parkir', $tenantId)
            ->when($isSqlite,
                fn($q) => $q->selectRaw("MIN(CAST(strftime('%Y', COALESCE(waktu_unggah_bukti, waktu_booking)) AS INTEGER)) as min_yr"),
                fn($q) => $q->selectRaw("MIN(YEAR(COALESCE(waktu_unggah_bukti, waktu_booking))) as min_yr")
            )->value('min_yr');

        $validYears = array_filter(
            [(int) $earliestTrx, (int) $earliestMember, (int) $earliestBooking],
            fn($y) => $y >= 2020 && $y <= $currentYear
        );

        $startYear = !empty($validYears) ? min($validYears) : ($currentYear - 1);
        if ($startYear >= $currentYear) {
            $startYear = $currentYear - 1;
        }

        $availableYears = range($startYear, $currentYear);

        // Ambil data agregat per tahun dan per bulan sekaligus
        $trxData = Transaksi::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Sudah Bayar')
            ->whereRaw("{$yearTrxExpr} >= ?", [$startYear])
            ->select(DB::raw("{$yearTrxExpr} as tahun"), DB::raw("{$monthTrxExpr} as bulan"), $revExpr, DB::raw('COUNT(*) as volume'))
            ->groupBy('tahun', 'bulan')
            ->get();

        $memberData = \App\Models\LanggananMember::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->whereRaw("{$yearMemberExpr} >= ?", [$startYear])
            ->select(DB::raw("{$yearMemberExpr} as tahun"), DB::raw("{$monthMemberExpr} as bulan"), DB::raw('SUM(biaya_langganan) as omzet'), DB::raw('COUNT(*) as volume'))
            ->groupBy('tahun', 'bulan')
            ->get();

        $bookingData = \App\Models\Booking::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->whereRaw("{$yearBookingExpr} >= ?", [$startYear])
            ->select(DB::raw("{$yearBookingExpr} as tahun"), DB::raw("{$monthBookingExpr} as bulan"), DB::raw('SUM(biaya_inap_dibayar) as omzet'), DB::raw('COUNT(*) as volume'))
            ->groupBy('tahun', 'bulan')
            ->get();

        $monthlyData = [];
        foreach ($availableYears as $yr) {
            $monthlyData[$yr] = [
                'omzet' => array_fill(1, 12, 0.0),
                'volume' => array_fill(1, 12, 0),
            ];
        }

        foreach ($trxData as $r) {
            $yr = (int) $r->tahun;
            $bln = (int) $r->bulan;
            if (isset($monthlyData[$yr])) {
                $monthlyData[$yr]['omzet'][$bln] += (float) $r->omzet;
                $monthlyData[$yr]['volume'][$bln] += (int) $r->volume;
            }
        }

        foreach ($memberData as $r) {
            $yr = (int) $r->tahun;
            $bln = (int) $r->bulan;
            if (isset($monthlyData[$yr])) {
                $monthlyData[$yr]['omzet'][$bln] += (float) $r->omzet;
                $monthlyData[$yr]['volume'][$bln] += (int) $r->volume;
            }
        }

        foreach ($bookingData as $r) {
            $yr = (int) $r->tahun;
            $bln = (int) $r->bulan;
            if (isset($monthlyData[$yr])) {
                $monthlyData[$yr]['omzet'][$bln] += (float) $r->omzet;
                $monthlyData[$yr]['volume'][$bln] += (int) $r->volume;
            }
        }

        // Palet warna estetik untuk membedakan tahun
        $palette = [
            0 => ['bg' => 'rgba(13, 99, 165, 0.95)', 'border' => '#0D63A5'], // Tahun ini (Brand Primary)
            1 => ['bg' => 'rgba(100, 116, 139, 0.75)', 'border' => '#64748B'], // 1 tahun lalu (Slate)
            2 => ['bg' => 'rgba(245, 158, 11, 0.75)', 'border' => '#F59E0B'],  // 2 tahun lalu (Amber)
            3 => ['bg' => 'rgba(16, 185, 129, 0.75)', 'border' => '#10B981'],  // 3 tahun lalu (Emerald)
            4 => ['bg' => 'rgba(139, 92, 246, 0.75)', 'border' => '#8B5CF6'],  // 4 tahun lalu (Purple)
        ];

        $datasets = [];
        $totalYears = count($availableYears);

        foreach ($availableYears as $idx => $yr) {
            $offset = $totalYears - 1 - $idx;
            $color = $palette[$offset] ?? ['bg' => 'rgba(148, 163, 184, 0.75)', 'border' => '#94A3B8'];
            $isCurrent = ($yr === $currentYear);
            $suffix = $isCurrent ? ' (Tahun Ini)' : ($offset === 1 ? ' (Tahun Lalu)' : '');

            $datasets[] = [
                'year' => $yr,
                'label' => "Tahun {$yr}{$suffix}",
                'data' => array_values($monthlyData[$yr]['omzet']),
                'volume_data' => array_values($monthlyData[$yr]['volume']),
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'borderWidth' => 1,
                'borderRadius' => 6,
                'maxBarThickness' => max(12, min(24, intval(52 / max(1, $totalYears)))),
            ];
        }

        // 4. Perbandingan Arus Tahunan (Annual Comparison) & Analitik Google Currency Style
        $annualSummary = [];
        $yearAnalytics = [];
        $prevVol = null;
        $prevOmzet = null;

        foreach ($availableYears as $idx => $yr) {
            $omzets = array_values($monthlyData[$yr]['omzet']);
            $volumes = array_values($monthlyData[$yr]['volume']);
            $totOmzet = array_sum($omzets);
            $totVol = array_sum($volumes);

            $growthVol = null;
            $growthOmzet = null;
            if ($prevVol !== null && $prevVol > 0) {
                $growthVol = round((($totVol - $prevVol) / $prevVol) * 100, 1);
            }
            if ($prevOmzet !== null && $prevOmzet > 0) {
                $growthOmzet = round((($totOmzet - $prevOmzet) / $prevOmzet) * 100, 1);
            }

            $offset = $totalYears - 1 - $idx;
            $color = $palette[$offset] ?? ['bg' => 'rgba(148, 163, 184, 0.75)', 'border' => '#94A3B8'];

            $annualSummary[] = [
                'year' => $yr,
                'label' => (string) $yr,
                'volume' => $totVol,
                'omzet' => $totOmzet,
                'growth_volume' => $growthVol,
                'growth_omzet' => $growthOmzet,
                'is_current' => ($yr === $currentYear),
                'color' => $color['border'],
                'bgColor' => $color['bg'],
            ];

            // Metrik detail per tahun (High, Low, Rata-rata per bulan)
            $highVal = !empty($omzets) ? max($omzets) : 0;
            $highIdx = array_search($highVal, $omzets);
            $highMonth = $highIdx !== false ? $monthNames[$highIdx] : '-';

            $nonZeroOmzets = array_filter($omzets, fn($v) => $v > 0);
            $lowVal = !empty($nonZeroOmzets) ? min($nonZeroOmzets) : (!empty($omzets) ? min($omzets) : 0);
            $lowIdx = array_search($lowVal, $omzets);
            $lowMonth = $lowIdx !== false ? $monthNames[$lowIdx] : '-';

            $avgOmzet = round($totOmzet / 12, 0);

            $yearAnalytics[$yr] = [
                'year' => $yr,
                'total_omzet' => $totOmzet,
                'total_volume' => $totVol,
                'high_val' => $highVal,
                'high_month' => $highMonth,
                'low_val' => $lowVal,
                'low_month' => $lowMonth,
                'avg_omzet' => $avgOmzet,
                'growth_omzet' => $growthOmzet,
                'color' => $color['border'],
                'bgColor' => $color['bg'],
                'monthly_omzet' => $omzets,
                'monthly_volume' => $volumes,
            ];

            $prevVol = $totVol;
            $prevOmzet = $totOmzet;
        }

        // Tren Agregat Tahunan (Mode Per Tahun ala Google Currency)
        $annualLabels = array_map(fn($y) => "Thn " . $y, $availableYears);
        $annualOmzets = array_map(fn($y) => $yearAnalytics[$y]['total_omzet'], $availableYears);
        $annualVolumes = array_map(fn($y) => $yearAnalytics[$y]['total_volume'], $availableYears);

        $highAnnualVal = !empty($annualOmzets) ? max($annualOmzets) : 0;
        $highAnnualIdx = array_search($highAnnualVal, $annualOmzets);
        $highAnnualLabel = $highAnnualIdx !== false ? $annualLabels[$highAnnualIdx] : '-';

        $lowAnnualVal = !empty($annualOmzets) ? min($annualOmzets) : 0;
        $lowAnnualIdx = array_search($lowAnnualVal, $annualOmzets);
        $lowAnnualLabel = $lowAnnualIdx !== false ? $annualLabels[$lowAnnualIdx] : '-';

        $firstAnnual = $annualOmzets[0] ?? 0;
        $lastAnnual = end($annualOmzets) ?? 0;
        $overallAnnualGrowth = $firstAnnual > 0 ? round((($lastAnnual - $firstAnnual) / $firstAnnual) * 100, 1) : 0;

        $annualTrend = [
            'labels' => $annualLabels,
            'years' => $availableYears,
            'omzets' => $annualOmzets,
            'volumes' => $annualVolumes,
            'total_all_time' => array_sum($annualOmzets),
            'total_volume_all_time' => array_sum($annualVolumes),
            'high_val' => $highAnnualVal,
            'high_label' => $highAnnualLabel,
            'low_val' => $lowAnnualVal,
            'low_label' => $lowAnnualLabel,
            'growth' => $overallAnnualGrowth,
        ];

        // Backward compatibility untuk key 2025 vs 2026
        $omzet2025 = array_sum($monthlyData[2025]['omzet'] ?? []);
        $omzet2026 = array_sum($monthlyData[2026]['omzet'] ?? []);
        $vol2025 = array_sum($monthlyData[2025]['volume'] ?? []);
        $vol2026 = array_sum($monthlyData[2026]['volume'] ?? []);
        $growthOmzet = $omzet2025 > 0 ? round((($omzet2026 - $omzet2025) / $omzet2025) * 100, 1) : 0;
        $growthVol = $vol2025 > 0 ? round((($vol2026 - $vol2025) / $vol2025) * 100, 1) : 0;

        $yoyChart = [
            'labels' => $monthNames,
            'years' => $availableYears,
            'current_year' => $currentYear,
            'datasets' => $datasets,
            'year_analytics' => $yearAnalytics,
            'annual_trend' => $annualTrend,
            'annual_flow' => [
                'labels' => array_map(fn($a) => "Thn " . $a['year'], $annualSummary),
                'volumes' => array_map(fn($a) => $a['volume'], $annualSummary),
                'omzets' => array_map(fn($a) => $a['omzet'], $annualSummary),
                'colors' => array_map(fn($a) => $a['bgColor'], $annualSummary),
                'borderColors' => array_map(fn($a) => $a['color'], $annualSummary),
            ],
            // Legacy keys
            'omzet_2025' => array_values($monthlyData[2025]['omzet'] ?? array_fill(1, 12, 0)),
            'omzet_2026' => array_values($monthlyData[2026]['omzet'] ?? array_fill(1, 12, 0)),
            'volume_2025' => array_values($monthlyData[2025]['volume'] ?? array_fill(1, 12, 0)),
            'volume_2026' => array_values($monthlyData[2026]['volume'] ?? array_fill(1, 12, 0)),
        ];

        // Slot Turnover Rate = total kendaraan dilayani hari ini / total kapasitas
        $kapasitas = max(1, (int) $tenant->kapasitas_reguler);
        $slotTurnover = round($totalKeluarHariIni / $kapasitas, 2);

        $yoyMetrics = [
            'available_years' => $availableYears,
            'annual_summary' => $annualSummary,
            'start_year' => $startYear,
            'current_year' => $currentYear,
            'omzet_2025' => $omzet2025,
            'omzet_2026' => $omzet2026,
            'growth_omzet' => $growthOmzet,
            'vol_2025' => $vol2025,
            'vol_2026' => $vol2026,
            'growth_vol' => $growthVol,
            'slot_turnover' => $slotTurnover,
        ];

        return view('owner.dashboard', compact(
            'tenant',
            'kendaraanAktif',
            'totalMasukHariIni',
            'totalKeluarHariIni',
            'anomaliMenginap',
            'recentTransaksi',
            'chart',
            'yoyChart',
            'yoyMetrics'
        ));
    }
}
