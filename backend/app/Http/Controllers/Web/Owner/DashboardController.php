<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;
        $today = now()->toDateString();

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
                'pengendara:id_pengendara,nama_lengkap,username',
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
                'pengendara:id_pengendara,nama_lengkap',
                'petugas:id_user,nama_lengkap',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->orderByDesc('id_transaksi')
            ->limit(8)
            ->get();

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
            'masuk' => $keys->map(fn($k) => (int) ($masuk[$k] ?? 0))->values(),
            'keluar' => $keys->map(fn($k) => (int) ($keluar[$k] ?? 0))->values(),
        ];

        return view('owner.dashboard', compact(
            'kendaraanAktif', 'totalMasukHariIni', 'totalKeluarHariIni', 'anomaliMenginap', 'recentTransaksi', 'chart'
        ));
    }
}
