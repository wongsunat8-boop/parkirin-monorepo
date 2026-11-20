<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\PaketLangganan;
use App\Models\Tarif;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;
        $tenant = Auth::guard('web')->user()->tempatParkir;

        $unverifiedCount = LanggananMember::where('id_tempat_parkir', $tenantId)
            ->whereNull('id_user_verifikasi')
            ->whereNotNull('bukti_bayar')
            ->count() +
            Booking::where('id_tempat_parkir', $tenantId)
            ->whereNull('id_user_verifikasi')
            ->whereNotNull('bukti_bayar')
            ->count();

        $stats = [
            'aktif' => Transaksi::where('id_tempat_parkir', $tenantId)->where('status','masuk')->whereNull('waktu_keluar')->count(),
            'tarif' => Tarif::where('id_tempat_parkir', $tenantId)->where('status_aktif', 1)->count(),
            'paket' => PaketLangganan::where('id_tempat_parkir', $tenantId)->where('status_aktif', 1)->count(),
            'petugas' => User::where('id_tempat_parkir', $tenantId)->where('role','petugas')->where('status_aktif',1)->count(),
            'kendaraan' => Kendaraan::whereHas('transaksi', fn($q) => $q->where('id_tempat_parkir', $tenantId))->distinct()->count('id_kendaraan'),
            'unverified' => $unverifiedCount,
        ];

        $start = now()->subDays(6)->startOfDay();
        $period = collect(CarbonPeriod::create($start, now()->startOfDay()));
        $labels = $period->map(fn(Carbon $d) => $d->translatedFormat('d M'))->values();
        $keys = $period->map(fn(Carbon $d) => $d->toDateString())->values();
        $masuk = Transaksi::where('id_tempat_parkir', $tenantId)->whereDate('waktu_masuk', '>=', $start->toDateString())
            ->select(DB::raw('DATE(waktu_masuk) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd');
        $keluar = Transaksi::where('id_tempat_parkir', $tenantId)->whereNotNull('waktu_keluar')->whereDate('waktu_keluar', '>=', $start->toDateString())
            ->select(DB::raw('DATE(waktu_keluar) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd');
        $chart = [
            'labels' => $labels,
            'masuk' => $keys->map(fn($k) => (int) ($masuk[$k] ?? 0))->values(),
            'keluar' => $keys->map(fn($k) => (int) ($keluar[$k] ?? 0))->values(),
        ];

        $kapasitas = (int) ($tenant?->kapasitas ?? 0);
        $terisi = (int) ($stats['aktif'] ?? 0);
        $chart['occupancy'] = ['terisi' => $terisi, 'sisa' => max(0, $kapasitas - $terisi), 'kapasitas' => $kapasitas];

        return view('admin.dashboard', compact('tenant','stats','chart','unverifiedCount'));
    }
}
