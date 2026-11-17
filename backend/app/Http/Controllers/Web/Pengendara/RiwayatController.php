<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function index(Request $request): View
    {
        $pengendara = auth('pengendara')->user();
        $query = Transaksi::with(['tempatParkir', 'kendaraan', 'tarif'])
            ->where('id_pengendara', $pengendara->id_pengendara);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($bayar = $request->get('status_pembayaran')) {
            $query->where('status_pembayaran', $bayar);
        }

        $riwayat = $query->orderByDesc('id_transaksi')->cursorPaginate(15)->withQueryString();
        return view('pengendara.riwayat.index', compact('riwayat'));
    }

    public function bayarCashless(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($transaksi->id_pengendara !== $pengendara->id_pengendara) abort(403);
        if ($transaksi->status_pembayaran === 'Sudah Bayar') {
            return back()->with('error', 'Transaksi sudah berstatus Sudah Bayar.');
        }
        $transaksi->update(['status_pembayaran' => 'Sudah Bayar']);
        return back()->with('success', 'Simulasi pembayaran Cashless/QRIS berhasil untuk transaksi ini.');
    }

    public function struk(Request $request, Transaksi $transaksi): View
    {
        $pengendara = auth('pengendara')->user();
        if ($transaksi->id_pengendara !== $pengendara->id_pengendara) abort(403);
        $transaksi->load(['tempatParkir', 'kendaraan', 'tarif', 'booking']);
        return view('pengendara.riwayat.struk', compact('transaksi'));
    }
}
