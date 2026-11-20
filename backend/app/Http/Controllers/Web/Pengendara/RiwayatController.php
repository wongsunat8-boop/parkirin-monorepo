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
        $query = $pengendara->transaksi()
            ->with(['tempatParkir', 'kendaraan', 'tarif']);

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
        if ($transaksi->kendaraan?->id_pengendara !== $pengendara->id_pengendara) abort(403);
        if ($transaksi->status_pembayaran === 'Sudah Bayar') {
            return back()->with('error', 'Transaksi sudah berstatus Sudah Bayar.');
        }

        $transaksi->update([
            'metode_pembayaran' => 'QRIS_Mitra',
            'status_pembayaran' => 'Sudah Bayar',
        ]);

        return back()->with('success', "Pembayaran transaksi #{$transaksi->id_transaksi} ({$transaksi->kode_karcis}) via QRIS resmi berhasil dikonfirmasi. Status: Sudah Bayar.");
    }

    public function struk(Request $request, Transaksi $transaksi): View
    {
        $pengendara = auth('pengendara')->user();
        if ($transaksi->kendaraan?->id_pengendara !== $pengendara->id_pengendara) abort(403);
        $transaksi->load(['tempatParkir', 'kendaraan', 'tarif', 'booking']);
        return view('pengendara.riwayat.struk', compact('transaksi'));
    }
}
