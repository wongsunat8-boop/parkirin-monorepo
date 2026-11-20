<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\LanggananMember;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiController extends Controller
{
    private function tenantId(): int
    {
        return (int) (Auth::guard('web')->user()->id_tempat_parkir ?? 0);
    }

    private function baseQuery(Request $request)
    {
        return Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara',
                'petugasKeluar:id_user,nama_lengkap',
                'tarif:id_tarif,tipe_tarif,jenis_kendaraan',
            ])
            ->where('id_tempat_parkir', $this->tenantId())
            ->when($request->filled('dari'), fn($q) => $q->whereDate('waktu_masuk', '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate('waktu_masuk', '<=', $request->sampai))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('pembayaran'), fn($q) => $q->where('status_pembayaran', $request->pembayaran));
    }

    private function summary(Request $request): array
    {
        $tenantId = $this->tenantId();
        $base = $this->baseQuery($request);
        $expr = DB::raw('biaya_dasar + denda_overstay + denda_karcis_hilang');

        $karcisTotal = (clone $base)->count();
        $karcisPendapatan = (float) (clone $base)->where('status_pembayaran', 'Sudah Bayar')->sum($expr);
        $karcisTunai = (float) (clone $base)->where('status_pembayaran', 'Sudah Bayar')->where('metode_pembayaran', 'Tunai')->sum($expr);
        $karcisNonTunai = (float) (clone $base)->where('status_pembayaran', 'Sudah Bayar')->where('metode_pembayaran', '!=', 'Tunai')->sum($expr);
        $karcisBelumBayar = (float) (clone $base)->where('status_pembayaran', 'Belum Bayar')->sum($expr);

        // Member Pass Non-Tunai
        $memberQuery = LanggananMember::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $request->sampai));
        $memberTotal = (clone $memberQuery)->count();
        $memberPendapatan = (float) (clone $memberQuery)->sum('biaya_langganan');

        // Booking Inap Non-Tunai
        $bookingQuery = Booking::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $request->sampai));
        $bookingTotal = (clone $bookingQuery)->count();
        $bookingPendapatan = (float) (clone $bookingQuery)->sum('biaya_inap_dibayar');

        $totalNonTunai = $karcisNonTunai + $memberPendapatan + $bookingPendapatan;
        $grandPendapatan = $karcisTunai + $totalNonTunai;

        return [
            'total' => $karcisTotal + $memberTotal + $bookingTotal,
            'karcis_total' => $karcisTotal,
            'member_total' => $memberTotal,
            'booking_total' => $bookingTotal,
            'pendapatan' => $grandPendapatan,
            'pendapatan_karcis' => $karcisPendapatan,
            'pendapatan_member' => $memberPendapatan,
            'pendapatan_booking' => $bookingPendapatan,
            'tunai' => $karcisTunai,
            'total_non_tunai' => $totalNonTunai,
            'non_tunai_karcis' => $karcisNonTunai,
            'non_tunai_member' => $memberPendapatan,
            'non_tunai_booking' => $bookingPendapatan,
            'belum_bayar' => $karcisBelumBayar,
        ];
    }

    public function getYoySummary(int $tenantId): array
    {
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthFullNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExpr = $isSqlite ? "CAST(strftime('%m', waktu_masuk) AS INTEGER)" : "MONTH(waktu_masuk)";
        $revExpr = DB::raw('SUM(biaya_dasar + denda_overstay + denda_karcis_hilang) as omzet');
        $volExpr = DB::raw('COUNT(*) as volume');

        $data2025 = Transaksi::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Sudah Bayar')
            ->whereYear('waktu_masuk', 2025)
            ->select(DB::raw("{$monthExpr} as bulan"), $revExpr, $volExpr)
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $data2026 = Transaksi::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Sudah Bayar')
            ->whereYear('waktu_masuk', 2026)
            ->select(DB::raw("{$monthExpr} as bulan"), $revExpr, $volExpr)
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Member Pass Non-Tunai
        $monthMemberExpr = $isSqlite 
            ? "CAST(strftime('%m', COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)) AS INTEGER)" 
            : "MONTH(COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai))";
        $yearMemberExpr = $isSqlite 
            ? "CAST(strftime('%Y', COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)) AS INTEGER)" 
            : "YEAR(COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai))";

        $member2025 = LanggananMember::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->whereRaw("{$yearMemberExpr} = 2025")
            ->select(DB::raw("{$monthMemberExpr} as bulan"), DB::raw('SUM(biaya_langganan) as omzet'), DB::raw('COUNT(*) as volume'))
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $member2026 = LanggananMember::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->whereRaw("{$yearMemberExpr} = 2026")
            ->select(DB::raw("{$monthMemberExpr} as bulan"), DB::raw('SUM(biaya_langganan) as omzet'), DB::raw('COUNT(*) as volume'))
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Booking Inap Non-Tunai
        $monthBookingExpr = $isSqlite 
            ? "CAST(strftime('%m', COALESCE(waktu_unggah_bukti, waktu_booking)) AS INTEGER)" 
            : "MONTH(COALESCE(waktu_unggah_bukti, waktu_booking))";
        $yearBookingExpr = $isSqlite 
            ? "CAST(strftime('%Y', COALESCE(waktu_unggah_bukti, waktu_booking)) AS INTEGER)" 
            : "YEAR(COALESCE(waktu_unggah_bukti, waktu_booking))";

        $booking2025 = Booking::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->whereRaw("{$yearBookingExpr} = 2025")
            ->select(DB::raw("{$monthBookingExpr} as bulan"), DB::raw('SUM(biaya_inap_dibayar) as omzet'), DB::raw('COUNT(*) as volume'))
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $booking2026 = Booking::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->whereRaw("{$yearBookingExpr} = 2026")
            ->select(DB::raw("{$monthBookingExpr} as bulan"), DB::raw('SUM(biaya_inap_dibayar) as omzet'), DB::raw('COUNT(*) as volume'))
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $yoy = [
            'labels' => $monthNames,
            'full_months' => $monthFullNames,
            'omzet_2025' => [],
            'omzet_2026' => [],
            'vol_2025' => [],
            'vol_2026' => [],
            'total_omzet_2025' => 0.0,
            'total_omzet_2026' => 0.0,
            'total_vol_2025' => 0,
            'total_vol_2026' => 0,
            'peak_omzet_2026' => 0.0,
            'peak_month_2026' => '-',
        ];

        for ($m = 1; $m <= 12; $m++) {
            $o25 = (float) ($data2025[$m]->omzet ?? 0) + (float) ($member2025[$m]->omzet ?? 0) + (float) ($booking2025[$m]->omzet ?? 0);
            $o26 = (float) ($data2026[$m]->omzet ?? 0) + (float) ($member2026[$m]->omzet ?? 0) + (float) ($booking2026[$m]->omzet ?? 0);
            $v25 = (int) ($data2025[$m]->volume ?? 0) + (int) ($member2025[$m]->volume ?? 0) + (int) ($booking2025[$m]->volume ?? 0);
            $v26 = (int) ($data2026[$m]->volume ?? 0) + (int) ($member2026[$m]->volume ?? 0) + (int) ($booking2026[$m]->volume ?? 0);

            $yoy['omzet_2025'][] = $o25;
            $yoy['omzet_2026'][] = $o26;
            $yoy['vol_2025'][] = $v25;
            $yoy['vol_2026'][] = $v26;

            $yoy['total_omzet_2025'] += $o25;
            $yoy['total_omzet_2026'] += $o26;
            $yoy['total_vol_2025'] += $v25;
            $yoy['total_vol_2026'] += $v26;

            if ($o26 > $yoy['peak_omzet_2026']) {
                $yoy['peak_omzet_2026'] = $o26;
                $yoy['peak_month_2026'] = $monthFullNames[$m - 1];
            }
        }

        $yoy['growth_omzet'] = $yoy['total_omzet_2025'] > 0
            ? round((($yoy['total_omzet_2026'] - $yoy['total_omzet_2025']) / $yoy['total_omzet_2025']) * 100, 1)
            : 0;

        $yoy['growth_vol'] = $yoy['total_vol_2025'] > 0
            ? round((($yoy['total_vol_2026'] - $yoy['total_vol_2025']) / $yoy['total_vol_2025']) * 100, 1)
            : 0;

        $yoy['max_omzet'] = max(array_merge($yoy['omzet_2025'], $yoy['omzet_2026'], [1]));

        $yoy['kesimpulan'] = sprintf(
            "Pemasukan tahun 2026 mencapai Rp %s (%s%s%% dibandingkan tahun 2025 sebesar Rp %s). Total lalu lintas tercatat %s kendaraan (%s%s%%). Puncak pemasukan tahun berjalan tercatat pada bulan %s sebesar Rp %s.",
            number_format($yoy['total_omzet_2026'], 0, ',', '.'),
            $yoy['growth_omzet'] >= 0 ? '+' : '',
            $yoy['growth_omzet'],
            number_format($yoy['total_omzet_2025'], 0, ',', '.'),
            number_format($yoy['total_vol_2026'], 0, ',', '.'),
            $yoy['growth_vol'] >= 0 ? '+' : '',
            $yoy['growth_vol'],
            $yoy['peak_month_2026'],
            number_format($yoy['peak_omzet_2026'], 0, ',', '.')
        );

        return $yoy;
    }

    public function getSevenDaysSummary(int $tenantId): array
    {
        $now = now();
        $start = $now->copy()->subDays(6)->startOfDay();

        $masukMap = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereDate('waktu_masuk', '>=', $start->toDateString())
            ->select(DB::raw('DATE(waktu_masuk) as d'), DB::raw('count(*) as c'))
            ->groupBy('d')
            ->pluck('c', 'd');

        $keluarMap = Transaksi::where('id_tempat_parkir', $tenantId)
            ->whereNotNull('waktu_keluar')
            ->whereDate('waktu_keluar', '>=', $start->toDateString())
            ->select(DB::raw('DATE(waktu_keluar) as d'), DB::raw('count(*) as c'))
            ->groupBy('d')
            ->pluck('c', 'd');

        $sevenDays = [
            'days' => [],
            'total_masuk' => 0,
            'total_keluar' => 0,
            'total_semua' => 0,
            'busiest_day' => '-',
            'busiest_count' => 0,
        ];

        for ($i = 0; $i < 7; $i++) {
            $cur = $start->copy()->addDays($i);
            $dStr = $cur->toDateString();
            $inCount = (int) ($masukMap[$dStr] ?? 0);
            $outCount = (int) ($keluarMap[$dStr] ?? 0);
            $sum = $inCount + $outCount;

            $sevenDays['days'][] = [
                'date' => $dStr,
                'label' => $cur->copy()->locale('id')->translatedFormat('D, d M'),
                'short_label' => $cur->copy()->locale('id')->translatedFormat('d M'),
                'full_label' => $cur->copy()->locale('id')->translatedFormat('l, d F Y'),
                'masuk' => $inCount,
                'keluar' => $outCount,
                'total' => $sum,
            ];

            $sevenDays['total_masuk'] += $inCount;
            $sevenDays['total_keluar'] += $outCount;

            if ($sum > $sevenDays['busiest_count']) {
                $sevenDays['busiest_count'] = $sum;
                $sevenDays['busiest_day'] = $cur->copy()->locale('id')->translatedFormat('l, d F Y');
            }
        }

        $sevenDays['total_semua'] = $sevenDays['total_masuk'] + $sevenDays['total_keluar'];
        $sevenDays['rata_rata_harian'] = round($sevenDays['total_semua'] / 7, 1);

        $counts = [];
        foreach ($sevenDays['days'] as $day) {
            $counts[] = $day['masuk'];
            $counts[] = $day['keluar'];
        }
        $sevenDays['max_count'] = max(array_merge($counts, [1]));

        $sevenDays['kesimpulan'] = sprintf(
            "Aktivitas 7 hari terakhir melayani %s kendaraan masuk dan %s kendaraan keluar (rata-rata %s kendaraan/hari). Arus lalu lintas tertinggi terjadi pada hari %s dengan total %s kendaraan.",
            number_format($sevenDays['total_masuk'], 0, ',', '.'),
            number_format($sevenDays['total_keluar'], 0, ',', '.'),
            number_format($sevenDays['rata_rata_harian'], 1, ',', '.'),
            $sevenDays['busiest_day'],
            number_format($sevenDays['busiest_count'], 0, ',', '.')
        );

        return $sevenDays;
    }

    public function getFilterSummary(Request $request, $baseQuery): array
    {
        $tenantId = $this->tenantId();
        $base = clone $baseQuery;
        $expr = DB::raw('biaya_dasar + denda_overstay + denda_karcis_hilang');

        $karcisTotal = (clone $base)->count();
        $karcisLunasCount = (clone $base)->where('status_pembayaran', 'Sudah Bayar')->count();
        $karcisPendapatan = (float) (clone $base)->where('status_pembayaran', 'Sudah Bayar')->sum($expr);
        $karcisTunai = (float) (clone $base)->where('status_pembayaran', 'Sudah Bayar')->where('metode_pembayaran', 'Tunai')->sum($expr);
        $karcisNonTunai = (float) (clone $base)->where('status_pembayaran', 'Sudah Bayar')->where('metode_pembayaran', '!=', 'Tunai')->sum($expr);
        $belumCount = (clone $base)->where('status_pembayaran', 'Belum Bayar')->count();
        $belumBayar = (float) (clone $base)->where('status_pembayaran', 'Belum Bayar')->sum($expr);

        // Member Pass Non-Tunai
        $memberLunasQuery = LanggananMember::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $request->sampai));
        $memberLunasCount = (clone $memberLunasQuery)->count();
        $memberLunasNominal = (float) (clone $memberLunasQuery)->sum('biaya_langganan');

        // Booking Inap Non-Tunai
        $bookingLunasQuery = Booking::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $request->sampai));
        $bookingLunasCount = (clone $bookingLunasQuery)->count();
        $bookingLunasNominal = (float) (clone $bookingLunasQuery)->sum('biaya_inap_dibayar');

        $totalNonTunai = $karcisNonTunai + $memberLunasNominal + $bookingLunasNominal;
        $totalLunas = $karcisTunai + $totalNonTunai;
        $totalTrx = $karcisTotal + $memberLunasCount + $bookingLunasCount;
        $totalLunasCount = $karcisLunasCount + $memberLunasCount + $bookingLunasCount;

        $rataRata = $totalTrx > 0 ? round(($totalLunas + $belumBayar) / $totalTrx) : 0;
        $persenLunas = $totalTrx > 0 ? round(($totalLunasCount / $totalTrx) * 100, 1) : 0;

        $periodeStr = 'Semua Periode Riwayat';
        if ($request->filled('dari') && $request->filled('sampai')) {
            $periodeStr = Carbon::parse($request->dari)->locale('id')->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($request->sampai)->locale('id')->translatedFormat('d M Y');
        } elseif ($request->filled('dari')) {
            $periodeStr = 'Mulai ' . Carbon::parse($request->dari)->locale('id')->translatedFormat('d M Y');
        } elseif ($request->filled('sampai')) {
            $periodeStr = 'Hingga ' . Carbon::parse($request->sampai)->locale('id')->translatedFormat('d M Y');
        }

        $kesimpulan = sprintf(
            "Laporan transaksi mencatat total %s transaksi (%s). Sebanyak %s transaksi (%s%%) berstatus Lunas dengan total penerimaan Rp %s (Tunai Gerbang: Rp %s, Non-Tunai Total: Rp %s [Pos Kasir: Rp %s, Member: Rp %s, Booking: Rp %s]), sementara %s transaksi berstatus Belum Bayar senilai Rp %s. Rata-rata biaya parkir tercatat Rp %s per kendaraan.",
            number_format($totalTrx, 0, ',', '.'),
            $periodeStr,
            number_format($totalLunasCount, 0, ',', '.'),
            $persenLunas,
            number_format($totalLunas, 0, ',', '.'),
            number_format($karcisTunai, 0, ',', '.'),
            number_format($totalNonTunai, 0, ',', '.'),
            number_format($karcisNonTunai, 0, ',', '.'),
            number_format($memberLunasNominal, 0, ',', '.'),
            number_format($bookingLunasNominal, 0, ',', '.'),
            number_format($belumCount, 0, ',', '.'),
            number_format($belumBayar, 0, ',', '.'),
            number_format($rataRata, 0, ',', '.')
        );

        return [
            'total' => $totalTrx,
            'karcis_total' => $karcisTotal,
            'lunas_count' => $totalLunasCount,
            'pendapatan' => $totalLunas,
            'pendapatan_karcis' => $karcisPendapatan,
            'tunai' => $karcisTunai,
            'total_non_tunai' => $totalNonTunai,
            'non_tunai_kasir' => $karcisNonTunai,
            'non_tunai_member' => $memberLunasNominal,
            'non_tunai_booking' => $bookingLunasNominal,
            'belum_count' => $belumCount,
            'belum_bayar' => $belumBayar,
            'rata_rata' => $rataRata,
            'persen_lunas' => $persenLunas,
            'periode_str' => $periodeStr,
            'kesimpulan' => $kesimpulan,
        ];
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $tab = $request->query('tab', 'karcis');
        $summary = $this->summary($request);

        $transaksis = null;
        $members = null;
        $bookings = null;

        if ($tab === 'member') {
            $members = LanggananMember::with(['pengendara', 'kendaraan', 'paket', 'userVerifikasi'])
                ->where('id_tempat_parkir', $tenantId)
                ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $request->dari))
                ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $request->sampai))
                ->when($request->filled('pembayaran'), fn($q) => $q->where('status_pembayaran', $request->pembayaran === 'Sudah Bayar' ? 'Lunas' : $request->pembayaran))
                ->orderByDesc('id_langganan')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'booking') {
            $bookings = Booking::with(['pengendara', 'kendaraan', 'userVerifikasi'])
                ->where('id_tempat_parkir', $tenantId)
                ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $request->dari))
                ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $request->sampai))
                ->when($request->filled('pembayaran'), fn($q) => $q->where('status_pembayaran', $request->pembayaran === 'Sudah Bayar' ? 'Lunas' : $request->pembayaran))
                ->orderByDesc('id_booking')
                ->paginate(15)
                ->withQueryString();
        } else {
            $tab = 'karcis';
            $transaksis = $this->baseQuery($request)
                ->orderByDesc('id_transaksi')
                ->cursorPaginate(15)
                ->withQueryString();
        }

        return view('owner.transaksi.index', compact('tab', 'transaksis', 'members', 'bookings', 'summary'));
    }

    public function print(Request $request)
    {
        $tenantId = $this->tenantId();
        $user = Auth::guard('web')->user();
        $tenant = $user?->tempatParkir;

        $yoy = $this->getYoySummary($tenantId);
        $sevenDays = $this->getSevenDaysSummary($tenantId);

        $base = $this->baseQuery($request);
        $filterSummary = $this->getFilterSummary($request, $base);
        $summary = [
            'total' => $filterSummary['total'],
            'pendapatan' => $filterSummary['pendapatan'],
            'total_non_tunai' => $filterSummary['total_non_tunai'],
            'tunai' => $filterSummary['tunai'],
            'belum_bayar' => $filterSummary['belum_bayar'],
        ];

        $filters = [
            'dari' => $request->input('dari'),
            'sampai' => $request->input('sampai'),
            'status' => $request->input('status'),
            'pembayaran' => $request->input('pembayaran'),
        ];

        // Ambil data transaksi yang difilter (dibatasi 500 teratas agar layout cetak A4 tetap ringan dan rapi)
        $transaksis = (clone $base)->orderByDesc('id_transaksi')->limit(500)->get();

        $members = LanggananMember::with(['pengendara', 'kendaraan', 'paket', 'userVerifikasi'])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $request->sampai))
            ->orderByDesc('id_langganan')
            ->limit(100)
            ->get();

        $bookings = Booking::with(['pengendara', 'kendaraan', 'userVerifikasi'])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $request->sampai))
            ->orderByDesc('id_booking')
            ->limit(100)
            ->get();

        $autoPrint = $request->boolean('auto', false);

        return view('owner.transaksi.print', compact(
            'transaksis',
            'members',
            'bookings',
            'summary',
            'filterSummary',
            'filters',
            'tenant',
            'yoy',
            'sevenDays',
            'autoPrint'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $tenantId = $this->tenantId();
        $tenant = Auth::guard('web')->user()?->tempatParkir;
        $tenantName = $tenant->nama_tempat ?? 'Tenant';
        $filename = 'rekap-transaksi-' . (Str::slug($tenantName) ?: 'lahan') . '-' . now()->format('Ymd-His') . '.csv';

        $yoy = $this->getYoySummary($tenantId);
        $sevenDays = $this->getSevenDaysSummary($tenantId);
        $baseQuery = $this->baseQuery($request);
        $filterSummary = $this->getFilterSummary($request, $baseQuery);

        $filters = [
            'dari' => $request->input('dari', '-'),
            'sampai' => $request->input('sampai', '-'),
            'status' => $request->input('status', 'Semua'),
            'pembayaran' => $request->input('pembayaran', 'Semua'),
        ];

        $rawUserName = Auth::guard('web')->user()?->nama_lengkap ?? 'Owner';
        $userName = str_contains(strtolower($rawUserName), 'owner') ? $rawUserName : $rawUserName . ' (Owner)';
        $query = (clone $baseQuery)->orderBy('id_transaksi');

        return response()->streamDownload(function () use ($tenant, $tenantId, $userName, $yoy, $sevenDays, $filterSummary, $filters, $query, $request) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM untuk kompatibilitas sempurna Microsoft Excel Windows
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // KOP & IDENTITAS LAPORAN
            fputcsv($out, ['PARKIRIN - SISTEM MANAJEMEN PARKIR DIGITAL']);
            fputcsv($out, ['LAPORAN LENGKAP REKAPITULASI TRANSAKSI PARKIR (STANDAR A4)']);
            fputcsv($out, ['Tempat Parkir', $tenant->nama_tempat ?? '-']);
            fputcsv($out, ['ID Lokasi Lahan', '#' . ($tenant->id_tempat_parkir ?? '-')]);
            fputcsv($out, ['Kapasitas Operasional', ($tenant->kapasitas_reguler ?? 0) . ' Reguler / ' . ($tenant->kuota_inap ?? 0) . ' Menginap']);
            fputcsv($out, ['Waktu Ekspor Dokumen', now()->locale('id')->translatedFormat('d F Y, H:i') . ' WIB']);
            fputcsv($out, ['Diekspor Oleh', $userName . ' (Owner)']);
            fputcsv($out, []);

            // BAGIAN 1: RANGKUMAN TRANSAKSI PERBANDINGAN TAHUNAN (YOY)
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['BAGIAN 1: RANGKUMAN TRANSAKSI PERBANDINGAN TAHUNAN (2025 vs 2026)']);
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['Bulan', 'Pemasukan 2025 (Rp)', 'Pemasukan 2026 (Rp)', 'Lalu Lintas 2025 (Kendaraan)', 'Lalu Lintas 2026 (Kendaraan)']);
            for ($i = 0; $i < 12; $i++) {
                fputcsv($out, [
                    $yoy['full_months'][$i],
                    $yoy['omzet_2025'][$i],
                    $yoy['omzet_2026'][$i],
                    $yoy['vol_2025'][$i],
                    $yoy['vol_2026'][$i],
                ]);
            }
            fputcsv($out, [
                'TOTAL TAHUNAN',
                $yoy['total_omzet_2025'],
                $yoy['total_omzet_2026'],
                $yoy['total_vol_2025'],
                $yoy['total_vol_2026'],
            ]);
            fputcsv($out, ['Pertumbuhan Pemasukan (%)', ($yoy['growth_omzet'] >= 0 ? '+' : '') . $yoy['growth_omzet'] . '%']);
            fputcsv($out, ['Pertumbuhan Lalu Lintas (%)', ($yoy['growth_vol'] >= 0 ? '+' : '') . $yoy['growth_vol'] . '%']);
            fputcsv($out, ['Bulan Puncak Penerimaan 2026', $yoy['peak_month_2026'] . ' (Rp ' . number_format($yoy['peak_omzet_2026'], 0, ',', '.') . ')']);
            fputcsv($out, ['Kesimpulan Analisis Tahunan', $yoy['kesimpulan']]);
            fputcsv($out, []);

            // BAGIAN 2: RANGKUMAN TRANSAKSI 7 HARI TERAKHIR
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['BAGIAN 2: RANGKUMAN TRANSAKSI 7 HARI TERAKHIR']);
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['Tanggal', 'Hari', 'Kendaraan Masuk', 'Kendaraan Keluar', 'Total Lalu Lintas']);
            foreach ($sevenDays['days'] as $d) {
                fputcsv($out, [
                    $d['date'],
                    $d['full_label'],
                    $d['masuk'],
                    $d['keluar'],
                    $d['total'],
                ]);
            }
            fputcsv($out, ['TOTAL 7 HARI', '-', $sevenDays['total_masuk'], $sevenDays['total_keluar'], $sevenDays['total_semua']]);
            fputcsv($out, ['Rata-rata Harian', $sevenDays['rata_rata_harian'] . ' kendaraan/hari']);
            fputcsv($out, ['Hari Paling Sibuk', $sevenDays['busiest_day'] . ' (' . number_format($sevenDays['busiest_count'], 0, ',', '.') . ' kendaraan)']);
            fputcsv($out, ['Kesimpulan Analisis 7 Hari', $sevenDays['kesimpulan']]);
            fputcsv($out, []);

            // BAGIAN 3: RANGKUMAN TRANSAKSI TERPILIH (FILTER)
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['BAGIAN 3: RANGKUMAN TRANSAKSI TERPILIH (SESUAI FILTER AKTIF)']);
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['Filter Rentang Tanggal', ($filters['dari'] ?: 'Awal') . ' s/d ' . ($filters['sampai'] ?: 'Sekarang')]);
            fputcsv($out, ['Filter Status Kendaraan', $filters['status'] ?: 'Semua']);
            fputcsv($out, ['Filter Status Pembayaran', $filters['pembayaran'] ?: 'Semua']);
            fputcsv($out, ['Total Transaksi Sesuai Filter', $filterSummary['total']]);
            fputcsv($out, ['Total Transaksi Lunas (Sudah Bayar)', $filterSummary['lunas_count'] . ' transaksi (' . $filterSummary['persen_lunas'] . '%)']);
            fputcsv($out, ['Total Penerimaan Lunas (Rp)', $filterSummary['pendapatan']]);
            fputcsv($out, ['Penerimaan Tunai Pos Kasir (Rp)', $filterSummary['tunai']]);
            fputcsv($out, ['Penerimaan Non-Tunai Total (Rp)', $filterSummary['total_non_tunai']]);
            fputcsv($out, ['- Non-Tunai Pos Kasir (Rp)', $filterSummary['non_tunai_kasir']]);
            fputcsv($out, ['- Non-Tunai Member Pass (Rp)', $filterSummary['non_tunai_member']]);
            fputcsv($out, ['- Non-Tunai Booking Inap (Rp)', $filterSummary['non_tunai_booking']]);
            fputcsv($out, ['Total Transaksi Belum Bayar', $filterSummary['belum_count'] . ' transaksi']);
            fputcsv($out, ['Nilai Tagihan Belum Bayar (Rp)', $filterSummary['belum_bayar']]);
            fputcsv($out, ['Rata-rata Biaya Parkir (Rp)', $filterSummary['rata_rata']]);
            fputcsv($out, ['Kesimpulan Data Terfilter', $filterSummary['kesimpulan']]);
            fputcsv($out, []);

            // DAFTAR DETAIL TRANSAKSI
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['DAFTAR DETAIL RIWAYAT TRANSAKSI SESUAI FILTER']);
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, [
                'No',
                'ID Transaksi',
                'Plat Nomor',
                'Jenis Kendaraan',
                'Nama Pengendara',
                'Petugas Keluar',
                'Waktu Masuk',
                'Waktu Keluar',
                'Durasi (Jam)',
                'Biaya Total (Rp)',
                'Metode Pembayaran',
                'Status Pembayaran',
                'Status Parkir'
            ]);

            $rowNumber = 1;
            $query->chunkById(500, function ($rows) use ($out, &$rowNumber) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $rowNumber++,
                        $t->id_transaksi,
                        $t->kendaraan->plat_nomor ?? '-',
                        $t->kendaraan->jenis_kendaraan ?? '-',
                        $t->pengendara->nama_lengkap ?? 'Offline',
                        $t->petugasKeluar->nama_lengkap ?? '-',
                        optional($t->waktu_masuk)->format('Y-m-d H:i'),
                        optional($t->waktu_keluar)->format('Y-m-d H:i') ?? '-',
                        $t->durasi_jam,
                        $t->biaya_total,
                        $t->metode_pembayaran ?? '-',
                        $t->status_pembayaran,
                        $t->status,
                    ]);
                }
            }, 'id_transaksi');

            // BAGIAN 4: REKAP PENERIMAAN NON-TUNAI ONLINE (MEMBER & BOOKING INAP)
            fputcsv($out, []);
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, ['BAGIAN 4: REKAP PENERIMAAN NON-TUNAI ONLINE TERVERIFIKASI (MEMBER PASS & BOOKING INAP)']);
            fputcsv($out, ['========================================================================================']);
            fputcsv($out, [
                'No',
                'Layanan',
                'Kode Transaksi',
                'Plat Nomor',
                'Jenis Kendaraan',
                'Pengendara',
                'Waktu Transaksi',
                'Keterangan / Paket',
                'Nominal (Rp)',
                'Status Pembayaran'
            ]);

            $onlineRow = 1;
            $memberExports = LanggananMember::with(['pengendara', 'kendaraan', 'paket'])
                ->where('id_tempat_parkir', $tenantId)
                ->where('status_pembayaran', 'Lunas')
                ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $request->dari))
                ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $request->sampai))
                ->orderByDesc('id_langganan')
                ->get();

            foreach ($memberExports as $m) {
                fputcsv($out, [
                    $onlineRow++,
                    'Member Pass',
                    $m->kode_member,
                    $m->kendaraan->plat_nomor ?? '-',
                    $m->kendaraan->jenis_kendaraan ?? '-',
                    $m->pengendara->nama_lengkap ?? '-',
                    $m->waktu_unggah_bukti ?? $m->waktu_registrasi ?? $m->tanggal_mulai,
                    $m->paket->nama_paket ?? 'Member',
                    $m->biaya_langganan,
                    $m->status_pembayaran,
                ]);
            }

            $bookingExports = Booking::with(['pengendara', 'kendaraan'])
                ->where('id_tempat_parkir', $tenantId)
                ->where('status_pembayaran', 'Lunas')
                ->when($request->filled('dari'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $request->dari))
                ->when($request->filled('sampai'), fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $request->sampai))
                ->orderByDesc('id_booking')
                ->get();

            foreach ($bookingExports as $b) {
                fputcsv($out, [
                    $onlineRow++,
                    'Booking Inap',
                    $b->kode_booking,
                    $b->kendaraan->plat_nomor ?? '-',
                    $b->kendaraan->jenis_kendaraan ?? '-',
                    $b->pengendara->nama_lengkap ?? '-',
                    $b->waktu_unggah_bukti ?? $b->waktu_booking,
                    'Inap ' . $b->waktu_mulai_inap . ' s/d ' . $b->waktu_rencana_keluar,
                    $b->biaya_inap_dibayar,
                    $b->status_pembayaran,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Catatan: Dokumen ini diterbitkan secara otomatis oleh Parkirin. Seluruh data diverifikasi oleh sistem.']);
            fputcsv($out, ['Penanggung Jawab / Pemilik Lahan', $tenant->nama_tempat ?? 'Tenant', now()->locale('id')->translatedFormat('d F Y')]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
