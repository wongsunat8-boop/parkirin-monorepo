<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanBanned;
use App\Models\Pengendara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengendaraController extends Controller
{
    private function tenantId(): int { return (int) Auth::guard('web')->user()->id_tempat_parkir; }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $pengendaras = Pengendara::query()
            ->select('pengendara.*')
            ->selectSub(function ($q) use ($tenantId) {
                $q->from('transaksi')->selectRaw('count(*)')
                  ->whereColumn('transaksi.id_pengendara', 'pengendara.id_pengendara')
                  ->where('transaksi.id_tempat_parkir', $tenantId);
            }, 'frekuensi_parkir')
            ->whereHas('transaksi', fn($q) => $q->where('id_tempat_parkir', $tenantId))
            ->with(['kendaraan:id_kendaraan,id_pengendara,plat_nomor,jenis_kendaraan'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(fn($qq) => $qq->where('nama_lengkap','like','%'.$request->q.'%')
                    ->orWhere('username','like','%'.$request->q.'%')
                    ->orWhere('email','like','%'.$request->q.'%'));
            })
            ->orderByDesc('frekuensi_parkir')
            ->paginate(20)->withQueryString();

        return view('admin.pengendara.index', compact('pengendaras'));
    }

    public function ajukanBanned(Request $request, Pengendara $pengendara)
    {
        // Ensure pengendara is related to this tenant via transaksi
        $tenantId = $this->tenantId();
        $related = $pengendara->transaksi()->where('id_tempat_parkir', $tenantId)->exists();
        abort_unless($related, 404);
        $data = $request->validate(['alasan' => 'required|string|max:100']);
        PengajuanBanned::create([
            'id_tempat_parkir' => $tenantId,
            'id_pengaju' => Auth::id(),
            'id_pengendara' => $pengendara->id_pengendara,
            'id_kendaraan' => null,
            'alasan' => $data['alasan'],
            'status_pengajuan' => 'Pending',
            'tanggal_pengajuan' => now(),
        ]);
        return back()->with('success', 'Pengajuan banned pengendara dikirim ke Owner.');
    }
}
