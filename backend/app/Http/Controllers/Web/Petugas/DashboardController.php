<?php

namespace App\Http\Controllers\Web\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;
        $tenant = TempatParkir::find($tenantId, ['id_tempat_parkir','nama_tempat','kapasitas','terisi','sediakan_booking','status_aktif']);

        $transaksiAktif = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara:id_pengendara,nama_lengkap',
                'tarif:id_tarif,jenis_kendaraan,tipe_tarif',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->orderByDesc('waktu_masuk')
            ->paginate(15);

        return view('petugas.dashboard', compact('tenant', 'transaksiAktif'));
    }
}
