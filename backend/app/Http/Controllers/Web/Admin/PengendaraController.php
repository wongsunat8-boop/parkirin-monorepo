<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengendara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengendaraController extends Controller
{
    private function tenantId(): int 
    { 
        return (int) Auth::guard('web')->user()->id_tempat_parkir; 
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $pengendaras = Pengendara::query()
            ->select('pengendara.*')
            ->selectSub(function ($q) use ($tenantId) {
                $q->from('transaksi')
                  ->join('kendaraan', 'kendaraan.id_kendaraan', '=', 'transaksi.id_kendaraan')
                  ->selectRaw('count(*)')
                  ->whereColumn('kendaraan.id_pengendara', 'pengendara.id_pengendara')
                  ->where('transaksi.id_tempat_parkir', $tenantId);
            }, 'frekuensi_parkir')
            ->whereHas('transaksi', fn($q) => $q->where('transaksi.id_tempat_parkir', $tenantId))
            ->with(['kendaraan:id_kendaraan,id_pengendara,plat_nomor,jenis_kendaraan'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(fn($qq) => $qq->where('nama_lengkap','like','%'.$request->q.'%')
                    ->orWhere('username','like','%'.$request->q.'%')
                    ->orWhere('email','like','%'.$request->q.'%'));
            })
            ->orderByDesc('frekuensi_parkir')
            ->paginate(10)->withQueryString();

        return view('admin.pengendara.index', compact('pengendaras'));
    }
}
