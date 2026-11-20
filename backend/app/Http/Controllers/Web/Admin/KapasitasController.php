<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\TempatParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KapasitasController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index()
    {
        $tenant = TempatParkir::findOrFail($this->tenantId());
        return view('admin.kapasitas.index', compact('tenant'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId();

        $data = $request->validate([
            'kapasitas' => 'required|integer|min:1',
            'kuota_inap' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($tenantId, $data) {
            $tenant = TempatParkir::where('id_tempat_parkir', $tenantId)->lockForUpdate()->firstOrFail();

            if ($tenant->kapasitas_reguler > 0) {
                return;
            }

            $tenant->update([
                'kapasitas_reguler' => $data['kapasitas'],
                'kuota_inap'        => $data['kuota_inap'] ?? 0,
            ]);

            LogAktivitas::create([
                'id_tempat_parkir' => $tenantId,
                'id_user'          => Auth::id(),
                'aktivitas'        => "Inisialisasi Kapasitas Awal Tenant: Reguler {$data['kapasitas']} slot, Inap " . ($data['kuota_inap'] ?? 0) . " slot",
                'waktu_aktivitas'  => now(),
            ]);
        });

        return back()->with('success', 'Kapasitas awal berhasil ditetapkan.');
    }

    public function update(Request $request)
    {
        $tenantId = $this->tenantId();

        $data = $request->validate([
            'kapasitas'  => 'required|integer|min:1',
            'kuota_inap' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($tenantId, $data) {
            $tenant = TempatParkir::where('id_tempat_parkir', $tenantId)->lockForUpdate()->firstOrFail();

            $tenant->update([
                'kapasitas_reguler' => $data['kapasitas'],
                'kuota_inap'        => $data['kuota_inap'],
            ]);

            LogAktivitas::create([
                'id_tempat_parkir' => $tenantId,
                'id_user'          => Auth::id(),
                'aktivitas'        => "Perbarui Kapasitas Tenant: Reguler {$data['kapasitas']} slot, Inap {$data['kuota_inap']} slot",
                'waktu_aktivitas'  => now(),
            ]);
        });

        return back()->with('success', 'Kapasitas dan kuota inap berhasil diperbarui.');
    }

    public function toggleBooking()
    {
        $tenantId = $this->tenantId();

        DB::transaction(function () use ($tenantId) {
            $tenant = TempatParkir::where('id_tempat_parkir', $tenantId)->lockForUpdate()->firstOrFail();
            $newStatus = $tenant->sediakan_booking_inap ? 0 : 1;
            $tenant->update(['sediakan_booking_inap' => $newStatus]);

            LogAktivitas::create([
                'id_tempat_parkir' => $tenantId,
                'id_user'          => Auth::id(),
                'aktivitas'        => 'Ubah Status Layanan Booking Inap menjadi: ' . ($newStatus ? 'Aktif' : 'Nonaktif'),
                'waktu_aktivitas'  => now(),
            ]);
        });

        return back()->with('success', 'Pengaturan layanan booking inap diperbarui.');
    }

    public function toggleMember()
    {
        $tenantId = $this->tenantId();

        DB::transaction(function () use ($tenantId) {
            $tenant = TempatParkir::where('id_tempat_parkir', $tenantId)->lockForUpdate()->firstOrFail();
            $newStatus = $tenant->sediakan_member ? 0 : 1;
            $tenant->update(['sediakan_member' => $newStatus]);

            LogAktivitas::create([
                'id_tempat_parkir' => $tenantId,
                'id_user'          => Auth::id(),
                'aktivitas'        => 'Ubah Status Layanan Member Pass menjadi: ' . ($newStatus ? 'Aktif' : 'Nonaktif'),
                'waktu_aktivitas'  => now(),
            ]);
        });

        return back()->with('success', 'Pengaturan layanan paket member diperbarui.');
    }

    public function updateQris(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'qris_image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $path = $request->file('qris_image')->store('qris', 'public');
        $storedPath = 'storage/' . $path;

        $tenant = TempatParkir::findOrFail($tenantId);
        $tenant->update(['qris_image' => $storedPath]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenantId,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Admin memperbarui Gambar QRIS Resmi Pembayaran Online Tenant",
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('success', 'Gambar QRIS resmi tenant berhasil diperbarui.');
    }
}
