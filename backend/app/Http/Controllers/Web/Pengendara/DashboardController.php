<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pengendara = auth('pengendara')->user();
        $pid = $pengendara->id_pengendara;

        // Karcis parkir saat kendaraan berstatus 'masuk'
        $karcisAktif = $pengendara->transaksi()
            ->with(['tempatParkir', 'kendaraan', 'tarif'])
            ->where('status', 'masuk')
            ->orderByDesc('waktu_masuk')
            ->first();

        // Langganan Member Aktif
        $memberAktif = LanggananMember::with(['tempatParkir', 'paket', 'kendaraan'])
            ->where('id_pengendara', $pid)
            ->where('status_langganan', 'Aktif')
            ->where('status_pembayaran', 'Lunas')
            ->where('tanggal_mulai', '<=', now()->toDateString())
            ->get()
            ->first(function ($m) {
                if (!$m->paket) return false;
                $endDate = Carbon::parse($m->tanggal_mulai)->addDays($m->paket->durasi_hari);
                return $endDate->gte(today());
            });

        // Booking Inap Terjadwal / Aktif
        $bookingAktif = Booking::with(['tempatParkir', 'kendaraan'])
            ->where('id_pengendara', $pid)
            ->whereIn('status', ['Terjadwal', 'Aktif_Inap'])
            ->orderByDesc('id_booking')
            ->first();

        // Daftar Kendaraan
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
            $weeklyCounts[] = $pengendara->transaksi()
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
            $monthlyCounts[] = $pengendara->transaksi()
                ->whereBetween('waktu_masuk', [$ms, $me])->count();
        }

        $totalParkir = $pengendara->transaksi()->count();

        return view('pengendara.dashboard', compact(
            'karcisAktif', 'memberAktif', 'bookingAktif', 'kendaraan', 'totalParkir',
            'weeklyLabels', 'weeklyCounts', 'monthlyLabels', 'monthlyCounts'
        ));
    }

    public function bayarCashless(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($transaksi->kendaraan?->id_pengendara !== $pengendara->id_pengendara) {
            abort(403);
        }
        if ($transaksi->status_pembayaran === 'Sudah Bayar') {
            return back()->with('error', 'Transaksi sudah berstatus Sudah Bayar.');
        }
        $transaksi->update([
            'metode_pembayaran' => 'QRIS_Mitra',
            'status_pembayaran' => 'Sudah Bayar',
        ]);
        return back()->with('success', "Pembayaran transaksi #{$transaksi->id_transaksi} ({$transaksi->kode_karcis}) via QRIS resmi berhasil dikonfirmasi. Status: Sudah Bayar.");
    }
}
