<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    private function baseQuery(Request $request)
    {
        return Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara:id_pengendara,nama_lengkap',
                'petugas:id_user,nama_lengkap',
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
        $base = $this->baseQuery($request);
        return [
            'total' => (clone $base)->count(),
            'pendapatan' => (clone $base)->where('status_pembayaran','Sudah Bayar')->sum('biaya_total'),
            'belum_bayar' => (clone $base)->where('status_pembayaran','Belum Bayar')->sum('biaya_total'),
        ];
    }

    public function index(Request $request)
    {
        $transaksis = $this->baseQuery($request)
            ->orderByDesc('id_transaksi')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->summary($request);

        return view('owner.transaksi.index', compact('transaksis', 'summary'));
    }

    public function print(Request $request)
    {
        $user = Auth::guard('web')->user();
        $tenant = $user?->tempatParkir;
        $transaksis = $this->baseQuery($request)->orderBy('id_transaksi')->get();
        $summary = $this->summary($request);
        $filters = [
            'dari' => $request->input('dari'),
            'sampai' => $request->input('sampai'),
            'status' => $request->input('status'),
            'pembayaran' => $request->input('pembayaran'),
        ];
        $autoPrint = $request->boolean('auto', true);

        return view('owner.transaksi.print', compact('transaksis', 'summary', 'filters', 'tenant', 'autoPrint'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'rekap-transaksi-'.now()->format('Ymd-His').'.csv';
        $query = $this->baseQuery($request)->orderBy('id_transaksi');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Plat Nomor','Jenis','Pengendara','Petugas','Waktu Masuk','Waktu Keluar','Durasi (jam)','Biaya','Pembayaran','Status']);
            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->id_transaksi,
                        $t->kendaraan->plat_nomor ?? '-',
                        $t->kendaraan->jenis_kendaraan ?? '-',
                        $t->pengendara->nama_lengkap ?? 'Offline',
                        $t->petugas->nama_lengkap ?? '-',
                        optional($t->waktu_masuk)->format('Y-m-d H:i'),
                        optional($t->waktu_keluar)->format('Y-m-d H:i'),
                        $t->durasi_jam,
                        $t->biaya_total,
                        $t->status_pembayaran,
                        $t->status,
                    ]);
                }
            }, 'id_transaksi');
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
