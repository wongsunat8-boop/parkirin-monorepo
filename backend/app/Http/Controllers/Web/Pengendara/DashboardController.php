<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pengendara = auth('pengendara')->user();
        $pid = $pengendara->id_pengendara;

        $karcisAktif = Transaksi::with(['tempatParkir', 'kendaraan', 'tarif'])
            ->where('id_pengendara', $pid)
            ->where('status', 'masuk')
            ->orderByDesc('waktu_masuk')
            ->first();

        $kendaraan = Kendaraan::withCount('transaksi')
            ->where('id_pengendara', $pid)
            ->orderBy('plat_nomor')
            ->get();

        // Frekuensi parkir mingguan (8 minggu terakhir, berdasarkan waktu_masuk)
        $weeklyLabels = [];
        $weeklyCounts = [];
        $startWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(7);
        for ($i = 0; $i < 8; $i++) {
            $ws = $startWeek->copy()->addWeeks($i);
            $we = $ws->copy()->endOfWeek(Carbon::SUNDAY);
            $weeklyLabels[] = $ws->format('d M');
            $weeklyCounts[] = Transaksi::where('id_pengendara', $pid)
                ->whereBetween('waktu_masuk', [$ws, $we])->count();
        }

        // Frekuensi parkir bulanan (6 bulan terakhir)
        $monthlyLabels = [];
        $monthlyCounts = [];
        $startMonth = Carbon::now()->startOfMonth()->subMonths(5);
        for ($i = 0; $i < 6; $i++) {
            $ms = $startMonth->copy()->addMonths($i);
            $me = $ms->copy()->endOfMonth();
            $monthlyLabels[] = $ms->translatedFormat('M Y');
            $monthlyCounts[] = Transaksi::where('id_pengendara', $pid)
                ->whereBetween('waktu_masuk', [$ms, $me])->count();
        }

        $totalParkir = Transaksi::where('id_pengendara', $pid)->count();

        return view('pengendara.dashboard', compact(
            'karcisAktif', 'kendaraan', 'totalParkir',
            'weeklyLabels', 'weeklyCounts', 'monthlyLabels', 'monthlyCounts'
        ));
    }

    public function bayarCashless(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($transaksi->id_pengendara !== $pengendara->id_pengendara) {
            abort(403);
        }
        if ($transaksi->status_pembayaran === 'Sudah Bayar') {
            return back()->with('error', 'Transaksi sudah berstatus Sudah Bayar.');
        }
        $transaksi->update(['status_pembayaran' => 'Sudah Bayar']);
        return back()->with('success', 'Pembayaran Cashless/QRIS (simulasi) berhasil. Status: Sudah Bayar.');
    }
}
