<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\PengajuanBanned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanBannedController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index(Request $request)
    {
        $pengajuans = PengajuanBanned::with([
                'pengaju:id_user,nama_lengkap',
                'pengendara:id_pengendara,nama_lengkap,username,skor_reputasi',
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
            ])
            ->where('id_tempat_parkir', $this->tenantId())
            ->when($request->filled('status'), fn($q) => $q->where('status_pengajuan', $request->status))
            ->orderByRaw("FIELD(status_pengajuan,'Pending','Disetujui','Ditolak','Dicabut')")
            ->orderByDesc('id_banned')
            ->paginate(15)
            ->withQueryString();

        return view('owner.pengajuan_banned.index', compact('pengajuans'));
    }

    public function approve(PengajuanBanned $pengajuan)
    {
        abort_unless($pengajuan->id_tempat_parkir === $this->tenantId(), 404);
        DB::transaction(function () use ($pengajuan) {
            $pengajuan->update(['status_pengajuan' => 'Disetujui']);
            if ($pengajuan->id_pengendara && $pengajuan->pengendara) {
                $pengendara = $pengajuan->pengendara()->lockForUpdate()->first();
                if ($pengendara) {
                    $pengendara->update(['skor_reputasi' => max(0, (int)$pengendara->skor_reputasi - 10)]);
                }
            }
            LogAktivitas::catat(Auth::id(), 'Menyetujui pengajuan banned #'.$pengajuan->id_banned, $this->tenantId());
        });
        return back()->with('success', 'Pengajuan banned disetujui dan skor reputasi pengendara dikurangi.');
    }

    public function reject(PengajuanBanned $pengajuan)
    {
        abort_unless($pengajuan->id_tempat_parkir === $this->tenantId(), 404);
        $pengajuan->update(['status_pengajuan' => 'Ditolak']);
        LogAktivitas::catat(Auth::id(), 'Menolak pengajuan banned #'.$pengajuan->id_banned, $this->tenantId());
        return back()->with('success', 'Pengajuan banned ditolak.');
    }

    public function revoke(PengajuanBanned $pengajuan)
    {
        abort_unless($pengajuan->id_tempat_parkir === $this->tenantId(), 404);
        $pengajuan->update(['status_pengajuan' => 'Dicabut']);
        LogAktivitas::catat(Auth::id(), 'Mencabut pengajuan banned #'.$pengajuan->id_banned, $this->tenantId());
        return back()->with('success', 'Pengajuan banned dicabut.');
    }
}
