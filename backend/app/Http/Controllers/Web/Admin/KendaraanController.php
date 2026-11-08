<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\PengajuanBanned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KendaraanController extends Controller
{
    private function tenantId(): int { return (int) Auth::guard('web')->user()->id_tempat_parkir; }

    public function index(Request $request)
    {
        $kendaraans = Kendaraan::with([
                'pengendara:id_pengendara,nama_lengkap,username',
                'userInput:id_user,nama_lengkap,id_tempat_parkir',
            ])
            ->withCount(['transaksi' => fn($q) => $q->where('id_tempat_parkir', $this->tenantId())])
            ->where(function ($q) use ($request) {
                // Show vehicles relevant to this tenant: input by tenant users or having transaksi at tenant
                $q->whereHas('userInput', fn($u) => $u->where('id_tempat_parkir', $this->tenantId()))
                  ->orWhereHas('transaksi', fn($t) => $t->where('id_tempat_parkir', $this->tenantId()));
            })
            ->when($request->filled('q'), fn($q) => $q->where('plat_nomor','like','%'.$request->q.'%'))
            ->when($request->filled('jenis'), fn($q) => $q->where('jenis_kendaraan', $request->jenis))
            ->orderByDesc('id_kendaraan')
            ->paginate(20)->withQueryString();

        return view('admin.kendaraan.index', compact('kendaraans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plat_nomor' => 'required|string|max:15|unique:kendaraan,plat_nomor',
            'jenis_kendaraan' => ['required', Rule::in(['Motor','Mobil','Sepeda'])],
            'warna' => 'required|string|max:20',
        ]);
        Kendaraan::create($data + [
            'id_pengendara' => null,
            'id_user_input' => Auth::id(),
        ]);
        return back()->with('success', 'Kendaraan pelanggan offline berhasil ditambahkan.');
    }

    public function ajukanBanned(Request $request, Kendaraan $kendaraan)
    {
        $data = $request->validate(['alasan' => 'required|string|max:100']);
        PengajuanBanned::create([
            'id_tempat_parkir' => $this->tenantId(),
            'id_pengaju' => Auth::id(),
            'id_pengendara' => $kendaraan->id_pengendara,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'alasan' => $data['alasan'],
            'status_pengajuan' => 'Pending',
            'tanggal_pengajuan' => now(),
        ]);
        return back()->with('success', 'Pengajuan banned kendaraan dikirim ke Owner.');
    }
}
