<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\PaketLangganan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaketLanggananController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        $pakets = PaketLangganan::query()
            ->where('id_tempat_parkir', $tenantId)
            ->when($request->filled('q'), fn($q) => $q->where('nama_paket', 'like', '%'.$request->q.'%'))
            ->when($request->filled('jenis'), fn($q) => $q->where('jenis_kendaraan', $request->jenis))
            ->when($request->filled('status'), fn($q) => $q->where('status_aktif', $request->status))
            ->orderByDesc('id_paket')
            ->paginate(10)
            ->withQueryString();

        return view('admin.paket_langganan.index', compact('pakets'));
    }

    public function create()
    {
        return view('admin.paket_langganan.form', ['paket' => new PaketLangganan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request);
        $data['id_tempat_parkir'] = $this->tenantId();
        $data['status_aktif'] = $request->boolean('status_aktif', true) ? 1 : 0;

        // Cegah race condition duplikasi paket yang sama persis dalam 5 detik terakhir oleh user yang sama
        $recentLog = LogAktivitas::where('id_tempat_parkir', $data['id_tempat_parkir'])
            ->where('id_user', Auth::id())
            ->where('aktivitas', 'like', "Tambah Paket Langganan [{$data['nama_paket']}]%")
            ->where('waktu_aktivitas', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentLog) {
            return redirect()->route('admin.paket-langganan.index')->with('success', 'Paket langganan berhasil ditambahkan.');
        }

        $paket = PaketLangganan::create($data);

        LogAktivitas::create([
            'id_tempat_parkir' => $data['id_tempat_parkir'],
            'id_user'          => Auth::id(),
            'aktivitas'        => "Tambah Paket Langganan [{$paket->nama_paket}] - Rp {$paket->harga_paket}",
            'waktu_aktivitas'  => now(),
        ]);

        return redirect()->route('admin.paket-langganan.index')->with('success', 'Paket langganan berhasil ditambahkan.');
    }

    public function edit(PaketLangganan $paketLangganan)
    {
        abort_unless((int) $paketLangganan->id_tempat_parkir === $this->tenantId(), 403, 'Akses ditolak.');
        return view('admin.paket_langganan.form', ['paket' => $paketLangganan]);
    }

    public function update(Request $request, PaketLangganan $paketLangganan)
    {
        abort_unless((int) $paketLangganan->id_tempat_parkir === $this->tenantId(), 403, 'Akses ditolak.');

        $data = $this->validateInput($request);
        $data['status_aktif'] = $request->boolean('status_aktif') ? 1 : 0;

        $paketLangganan->update($data);

        LogAktivitas::create([
            'id_tempat_parkir' => $this->tenantId(),
            'id_user'          => Auth::id(),
            'aktivitas'        => "Perbarui Paket Langganan [{$paketLangganan->nama_paket}]",
            'waktu_aktivitas'  => now(),
        ]);

        return redirect()->route('admin.paket-langganan.index')->with('success', 'Paket langganan berhasil diperbarui.');
    }

    public function toggle(PaketLangganan $paketLangganan)
    {
        abort_unless((int) $paketLangganan->id_tempat_parkir === $this->tenantId(), 403, 'Akses ditolak.');

        $paketLangganan->update(['status_aktif' => $paketLangganan->status_aktif ? 0 : 1]);

        return back()->with('success', 'Status keaktifan paket berhasil diubah.');
    }

    public function destroy(PaketLangganan $paketLangganan)
    {
        abort_unless((int) $paketLangganan->id_tempat_parkir === $this->tenantId(), 403, 'Akses ditolak.');

        if ($paketLangganan->langgananMember()->exists()) {
            return back()->with('error', 'Paket tidak dapat dihapus karena sudah memiliki riwayat pendaftaran member.');
        }

        $paketLangganan->delete();

        return redirect()->route('admin.paket-langganan.index')->with('success', 'Paket langganan berhasil dihapus.');
    }

    private function validateInput(Request $request): array
    {
        if ($request->has('harga_paket')) {
            $cleaned = str_replace('.', '', (string) $request->input('harga_paket'));
            $request->merge(['harga_paket' => $cleaned === '' ? 0 : $cleaned]);
        }

        return $request->validate([
            'nama_paket' => 'required|string|max:50',
            'jenis_kendaraan' => ['required', Rule::in(['Motor', 'Mobil', 'Sepeda'])],
            'durasi_hari' => 'required|integer|min:1|max:365',
            'batas_masuk_harian' => 'nullable|integer|min:1|max:50',
            'harga_paket' => 'required|numeric|min:0',
        ], [
            'batas_masuk_harian.min' => 'Batas masuk harian minimal 1 kali per hari, atau kosongkan jika tanpa batas (unlimited).',
        ]);
    }
}
