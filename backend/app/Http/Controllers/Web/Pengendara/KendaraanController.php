<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    private function normalizePlat(string $plat): string
    {
        return Kendaraan::formatPlatNomor($plat);
    }

    /**
     * Store a vehicle, supporting Vehicle Auto-Reassign if unlinked or owned by another rider without active contracts.
     */
    public function store(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        
        $validated = $request->validate([
            'plat_nomor'      => ['required', 'string', 'max:15'],
            'jenis_kendaraan' => ['required', 'in:Motor,Mobil,Sepeda'],
            'warna'           => ['required', 'string', 'max:20'],
        ]);

        $plat = $this->normalizePlat($validated['plat_nomor']);
        $rawCleanPlat = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validated['plat_nomor']));

        // Find existing vehicle by formatted plate OR stripped plate
        $existing = Kendaraan::where('plat_nomor', $plat)
            ->orWhere('plat_nomor', $rawCleanPlat)
            ->first();

        if ($existing) {
            // Check if already owned by current pengendara
            if ($existing->id_pengendara === $pengendara->id_pengendara) {
                return back()->with('error', 'Kendaraan dengan plat tersebut sudah terdaftar di akun Anda.');
            }

            // If owned by another rider, check for active contracts
            if ($existing->id_pengendara) {
                $hasActiveParkir = Transaksi::where('id_kendaraan', $existing->id_kendaraan)
                    ->where('status', 'masuk')
                    ->exists();

                $hasActiveMember = LanggananMember::with('paket')
                    ->where('id_kendaraan', $existing->id_kendaraan)
                    ->where('status_langganan', 'Aktif')
                    ->where('status_pembayaran', 'Lunas')
                    ->get()
                    ->contains(function ($m) {
                        if (!$m->paket) return false;
                        return \Carbon\Carbon::parse($m->tanggal_mulai)->addDays($m->paket->durasi_hari)->gte(today());
                    });

                $hasActiveBooking = Booking::where('id_kendaraan', $existing->id_kendaraan)
                    ->whereIn('status', ['Terjadwal', 'Aktif_Inap'])
                    ->exists();

                if ($hasActiveParkir || $hasActiveMember || $hasActiveBooking) {
                    return back()->with('error', 'Plat nomor sedang terikat kontrak aktif (parkir, member, atau booking) pada akun lain. Kendaraan belum dapat dialihkan.');
                }
            }

            // Auto-Reassign: Transfer ownership to current pengendara
            $existing->update([
                'plat_nomor'      => $plat,
                'jenis_kendaraan' => $validated['jenis_kendaraan'],
                'warna'           => $validated['warna'],
                'id_pengendara'   => $pengendara->id_pengendara,
            ]);

            return back()->with('success', 'Kendaraan berhasil didaftarkan dan dialihkan ke akun Anda (Vehicle Auto-Reassign).');
        }

        // New vehicle creation
        Kendaraan::create([
            'plat_nomor'      => $plat,
            'jenis_kendaraan' => $validated['jenis_kendaraan'],
            'warna'           => $validated['warna'],
            'id_pengendara'   => $pengendara->id_pengendara,
        ]);

        return back()->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    /**
     * Update vehicle details (respecting Immutable History).
     */
    public function update(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($kendaraan->id_pengendara !== $pengendara->id_pengendara) {
            abort(403);
        }

        $locked = $kendaraan->hasTransactionHistory();
        $rules = ['warna' => ['required', 'string', 'max:20']];
        if (!$locked) {
            $rules['plat_nomor'] = ['required', 'string', 'max:15'];
            $rules['jenis_kendaraan'] = ['required', 'in:Motor,Mobil,Sepeda'];
        }
        $validated = $request->validate($rules);

        $data = ['warna' => $validated['warna']];
        if (!$locked) {
            $plat = $this->normalizePlat($validated['plat_nomor']);
            $conflict = Kendaraan::where('plat_nomor', $plat)
                ->where('id_kendaraan', '!=', $kendaraan->id_kendaraan)
                ->exists();
            if ($conflict) {
                return back()->with('error', 'Plat nomor sudah digunakan kendaraan lain.');
            }
            $data['plat_nomor'] = $plat;
            $data['jenis_kendaraan'] = $validated['jenis_kendaraan'];
        }

        $kendaraan->update($data);

        return back()->with('success', $locked
            ? 'Warna kendaraan diperbarui. Plat nomor & jenis dikunci karena memiliki riwayat transaksi (Immutable History).'
            : 'Kendaraan berhasil diperbarui.');
    }

    /**
     * Unlink or delete vehicle.
     */
    public function unlink(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($kendaraan->id_pengendara !== $pengendara->id_pengendara) {
            abort(403);
        }

        if ($kendaraan->hasTransactionHistory()) {
            $kendaraan->update(['id_pengendara' => null]);
            return back()->with('success', 'Kendaraan dilepas dari akun (riwayat transaksi tetap tersimpan).');
        }

        $kendaraan->delete();
        return back()->with('success', 'Kendaraan berhasil dihapus dari akun Anda.');
    }
}
